<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{

    public function index() // método get
    {
        return Categoria::where('estado', 1)->get();
    }

    public function store(Request $request) // método post
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
            'descripcion' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        $nombre = $request->input('nombre',null);
        $descripcion = $request->input('descripcion',null);
    
        $categoria = Categoria::firstOrNew(['nombre' => $nombre]);
    
        if ($categoria->exists && $categoria->estado == 0) {
            $categoria->estado = 1;
            $categoria->descripcion = $descripcion;
            $categoria->save();
        } elseif (!$categoria->exists) {
            $categoria->descripcion = $descripcion;
            $categoria->save();
        }
    
        return response()->json(['message' => 'La categoría ha sido agregada correctamente'],200);
    }

    public function show(Categoria $categoria) //metodo get específico
    {
        return $categoria;
    }


    public function update(Request $request, Categoria $categoria) //método put (actualizar)
    {
        
        $validator = Validator::make($request->all(), [
            'nombre' =>'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
            'descripcion' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }   
        
        $nombre = $request->filled('nombre') ? $request->input('nombre') : $categoria->nombre;
        $descripcion = $request->filled('descripcion') ? $request->input('descripcion') : $categoria->descripcion;
        
        $categoria1 = Categoria::firstOrNew(['nombre' => $nombre]);

        if ($categoria->exists && $categoria->estado == 0) {
            $categoria->estado = 0;
            $categoria1->estado = 1;
            $categoria->descripcion = $descripcion;
            $categoria->save();
            $categoria1->save();
        } elseif (!$categoria->exists) {
            $categoria->nombre = $nombre;
            $categoria->descripcion = $descripcion;
            $categoria->save();
        }
    
        return response()->json(['message' => 'La categoría ha sido modificada correctamente'],200);
    }

    public function destroy(Categoria $categoria) // método delete
    {
        $categoria->estado = 0;
        $categoria->save();
        return response()->json(['message' => 'La categoría ha sido eliminada correctamente'],200);
    }
}
