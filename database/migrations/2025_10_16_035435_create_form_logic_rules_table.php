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
        Schema::create('form_logic_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('formularios')->onDelete('cascade');
            $table->string('nombre');
            $table->string('evento');
            $table->boolean('activo')->default(true);
            $table->json('parametros')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_logic_rules');
    }
};
