<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_recepcionista')->nullable()->after('id_doctor');
            $table->foreign('id_recepcionista')->references('id')->on('recepcionistas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['id_recepcionista']);
            $table->dropColumn('id_recepcionista');
        });
    }
};
