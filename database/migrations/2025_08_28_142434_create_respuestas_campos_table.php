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
        Schema::create('respuestas_campos', function (Blueprint $table) {
            $table->id();

            // Relación con respuestas_form
            $table->unsignedBigInteger('respuesta_id');
            $table->foreign('respuesta_id')
                ->references('id')
                ->on('respuestas_forms')
                ->onDelete('cascade');

            // Relación con campos_form
            $table->unsignedBigInteger('cf_id');
            $table->foreign('cf_id')
                ->references('id')
                ->on('campos_forms')
                ->onDelete('cascade');

            $table->text('valor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuestas_campos');
    }
};
