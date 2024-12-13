<?php

namespace Database\Seeders;

use App\Models\Estado;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear los roles en la base de datos
        Rol::factory()->create([
            'nombre_rol' => 'Administrador',
            'descripcion' => 'test@example.com',
        ]);
        Rol::factory()->create([
            'nombre_rol' => 'Nutricionista',
            'descripcion' => 'test@example.com',
        ]);
        Rol::factory()->create([
            'nombre_rol' => 'Paciente',
            'descripcion' => 'test@example.com',
        ]);

        // Crear estados en la base de datos
        Estado::factory()->create([
            'nombre_estado' => 'No tomado',
            'descripcion' => 'Turno no tomado aún.',
        ]);

        Estado::factory()->create([
            'nombre_estado' => 'Terminado',
            'descripcion' => 'Turno ya finalizado.',
        ]);

        Estado::factory()->create([
            'nombre_estado' => 'Reservado',
            'descripcion' => 'Turno reservado por un paciente.',
        ]);

        Estado::factory()->create([
            'nombre_estado' => 'Cancelado',
            'descripcion' => 'Turno cancelado.',
        ]);

        Usuario::factory()->create([
            'rol_id'=> '1',
            'nombre'=> 'admin',
            'email'=> 'admin@admin.com',
            'password'=> bcrypt('admin')
        ]);

        Usuario::factory()->create([
            'rol_id'=> '2',
            'nombre'=> 'Dayana Moreira',
            'email'=> 'daya@daya.com',
            'password'=> bcrypt('1234')
        ]);
        Usuario::factory()->create([
            'rol_id'=> '2',
            'nombre'=> 'Alexander Bravo',
            'email'=> 'ale@ale.com',
            'password'=> bcrypt('1234')
        ]);

        Usuario::factory()->create([
            'rol_id'=> 3,
            'nombre'=> "Ruben Mera",
            'email' => "dariomera911@gmail.com",
            'password' => bcrypt('1234'),
        ]);


    }
}