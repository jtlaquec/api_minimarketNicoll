<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\LoginFormRequest;
use Illuminate\Http\Request;
use App\Models\DetallePermiso;
use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::with('role')
        ->get();

        $usuarios->each(function ($usuario) {
            $usuario->nombreRol = $usuario->role->nombre;
            unset($usuario->role); // Elimina la relación "role" del resultado para evitar redundancia
        });

        return $usuarios;

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'dni' => ['required', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/'],
                'nombre' => ['required', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/'],
                'celular' => ['nullable','regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/'],
                'password' => ['required', 'string'],
                'role_id' => ['required', 'integer', 'min:1'],
                'estado' => ['nullable', 'integer', 'min:0'],
            ]);
        
            $validator->validate();
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], $e->status);
        }


        $user = new User();
        $user->dni = $request->input('dni');
        $user->nombre = $request->input('nombre');
        $user->celular = $request->input('celular', null);
        $user->password = Hash::make($request->password);
        $user->role_id = $request->input('role_id');
        $user->estado = $request->input('estado', 1);
        $user->save();
        return $user;
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {


        try {
            $validator = Validator::make($request->all(), [
                'dni' => ['nullable', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/'],
                'nombre' => ['nullable', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/'],
                'celular' => ['nullable','regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/'],
                'password' => ['nullable', 'string'],
                'role_id' => ['nullable', 'integer', 'min:1'],
                'estado' => ['nullable', 'integer', 'min:0'],
            ]);
        
            $validator->validate();
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], $e->status);
        }

        $user->dni = $request->filled('dni') ? $request->input('dni') : $user->dni;
        $user->nombre = $request->filled('nombre') ? $request->input('nombre') : $user->nombre;
        $user->celular = $request->filled('celular') ? $request->input('celular') : $user->celular;
        $user->password = $request->filled('password') ? Hash::make($request->password) : $user->password;
        $user->role_id = $request->filled('role_id') ? $request->input('role_id') : $user->role_id;
        $user->estado = $request->filled('estado') ? $request->input('estado') : $user->estado;
        $user->save();
        return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->estado = 0;
        $user->save();
        return $user;
    }


    public function login(LoginFormRequest $request)
    {
        $validator = Validator::make($request->all(), $request->rules(), $request->messages());
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        $credentials = $request->only('dni', 'password');
        $user = User::where('dni', $credentials['dni'])->first();
    


        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
    
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Contraseña incorrecta'], 401);
        }
        
        if ($user->estado == 0) {
            return response()->json(['message' => 'El usuario está desactivado'], 403);
        }
        // Autenticación exitosa, obtener los datos del usuario
        $user = $user->load('role.detallePermisos.permiso');
    
        // Construir la respuesta con los datos necesarios
        $response = [
            'id' => $user->id,
            'dni' => $user->dni,
            'nombre' => $user->nombre,
            'celular' => $user->celular,
            'rol' => $user->role->nombre,
            'role_id' => $user->role_id,
            'permisos' => [],
        ];
    
        foreach ($user->role->detallePermisos as $detallePermiso) {
            $permiso = $detallePermiso->permiso;
            $response['permisos'][$permiso->nombre] = $detallePermiso->estado;
        }
    
        return $response;
    }
    
    
    
    
    
}
