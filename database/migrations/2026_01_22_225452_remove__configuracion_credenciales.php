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
        Schema::table('configuracion_credenciales', function (Blueprint $table) {
            if (Schema::hasColumn('configuracion_credenciales', 'accion_usuario')) {
                $table->dropColumn('accion_usuario');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
