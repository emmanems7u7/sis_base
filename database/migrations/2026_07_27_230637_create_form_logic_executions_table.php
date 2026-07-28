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
        Schema::create('form_logic_executions', function (Blueprint $table) {

            $table->id();


            $table->foreignId('rule_id')->constrained('form_logic_rules')->cascadeOnDelete();
            $table->string('estado')->default('pendiente')->comment('pendiente, ejecutando, completado, error');
            $table->timestamp('inicio')->nullable();
            $table->timestamp('fin')->nullable();
            $table->integer('registros_afectados')->default(0);
            $table->text('mensaje')->nullable();
            $table->longText('error')->nullable();
            $table->json('resultado')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_logic_executions');
    }
};
