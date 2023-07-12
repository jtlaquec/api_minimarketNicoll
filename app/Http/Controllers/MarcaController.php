<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /* return Marca::all(); */
        return Marca::where('estado', 1)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
                
        $marca = new Marca();
        $marca->nombre = $request->input('nombre', null);
        $marca->save();
        return $marca;
    }

    /**
     * Display the specified resource.
     */
    public function show(Marca $marca)
    {
        return $marca;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Marca $marca)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        $marca->nombre = $request->filled('nombre') ? $request->input('nombre') : $marca->nombre;
        $marca->save();
        return $marca;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Marca $marca)
    {
        $marca->estado = 0;
        $marca->save();
        return response()->json(['message' => 'La marca ha sido eliminada correctamente'],200);
    }
}
