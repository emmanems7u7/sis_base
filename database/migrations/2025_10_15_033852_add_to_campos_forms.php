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
        Schema::table('campos_forms', function (Blueprint $table) {
            $table->unsignedBigInteger('form_ref_id')->nullable()->after('form_id');
            $table->foreign('form_ref_id')->references('id')->on('formularios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campos_forms', function (Blueprint $table) {
            //
        });
    }
};
