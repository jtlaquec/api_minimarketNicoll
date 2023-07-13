<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\DetallePermiso;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Role::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $role = new Role();
        $role->nombre = $request->nombre;
        $role->save();
        return $role;
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return $role;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $role->nombre = $request->input('nombre');
    
        // Actualizar el estado de los detallePermiso
        if ($request->has('detallePermisos')) {
            $detallePermisos = $request->input('detallePermisos');
            $detalleIds = collect($detallePermisos)->pluck('id');
            $detalles = DetallePermiso::whereIn('id', $detalleIds)->get();
    
            foreach ($detalles as $detalle) {
                $detallePermiso = collect($detallePermisos)->firstWhere('id', $detalle->id);
                $detalle->estado = $detallePermiso['estado'];
                $detalle->save();
            }
        }
    
        $role->save();
    
        return response()->json([
            'message' => 'Rol actualizado correctamente',
            'data' => $role
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->estado = 0;
        $role->save();
        return $role;
    }

    public function listarRoles($idRol)
    {
        $rol = Role::with(['permisos' => function ($query) {
            $query->select('detalle_permisos.id', 'permisos.id', 'permisos.nombre', 'detalle_permisos.estado')
                ->as('detalle');
        }])
            ->where('id', $idRol)
            ->select('id', 'nombre')
            ->first();
    
        return $rol;
    }


    public function actualizarRol(Request $request, $idRol)
    {
        $rol = Role::findOrFail($idRol);
        $rol->nombre = $request->input('nombre');
    
        // Actualizar el estado de los detallePermiso
        if ($request->has('detallePermisos')) {
            $detallePermisos = $request->input('detallePermisos');
            $detalleIds = collect($detallePermisos)->pluck('id');
            $detalles = DetallePermiso::whereIn('id', $detalleIds)->get();
    
            foreach ($detalles as $detalle) {
                $detallePermiso = collect($detallePermisos)->firstWhere('id', $detalle->id);
                $detalle->estado = $detallePermiso['estado'];
                $detalle->save();
            }
        }
    
        $rol->save();
    
        return response()->json([
            'message' => 'Rol actualizado correctamente',
            'data' => $rol
        ], 200);
    }
    







}
