<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;
    protected $casts = [
        'montoTotal' => 'float',
    ];
    public function CompraInventarios(){
        return $this->hasMany(CompraInventario::class);
    }

    public function Caja(){
        return $this->belongsTo(Caja::class);
    }
}
