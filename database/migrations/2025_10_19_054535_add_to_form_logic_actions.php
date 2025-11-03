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
            $table->string('tipo_valor')->default('static')->after('valor')->comment('Indica si el valor es fijo (static) o proviene de un campo del formulario de origen (campo)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_logic_actions', function (Blueprint $table) {
            //
        });
    }
};
