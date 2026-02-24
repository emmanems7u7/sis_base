<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('respuestas_grupos_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('respuestas_grupos')->onDelete('cascade');
            $table->foreignId('respuesta_id')->constrained('respuestas_forms')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['grupo_id', 'respuesta_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuestas_grupos_detalle');
    }
};
