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
        Schema::create('modulo_formulario_paralelos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('modulo_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->json('formularios')->nullable();

            $table->string('grupo')->nullable();

            $table->json('config')->nullable();

            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modulo_formulario_paralelos');
    }
};
