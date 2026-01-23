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
        Schema::create('plantilla_correos', function (Blueprint $table) {
            $table->id();


            $table->string('nombre', 100)
                ->comment('Nombre descriptivo de la plantilla');


            $table->string('asunto', 255)
                ->comment('Asunto del correo');

            $table->text('contenido')
                ->comment('Contenido HTML del correo');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantilla_correos');
    }
};
