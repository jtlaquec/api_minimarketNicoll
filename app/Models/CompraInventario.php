<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraInventario extends Model
{
    use HasFactory;

    protected $casts = [
        'precio' => 'float',
        'cantidad' => 'float',
    ];
    public function Inventario(){
        return $this->belongsTo(Inventario::class);
    }
    public function Compra(){
        return $this->belongsTo(Compra::class);
    }

}
