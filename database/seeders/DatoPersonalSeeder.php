<?php

namespace Database\Seeders;

use App\Models\DatoPersonal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatoPersonalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DatoPersonal::factory()->count(10)->create();
    }
}
