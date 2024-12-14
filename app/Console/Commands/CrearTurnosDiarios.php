<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Turno;
use Carbon\Carbon;

class CrearTurnosDiarios extends Command
{
    protected $signature = 'turnos:crear-diarios';
    protected $description = 'Crear automáticamente los turnos diarios, excluyendo los domingos';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $horarios = [
            'matutino' => ['08:00', '09:00', '10:00', '11:00'],
            'vespertino' => ['14:00', '15:00', '16:00', '17:00']
        ];

        $fechaActual = Carbon::now()->startOfDay(); // Fecha actual sin horas
        $diaActual = $fechaActual->format('l'); // Nombre del día en inglés

        if ($diaActual !== 'Sunday') {
            foreach ($horarios as $bloque) {
                foreach ($bloque as $hora) {
                    // Convertimos la hora a un formato que permita crear el turno en la fecha y hora correctas
                    $fechaHora = Carbon::parse("{$fechaActual->toDateString()} {$hora}");

                    // Crear turno si no existe
                    Turno::firstOrCreate([
                        'fecha' => $fechaActual->toDateString(),
                        'hora' => $hora,
                        'estado_id' => 1, // 1 = libre
                        'nutricionista_id' => 1, // ID de ejemplo
                    ]);
                }
            }
            $this->info("Turnos creados exitosamente para el día {$fechaActual->toFormattedDateString()}");
        } else {
            $this->info("Hoy es domingo, no se crean turnos.");
        }

        return 0;
    }
}
