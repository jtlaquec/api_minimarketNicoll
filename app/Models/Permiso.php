<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    use HasFactory;
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'detalle_permisos', 'permiso_id', 'role_id');
    }

}
