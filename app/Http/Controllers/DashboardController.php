<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function info()
    {
        $productos = Producto::where('estado', 1)->count();
        $usuarios = User::where('estado', 1)->count();
        
        // Sumatoria de ventas históricas
        $totalVentasHistorico = Venta::where('estado', 1)->sum('montoPagoCliente');
        
        // Sumatoria de compras históricas
        $totalComprasHistorico = Compra::where('estado', 1)->sum('montoTotal');
        
        // Sumatoria de ventas del mes actual
        $totalVentasMes = Venta::where('estado', 1)
            ->whereMonth('created_at', now()->month)
            ->sum('montoPagoCliente');
        
        // Sumatoria de ventas del día actual
        $totalVentasDia = Venta::where('estado', 1)
            ->whereDate('created_at', now()->toDateString())
            ->sum('montoPagoCliente');
        
        // Sumatoria de compras del día actual
        $totalComprasDia = Compra::where('estado', 1)
            ->whereDate('created_at', now()->toDateString())
            ->sum('montoTotal');
        
        $totalComprasMes = Compra::where('estado', 1)
            ->whereMonth('created_at', now()->month)
            ->sum('montoTotal');
        
            

        return [
            "productos" => $productos,
            "usuarios" => $usuarios,
            "totalVentasHistorico" => $totalVentasHistorico,
            "totalComprasHistorico" => $totalComprasHistorico,
            "totalVentasMes" => $totalVentasMes,
            "totalComprasMes" => $totalComprasMes,
            "totalVentasDia" => $totalVentasDia,
            "totalComprasDia" => $totalComprasDia,
            "meses"=>$this->ventasMensual(),

            
        ];
    }

    public function ventasMensual(){
        $ventas = Venta::select(
            DB::raw('sum(montoPagoCliente) as Total'),
            DB::raw("DATE_FORMAT(created_at, '%M %Y' ) as mes")
        )->where('estado',1)
            ->groupBy('mes')
            ->get();
            return $ventas;
    }
    


}
