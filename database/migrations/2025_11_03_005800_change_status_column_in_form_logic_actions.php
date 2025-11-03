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
        Schema::table('form_logic_actions', function (Blueprint $table) {
            $table->string('operacion')->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_logic_actions', function (Blueprint $table) {
            $table->enum('operacion', ['sumar', 'restar', 'actualizar', 'copiar', 'asignar']);

        });
    }
};
