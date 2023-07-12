<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\CompraInventario;
use App\Models\VentaInventario;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use FPDF;

class InventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $model = Producto::where('estado',1)->get();
        $list = [];
        foreach ($model as $m){
            $list[] = $this->kardex($m);
        }
        return $list;
/*      return Producto::with(['Marca','Medida','Categoria'])->where('estado', 1)->get();
        return Inventario::with(['Producto'])->where('estado', 1)->get(); */
    }

    /**
     * Store a newly created resource in storage.
     */



    public function kardex(Producto $producto)
     {
        $producto->marca = $producto->Marca;
        $producto->medida = $producto->Medida;
        $producto->categoria = $producto->Categoria;
        $producto->urlImagen = $producto->UrlImage();
        $producto->inventarios = $producto->Inventarios()->where('estado', 1)->get();
        $producto->entradas = $producto->inventarios->where('tipo', 1)->sum('cantidad');
        $producto->salidas = $producto->inventarios->where('tipo', 2)->sum('cantidad');
        $producto->invFinal = $producto->entradas - $producto->salidas;
        
        return $producto;
    }

    public function kardexMensual(Producto $producto, $anio, $mes)
    {
        // Buscar el producto por su ID junto con sus inventarios relacionados
        //$producto = Producto::with('inventarios')->findOrFail($idProducto);

        // Asociar producto a marca, medida y categoría
        $producto->marca = $producto->Marca;
        $producto->medida = $producto->Medida;
        $producto->categoria = $producto->Categoria;
    
        // Obtener los inventarios filtrados por año y mes, ordenados por fecha
        $inventarios = $producto->inventarios()
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->where('estado', 1)
            //->orderBy('fecha')
            ->get();
        
        
        
        // Calcular el saldo acumulado anterior
        $saldoAcumuladoAnterior = 0;
        $saldoTotalFinal = 0;
        $saldoAcumuladoAnterior += $producto->inventarios()
            ->where(function ($query) use ($anio, $mes) {
                $query->whereYear('fecha', '<', $anio)
                    ->orWhereMonth('fecha', '<', $mes);
            })
            ->where('estado', 1)
            ->selectRaw('SUM(CASE WHEN tipo = 1 THEN cantidad ELSE -cantidad END) as saldo')
            ->value('saldo');
        
        $saldoTotalFinal += $producto->inventarios()
            ->where(function ($query) use ($anio, $mes) {
                $query->whereYear('fecha', '<', $anio)
                    ->orWhereMonth('fecha', '<', $mes);
            })
            ->where('estado', 1)
            ->selectRaw('SUM(CASE WHEN tipo = 1 THEN (cantidad * costoUnitario) ELSE (-cantidad * costoUnitario) END) as saldo')
            ->value('saldo');
    



        // Obtener el costo inicial a partir del último inventario
        $costoInicial = ($saldoAcumuladoAnterior != 0) ? $saldoTotalFinal / $saldoAcumuladoAnterior : 0;

    
        // Crear un arreglo para almacenar los detalles del inventario por mes
        $detalleInventario = [];
        $inventarioActual = $saldoAcumuladoAnterior;
    
        // Calcular el inventario por mes y actualizar el saldo acumulado
        foreach ($inventarios as $inventario) {
            $entrada = $inventario->tipo == 1 ? $inventario->cantidad : 0;
            $salida = $inventario->tipo == 2 ? $inventario->cantidad : 0;
    
            $inventarioActual += $entrada - $salida;
            $saldoTotalFinal += ($entrada*$inventario->costoUnitario)-($salida*$inventario->costoUnitario);
    
            $tipoMovimiento = 'SIN TIPO';

            if ($inventario->tipo == 1){
                $tipoMovimiento = 'Entrada';
            }
            elseif($inventario->tipo==2){
                $tipoMovimiento = 'Salida';
            }
            
            // Agregar los detalles del inventario al arreglo
            $detalleInventario[] = [
                'fecha' => $inventario->fecha,
                'motivo' => $inventario->motivo,
                'tipo' => $inventario->tipo,
                'tipoMovimiento' => $tipoMovimiento,
                'entrada' => $entrada,
                'salida' => $salida,
                'costoUnitario' => $inventario->costoUnitario,
                'totalMovimiento' => ($entrada+$salida)*$inventario->costoUnitario,
                'saldo' => $inventarioActual
            ];
        }
    
        // Actualizar las propiedades del producto
        $producto->cantidadInicial = $saldoAcumuladoAnterior;
        $producto->costoInicial = $costoInicial;
        $producto->totalSaldoInicial = $saldoAcumuladoAnterior*$costoInicial;   
        $producto->detalleInventario = $detalleInventario;
        $producto->cantidadFinal = $inventarioActual;
        $producto->costoFinal = ($inventarioActual != 0) ? $saldoTotalFinal / $inventarioActual : 0;
        $producto->totalSaldoFinal = $saldoTotalFinal;
    
        // Eliminar la relación "inventarios" del producto
        $producto->unsetRelation('inventarios');
    
        return $producto;
    }
    
    public function kardexDia(Producto $producto, $anio, $mes, $dia)
    {
        // Buscar el producto por su ID junto con sus inventarios relacionados
        //$producto = Producto::with('inventarios')->findOrFail($idProducto);

        // Asociar producto a marca, medida y categoría
        $producto->marca = $producto->Marca;
        $producto->medida = $producto->Medida;
        $producto->categoria = $producto->Categoria;
    
        // Obtener los inventarios filtrados por año, mes y día, ordenados por fecha
        $inventarios = $producto->inventarios()
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->whereDay('fecha', $dia)
            ->where('estado', 1)
            //->orderBy('fecha')
            ->get();
    
        // Calcular el saldo acumulado anterior
        $saldoAcumuladoAnterior = 0;
        $saldoTotalFinal = 0;
        $saldoAcumuladoAnterior += $producto->inventarios()
            ->where(function ($query) use ($anio, $mes, $dia) {
                $query->whereYear('fecha', '<', $anio)
                    ->orWhereMonth('fecha', '<', $mes)
                    ->orWhereDay('fecha', '<', $dia);
            })
            ->where('estado', 1)
            ->selectRaw('SUM(CASE WHEN tipo = 1 THEN cantidad ELSE -cantidad END) as saldo')
            ->value('saldo');
    

        $saldoTotalFinal += $producto->inventarios()
            ->where(function ($query) use ($anio, $mes, $dia) {
                $query->whereYear('fecha', '<', $anio)
                    ->orWhereMonth('fecha', '<', $mes)
                    ->orWhereDay('fecha', '<', $dia);
            })
            ->where('estado', 1)
            ->selectRaw('SUM(CASE WHEN tipo = 1 THEN (cantidad * costoUnitario) ELSE (-cantidad * costoUnitario) END) as saldo')
            ->value('saldo');    
        

        // Obtener el costo inicial a partir del último inventario
        $costoInicial = ($saldoAcumuladoAnterior != 0) ? $saldoTotalFinal / $saldoAcumuladoAnterior : 0;

    
        // Crear un arreglo para almacenar los detalles del inventario por día
        $detalleInventario = [];
        $inventarioActual = $saldoAcumuladoAnterior;
    
        // Calcular el inventario por día y actualizar el saldo acumulado
        foreach ($inventarios as $inventario) {
            $entrada = $inventario->tipo == 1 ? $inventario->cantidad : 0;
            $salida = $inventario->tipo == 2 ? $inventario->cantidad : 0;
    
            $inventarioActual += $entrada - $salida;
            $saldoTotalFinal += ($entrada*$inventario->costoUnitario)-($salida*$inventario->costoUnitario);
    
            $tipoMovimiento = 'SIN TIPO';

            if ($inventario->tipo == 1){
                $tipoMovimiento = 'Entrada';
            }
            elseif($inventario->tipo==2){
                $tipoMovimiento = 'Salida';
            }
            
            // Agregar los detalles del inventario al arreglo
            $detalleInventario[] = [
                'fecha' => $inventario->fecha,
                'motivo' => $inventario->motivo,
                'tipo' => $inventario->tipo,
                'tipoMovimiento' => $tipoMovimiento,
                'entrada' => $entrada,
                'salida' => $salida,
                'costoUnitario' => $inventario->costoUnitario,
                'totalMovimiento' => ($entrada+$salida)*$inventario->costoUnitario,
                'saldo' => $inventarioActual
            ];
        }
    
        // Actualizar las propiedades del producto
        $producto->cantidadInicial = $saldoAcumuladoAnterior;
        $producto->costoInicial = $costoInicial;
        $producto->totalSaldoInicial = $saldoAcumuladoAnterior*$costoInicial;   
        $producto->detalleInventario = $detalleInventario;
        $producto->cantidadFinal = $inventarioActual;
        $producto->costoFinal = ($inventarioActual != 0) ? $saldoTotalFinal / $inventarioActual : 0;
        $producto->totalSaldoFinal = $saldoTotalFinal;
    
        // Eliminar la relación "inventarios" del producto
        $producto->unsetRelation('inventarios');
    
        return $producto;
    }
    
    
    
    


    public function kardexAnual(Producto $producto, $anio)
    {
        // Buscar el producto por su ID junto con sus inventarios relacionados
        //$producto = Producto::with('inventarios')->findOrFail($idProducto);
    
        // Asociar producto a marca, medida y categoría
        $producto->marca = $producto->Marca;
        $producto->medida = $producto->Medida;
        $producto->categoria = $producto->Categoria;
    
        // Obtener los inventarios filtrados por año, ordenados por fecha
        $inventarios = $producto->inventarios()
            ->whereYear('fecha', $anio)
            ->where('estado', 1)
            //->orderBy('fecha')
            ->get();
    
        // Calcular el saldo acumulado anterior
        $saldoAcumuladoAnterior = 0;
        $saldoTotalFinal = 0;

        $saldoAcumuladoAnterior += $producto->inventarios()
            ->whereYear('fecha', '<', $anio)
            ->where('estado', 1)
            ->selectRaw('SUM(CASE WHEN tipo = 1 THEN cantidad ELSE -cantidad END) as saldo')
            ->value('saldo');
            
        $saldoTotalFinal += $producto->inventarios()
            ->whereYear('fecha', '<', $anio)
            ->where('estado', 1)
            ->selectRaw('SUM(CASE WHEN tipo = 1 THEN (cantidad * costoUnitario) ELSE (-cantidad * costoUnitario) END) as saldo')
            ->value('saldo');
            
        // Obtener el costo inicial a partir del último inventario
        $costoInicial = ($saldoAcumuladoAnterior != 0) ? $saldoTotalFinal / $saldoAcumuladoAnterior : 0;

    
        // Crear un arreglo para almacenar los detalles del inventario por año
        $detalleInventario = [];
        $inventarioActual = $saldoAcumuladoAnterior;
        
        // Calcular el inventario por año y actualizar el saldo acumulado
        foreach ($inventarios as $inventario) {
            $entrada = $inventario->tipo == 1 ? $inventario->cantidad : 0;
            $salida = $inventario->tipo == 2 ? $inventario->cantidad : 0;
    
            $inventarioActual += $entrada - $salida;

            $saldoTotalFinal += ($entrada*$inventario->costoUnitario)-($salida*$inventario->costoUnitario);
            
            $tipoMovimiento = 'SIN TIPO';

            if ($inventario->tipo == 1){
                $tipoMovimiento = 'Entrada';
            }
            elseif($inventario->tipo==2){
                $tipoMovimiento = 'Salida';
            }
            
            // Agregar los detalles del inventario al arreglo
            $detalleInventario[] = [
                'fecha' => $inventario->fecha,
                'motivo' => $inventario->motivo,
                'tipo' => $inventario->tipo,
                'tipoMovimiento' => $tipoMovimiento,
                'entrada' => $entrada,
                'salida' => $salida,
                'costoUnitario' => $inventario->costoUnitario,
                'totalMovimiento' => ($entrada+$salida)*$inventario->costoUnitario,
                'saldo' => $inventarioActual
            ];
        }
    
        // Actualizar las propiedades del producto
        $producto->fechaYear = $anio;
        $producto->cantidadInicial = $saldoAcumuladoAnterior;
        $producto->costoInicial = $costoInicial;
        $producto->totalSaldoInicial = $saldoAcumuladoAnterior*$costoInicial;   
        $producto->detalleInventario = $detalleInventario;
        $producto->cantidadFinal = $inventarioActual;
        $producto->costoFinal = ($inventarioActual != 0) ? $saldoTotalFinal / $inventarioActual : 0;
        $producto->totalSaldoFinal = $saldoTotalFinal;
    
        // Eliminar la relación "inventarios" del producto
        $producto->unsetRelation('inventarios');
    
        return $producto;
    }
    
    


    public function kardexActual(Producto $producto)
    {
        $anio = date('Y');
        $mes = date('m');
    
        // Generar el kardex mensual del mes actual
        $kardexActual = $this->kardexMensual($producto, $anio, $mes);
    
        return $kardexActual;
    }
    
    
    
    


    public function kardexMensualPdf(Producto $producto, $anio, $mes)
    {
         // Buscar el producto por su ID junto con sus inventarios relacionados
        //$producto = Producto::with('inventarios')->findOrFail($idProducto);

         // Asociar producto a marca, medida y categoría
         $producto->marca = $producto->Marca;
         $producto->medida = $producto->Medida;
         $producto->categoria = $producto->Categoria;
     
         // Obtener los inventarios filtrados por año y mes, ordenados por fecha
         $inventarios = $producto->inventarios()
             ->whereYear('fecha', $anio)
             ->whereMonth('fecha', $mes)
             ->where('estado', 1)
             //->orderBy('fecha')
             ->get();
         
         
         
         // Calcular el saldo acumulado anterior
         $saldoAcumuladoAnterior = 0;
         $saldoTotalFinal = 0;
         $saldoAcumuladoAnterior += $producto->inventarios()
             ->where(function ($query) use ($anio, $mes) {
                 $query->whereYear('fecha', '<', $anio)
                     ->orWhereMonth('fecha', '<', $mes);
             })
             ->where('estado', 1)
             ->selectRaw('SUM(CASE WHEN tipo = 1 THEN cantidad ELSE -cantidad END) as saldo')
             ->value('saldo');
         
         $saldoTotalFinal += $producto->inventarios()
             ->where(function ($query) use ($anio, $mes) {
                 $query->whereYear('fecha', '<', $anio)
                     ->orWhereMonth('fecha', '<', $mes);
             })
             ->where('estado', 1)
             ->selectRaw('SUM(CASE WHEN tipo = 1 THEN (cantidad * costoUnitario) ELSE (-cantidad * costoUnitario) END) as saldo')
             ->value('saldo');
     
 
 
 
         // Obtener el costo inicial a partir del último inventario
         $costoInicial = ($saldoAcumuladoAnterior != 0) ? $saldoTotalFinal / $saldoAcumuladoAnterior : 0;
 
     
         // Crear un arreglo para almacenar los detalles del inventario por mes
         $detalleInventario = [];
         $inventarioActual = $saldoAcumuladoAnterior;
     
         // Calcular el inventario por mes y actualizar el saldo acumulado
         foreach ($inventarios as $inventario) {
             $entrada = $inventario->tipo == 1 ? $inventario->cantidad : 0;
             $salida = $inventario->tipo == 2 ? $inventario->cantidad : 0;
     
             $inventarioActual += $entrada - $salida;
             $saldoTotalFinal += ($entrada*$inventario->costoUnitario)-($salida*$inventario->costoUnitario);
     
             $tipoMovimiento = 'SIN TIPO';
 
             if ($inventario->tipo == 1){
                 $tipoMovimiento = 'Entrada';
             }
             elseif($inventario->tipo==2){
                 $tipoMovimiento = 'Salida';
             }
             
             // Agregar los detalles del inventario al arreglo
             $detalleInventario[] = [
                 'fecha' => $inventario->fecha,
                 'motivo' => $inventario->motivo,
                 'tipo' => $inventario->tipo,
                 'tipoMovimiento' => $tipoMovimiento,
                 'entrada' => $entrada,
                 'salida' => $salida,
                 'costoUnitario' => $inventario->costoUnitario,
                 'totalMovimiento' => ($entrada+$salida)*$inventario->costoUnitario,
                 'saldo' => $inventarioActual
             ];
         }
     
         // Actualizar las propiedades del producto
         $producto->cantidadInicial = $saldoAcumuladoAnterior;
         $producto->costoInicial = $costoInicial;
         $producto->totalSaldoInicial = $saldoAcumuladoAnterior*$costoInicial;   
         $producto->detalleInventario = $detalleInventario;
         $producto->cantidadFinal = $inventarioActual;
         $producto->costoFinal = ($inventarioActual != 0) ? $saldoTotalFinal / $inventarioActual : 0;
         $producto->totalSaldoFinal = $saldoTotalFinal;
    

        // Crear una instancia de FPDF
        $pdf = new FPDF('L', 'mm', 'A4');

        $pdf->AddPage();
        
        $pdf->SetFont('Arial', 'B', 12);

        $pdf->Cell(10, 10, 'INGRESO DE INVENTARIO PERMANENTE VALORIZADO - DETALLE DE INVENTARIO VALORIZADO ', 0, 1, '');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 10, 'PERIODO: ', 0, 0, '');
        $pdf->SetFont('Arial', '', 10);
        $nombreMes = Lang::get('datetime.months.' . $mes);
        $anioMes = $nombreMes. ' '.$anio;
        $pdf->Cell(100, 10, $anioMes, 0, 1, '');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 10, 'RUC:', 0, 0, '');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(100, 10, '10004050661', 0, 1, '');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 10, utf8_decode('RAZÓN SOCIAL:'), 0, 0, '');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(100, 10, 'Minimarket Nicoll', 0, 1, '');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 10, 'UNIDAD DE MEDIDA:', 0, 0, '');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(100, 10, utf8_decode($producto->medida->codigo. " ". $producto->medida->nombre), 0, 1, '');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 10, utf8_decode('DESCRIPCIÓN:'), 0, 0, '');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(100, 10, utf8_decode($producto->nombre), 0, 1, '');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 10,utf8_decode('MÉTODO DE VALUACIÓN:'), 0, 0, '');
        $pdf->SetFont('Arial', '', 10); // Cambiar a fuente normal
        $pdf->Cell(100, 10, 'Promedio', 0, 1, '');
        $pdf->Ln(5);
        // Cabecera principal

        $pdf->SetFont('Arial', 'B', 10); 
        $pdf->Cell(10, 10, utf8_decode('N°'), 1, 0, 'C');
        $pdf->Cell(25, 10, 'Fecha', 1, 0, 'C');
        $pdf->Cell(65, 10, 'Motivo', 1, 0, 'C');
        $pdf->Cell(60, 10, 'Entradas', 1, 0, 'C');
        $pdf->Cell(60, 10, 'Salidas', 1, 0, 'C');
        $pdf->Cell(60, 10, 'Saldos', 1, 1, 'C');
        
        // Subcabecera de Entradas
        $pdf->Cell(10, 10, '', 1, 0, 'C');
        $pdf->Cell(25, 10, '', 1, 0, 'C');
        $pdf->Cell(65, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, 'Cantidad', 1, 0, 'C');
        $pdf->Cell(20, 10, 'C.U.', 1, 0, 'C');
        $pdf->Cell(20, 10, 'Total', 1, 0, 'C');
        
        // Subcabecera de Salidas
        $pdf->Cell(20, 10, 'Cantidad', 1, 0, 'C');
        $pdf->Cell(20, 10, 'C.U.', 1, 0, 'C');
        $pdf->Cell(20, 10, 'Total', 1, 0, 'C');
        
        // Subcabecera de Saldos
        $pdf->Cell(20, 10, 'Cantidad', 1, 0, 'C');
        $pdf->Cell(20, 10, 'C.U.', 1, 0, 'C');
        $pdf->Cell(20, 10, 'Total', 1, 1, 'C');
        
        // Contenido de ejemplo
        $i = 1;
        $pdf->SetFont('Arial', '', 9);

        $pdf->Cell(10, 10, $i, 1, 0, 'C');
        $pdf->Cell(25, 10, '', 1, 0, 'C');
        $pdf->Cell(65, 10, 'POR EL SALDO ANTERIOR', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        
        // Subcabecera de Salidas
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        
        // Subcabecera de Saldos
  
        $pdf->Cell(20, 10, number_format($producto->cantidadInicial, 2), 1, 0, 'C');
        $pdf->Cell(20, 10, number_format($producto->costoInicial, 2), 1, 0, 'C');
        $pdf->Cell(20, 10, number_format($producto->totalSaldoInicial, 2), 1, 1, 'C');

        $entradasCantidad = 0;
        $entradasTotal = 0;
        $salidasCantidad = 0;
        $salidasTotal = 0;

        $saldoCant = 0;
        $saldoTot = 0;
        $saldoCant += $producto->saldoInicial;
        $saldoTot += $producto->saldoInicial*$producto->costoInicial;
        
        $i++;
        foreach ($producto->detalleInventario as $detalle) {
            $fechaFormateada = Carbon::parse($detalle['fecha'])->format('d-m-Y');
            $pdf->Cell(10, 10, $i, 1, 0, 'C'); // Celda vacía para N°
            $pdf->Cell(25, 10, $fechaFormateada, 1, 0, 'C'); // Fecha
            $pdf->Cell(65, 10, utf8_decode($detalle['motivo']), 1, 0, 'C'); // Motivo
        
            if ($detalle['entrada'] != 0) {
                $pdf->Cell(20, 10, number_format($detalle['entrada'], 2), 1, 0, 'C'); // Cantidad de entrada
                $pdf->Cell(20, 10, number_format($detalle['costoUnitario'], 2), 1, 0, 'C'); // C.U. de entrada
                $pdf->Cell(20, 10, number_format($detalle['entrada'] * $detalle['costoUnitario'], 2), 1, 0, 'C'); // Total de entrada
                $entradasCantidad += $detalle['entrada'];
                $entradasTotal += $detalle['entrada'] * $detalle['costoUnitario'];
            } else {
                $pdf->Cell(20, 10, '', 1, 0, 'C'); // Celda vacía
                $pdf->Cell(20, 10, '', 1, 0, 'C'); // Celda vacía
                $pdf->Cell(20, 10, '', 1, 0, 'C'); // Celda vacía
            }
        
            if ($detalle['salida'] != 0) {
                $pdf->Cell(20, 10, number_format($detalle['salida'], 2), 1, 0, 'C'); // Cantidad de salida
                $pdf->Cell(20, 10, number_format($detalle['costoUnitario'], 2), 1, 0, 'C'); // C.U. de salida
                $pdf->Cell(20, 10, number_format($detalle['salida'] * $detalle['costoUnitario'], 2), 1, 0, 'C'); // Total de salida
                $salidasCantidad += $detalle['salida'];
                $salidasTotal += $detalle['salida'] * $detalle['costoUnitario'];
            } else {
                $pdf->Cell(20, 10, '', 1, 0, 'C'); // Celda vacía
                $pdf->Cell(20, 10, '', 1, 0, 'C'); // Celda vacía
                $pdf->Cell(20, 10, '', 1, 0, 'C'); // Celda vacía
            }
            
            if ($detalle['tipo'] == 1){

                $saldoCant += $detalle['entrada'];
                $saldoTot += $detalle['entrada'] * $detalle['costoUnitario'];
                $saldoUnit = $saldoTot / $saldoCant;



                $pdf->Cell(20, 10, number_format($saldoCant, 2), 1, 0, 'C');
                $pdf->Cell(20, 10, number_format($saldoUnit, 2), 1, 0, 'C'); // C.U. para cálculo
                $pdf->Cell(20, 10, number_format($saldoTot, 2), 1, 1, 'C'); 
            }
            elseif ($detalle['tipo'] == 2){

                $saldoCant -= $detalle['salida'];
                $saldoTot -= $detalle['salida'] * $detalle['costoUnitario'];
                $saldoUnit = ($saldoCant != 0) ? $saldoTot / $saldoCant : 0;
                $pdf->Cell(20, 10, number_format($saldoCant, 2), 1, 0, 'C');
                $pdf->Cell(20, 10, number_format($saldoUnit, 2), 1, 0, 'C'); // C.U. para cálculo
                $pdf->Cell(20, 10, number_format($saldoTot, 2), 1, 1, 'C'); 
            }
 // Saldo
        
        
            $i++;
        }
        
        //FINAL

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(10, 10, '', 1, 0, 'C');
        $pdf->Cell(25, 10, '', 1, 0, 'C');
        $pdf->Cell(65, 10, 'TOTALES', 1, 0, 'C');
        $pdf->Cell(20, 10, number_format($entradasCantidad, 2), 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, number_format($entradasTotal, 2), 1, 0, 'C');
        
        // Subcabecera de Salidas
        $pdf->Cell(20, 10, number_format($salidasCantidad, 2), 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, number_format($salidasTotal, 2), 1, 0, 'C');
        
        // Subcabecera de Saldos
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 1, 'C');
        // Descargar el PDF
        //$pdf->Output('kardex.pdf', 'D');
        # Nombre del archivo PDF #
        /* $pdf->Output("I","Ticket_Nro_1.pdf",true); */
                // Generar el contenido del PDF
        $pdfContent = $pdf->Output("S");
        
        // Generar la respuesta con el contenido del PDF
        $response = Response::make($pdfContent);
        $response->header('Content-Type', 'application/pdf');
        $response->header('Content-Disposition', 'inline; filename="'.$anio."-".$mes." ".$producto->nombre.".pdf");

        return $response;
       
    }

        
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:1,2',
            'producto_id' => 'required|integer|min:1',
            'fecha' => 'nullable|date',
            'cantidad' => 'required|numeric|min:0',
            'costoUnitario' => 'nullable|numeric|min:0',
            'motivo' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
   
        $inventario = new Inventario();
        $inventario->tipo = $request->tipo;
        $inventario->producto_id = $request->input('producto_id');
        $inventario->fecha = $request->fecha ? $request->fecha . ' ' . now()->format('H:i:s') : now();
        $inventario->cantidad = $request->input('cantidad', 0);
        $inventario->motivo = $request->input('motivo', null);

        $inventario->costoUnitario = $request->input('costoUnitario', 0);
        $inventario->save();

        $producto = Producto::find($request->input('producto_id'));

        if ($inventario->tipo == 1){
            $producto->stock += $inventario->cantidad;
        }
        elseif ($inventario->tipo == 2){
            $producto->stock -= $inventario->cantidad;
        }

        $producto->save();

        return $inventario;

    }
    
    
    
    
    
    
    /**
     * Display the specified resource.
     */
    public function show(Inventario $inventario)
    {
        return response()->json(['message' => 'Método no implementado, usar kardex']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventario $inventario)
    {
        return response()->json(['message' => 'Método no implementado, usar metodo post para corregir']);
    }

    /**
     * Remove the specified resource from storage.
     */

     public function destroy(Inventario $inventario)
     {
        return response()->json(['message' => 'No se puede eliminar un inventario, si desea corregir debe de agregar una entrada o salida y justificar el motivo'],409);
     }
     
     
    
}
