<?php

namespace App\Http\Controllers;

use App\Models\Medida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /* return ["medidas"]; */
        return Medida::where('estado', 1)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
            'nombre' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        
        
        $Medida = new Medida();
        $Medida->codigo = $request->codigo;
        $Medida->nombre = $request->nombre;
        $Medida->save();
        return $Medida;
    }

    /**
     * Display the specified resource.
     */
    public function show(Medida $medida)
    {
        return $medida;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Medida $medida)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
            'nombre' => 'nullable|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $medida->codigo = $request->filled('codigo') ? $request->input('codigo') : $medida->codigo;
        $medida->nombre = $request->filled('nombre') ? $request->input('nombre') : $medida->nombre;
        $medida->save();
        return $medida;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medida $medida)
    {
        $medida->estado = 0;
        $medida->save();
        return response()->json(['message' => 'La medida ha sido eliminada correctamente'],200);
    }
}
