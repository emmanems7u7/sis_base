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
        Schema::table('widgets', function (Blueprint $table) {
            $table->foreignId('formulario_id')->after('id')->nullable()->constrained('formularios')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('modulo_id')->after('formulario_id')->nullable()->constrained('modulos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('widgets', function (Blueprint $table) {
            //
        });
    }
};
