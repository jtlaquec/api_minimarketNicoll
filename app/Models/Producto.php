<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $casts = [
        'precioCompra' => 'float',
        'precioVenta' => 'float',
        'stock' => 'float',
    ];
    public function Marca(){
        return $this->belongsTo(Marca::class);
    }

    public function Medida(){
        return $this->belongsTo(Medida::class);
    }

    public function Categoria(){
        return $this->belongsTo(Categoria::class);
    }

    public function Inventarios(){
        return $this->hasMany(Inventario::class);
    }

    public function UrlImage(){
        return url($this->patch);
    }
    
}
