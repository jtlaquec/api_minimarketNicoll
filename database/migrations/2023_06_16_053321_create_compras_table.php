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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            /* $table->foreignId('user_id')->nullable()->constrained('users'); */
            $table->text('motivo')->nullable();
            $table->string('tipoComprobante')->nullable();
            $table->string('serieComprobante')->nullable();
            $table->string('numeroComprobante')->nullable();
            $table->string('nombreProveedor')->nullable();
            $table->decimal('montoTotal',8,2)->default(0);
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
