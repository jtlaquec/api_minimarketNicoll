<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $casts = [
        'monto' => 'float',
    ];
    public function Venta(){
        return $this->belongsTo(Venta::class);
    }
    public function Compra(){
        return $this->belongsTo(Compra::class);
    }
}
