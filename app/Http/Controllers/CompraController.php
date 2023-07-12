<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Caja;
use App\Models\Compra;
use App\Models\CompraInventario;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $compra = Compra::where('estado',1)->get();
        $list = [];
        foreach($compra as $m){
            $list[] = $this->show($m);
        }
        return $list;
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'tipoComprobante' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
                'serieComprobante' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
                'numeroComprobante' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
                'nombreProveedor' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
                'carrito' => ['required', 'array'],
                'carrito.*' => ['array'],
                'carrito.*.producto.id' => ['required', 'exists:productos,id'],
                'carrito.*.cantidad' => ['required','numeric','min:0',],
                'carrito.*.precio' => ['required', 'numeric', 'min:0'],
            ]);
        
            $validator->validate();
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], $e->status);
        }


             
        $compra = new Compra();
        $total = 0;

        $compra->tipoComprobante = $request->input('tipoComprobante','-');
        $compra->serieComprobante = $request->input('serieComprobante','-');
        $compra->numeroComprobante = $request->input('numeroComprobante','-');
        $compra->nombreProveedor = $request->input('nombreProveedor','Genérico');
        $motivo = "Por la compra ".$request->input('tipoComprobante','-')." ".$request->input('serieComprobante','-')."-".$request->input('numeroComprobante','-');
        $compra->motivo = $motivo;
        $compra->save();

        foreach($request->carrito as $m){
            $producto = $m['producto'];
            $inventario = new Inventario();
            $inventario->producto_id = $producto['id'];
            $inventario->tipo = 1; 
            $inventario->costoUnitario = $m['precio'];
            $inventario->cantidad = $m['cantidad'];
            $inventario->motivo = $motivo;

            $inventario->save();
            $compraInventario = new CompraInventario();
            $compraInventario->inventario_id = $inventario->id;
            $compraInventario->compra_id = $compra->id;
            $compraInventario->cantidad = $m['cantidad'];
            $compraInventario->precio = $m['precio'];
            $compraInventario->save();

            $producto = Producto::find($producto['id']);
            // Modificar stock y costo promedio de la tabla producto
            $producto->stock += $m['cantidad'];
            $producto->save();
            $cantidad = $m['cantidad'];
            $precio = $m['precio'];
            $subtotal = $cantidad * $precio;
            $total += $subtotal;
                    
        }
  

        $compra->montoTotal = $total;
        $compra->save();
        $caja = new Caja();
        $caja->tipo = 1;
        $caja->fecha = $compra->created_at;
        $caja->compra_id = $compra->id;
        $caja->monto = $compra->montoTotal;
        $caja->motivo = $compra->motivo;
        $caja->save();

        return $compra;
    }
    /**
     * Display the specified resource.
     */
    public function show(Compra $compra)
    {
        $compra->compra_inventarios = $compra->CompraInventarios()->with(['Inventario'=>function($i){
            $i->with(['Producto'=>function($a){
                $a->with(['Marca','Categoria','Medida']);
            }]);
        }])->get();
        $compra->fecha = $compra->created_at->format('Y-m-d');
        return $compra;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Compra $compra)
    {
        return response()->json(['message' => 'Método no implementado']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Compra $compra)
    {
        $compra->estado = 0;
        $compra->save();


        $caja = $compra->Caja;
        $caja->estado = 0;

    
        // Obtener los registros de compra_inventarios relacionados con la compra
        $compraInventarios = $compra->CompraInventarios;
    
        foreach ($compraInventarios as $compraInventario) {
            $compraInventario->estado = 0;
            $compraInventario->save();
            // Obtener el inventario relacionado
            $inventario = $compraInventario->Inventario;
            $inventario->estado = 0;
            $inventario->save();  
        }
        return response()->json(['message' => 'Compra eliminada']);
    }
}
