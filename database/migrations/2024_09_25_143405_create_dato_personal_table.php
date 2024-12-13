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
        Schema::create('dato_personal', function (Blueprint $table) {
            $table->id();
            $table->string('ci')->unique(); //La cédula de identidad debe ser única
            $table->string('telefono'); //El número de teléfono se guarda en string para permitir ceros a la izquierda
            $table->date('fecha_nacimiento'); //La fecha de nacimiento se guarda para calcular la edad de manera dinámica
            $table->string('ciudad'); 
            $table->enum('sexo', ['Masculino', 'Femenino']); //La columna "sexo" solamente acepta dos valores: "Masculino" y "Femenino"
            $table->foreignId('usuario_id')->constrained('usuario')->onDelete('cascade'); //Clave foránea que hace referencia al usuario
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dato_personal');
    }
};
