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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobantes');
            $table->string('tipoDocumento')->nullable()->default('DNI');
            $table->string('numeroDocumento')->nullable()->default('00000000');
            $table->string('nombreCliente')->nullable()->default('PÚBLICO EN GENERAL');
            $table->decimal('montoPagoCliente',8,2)->default(0);
            $table->text('motivo')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
