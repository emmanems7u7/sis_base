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
        Schema::create('configuracion_credenciales', function (Blueprint $table) {
            $table->id();
            $table->integer('conf_long_min');
            $table->integer('conf_long_max');
            $table->integer('conf_req_upper');
            $table->integer('conf_req_num');
            $table->integer('conf_req_esp');
            $table->integer('conf_duracion_min');
            $table->integer('conf_duracion_max');
            $table->integer('conf_tiempo_bloqueo');
            $table->string('conf_defecto', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_credenciales');
    }
};
