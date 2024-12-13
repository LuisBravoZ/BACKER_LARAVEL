<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('atencion', function (Blueprint $table) {
        $table->id();
        $table->foreignId('turno_id')->constrained('turno')->onDelete('cascade');
        
        // Campos para los datos de atención
        $table->float('altura');
        $table->float('peso');
        $table->float('cintura');
        $table->float('cadera');
        $table->float('circunferencia_muneca');
        $table->float('circunferencia_cuello');
        $table->float('actividad_fisica');
        
        // Campos para los cálculos
        $table->float('imc');
        $table->float('tmb');
        $table->float('cintura_talla');
        $table->float('cintura_cadera');
        $table->float('porcentaje_grasa'); 
        $table->float('complexion_hueso');
        
        // Campo de observación
        $table->string('observacion')->nullable();
        
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atencion');
    }
};
