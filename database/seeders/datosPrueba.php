<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class datosPrueba extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
                
        DB::table('series')->insert([
            [
                'series' => 'B001',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('categorias')->insert([
            [
                'nombre' => 'Alimentos y Despensas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Bebidas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Cervezas, Vinos y Licores',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Dulces y Botanas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Mascotas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Hogar y Limpieza',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Higiene y Cuidado Personal',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Farmacia',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Bebes',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('marcas')->insert([
            [
                'nombre' => 'Gloria',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('marcas')->insert([
            [
                'nombre' => 'Nestle',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('medidas')->insert([
            [
                'codigo' => 'UND',
                'nombre' => 'Unidades',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'GRM',
                'nombre' => 'Gramos',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'KGM',
                'nombre' => 'Kilogramos',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'LTR',
                'nombre' => 'Litros',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'CA',
                'nombre' => 'Latas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'BX',
                'nombre' => 'Cajas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo' => 'MIL',
                'nombre' => 'Millares',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('permisos')->insert([
            [
                'nombre' => 'Productos',
                'descripcion' => 'Acceso al módulo Productos (incluido Categoría, Marca, Unidades)',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Ventas',
                'descripcion' => 'Acceso al módulo Ventas (Nueva Venta, Ver Ventas)',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Compras',
                'descripcion' => 'Acceso al módulo Compras (Nueva Compra, Ver Compras)',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Inventarios',
                'descripcion' => 'Acceso al módulo Inventarios',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Cajas',
                'descripcion' => 'Acceso al módulo Cajas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Usuarios',
                'descripcion' => 'Acceso al módulo Usuarios',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);


        DB::table('roles')->insert([
            [
                'nombre' => 'Administrador',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Vendedor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('productos')->insert([
            [
                'nombre' => 'Producto 1',
                'descripcion' => 'Soy un producto de prueba',
                'marca_id' => 1,
                'medida_id' => 1,
                'categoria_id' => 1,
                'precioVenta' => 10,
                'stock' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Producto 2',
                'descripcion' => 'Soy un producto de prueba',
                'marca_id' => 1,
                'medida_id' => 1,
                'categoria_id' => 1,
                'precioVenta' => 20,
                'stock' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Producto 3',
                'descripcion' => 'Soy un producto de prueba',
                'marca_id' => 1,
                'medida_id' => 1,
                'categoria_id' => 1,
                'precioVenta' => 30,
                'stock' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Producto 4',
                'descripcion' => 'Soy un producto de prueba',
                'marca_id' => 1,
                'medida_id' => 1,
                'categoria_id' => 1,
                'precioVenta' => 40,
                'stock' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('users')->insert([
            [
                'dni' => '77000000',
                'nombre' => 'Sofia Nicoll Copari Mendoza',
                'celular' => '940451245',
                'password' => '$2y$10$wnkHE7Zn.bFTQ86sVbp8M.m9l2W0AAE5/OJWi/9xk2s4.06gdLhiG',
                'role_id' => 1,
                'estado' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'dni' => '00000000',
                'nombre' => 'Alexander Cairo',
                'celular' => '930457895',
                'password' => '$2y$10$wnkHE7Zn.bFTQ86sVbp8M.m9l2W0AAE5/OJWi/9xk2s4.06gdLhiG',
                'role_id' => 2,
                'estado' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        
        ]);


        DB::table('cajas')->insert([
            [
                'tipo' => '2',
                'monto' => 4000,
                'motivo' => 'Por la inversión inicial',
                'fecha' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('inventarios')->insert([
            [
                'tipo' => '1',
                'producto_id' => 1,
                'fecha' => $now,
                'cantidad' => 10,
                'costoUnitario' => 1,
                'motivo' => 'Por el inventario inicial',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo' => '1',
                'producto_id' => 2,
                'fecha' => $now,
                'cantidad' => 20,
                'costoUnitario' => 2,
                'motivo' => 'Por el inventario inicial',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo' => '1',
                'producto_id' => 3,
                'fecha' => $now,
                'cantidad' => 30,
                'costoUnitario' => 3,
                'motivo' => 'Por el inventario inicial',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo' => '1',
                'producto_id' => 4,
                'fecha' => $now,
                'cantidad' => 40,
                'costoUnitario' => 4,
                'motivo' => 'Por el inventario inicial',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);






    }
}
