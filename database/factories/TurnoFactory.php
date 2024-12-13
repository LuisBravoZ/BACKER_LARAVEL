<?php

namespace Database\Factories;

use App\Models\Estado;
use App\Models\Usuario;
use App\Models\Turno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Turno>
 */
class TurnoFactory extends Factory
{
    protected $model = Turno::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obtener un estado aleatorio
        $estado = Estado::inRandomOrder()->first();

        // Asignar paciente según el estado
        $pacienteId = null;

        // Asignamos paciente si el estado es "Reservado", "Terminado" o "Cancelado"
        if (in_array($estado->nombre_estado, ['Reservado', 'Terminado', 'Cancelado'])) {
            $pacienteId = Usuario::where('rol_id', 3)->inRandomOrder()->value('id');
        }

        return [
            // Asignar paciente si el estado es "Reservado", "Terminado" o "Cancelado"
            'paciente_id' => $pacienteId,

            // Asignar un nutricionista
            'nutricionista_id' => Usuario::where('rol_id', 2)->inRandomOrder()->value('id'),

            // Asignar el estado
            'estado_id' => $estado->id,

            'fecha' => $this->faker->date(),
            'hora' => $this->faker->time(),
        ];
    }
}
