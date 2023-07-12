<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productos = Producto::with(['Marca', 'Medida', 'Categoria'])
        ->where('estado', 1)
        ->get();

        $productos->each(function ($producto) {
            $producto->url_imagen = $producto->UrlImage();
        });

        return $productos;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'marca_id' => 'required|integer|min:1',
            'medida_id' => 'required|integer|min:1',
            'categoria_id' => 'required|integer|min:1',
            'precioVenta' => 'nullable|numeric|min:0',
            'igvSunat' => 'nullable|integer|min:1',
            'file' => 'nullable|image',            
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        $producto = new Producto();
        $producto->nombre = $request->input('nombre');
        $producto->descripcion = $request->input('descripcion', null);
        $producto->marca_id = $request->input('marca_id', null);
        $producto->medida_id = $request->input('medida_id', null);
        $producto->categoria_id = $request->input('categoria_id', null);
        $producto->precioCompra = 0;
        $producto->precioVenta = $request->input('precioVenta', 0);
        $producto->stock = 0;
        $producto->igvSunat = $request->input('igvSunat', 1);
        
        if ($request->hasFile('file')) {
            $file = $request->file('file')->store('public/productos');
            $url = Storage::url($file);
            $producto->patch = $url;
        } else {
            $producto->patch = null;
        }
        
        $producto->save();
        return $producto;
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        $producto->marca = $producto->Marca;
        $producto->medida = $producto->Medida;
        $producto->categoria = $producto->Categoria;
        $producto->url_imagen = $producto->UrlImage();        
        return $producto;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {

        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'marca_id' => 'nullable|integer|min:1',
            'medida_id' => 'nullable|integer|min:1',
            'categoria_id' => 'nullable|integer|min:1',
            'precioVenta' => 'nullable|numeric|min:0',
            'igvSunat' => 'nullable|integer|min:1',
            'file' => 'nullable|image',            
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }


        $producto->nombre = $request->filled('nombre') ? $request->input('nombre') : $producto->nombre;
        $producto->descripcion = $request->filled('descripcion') ? $request->input('descripcion') : $producto->descripcion;
        $producto->marca_id = $request->filled('marca_id') ? $request->input('marca_id') : $producto->marca_id;
        $producto->medida_id = $request->filled('medida_id') ? $request->input('medida_id') : $producto->medida_id;
        $producto->categoria_id = $request->filled('categoria_id') ? $request->input('categoria_id') : $producto->categoria_id;
        $producto->precioVenta = $request->filled('precioVenta') ? $request->input('precioVenta') : $producto->precioVenta;
        $producto->igvSunat = $request->filled('igvSunat') ? $request->input('igvSunat') : $producto->igvSunat;
        
        if ($request->hasFile('file')) {
            // Eliminar el archivo anterior
            $urlAnterior = str_replace('storage','public',$producto->patch);
            
            if (Storage::exists($urlAnterior)){
                Storage::delete($urlAnterior);
            }
            
            // Guardar el nuevo archivo
            $file = $request->file('file')->store('public/productos');
            $url = Storage::url($file);
            $producto->patch = $url;
        }    
        
        $producto->save();
        return $producto;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        
        if (!is_null($producto->patch)) {
            $urlAnterior = str_replace('storage','public',$producto->patch);
            if (Storage::exists($urlAnterior)){
                Storage::delete($urlAnterior);
            }
        }

        $producto->patch = null;
        
        $producto->estado = 0;
        $producto->save();
        return response()->json(['message' => 'El producto ha sido eliminado correctamente']);
    }


    public function listarProductos(){
        $productos = Producto::select('id', 'nombre')
        ->where('estado', 1)
        ->get();

    return $productos;
    }
}
