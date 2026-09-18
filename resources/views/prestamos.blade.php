<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Gestión de Préstamos</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #080d19;

            color: #e5e7eb;

            min-height: 100vh;
        }


        .container {

            width: 100%;

            max-width: 1250px;

            margin: 35px auto;

            padding: 0 20px;
        }


        /* HEADER */

        .header {

            background: #111827;

            border: 1px solid #243044;

            border-radius: 14px;

            padding: 28px 30px;

            margin-bottom: 25px;

            box-shadow:
                0 8px 25px rgba(0, 0, 0, 0.35);
        }


        .header h1 {

            margin: 0;

            font-size: 30px;

            color: #60a5fa;
        }


        .header p {

            margin: 8px 0 0;

            color: #9ca3af;

            font-size: 15px;
        }


        /* CARD */

        .card {

            background: #111827;

            border: 1px solid #243044;

            border-radius: 14px;

            padding: 30px;

            box-shadow:
                0 8px 25px rgba(0, 0, 0, 0.35);

            margin-bottom: 25px;
        }


        .section-title {

            margin-top: 0;

            margin-bottom: 25px;

            font-size: 20px;

            color: #60a5fa;

            border-bottom:
                1px solid #263244;

            padding-bottom: 12px;
        }


        /* FORM */

        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;
        }


        .form-group {

            display: flex;

            flex-direction: column;
        }


        label {

            margin-bottom: 7px;

            font-weight: 600;

            color: #d1d5db;
        }


        input {

            width: 100%;

            padding: 12px 14px;

            background: #1f2937;

            color: #f9fafb;

            border:
                1px solid #374151;

            border-radius: 8px;

            font-size: 15px;

            outline: none;

            transition:
                border-color .2s,
                box-shadow .2s;
        }


        input::placeholder {

            color: #6b7280;
        }


        input:focus {

            border-color: #3b82f6;

            box-shadow:
                0 0 0 3px
                rgba(59, 130, 246, .15);
        }


        /* BOTONES */

        .actions {

            margin-top: 30px;

            display: flex;

            justify-content: flex-end;

            gap: 12px;
        }


        button {

            border: none;

            border-radius: 8px;

            padding: 12px 22px;

            font-size: 15px;

            font-weight: 600;

            cursor: pointer;

            transition: .2s;
        }


        .btn-clear {

            background: #374151;

            color: #e5e7eb;
        }


        .btn-clear:hover {

            background: #4b5563;
        }


        .btn-search {

            background: #2563eb;

            color: white;
        }


        .btn-search:hover {

            background: #1d4ed8;
        }


        /* INFORMACIÓN */

        .summary-grid {

    display: grid;

    grid-template-columns:
        repeat(7, minmax(0, 1fr));

    gap: 15px;

    margin-bottom: 25px;

    width: 100%;
}


       .summary-box {

    background: #0f172a;

    border:
        1px solid #263244;

    border-radius: 10px;

    padding: 14px;

    min-width: 0;

    overflow: hidden;
}


      .summary-label {

    color: #9ca3af;

    font-size: 12px;

    margin-bottom: 7px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


     .summary-value {

    color: #f9fafb;

    font-size: 18px;

    font-weight: bold;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


        /* TABLA */

        .table-container {

            width: 100%;

            overflow-x: auto;

            border:
                1px solid #263244;

            border-radius: 10px;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 750px;
        }


        thead {

            background: #1e293b;
        }


        th {

            padding: 14px 16px;

            text-align: right;

            color: #93c5fd;

            font-size: 14px;

            border-bottom:
                1px solid #334155;
        }


        th:first-child,
        th:nth-child(2) {

            text-align: center;
        }


        td {

            padding: 12px 16px;

            border-bottom:
                1px solid #1f2937;

            color: #d1d5db;

            text-align: right;

            font-size: 14px;
        }


        td:first-child,
        td:nth-child(2) {

            text-align: center;
        }


        tbody tr:hover {

            background: #172033;
        }


        .capital {

            color: #93c5fd;
        }


        .interes {

            color: #fbbf24;
        }


        tfoot {

            background: #172033;
        }


        tfoot td {

            font-weight: bold;

            color: #f9fafb;

            border-top:
                1px solid #334155;
        }


        /* ALERTA */

        .errors {

            background: #450a0a;

            border:
                1px solid #991b1b;

            color: #fecaca;

            border-radius: 8px;

            padding: 15px;

            margin-bottom: 20px;
        }


        .errors ul {

            margin: 0;

            padding-left: 20px;
        }


        /* SQL */

        .sql-container {

            margin-top: 25px;
        }


        .sql-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 10px;
        }


        .sql-header h3 {

            margin: 0;

            color: #60a5fa;

            font-size: 18px;
        }


        .btn-copy {

            background: #059669;

            color: white;

            padding: 10px 18px;
        }


        .btn-copy:hover {

            background: #047857;
        }


        .sql-code {

            width: 100%;

            min-height: 500px;

            resize: vertical;

            background: #020617;

            color: #d1d5db;

            border:
                1px solid #334155;

            border-radius: 10px;

            padding: 18px;

            font-family:
                Consolas,
                "Courier New",
                monospace;

            font-size: 13px;

            line-height: 1.5;

            outline: none;
        }


        /* RESPONSIVE */

       @media (max-width: 1100px) {

    .summary-grid {

        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }
}


        @media (max-width: 850px) {

            .form-grid {

                grid-template-columns: 1fr;
            }


            .summary-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }
        }


        @media (max-width: 600px) {

            .container {

                margin: 15px auto;

                padding: 0 10px;
            }


            .card {

                padding: 20px;
            }


            .summary-grid {

                grid-template-columns: 1fr;
            }


            .actions {

                flex-direction: column;
            }


            button {

                width: 100%;
            }


            .sql-header {

                flex-direction: column;

                align-items: flex-start;

                gap: 10px;
            }
        }

    </style>

</head>


<body>


<div class="container">


    <!-- HEADER -->

    <div class="header">

        <h1>
            Gestión de Préstamos
        </h1>

        <p>
            Consulta y cálculo de información del préstamo
        </p>

    </div>


    <!-- ERRORES -->

    @if ($errors->any())

        <div class="errors">

            <strong>
                Verifique los siguientes datos:
            </strong>

            <ul>

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    <!-- FORMULARIO -->

    <div class="card">

        <h2 class="section-title">
            Información del préstamo
        </h2>


        <form
            action="{{ route('prestamos.calcular') }}"
            method="POST"
        >

            @csrf


            <div class="form-grid">


                <!-- NUMERO -->

                <div class="form-group">

                    <label for="numero_prestamo">
                        Número de Préstamo
                    </label>

                    <input
                        type="text"
                        id="numero_prestamo"
                        name="numero_prestamo"
                        value="{{ old('numero_prestamo', $datos['numero_prestamo'] ?? '') }}"
                        placeholder="Ingrese el número de préstamo"
                        required
                    >

                </div>


                <!-- TASA -->

                <div class="form-group">

                    <label for="tasa">
                        Tasa del Préstamo (%)
                    </label>

                    <input
                        type="number"
                        id="tasa"
                        name="tasa"
                        value="{{ old('tasa', $datos['tasa'] ?? '') }}"
                        step="0.01"
                        min="0"
                        placeholder="Ej. 10.50"
                        required
                    >

                </div>


                <!-- FECHA INICIO -->

                <div class="form-group">

                    <label for="fecha_inicio">
                        Fecha de Inicio
                    </label>

                    <input
                        type="date"
                        id="fecha_inicio"
                        name="fecha_inicio"
                        value="{{ old('fecha_inicio', $datos['fecha_inicio'] ?? '') }}"
                        required
                    >

                </div>


                <!-- FECHA FINAL -->

                <div class="form-group">

                    <label for="fecha_final">
                        Fecha Final
                    </label>

                    <input
                        type="date"
                        id="fecha_final"
                        name="fecha_final"
                        value="{{ old('fecha_final', $datos['fecha_final'] ?? '') }}"
                        required
                    >

                </div>


                <!-- BALANCE INICIAL -->

                <div class="form-group">

                    <label for="balance_inicial">
                        Balance Inicial
                    </label>

                    <input
                        type="number"
                        id="balance_inicial"
                        name="balance_inicial"
                        value="{{ old('balance_inicial', $datos['balance_inicial'] ?? '') }}"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        required
                    >

                </div>


                <!-- SALDO CAPITAL -->

                <div class="form-group">

                    <label for="saldo_capital">
                        Saldo Capital
                    </label>

                    <input
                        type="number"
                        id="saldo_capital"
                        name="saldo_capital"
                        value="{{ old('saldo_capital', $datos['saldo_capital'] ?? '') }}"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        required
                    >

                </div>


                <!-- INTERES ORDINARIO -->

                <div class="form-group">

                    <label for="interes_ordinario">
                        Interés Ordinario
                    </label>

                    <input
                        type="number"
                        id="interes_ordinario"
                        name="interes_ordinario"
                        value="{{ old('interes_ordinario', $datos['interes_ordinario'] ?? '') }}"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                    >

                </div>


                <!-- INTERES MORATORIO -->

                <div class="form-group">

                    <label for="interes_moratorio">
                        Interés Moratorio
                    </label>

                    <input
                        type="number"
                        id="interes_moratorio"
                        name="interes_moratorio"
                        value="{{ old('interes_moratorio', $datos['interes_moratorio'] ?? '') }}"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                    >

                </div>


            </div>


            <!-- BOTONES -->

            <div class="actions">

                <button
                    type="reset"
                    class="btn-clear"
                >
                    Limpiar
                </button>


                <button
                    type="submit"
                    class="btn-search"
                >
                    Consultar
                </button>

            </div>


        </form>

    </div>


    <!-- RESULTADO -->

    @isset($amortizacion)


        <!-- RESUMEN -->

        <div class="card">

            <h2 class="section-title">
                Tabla de Amortización
            </h2>


            <div class="summary-grid">


                <!-- NUMERO DE PRESTAMO -->

                <div class="summary-box">

                    <div class="summary-label">
                        Número de Préstamo
                    </div>

                    <div class="summary-value">
                        {{ $numeroPrestamo }}
                    </div>

                </div>


                <!-- TASA ANUAL -->

                <div class="summary-box">

                    <div class="summary-label">
                        Tasa Anual
                    </div>

                    <div class="summary-value">
                        {{ number_format($tasaAnual, 2) }}%
                    </div>

                </div>


                <!-- NUMERO DE CUOTAS -->

                <div class="summary-box">

                    <div class="summary-label">
                        Número de Cuotas
                    </div>

                    <div class="summary-value">
                        {{ $numeroCuotas }}
                    </div>

                </div>


                <!-- CUOTA -->

                <div class="summary-box">

                    <div class="summary-label">
                        Cuota
                    </div>

                    <div class="summary-value">
                        {{ number_format($cuota, 2) }}
                    </div>

                </div>


                <!-- VALOR PRIMERA CUOTA -->

                <div class="summary-box">

                    <div class="summary-label">
                        Valor Primera Cuota
                    </div>

                    <div class="summary-value">
                        {{ number_format($valorPrimeraCuota, 2) }}
                    </div>

                </div>


              <!-- SUMA INTERÉS -->

<div class="summary-box">

    <div class="summary-label">
        Suma Interés Vencidos
    </div>

    <div class="summary-value">
        {{ number_format($sumaInteres, 2) }}
    </div>

</div>


<!-- VALOR 82 -->

<div class="summary-box">

    <div class="summary-label">
        Valor 82
    </div>

    <div class="summary-value">
        {{ number_format($valor82, 2) }}
    </div>

</div>


<!-- VALOR TOTAL 51 -->

<div class="summary-box">

    <div class="summary-label">
        Valor Total 51
    </div>

    <div class="summary-value">
        {{ number_format($valorTotal51, 2) }}
    </div>

</div>

            </div> <!-- CIERRE summary-grid -->


            <!-- TABLA -->

            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>
                                PlaNuCuota
                            </th>

                            <th>
                                PlaPFecPago
                            </th>

                            <th>
                                PlaPMonto (50)
                            </th>

                            <th>
                                PlaPMonto (51)
                            </th>

                            <th>
                                Total Cuota
                            </th>

                            <th>
                                Saldo
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach ($amortizacion as $fila)

                            <tr>

                                <td>
                                    {{ $fila['cuota'] }}
                                </td>

                                <td>
                                    {{ $fila['fecha'] }}
                                </td>

                                <td class="capital">
                                    {{ number_format($fila['capital'], 2) }}
                                </td>

                                <td class="interes">
                                    {{ number_format($fila['interes'], 2) }}
                                </td>

                                <td>
                                    {{ number_format($fila['total'], 2) }}
                                </td>

                                <td>
                                    {{ number_format($fila['saldo'], 2) }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>


                    <tfoot>

                        <tr>

                            <td colspan="2">
                                TOTALES
                            </td>

                            <td class="capital">
                                {{ number_format($totalCapital, 2) }}
                            </td>

                            <td class="interes">
                                {{ number_format($totalInteres, 2) }}
                            </td>

                            <td>
                                {{ number_format($totalPagos, 2) }}
                            </td>

                            <td>
                                0.00
                            </td>

                        </tr>

                    </tfoot>


                </table>

            </div>

        </div>


        <!-- SCRIPT SQL -->

        <div class="card sql-container">

            <div class="sql-header">

                <h3>
                    Script SQL Server
                </h3>


                <button
                    type="button"
                    class="btn-copy"
                    onclick="copiarSQL()"
                >
                    Copiar SQL
                </button>

            </div>


            <textarea
                id="sqlScript"
                class="sql-code"
                readonly
            >{{ $sql }}</textarea>

        </div>


    @endisset


</div>


<script>

function copiarSQL()
{

    const textarea =
        document.getElementById('sqlScript');


    textarea.select();


    textarea.setSelectionRange(
        0,
        999999
    );


    navigator.clipboard.writeText(
        textarea.value
    ).then(function () {

        const boton =
            document.querySelector('.btn-copy');


        const textoOriginal =
            boton.innerText;


        boton.innerText =
            '¡Copiado!';


        setTimeout(function () {

            boton.innerText =
                textoOriginal;

        }, 2000);

    });

}

</script>


</body>

</html>