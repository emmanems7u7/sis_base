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
        Schema::create('auditoria_accions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_id')->nullable();
            $table->string('tipo_accion', 20);
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('estado', 20);
            $table->text('mensaje')->nullable();
            $table->json('detalle')->nullable();
            $table->json('errores')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_accions');
    }
};
