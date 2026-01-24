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
        Schema::create('campos_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->nullable()->constrained('formularios')->onDelete('cascade');

            $table->string('nombre');
            $table->string('etiqueta');

            $table->string('tipo', 50)->nullable()->comment('Clave foránea a catalogos.catalogo_codigo');
            $table->foreign('tipo')->references('catalogo_codigo')->on('catalogos')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            //$table->enum('tipo', ['text', 'number', 'checkbox', 'radio', 'textarea', 'select', 'date']);

            $table->integer('posicion')->default(0);
            $table->boolean('requerido')->default(false);

            $table->foreignId('categoria_id')
                ->nullable()
                ->constrained('categorias')
                ->onDelete('cascade');

            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campos_forms');
    }
};
