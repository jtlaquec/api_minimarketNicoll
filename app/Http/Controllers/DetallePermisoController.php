<?php

namespace App\Http\Controllers;

use App\Models\DetallePermiso;
use Illuminate\Http\Request;

class DetallePermisoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DetallePermiso::with(['Role','Permiso'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return response()->json(['message' => 'No es necesario crear la relación, se crea automáticamente']);
    }

    /**
     * Display the specified resource.
     */
    public function show(DetallePermiso $detallePermiso)
    {
        return $detallePermiso;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DetallePermiso $detallePermiso)
    {
        $detallePermiso->role_id= $request->role_id;
        $detallePermiso->permiso_id= $request->permiso_id;
        $detallePermiso->save();
        return $detallePermiso;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DetallePermiso $detallePermiso)
    {
        $detallePermiso->estado = 0;
        $detallePermiso->save();
        return $detallePermiso;
    }
}
