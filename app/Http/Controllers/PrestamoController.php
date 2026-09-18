<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class PrestamoController extends Controller
{
    /**
     * Pantalla principal
     */
    public function index()
    {
        return view('prestamos');
    }

    /**
     * Calcular amortización
     */
    public function calcular(Request $request)
    {
        $datos = $request->validate([
            'numero_prestamo'    => 'required|string',
            'tasa'               => 'required|numeric|min:0',
            'fecha_inicio'       => 'required|date',
            'fecha_final'        => 'required|date|after_or_equal:fecha_inicio',
            'balance_inicial'    => 'required|numeric|min:0',
            'saldo_capital'      => 'nullable|numeric',
            'interes_ordinario'  => 'nullable|numeric',
            'interes_moratorio'  => 'nullable|numeric',
        ]);

        /*
        |--------------------------------------------------------------------------
        | DATOS
        |--------------------------------------------------------------------------
        */

        $numeroPrestamo = $datos['numero_prestamo'];

        $tasaAnual = (float) $datos['tasa'];

        $balanceInicial = (float) $datos['balance_inicial'];

        $saldoCapital = (float) ($datos['saldo_capital'] ?? 0);

        $fechaInicio = Carbon::parse($datos['fecha_inicio']);

        $fechaFinal = Carbon::parse($datos['fecha_final']);


        /*
        |--------------------------------------------------------------------------
        | VALOR PRIMERA CUOTA
        |--------------------------------------------------------------------------
        |
        | Fórmula:
        |
        | Balance Inicial - Saldo Capital
        |
        */

        $valorPrimeraCuota = $balanceInicial - $saldoCapital;


        /*
        |--------------------------------------------------------------------------
        | NÚMERO DE CUOTAS
        |--------------------------------------------------------------------------
        */

        $numeroCuotas =
            (($fechaFinal->year - $fechaInicio->year) * 12)
            + ($fechaFinal->month - $fechaInicio->month)
            + 1;


        /*
        |--------------------------------------------------------------------------
        | TASA MENSUAL
        |--------------------------------------------------------------------------
        */

        $tasaMensual = ($tasaAnual / 100) / 12;


        /*
        |--------------------------------------------------------------------------
        | CUOTA MENSUAL
        |--------------------------------------------------------------------------
        */

        if ($tasaMensual > 0) {

            $cuota = $balanceInicial
                * (
                    $tasaMensual
                    /
                    (1 - pow(1 + $tasaMensual, -$numeroCuotas))
                );

        } else {

            $cuota = $balanceInicial / $numeroCuotas;
        }


        /*
        |--------------------------------------------------------------------------
        | TABLA DE AMORTIZACIÓN
        |--------------------------------------------------------------------------
        */

        $amortizacion = [];

        $saldo = $balanceInicial;


       for ($i = 1; $i <= $numeroCuotas; $i++) {

    if ($i == 1) {

        // La primera cuota conserva exactamente
        // la fecha de inicio del préstamo.
        $fechaPago = $fechaInicio->copy();

    } else {

        // Las cuotas siguientes siempre se llevan
        // al último día del mes correspondiente.
        //
        // Se usa startOfMonth() ANTES de addMonths()
        // para evitar el problema de fechas como 31 de enero.
        $fechaPago = $fechaInicio
            ->copy()
            ->startOfMonth()
            ->addMonths($i - 1)
            ->endOfMonth();

    }

    $interes = $saldo * $tasaMensual;

            /*
            |--------------------------------------------------------------------------
            | CAPITAL
            |--------------------------------------------------------------------------
            */

            $capital = $cuota - $interes;


            /*
            |--------------------------------------------------------------------------
            | ÚLTIMA CUOTA
            |--------------------------------------------------------------------------
            */

            if ($i == $numeroCuotas) {

                $capital = $saldo;

                $total = $capital + $interes;

            } else {

                $total = $cuota;
            }


            /*
            |--------------------------------------------------------------------------
            | NUEVO SALDO
            |--------------------------------------------------------------------------
            */

            $nuevoSaldo = $saldo - $capital;


            if (abs($nuevoSaldo) < 0.01) {

                $nuevoSaldo = 0;
            }


            /*
            |--------------------------------------------------------------------------
            | GUARDAR CUOTA
            |--------------------------------------------------------------------------
            */

            $amortizacion[] = [

                'cuota' => $i,

                'fecha' => $fechaPago->format('d/m/Y'),

                'fecha_sql' => $fechaPago->format('Y-m-d'),

                'capital' => round($capital, 2),

                'interes' => round($interes, 2),

                'total' => round($total, 2),

                'saldo' => round($nuevoSaldo, 2),

            ];


            $saldo = $nuevoSaldo;
        }


        /*
        |--------------------------------------------------------------------------
        | TOTALES
        |--------------------------------------------------------------------------
        */

        $totalCapital = collect($amortizacion)
            ->sum('capital');

        $totalInteres = collect($amortizacion)
            ->sum('interes');

        $totalPagos = collect($amortizacion)
            ->sum('total');


        /*
        |--------------------------------------------------------------------------
        | SUMA DE INTERÉS
        |--------------------------------------------------------------------------
        |
        | Se suman los intereses (SalCod 51)
        | desde la FECHA DE INICIO del préstamo
        | hasta el 01/07/2026, ambas fechas incluidas.
        |
        */

        $fechaCorteInteres = Carbon::create(2026, 7, 1);

        $sumaInteres = collect($amortizacion)
            ->filter(function ($fila) use ($fechaInicio, $fechaCorteInteres) {

                $fechaPago = Carbon::createFromFormat(
                    'd/m/Y',
                    $fila['fecha']
                );

                return $fechaPago->greaterThanOrEqualTo($fechaInicio)
                    && $fechaPago->lessThanOrEqualTo($fechaCorteInteres);
            })
            ->sum('interes');

        $sumaInteres = round($sumaInteres, 2);



/*
        |--------------------------------------------------------------------------
        | VALOR 82
        |--------------------------------------------------------------------------
        |
        | Si el Interés Ordinario ingresado es MAYOR que la
        | Suma Interés Vencidos:
        |
        |     Valor 82 = Interés Ordinario - Suma Interés Vencidos
        |
        | Si es menor o igual:
        |
        |     Valor 82 = 0.00
        |
        | El SalCod 82 SIEMPRE se registra en la primera cuota
        | y con la fecha de la primera cuota.
        |
        */

        $interesOrdinarioIngresado = (float) (
            $datos['interes_ordinario'] ?? 0
        );

        if ($interesOrdinarioIngresado > $sumaInteres) {

            $valor82 = round(
                $interesOrdinarioIngresado - $sumaInteres,
                2
            );

        } else {

            $valor82 = 0.00;
        }

        // Fecha de la primera cuota para SalCod 82
        $fechaPrimeraCuota82 = $amortizacion[0]['fecha_sql'];


        /*
        |--------------------------------------------------------------------------
        | VALOR TOTAL 51
        |--------------------------------------------------------------------------
        |
        | Si el Interés Ordinario ingresado es MENOR que la
        | Suma Interés Vencidos:
        |
        |     Valor Total 51 = Suma Interés Vencidos
        |                        - Interés Ordinario
        |
        | Si es mayor o igual:
        |
        |     Valor Total 51 = 0.00
        |
        */

        if ($interesOrdinarioIngresado < $sumaInteres) {

            $valorTotal51 = round(
                $sumaInteres - $interesOrdinarioIngresado,
                2
            );

        } else {

            $valorTotal51 = 0.00;
        }

        /*
        |--------------------------------------------------------------------------
        | GENERAR SCRIPT SQL SERVER
        |--------------------------------------------------------------------------
        */

       /*
|--------------------------------------------------------------------------
| SQL PRINCIPAL
|--------------------------------------------------------------------------
| Primero se reinician los valores pagados del préstamo.
| Luego se aplican los nuevos valores de amortización.
|--------------------------------------------------------------------------
*/

$sql = "USE SIFCO_SSU;\n";
$sql .= "GO\n\n";

/*
|--------------------------------------------------------------------------
| REINICIAR PlaPVaPag
|--------------------------------------------------------------------------
*/

$sql .= "UPDATE SIFCO.CrPlanPagos\n";
$sql .= "SET\n";
$sql .= "    PlaPVaPag = 0\n";
$sql .= "WHERE PreNumero = '"
    . $numeroPrestamo
    . "';\n";
$sql .= "GO\n\n";

/*
|--------------------------------------------------------------------------
| ACTUALIZAR PLAN DE PAGOS
|--------------------------------------------------------------------------
*/

$sql .= "UPDATE P\n";
$sql .= "SET\n";
$sql .= "    P.PlaPFecPago = V.FechaPago,\n";
$sql .= "    P.PlaPMonto   = V.Monto,\n";
$sql .= "    P.PlaPVaPag   = CASE\n";
$sql .= "        WHEN V.Cuota = 1 AND V.SalCod = 50\n";
$sql .= "        THEN V.ValorPrimeraCuota\n";
$sql .= "        WHEN V.SalCod = 51\n";
$sql .= "        THEN V.ValorPrimeraCuota\n";
$sql .= "        ELSE P.PlaPVaPag\n";
$sql .= "    END\n";
$sql .= "FROM SIFCO.CrPlanPagos P\n";
$sql .= "INNER JOIN\n";
$sql .= "(\n";
$sql .= "    VALUES\n";

        $sql .= "    P.PlaPFecPago = V.FechaPago,\n";

        $sql .= "    P.PlaPMonto   = V.Monto,\n";

        $sql .= "    P.PlaPVaPag   = CASE\n";

        $sql .= "        WHEN V.Cuota = 1 AND V.SalCod = 50\n";

        $sql .= "        THEN V.ValorPrimeraCuota\n";

        $sql .= "        WHEN V.SalCod = 51\n";

        $sql .= "        THEN V.ValorPrimeraCuota\n";

        $sql .= "        ELSE P.PlaPVaPag\n";

        $sql .= "    END\n";


        $sql .= "FROM SIFCO.CrPlanPagos P\n";

        $sql .= "INNER JOIN\n";

        $sql .= "(\n";

        $sql .= "    VALUES\n";


        $filasSql = [];

        // Saldo restante del Valor Total 51 que se irá aplicando
        // contra las cuotas SalCod 51 hasta el 01/07/2026.
        $valorTotal51Restante = $valorTotal51;


        foreach ($amortizacion as $fila) {

            $numeroCuota = $fila['cuota'];

            $fechaSql = $fila['fecha_sql'];

            $capital = number_format(
                $fila['capital'],
                2,
                '.',
                ''
            );

            $interes = number_format(
                $fila['interes'],
                2,
                '.',
                ''
            );

            /*
            |--------------------------------------------------------------------------
            | VALOR TOTAL 51 APLICADO
            |--------------------------------------------------------------------------
            |
            | El Valor Total 51 se va consumiendo contra los intereses
            | SalCod 51, comenzando por la primera cuota y avanzando
            | solamente hasta el 01/07/2026.
            |
            | El valor aplicado nunca puede ser mayor que:
            |   1. El saldo restante del Valor Total 51
            |   2. El interés de la cuota
            |
            */

            $valor51AplicadoSql = '0.00';

            $fechaPagoActual = Carbon::createFromFormat(
                'Y-m-d',
                $fechaSql
            );

            if (
                $valorTotal51Restante > 0
                && $fechaPagoActual->lessThanOrEqualTo($fechaCorteInteres)
            ) {

                $valor51Aplicado = min(
                    $valorTotal51Restante,
                    $fila['interes']
                );

                $valor51Aplicado = round(
                    $valor51Aplicado,
                    2
                );

                $valor51AplicadoSql = number_format(
                    $valor51Aplicado,
                    2,
                    '.',
                    ''
                );

                $valorTotal51Restante = round(
                    $valorTotal51Restante - $valor51Aplicado,
                    2
                );
            }


            /*
            |--------------------------------------------------------------------------
            | VALOR PRIMERA CUOTA
            |--------------------------------------------------------------------------
            |
            | Solo se coloca el valor calculado en:
            |
            | Cuota 1
            | SalCod 50
            |
            | Las demás cuotas llevan 0.00
            |
            */

            if ($numeroCuota == 1) {

                $valorPrimeraCuotaSql = number_format(
                    $valorPrimeraCuota,
                    2,
                    '.',
                    ''
                );

            } else {

                $valorPrimeraCuotaSql = '0.00';
            }


            /*
            |--------------------------------------------------------------------------
            | SALCOD 50 - CAPITAL
            |--------------------------------------------------------------------------
            */

            $filasSql[] =
                "    ('"
                . $numeroPrestamo
                . "',"
                . $numeroCuota
                . ",50,'"
                . $fechaSql
                . "',"
                . $capital
                . ","
                . $valorPrimeraCuotaSql
                . "),";


            /*
            |--------------------------------------------------------------------------
            | SALCOD 51 - INTERÉS
            |--------------------------------------------------------------------------
            |
            | El interés continúa actualizándose en PlaPMonto.
            |
            | ValorPrimeraCuota = 0.00
            |
            */

            $filasSql[] =
                "    ('"
                . $numeroPrestamo
                . "',"
                . $numeroCuota
                . ",51,'"
                . $fechaSql
                . "',"
                . $interes
                . ","
                . $valor51AplicadoSql
                . "),";
        }


        /*
        |--------------------------------------------------------------------------
        | SALCOD 82 - VALOR 82
        |--------------------------------------------------------------------------
        |
        | Se agrega una fila SalCod 82 solamente cuando
        | el Valor 82 sea mayor que 0.00.
        |
        | Cuota = 1
        | Fecha = fecha de la primera cuota
        | Monto = Valor 82
        |
        */

        if ($valor82 > 0) {

            $valor82Sql = number_format(
                $valor82,
                2,
                '.',
                ''
            );

            $filasSql[] =
                "    ('"
                . $numeroPrestamo
                . "',1,82,'"
                . $fechaPrimeraCuota82
                . "',"
                . $valor82Sql
                . ",0.00),";
        }


        /*
        |--------------------------------------------------------------------------
        | QUITAR COMA DE LA ÚLTIMA FILA
        |--------------------------------------------------------------------------
        */

        $ultimo = array_pop($filasSql);

        $sql .= implode("\n", $filasSql);

        $sql .= "\n" . rtrim($ultimo, ",");


        $sql .= "\n";

        $sql .= ") V(PreNumero,Cuota,SalCod,FechaPago,Monto,ValorPrimeraCuota)\n";

        $sql .= "    ON P.PreNumero = V.PreNumero\n";

        $sql .= "   AND P.PlaPNuCuota = V.Cuota\n";

        $sql .= "   AND P.PlaPSalCod = V.SalCod;\n";

        $sql .= "GO";

         /*
        |--------------------------------------------------------------------------
        | ELIMINAR CUOTAS SOBRANTES
        |--------------------------------------------------------------------------
        |
        | Se eliminan las cuotas que sean mayores al número de cuotas
        | calculado por el proyecto.
        |
        */

        $sql .= "\n\n";
        $sql .= "USE SIFCO_SSU;\n";
        $sql .= "GO\n\n";
        $sql .= "DELETE FROM SIFCO.CrPlanPagos\n";
        $sql .= "WHERE PreNumero = '"
            . $numeroPrestamo
            . "'\n";
        $sql .= "  AND PlaPNuCuota > "
            . $numeroCuotas
            . ";\n";
        $sql .= "GO";


        /*
|--------------------------------------------------------------------------
| ACTUALIZAR ESTADO DE CUOTAS Y MORA
|--------------------------------------------------------------------------
*/

$sql .= "\n\n";

$sql .= "UPDATE SIFCO.CrPlanPagos\n";
$sql .= "SET PlaPAtrasada = 'S'\n";
$sql .= "WHERE PreNumero = '"
    . $numeroPrestamo
    . "' AND PlaPFecPago < '2026-07-30';\n";
$sql .= "GO\n\n";

$sql .= "UPDATE SIFCO.CrPlanPagos\n";
$sql .= "SET PlaPAtrasada = 'N'\n";
$sql .= "WHERE PreNumero = '"
    . $numeroPrestamo
    . "' AND PlaPFecPago > '2026-06-30';\n";
$sql .= "GO\n\n";

$sql .= "UPDATE SIFCO.CrPlanPagos\n";
$sql .= "SET PlaPMoraMonto = 0, "
    . "PlaPMoraValorPagado = 0, "
    . "PlaPMoraDias = 0, "
    . "PlaPMoraPagado = 0\n";
$sql .= "WHERE PreNumero = '"
    . $numeroPrestamo
    . "';\n";
$sql .= "GO";





        /*
        |--------------------------------------------------------------------------
        | MOSTRAR RESULTADO
        |--------------------------------------------------------------------------
        */

        return view('prestamos', [

            'datos' => $datos,

            'numeroPrestamo' => $numeroPrestamo,

            'tasaAnual' => $tasaAnual,

            'numeroCuotas' => $numeroCuotas,

            'cuota' => $cuota,

            'balanceInicial' => $balanceInicial,

            'saldoCapital' => $saldoCapital,

            'valorPrimeraCuota' => round($valorPrimeraCuota, 2),

            'sumaInteres' => $sumaInteres,

            'valor82' => $valor82,

            'valorTotal51' => $valorTotal51,

            'interesOrd' => $datos['interes_ordinario'] ?? 0,

            'interesMora' => $datos['interes_moratorio'] ?? 0,

            'amortizacion' => $amortizacion,

            'totalCapital' => round($totalCapital, 2),

            'totalInteres' => round($totalInteres, 2),

            'totalPagos' => round($totalPagos, 2),

            'sql' => $sql,

        ]);
    }
}