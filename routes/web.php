<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
//Route::put('roles/{role}', 'RoleController@update');
Route::group(['prefix'=>'api'],function(){

    Route::apiResource('cajas', 'CajaController');
    Route::apiResource('categorias', 'CategoriaController');
    Route::apiResource('compras', 'CompraController');
    Route::apiResource('detallePermisos', 'DetallePermisoController');
    Route::apiResource('inventarios', 'InventarioController');
    Route::get('inventarios/kardex/{producto}', 'InventarioController@kardex');
    Route::apiResource('marcas', 'MarcaController');
    Route::apiResource('medidas', 'MedidaController');
    Route::apiResource('permisos', 'PermisoController');
    Route::apiResource('productos', 'ProductoController');
    Route::apiResource('roles', 'RoleController');
    Route::apiResource('users', 'UserController');
    Route::apiResource('ventas', 'VentaController');
});

Route::get('/reports/ticket', function () {
    return view('reports.ticket');


Route::get('api/storage/{path}', function ($path) {
        $filePath = storage_path('app/public/' . $path);
    
        if (file_exists($filePath)) {
            return response()->file($filePath);
        } else {
            abort(404);
        }
    })->where('path', '(.*)');



});







Route::get('/', function () {
    return view('welcome');
});
