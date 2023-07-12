<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CajaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index1()
    {  
        $cajas = Caja::with(['Compra', 'Venta'])
        ->where('estado', 1)
        //->orderBy('fecha')
        ->get();
        $reporte = [];
        $saldoFinal = 0;
        $sumatoriaIngresos = 0;
        $sumatoriaGastos = 0;

        foreach ($cajas as $caja) {
            $operacion = [
                'numero_correlativo' => $caja->id,
                'fecha' => $caja->fecha,
                'motivo' => $caja->motivo,
                'tipo' => $caja->tipo == 1 ? 'Gasto' : 'Ingreso',
                'monto' => $caja->monto,
                'saldo' => $saldoFinal += ($caja->tipo == 1 ? -$caja->monto : $caja->monto),
            ];

            if ($caja->tipo == 1) {
                $sumatoriaGastos += $caja->monto;
                $saldoFinal -= $caja->monto;
            } else {
                $sumatoriaIngresos += $caja->monto;
                $saldoFinal += $caja->monto;
            }

            $reporte[] = $operacion;
        }

        $informe = [
            'saldoInicial' => 0,
            'reporte' => $reporte,
            'sumatoria_ingresos' => $sumatoriaIngresos,
            'sumatoria_gastos' => $sumatoriaGastos,
            'saldoTotal' => $saldoFinal,
        ];

        return $informe;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:1,2',
            'fecha' => 'nullable|date',
            'monto' => 'required|numeric',
            'motivo' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        $caja = new Caja();
        $caja->tipo = $request->input('tipo');
        $caja->fecha = $request->fecha ? $request->fecha . ' ' . now()->format('H:i:s') : now();
        $caja->monto = $request->input('monto');
        $caja->motivo = $request->input('motivo', null);
        $caja->save();
    
        return $caja;

    }

    /**
     * Display the specified resource.
     */
    public function show(Caja $caja)
    {
        $caja->compra = $caja->Compra;
        $caja->venta = $caja->Venta;
        return $caja;
    }

    public function CajaReporteAnual($anio)
    {
        $saldoAnteriorGastos = Caja::whereYear('fecha', '<', $anio)
            ->where('tipo', 1) // Tipo 1 representa gastos
            ->where('estado', 1)
            ->sum('monto');
    
        $saldoAnteriorIngresos = Caja::whereYear('fecha', '<', $anio)
            ->where('tipo', 2) // Tipo 2 representa ingresos
            ->where('estado', 1)
            ->sum('monto');
    
        $saldoAnterior = $saldoAnteriorIngresos - $saldoAnteriorGastos;
        $saldoAnterior1 = $saldoAnterior ?? 0;
        $saldoAnterior = $saldoAnterior ?? 0;
    
        $cajas = Caja::with(['Compra', 'Venta'])
            ->whereYear('fecha', $anio)
            ->where('estado', 1)
            //->orderBy('fecha')
            ->get();
    
        $reporte = [];
        $saldoFinal = 0;
        $sumatoriaIngresos = 0;
        $sumatoriaGastos = 0;
    
        foreach ($cajas as $caja) {
            $operacion = [
                'numero_correlativo' => $caja->id,
                'fecha' => $caja->fecha,
                'motivo' => $caja->motivo,
                'tipo' => $caja->tipo == 1 ? 'Gasto' : 'Ingreso',
                'monto' => $caja->monto,
                'saldo' => $saldoAnterior += ($caja->tipo == 1 ? -$caja->monto : $caja->monto),
            ];
    
            if ($caja->tipo == 1) {
                $sumatoriaGastos += $caja->monto;
                $saldoFinal -= $caja->monto;
            } else {
                $sumatoriaIngresos += $caja->monto;
                $saldoFinal += $caja->monto;
            }
    
            $reporte[] = $operacion;
        }
    
        $informe = [
            'year' => $anio,
            'saldoInicial' => $saldoAnterior1,
            'reporte' => $reporte,
            'sumatoria_ingresos' => $sumatoriaIngresos,
            'sumatoria_gastos' => $sumatoriaGastos,
            'saldoTotal' => $saldoAnterior1 + $sumatoriaIngresos - $sumatoriaGastos,
        ];
    
        return $informe;
    }
    
    
    
    


    public function CajaReporteMensual($anio, $mes)
    {
        $saldoAnteriorGastos = Caja::whereYear('fecha', $anio)
            ->whereMonth('fecha', '<', $mes)
            ->where('tipo', 1) // Tipo 1 representa gastos
            ->where('estado', 1)
            ->sum('monto');
    
        $saldoAnteriorIngresos = Caja::whereYear('fecha', $anio)
            ->whereMonth('fecha', '<', $mes)
            ->where('tipo', 2) // Tipo 2 representa ingresos
            ->where('estado', 1)
            ->sum('monto');
    
        $saldoAnterior = $saldoAnteriorIngresos - $saldoAnteriorGastos;
        $saldoAnterior1 = $saldoAnterior ?? 0;
        $saldoAnterior = $saldoAnterior ?? 0;
    
        $cajas = Caja::with(['Compra', 'Venta'])
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->where('estado', 1)
            //->orderBy('fecha')
            ->get();
    
        $reporte = [];
        $saldoFinal = 0;
        $sumatoriaIngresos = 0;
        $sumatoriaGastos = 0;
    
        foreach ($cajas as $caja) {
            $operacion = [
                'numero_correlativo' => $caja->id,
                'fecha' => $caja->fecha,
                'motivo' => $caja->motivo,
                'tipo' => $caja->tipo == 1 ? 'Gasto' : 'Ingreso',
                'monto' => $caja->monto,
                'saldo' => $saldoAnterior += ($caja->tipo == 1 ? -$caja->monto : $caja->monto),
            ];
    
            if ($caja->tipo == 1) {
                $sumatoriaGastos += $caja->monto;
                $saldoFinal -= $caja->monto;
            } else {
                $sumatoriaIngresos += $caja->monto;
                $saldoFinal += $caja->monto;
            }
    
            $reporte[] = $operacion;
        }
        
        $informe = [
            'saldoInicial' => $saldoAnterior1,
            'reporte' => $reporte,
            'sumatoria_ingresos' => $sumatoriaIngresos,
            'sumatoria_gastos' => $sumatoriaGastos,
            'saldoTotal' => $saldoAnterior1 + $sumatoriaIngresos - $sumatoriaGastos,
        ];
    
        return $informe;
    }
    
    
    

    public function index()
    {
        $today = now();
        $anio = $today->year;
        $mes = $today->month;
        
        $saldoAnteriorGastos = Caja::whereYear('fecha', $anio)
        ->whereMonth('fecha', '<', $mes)
        ->where('tipo', 1) // Tipo 1 representa gastos
        ->sum('monto');

        $saldoAnteriorIngresos = Caja::whereYear('fecha', $anio)
            ->whereMonth('fecha', '<', $mes)
            ->where('tipo', 2) // Tipo 2 representa ingresos
            ->sum('monto');

        $saldoAnterior = $saldoAnteriorIngresos - $saldoAnteriorGastos;
        $saldoAnterior1 = $saldoAnterior ?? 0;
        // Si no hay registros anteriores, establecer saldoAnterior en 0
        $saldoAnterior = $saldoAnterior ?? 0;

        $cajas = Caja::with(['Compra', 'Venta'])
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            //->orderBy('fecha')
            ->get();

        $reporte = [];
        $saldoFinal = 0;
        $sumatoriaIngresos = 0;
        $sumatoriaGastos = 0;

        foreach ($cajas as $caja) {
            $operacion = [
                'numero_correlativo' => $caja->id,
                'fecha' => $caja->fecha,
                'motivo' => $caja->motivo,
                'tipo' => $caja->tipo == 1 ? 'Gasto' : 'Ingreso',
                'monto' => $caja->monto,
                'saldo' => $saldoAnterior += ($caja->tipo == 1 ? -$caja->monto : $caja->monto),
            ];

            if ($caja->tipo == 1) {
                $sumatoriaGastos += $caja->monto;
                $saldoFinal -= $caja->monto;
            } else {
                $sumatoriaIngresos += $caja->monto;
                $saldoFinal += $caja->monto;
            }

            $reporte[] = $operacion;
        }
        
        $informe = [
            'saldoInicial' => $saldoAnterior1,
            'reporte' => $reporte,
            'sumatoria_ingresos' => $sumatoriaIngresos,
            'sumatoria_gastos' => $sumatoriaGastos,
            'saldoTotal' => $saldoAnterior1 + $sumatoriaIngresos - $sumatoriaGastos,
        ];

        return $informe;




    }


    public function CajaReporteDia($anio, $mes, $dia)
    {
        $cajas = Caja::with(['Compra', 'Venta'])
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->whereDay('fecha', $dia)
            ->where('estado', 1)
            //->orderBy('fecha')
            ->get();
    
        $reporte = [];
        $saldo = 0;
        $sumatoriaIngresos = 0;
        $sumatoriaGastos = 0;
    
        $saldosAnteriores = Caja::selectRaw('SUM(CASE WHEN tipo = 2 THEN monto ELSE -monto END) AS saldo_anterior')
            ->whereDate('fecha', '<', "$anio-$mes-$dia")
            ->where('estado', 1)
            ->first();
        
        $saldoAnterior = $saldosAnteriores->saldo_anterior ?? 0;
        $saldoAnterior = +(float)($saldoAnterior);
        $saldoAnterior1 = +(float)($saldoAnterior ?? 0);
        $saldoFinal = 0;
    
        foreach ($cajas as $caja) {

            $operacion = [
                'numero_correlativo' => $caja->id,
                'fecha' => $caja->fecha,
                'motivo' => $caja->motivo,
                'tipo' => $caja->tipo == 1 ? 'Gasto' : 'Ingreso',
                'monto' => $caja->monto,
                'saldo' => $saldoAnterior += ($caja->tipo == 1 ? -$caja->monto : $caja->monto),
            ];
    
    
            if ($caja->tipo == 1) {
                $sumatoriaGastos += $caja->monto;
                $saldoFinal -= $caja->monto;
            } else {
                $sumatoriaIngresos += $caja->monto;
                $saldoFinal += $caja->monto;
            }
    
            $reporte[] = $operacion;
        }
    
        $informe = [
            'saldoInicial' => $saldoAnterior1,
            'reporte' => $reporte,
            'sumatoria_ingresos' => $sumatoriaIngresos,
            'sumatoria_gastos' => $sumatoriaGastos,
            /* 'saldoTotal' => $saldoFinal, */
            'saldoTotal' => $saldoAnterior1 + $sumatoriaIngresos - $sumatoriaGastos,
        ];
    
        return $informe;
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Caja $caja)
    {

        $validator = Validator::make($request->all(), [
            'tipo' => 'nullable|in:1,2',
            'fecha' => 'nullable|date',
            'monto' => 'nullable|numeric',
            'motivo' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $caja->tipo = $request->filled('tipo') ? $request->input('tipo') : $caja->tipo;
        $caja->monto = $request->filled('monto') ? $request->input('monto') : $caja->monto;
        $caja->fecha = $request->fecha ? $request->fecha . ' ' . now()->format('H:i:s') : now();       
        $caja->motivo = $request->filled('motivo') ? $request->input('motivo') : $caja->motivo;
        $caja->save();
        return $caja;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Caja $caja)
    {      
    
        if (!$caja->compra_id == null){
            return response()->json(['message' => 'Existe una compra asociada a caja, debe de eliminar la compra asociada'],409);
        } 
        if (!$caja->venta_id == null){
            return response()->json(['message' => 'Existe una venta asociada a caja, debe de eliminar la venta asociada'],409);
        } 
        $caja->estado = 0;
        $caja->save();
        return response()->json(['message' => 'La caja ha sido eliminada correctamente'],200);
    }
}
