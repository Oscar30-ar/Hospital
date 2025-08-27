
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
        Schema::create('especialidades_doctores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_especialidad')->constrained('especialidades')->onDelete('cascade');
            $table->foreignId('id_doctor')->constrained('doctores')->onDelete('cascade');
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialidades_doctores');
    }
};
