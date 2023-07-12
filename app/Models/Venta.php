<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;
    protected $casts = [
        'montoPagoCliente' => 'float',
    ];
    public function VentaInventarios(){
        return $this->hasMany(VentaInventario::class);
    }

    public function Comprobante(){
        return $this->belongsTo(Comprobante::class);
    }

    public function Caja(){
        return $this->belongsTo(Caja::class);
    }
}
