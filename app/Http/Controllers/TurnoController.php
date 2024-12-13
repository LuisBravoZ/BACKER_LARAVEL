<?php

namespace App\Http\Controllers;
use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class TurnoController extends Controller
{
    public function index()
    {
        // Listar todos los turnos con el paciente_id y el estado correspondiente.
        $turnos = Turno::with('paciente', 'nutricionista', 'estado')->get();
        return response()->json($turnos);
    }

    // Reservar turno
    public function reservar(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(["message" => "Usuario no encontrado"], 404);
        }

        DB::beginTransaction(); // Iniciar la transacción

        try {
            $turno = Turno::find($request->turno_id);

            if (!$turno) {
                return response()->json(["message" => "Turno no encontrado"], 404);
            }

            // Verificar si el usuario ya tiene un turno reservado en la misma fecha
            $existeTurnoEnMismaFecha = Turno::where('fecha', $turno->fecha)
                ->where('paciente_id', $usuario->id)
                ->where('estado_id', 3) // 3 = reservado
                ->exists();

            if ($existeTurnoEnMismaFecha) {
                return response()->json(["message" => "Ya tienes un turno reservado en esta fecha."], 400);
            }

            // Verificar que el turno esté libre antes de reservarlo
            if ($turno->estado_id == 1) { // 1 = libre
                $turno->update([
                    'estado_id' => 3, // 3 = reservado
                    'paciente_id' => $usuario->id, // Asignar el ID del usuario actual
                ]);

                DB::commit(); // Confirmar la transacción

                return response()->json([
                    "turno" => $turno,
                    "message" => "Turno reservado con éxito"
                ], 200);
            } else {
                DB::rollBack(); // Revertir la transacción si el turno no está disponible
                return response()->json(["message" => "Turno no disponible"], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción en caso de error
            return response()->json(["message" => "Ocurrió un error al reservar el turno"], 500);
        }
    }



    public function turnosReservados(Request $request)
    {
        // Validar que el usuario autenticado sea nutricionista
        $usuario = Auth::user();

        if (!$usuario || $usuario->rol_id !== 2) { // 2 = nutricionista
            return response()->json(["message" => "Acceso no autorizado"], 403);
        }

        // Obtener los turnos reservados del nutricionista
        $turnos = Turno::with('paciente') // Relación con paciente
            ->where('nutricionista_id', $usuario->id) // Turnos asignados al nutricionista autenticado
            ->where('estado_id', 3) // 3 = reservado
            ->get();

        // Validar si no hay turnos
        if ($turnos->isEmpty()) {
            return response()->json(["message" => "No hay turnos reservados"], 200);
        }

        return response()->json($turnos, 200);
    }

    public function filtrarTurnosPorNutricionista(Request $request, $nutricionistaId)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(["message" => "Usuario no autenticado"], 401);
        }

        try {
            // Turnos disponibles para este nutricionista y que no estén reservados
            $turnosDisponibles = Turno::where('nutricionista_id', $nutricionistaId)
                ->where('estado_id', 1) // 1 = libre
                ->get();

            // Turnos reservados por el usuario
            $turnosReservados = Turno::where('nutricionista_id', $nutricionistaId)
                ->where('estado_id', 3) // 3 = reservado
                ->where('paciente_id', $usuario->id)
                ->get();

            return response()->json([
                "disponibles" => $turnosDisponibles,
                "reservados" => $turnosReservados,
            ]);
        } catch (\Exception $e) {
            return response()->json(["message" => "Error al cargar turnos"], 500);
        }
    }


    public function filtrarPorNutricionista(Request $request)
    {
        // Validar que el nutricionista_id esté presente
        $request->validate([
            'nutricionista_id' => 'required|exists:usuario,id', // Asegúrate de que exista en la tabla de usuarios
        ]);

        $nutricionistaId = $request->input('nutricionista_id');

        // Obtener los turnos filtrados por nutricionista
        $turnos = Turno::with('paciente', 'nutricionista', 'estado')
            ->where('nutricionista_id', $nutricionistaId)
            ->get();

        if ($turnos->isEmpty()) {
            return response()->json(["message" => "No hay turnos disponibles para este nutricionista"], 404);
        }

        return response()->json($turnos, 200);
    }


    public function store()
    {
        // Verificar que el usuario autenticado es un nutricionista
        $nutricionista = Auth::user();

        if (!$nutricionista || $nutricionista->rol_id !== 2) { // Ajustar la condición según tu implementación de roles
            return response()->json(["message" => "Acceso no autorizado"], 403);
        }

        $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        $horarios = [
            'matutino' => ['08:00', '09:00', '10:00', '11:00'],
            'vespertino' => ['14:00', '15:00', '16:00', '17:00']
        ];

        $fechaActual = now(); // Fecha actual para empezar la creación

        foreach ($diasSemana as $indice => $dia) {
            $fechaTurno = $fechaActual->copy()->addDays($indice); // Fecha del turno incrementada por día

            foreach ($horarios as $bloque) {
                foreach ($bloque as $hora) {
                    Turno::create([
                        'dia' => $dia,
                        'fecha' => $fechaTurno->toDateString(), // Fecha específica del turno
                        'hora' => $hora,
                        'estado_id' => 1, // 1 = libre
                        'nutricionista_id' => $nutricionista->id // ID del nutricionista autenticado
                    ]);
                }
            }
        }

        return response()->json(["message" => "Turnos creados con éxito"], 201);
    }


    public function show($id)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(["message" => "Usuario no encontrado"], 404);
        }

        $turno = Turno::with('paciente', 'nutricionista', 'estado')->find($id);

        if (!$turno) {
            return response()->json(["message" => "Turno no encontrado"], 404);
        }

        if ($turno->estado_id == 3 || $turno->estado_id == 2) { // 3 = reservado
            return response()->json([
                'id' => $turno->id,
                'fecha' => $turno->fecha,
                'hora' => $turno->hora,
                'estado' => $turno->estado ? [
                    'estado_id' => $turno->estado->id,
                    'nombre_estado' => $turno->estado->nombre_estado,
                    'descripcion' => $turno->estado->descripcion
                ] : null,
                'Paciente' => $turno->paciente ? [
                    'id' => $turno->paciente->id,
                    'nombre' => $turno->paciente->nombre,
                    'email' => $turno->paciente->email,
                ] : null,
                'nutricionista' => $turno->nutricionista ? [
                    'id' => $turno->nutricionista->id,
                    'nombre' => $turno->nutricionista->nombre,
                    'email' => $turno->nutricionista->email,
                ] : null,
                'created_at' => $turno->created_at,
                'updated_at' => $turno->updated_at,
            ]);
        } else {
            return response()->json(["message" => "El turno no está reservado"], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $turno = Turno::find($id);
        if ($turno->estado_id == 3 && $turno->paciente_id == Auth::id()) {
            $nuevoTurno = Turno::find($request->nuevo_turno_id);

            if ($nuevoTurno && $nuevoTurno->estado_id == 1) { // 1 = libre
                $turno->update(['estado_id' => 1, 'paciente_id' => null]); // liberar turno anterior
                $nuevoTurno->update(['estado_id' => 3, 'paciente_id' => Auth::id()]); // reservar nuevo turno

                return response()->json(["message" => "Turno actualizado con éxito"], 200);
            } else {
                return response()->json(["message" => "El nuevo turno no está disponible"], 400);
            }
        } else {
            return response()->json(["message" => "No tienes permiso para actualizar este turno"], 403);
        }
    }

    public function destroy($id)
    {
        $turno = Turno::find($id);
        $usuario = Auth::user();
        if ($turno->estado_id == 3 && $turno->paciente_id == $usuario->id) { // 3 = reservado
            $turno->update(['estado_id' => 1, 'paciente_id' => null]); // liberar turno
            return response()->json(["message" => "Turno cancelado"], 200);
        } else {
            return response()->json(["message" => "El turno no puede ser cancelado"], 400);
        }
    }
}