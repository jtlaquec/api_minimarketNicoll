<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/login', 'UserController@login');
Route::get('/reportes/ventas/{venta}', 'VentaController@pdf');
Route::get('/reportes/kardex/{producto}/{anio}/{mes}', 'InventarioController@kardexMensualPdf');

Route::get('/kardex/{producto}/{anio}/{mes}/{dia}', 'InventarioController@kardexDia');
Route::get('/kardex/{producto}/{anio}/{mes}', 'InventarioController@kardexMensual');
Route::get('/kardex/{producto}/{anio}', 'InventarioController@kardexAnual');
Route::get('/kardex/{producto}', 'InventarioController@kardexActual');

Route::get('/cajas/{anio}/{mes}/{dia}', 'CajaController@CajaReporteDia');
Route::get('/cajas/{anio}/{mes}', 'CajaController@CajaReporteMensual');
Route::get('/cajas/{anio}', 'CajaController@CajaReporteAnual');

Route::get('/dashboard', 'DashboardController@info');

Route::get('/productos/listarProductos', 'ProductoController@listarProductos');
Route::get('/roles/listarRoles/{idRol}', 'RoleController@listarRoles');

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */
