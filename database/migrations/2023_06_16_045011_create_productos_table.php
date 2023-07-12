<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('marca_id')->nullable()->constrained('marcas');
            $table->foreignId('medida_id')->nullable()->constrained('medidas');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->decimal('precioCompra',8,2)->nullable()->default(0);
            $table->decimal('precioVenta',8,2)->nullable()->default(0);
            $table->decimal('stock',8,2)->nullable()->default(0);
            $table->integer('igvSunat')->default(1);
            $table->text('patch')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
