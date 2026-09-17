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

        $fechaInicio = Carbon::parse($datos['fecha_inicio']);

        $fechaFinal = Carbon::parse($datos['fecha_final']);


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

            /*
            |--------------------------------------------------------------------------
            | FECHA DE PAGO
            |--------------------------------------------------------------------------
            */

            if ($i == 1) {

                $fechaPago = $fechaInicio->copy();

            } else {

                $fechaPago = $fechaInicio
                    ->copy()
                    ->addMonths($i - 1)
                    ->endOfMonth();
            }


            /*
            |--------------------------------------------------------------------------
            | INTERÉS
            |--------------------------------------------------------------------------
            */

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
        | GENERAR SCRIPT SQL SERVER
        |--------------------------------------------------------------------------
        */

        $sql = "USE SIFCO_SSU;\n";
        $sql .= "GO\n\n";

        $sql .= "UPDATE P\n";
        $sql .= "SET\n";
        $sql .= "    P.PlaPFecPago = V.FechaPago,\n";
        $sql .= "    P.PlaPMonto   = V.Monto\n";
        $sql .= "FROM SIFCO.CrPlanPagos P\n";
        $sql .= "INNER JOIN\n";
        $sql .= "(\n";
        $sql .= "    VALUES\n";


        $filasSql = [];


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
                . "),";


            /*
            |--------------------------------------------------------------------------
            | SALCOD 51 - INTERÉS
            |--------------------------------------------------------------------------
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
                . "),";
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
        $sql .= ") V(PreNumero,Cuota,SalCod,FechaPago,Monto)\n";

        $sql .= "    ON P.PreNumero = V.PreNumero\n";

        $sql .= "   AND P.PlaPNuCuota = V.Cuota\n";

        $sql .= "   AND P.PlaPSalCod = V.SalCod;\n";

        $sql .= "GO";


        /*
        |--------------------------------------------------------------------------
        | DEVOLVER VISTA
        |--------------------------------------------------------------------------
        */

        return view('prestamos', [

            'datos' => $datos,

            'numeroPrestamo' => $numeroPrestamo,

            'tasaAnual' => $tasaAnual,

            'numeroCuotas' => $numeroCuotas,

            'cuota' => $cuota,

            'balanceInicial' => $balanceInicial,

            'saldoCapital' => $datos['saldo_capital'] ?? 0,

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