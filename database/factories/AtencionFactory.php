<?php

namespace Database\Factories;

use App\Models\Atencion;
use App\Models\Turno;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\comentario>
 */
class AtencionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //obtener un turno aleatorio
       /*  $turnoasignar = Turno::inRandomOrder()->first();
        
        //asignar atencion segun turno
         $atencion=null;
        //asignar atencion a turno segun estado
        if(in_array($turnoasignar->estado_id,['Terminado'])){
            return $turnoasignar = Turno::whereNotNull('paciente_id')->inRandomOrder()->value('id'); 
        }  */
          // Obtener un turno aleatorio con estado "Atendido"
          
        $turnoasignar = Turno::where('estado_id', 2)->inRandomOrder()->first();

        if (!$turnoasignar) {
            // Manejar el caso donde no hay turnos disponibles
            throw new Exception('No hay turnos disponibles para asignar');
        }
 

        return [
        //'turno_id'=> fake()->unique()->randomElement(Turno::pluck('id')->toArray()),
        //'turno_id'=> Turno::inRamdomOrder()->array_values(),
        'turno_id'=>$turnoasignar->id,



        
        'altura'=> $this->faker->unique()->randomFloat(2),
        'peso'=> $this->faker->unique()->randomFloat(2),
        'cintura'=> $this->faker->unique()->randomFloat(2),
        'cadera'=> $this->faker->unique()->randomFloat(2),
        'circunferencia_muneca'=> $this->faker->unique()->randomFloat(2),
        'circunferencia_cuello'=> $this->faker->unique()->randomFloat(2),
        'actividad_fisica'=> $this->faker->unique()->randomFloat(2),
        'imc'=> $this->faker->unique()->randomFloat(2),
        'tmb'=> $this->faker->unique()->randomFloat(2),
        'cintura_talla'=> $this->faker->unique()->randomFloat(2),
        'cintura_cadera'=> $this->faker->unique()->randomFloat(2),
        'porcentaje_grasa'=> $this->faker->unique()->randomFloat(2),
        'complexion_hueso'=> $this->faker->unique()->randomFloat(2),
        'observacion'=> $this->faker->paragraph()
        ];
    }
}
