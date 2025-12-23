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
            // Primero eliminar la foreign key
            $table->dropForeign(['form_ref_id']);

            // Hacer el campo nullable
            $table->foreignId('form_ref_id')
                ->nullable()
                ->change();

            // Volver a crear la foreign key
            $table->foreign('form_ref_id')
                ->references('id')
                ->on('formularios')
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
