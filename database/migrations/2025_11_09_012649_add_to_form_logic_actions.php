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
            $table->string('tipo_accion', 50)->after('tipo_valor')->comment('Especifica el tipo de Accion por catalogo')->nullable();

            $table->foreign('tipo_accion')->references('catalogo_codigo')->on('catalogos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
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
