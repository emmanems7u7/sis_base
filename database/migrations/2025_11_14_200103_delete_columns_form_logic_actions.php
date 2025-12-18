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
            // Eliminamos columnas que ya no se usarán directamente
            if (Schema::hasColumn('form_logic_actions', 'campo_ref_id')) {
                $table->dropForeign(['campo_ref_id']);
                $table->dropColumn('campo_ref_id');
            }

            if (Schema::hasColumn('form_logic_actions', 'operacion')) {
                $table->dropColumn('operacion');
            }

            if (Schema::hasColumn('form_logic_actions', 'valor')) {
                $table->dropColumn('valor');
            }

            // Agregamos columna para tipo de acción si no existe
            if (!Schema::hasColumn('form_logic_actions', 'tipo_accion')) {
                $table->string('tipo_accion')->after('form_ref_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_logic_actions', function (Blueprint $table) {
            // Restaurar columnas eliminadas si se hace rollback
            if (!Schema::hasColumn('form_logic_actions', 'campo_ref_id')) {
                $table->foreignId('campo_ref_id')->nullable()->constrained('campos_forms')->onDelete('set null');
            }

            if (!Schema::hasColumn('form_logic_actions', 'operacion')) {
                $table->enum('operacion', ['sumar', 'restar', 'actualizar', 'copiar', 'asignar'])->nullable();
            }

            if (!Schema::hasColumn('form_logic_actions', 'valor')) {
                $table->string('valor')->nullable();
            }

            if (!Schema::hasColumn('form_logic_actions', 'tipo_accion')) {
                $table->string('tipo_accion')->after('form_ref_id');
            }
        });
    }
};
