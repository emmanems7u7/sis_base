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
        Schema::create('form_logic_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('form_logic_rules')->onDelete('cascade');
            $table->foreignId('form_ref_id')->constrained('formularios')->onDelete('cascade'); // Formulario destino
            $table->foreignId('campo_ref_id')->nullable()->constrained('campos_forms')->onDelete('set null'); // Campo a modificar
            $table->enum('operacion', ['sumar', 'restar', 'actualizar', 'copiar', 'asignar']);
            $table->string('valor')->nullable(); // Puede ser un número o nombre de campo origen
            $table->json('parametros')->nullable(); // Ej: {"campo_origen":"cantidad_vendida"}
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_logic_actions');
    }
};
