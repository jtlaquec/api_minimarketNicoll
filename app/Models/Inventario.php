<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;
    protected $casts = [
        'cantidad' => 'float',
        'costoUnitario' => 'float',
    ];

    public function Producto(){
        return $this->belongsTo(Producto::class);
    }
}
