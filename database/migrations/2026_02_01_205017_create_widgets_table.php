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
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulario_id')->nullable()->constrained('formularios')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('nombre');

            $table->string('tipo', 50)->comment('Especifica el tipo de widget')->nullable();

            $table->foreign('tipo')->references('catalogo_codigo')->on('catalogos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->json('configuracion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
