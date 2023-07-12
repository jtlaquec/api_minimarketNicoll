<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    public function detallePermisos()
    {
        return $this->hasMany(DetallePermiso::class, 'role_id');
    }
    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'detalle_permisos', 'role_id', 'permiso_id')
        ->as('detalle')
        ->withPivot('id', 'estado');
    }
}
