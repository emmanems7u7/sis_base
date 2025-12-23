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

            $table->string('archivo', 150)
                ->comment('Nombre del archivo blade sin extensión');

            $table->boolean('estado')
                ->default(true)
                ->comment('1 = activo, 0 = inactivo');

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
