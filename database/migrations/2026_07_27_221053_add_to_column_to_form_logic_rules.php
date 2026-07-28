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
        Schema::table('form_logic_rules', function (Blueprint $table) {
            $table->dropForeign(['form_id']);

            $table->foreignId('form_id')
                ->nullable()
                ->change();

            $table->foreign('form_id')
                ->references('id')
                ->on('formularios')
                ->onDelete('cascade');

            $table->foreignId('modulo_id')
                ->nullable()
                ->after('form_id');

            $table->foreign('modulo_id')
                ->references('id')
                ->on('modulos')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_logic_rules', function (Blueprint $table) {
            //
        });
    }
};
