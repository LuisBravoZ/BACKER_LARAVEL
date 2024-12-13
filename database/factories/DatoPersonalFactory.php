<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\comentario>
 */
class DatoPersonalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Asignar un usuario único a cada dato personal
            'usuario_id' => fake()->unique()->randomElement(Usuario::pluck('id')->toArray()),            
            'ci' => $this->faker->unique()->randomNumber(8), // Generar un CI único
            'telefono' => $this->faker->numerify('####'),
            'fecha_nacimiento' => $this->faker->date(),
            'ciudad' => $this->faker->city(),
            'sexo' => $this->faker->randomElement(['Masculino', 'Femenino'])
        ];
    }
}


