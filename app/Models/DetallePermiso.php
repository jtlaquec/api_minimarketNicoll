<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePermiso extends Model
{
    use HasFactory;

    protected $table = 'detalle_permisos';

    public function permiso()
    {
        return $this->belongsTo(Permiso::class, 'permiso_id');
    }
    
}
