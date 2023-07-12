<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PermisoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Permiso::where('estado',1)->get();

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'nombre' => ['required', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/'],

            ]);
        
            $validator->validate();
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], $e->status);
        }
        $permiso = new Permiso();
        $permiso->nombre = $request->nombre;
        $permiso->save();
        return $permiso;
    }

    /**
     * Display the specified resource.
     */
    public function show(Permiso $permiso)
    {
        return $permiso;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permiso $permiso)
    {
        $permiso->nombre = $request->nombre;
        $permiso->save();
        return $permiso;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permiso $permiso)
    {
        $permiso->estado = 0;
        $permiso->save();
        return $permiso;
    }
}
