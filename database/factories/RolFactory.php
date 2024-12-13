<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\comentario>
 */
class RolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //'nomb' => \App\Models\Usuario::factory()->create()->id,
            'nombre_rol' => $this->faker->name(),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
