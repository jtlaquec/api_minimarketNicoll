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
        Schema::create('compra_inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->nullable()->constrained('inventarios');
            $table->foreignId('compra_id')->nullable()->constrained('compras');
            $table->decimal('precio',8,2)->default(0);
            $table->decimal('cantidad',8,2)->default(0);
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra_inventarios');
    }
};
