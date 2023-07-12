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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->integer('tipo');
            $table->timestamp('fecha')->useCurrent()->nullable();
            $table->foreignId('venta_id')->nullable()->constrained('ventas');
            $table->foreignId('compra_id')->nullable()->constrained('compras');
            $table->decimal('monto',8,2)->default(0);
            $table->string('motivo')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
