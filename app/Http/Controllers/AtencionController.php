<?php

namespace App\Http\Controllers;

use App\Models\Atencion;
use App\Models\Turno;
use App\Models\Rol;
use Carbon\Carbon;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AtencionController extends Controller
{
    public function index()
{
    $usuario = Auth::user(); // Obtener el usuario autenticado

    if (!$usuario) {
        return response()->json(['message' => 'Usuario no autenticado'], 401);
    }

    // Determinar las atenciones según el rol del usuario
    if ($usuario->rol_id == 3) { // Paciente
        $atenciones = Atencion::with('turno.paciente') // Incluye turno y paciente
            ->whereHas('turno', function ($query) use ($usuario) {
                $query->where('paciente_id', $usuario->id);
            })
            ->get();

        return response()->json([
            'message' => 'Atenciones disponibles para el paciente:',
            'atencion' => $atenciones
        ], 200);
    } elseif ($usuario->rol_id == 2) { // Nutricionista
        $atenciones = Atencion::with('turno.paciente') // Incluye turno y paciente
            ->whereHas('turno', function ($query) use ($usuario) {
                $query->where('nutricionista_id', $usuario->id);
            })
            ->get();

        return response()->json([
            'message' => 'Todas las atenciones realizadas por el nutricionista:',
            'atencion' => $atenciones
        ], 200);
    }

    // Caso de rol no reconocido
    return response()->json([
        'message' => 'Rol no reconocido o no tiene atenciones disponibles',
        'atencion' => []
    ], 403);
}

public function show($id)
{
    $usuario = Auth::user();

    // Validar que el usuario esté autenticado
    if (!$usuario) {
        return response()->json(['message' => 'Usuario no autenticado'], 401);
    }

    // Buscar la atención por ID
    $atencion = Atencion::with('turno.paciente')->find($id);

    if (!$atencion) {
        return response()->json(['message' => 'Atención no encontrada'], 404);
    }

    // Validar que el usuario tiene acceso a la atención según su rol
    if ($usuario->rol_id == 3 && $atencion->turno->paciente_id !== $usuario->id) {
        return response()->json(['message' => 'Acceso denegado a esta atención'], 403);
    }

    if ($usuario->rol_id == 2 && $atencion->turno->nutricionista_id !== $usuario->id) {
        return response()->json(['message' => 'Acceso denegado a esta atención'], 403);
    }

    return response()->json($atencion, 200);
}


    public function crearStore(Request $request)
    {
        $usuario = Auth::user();

        // Validar que el usuario autenticado tenga el rol de 'Nutricionista'
        if ($usuario->rol_id !== 2) { // Asegúrate de que 2 es el ID correcto para 'Nutricionista'
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        // Validación de los datos usando Validator
        $validator = Validator::make($request->all(), [
            'turno_id' => 'required|exists:turno,id',
            'altura' => 'required|numeric|min:0',
            'peso' => 'required|numeric|min:0',
            'cintura' => 'required|numeric|min:0',
            'cadera' => 'required|numeric|min:0',
            'circunferencia_muneca' => 'required|numeric|min:0',
            'circunferencia_cuello' => 'required|numeric|min:0',
            'actividad_fisica' => 'required|numeric|min:0|max:5',
            'observacion' => 'nullable|string',
        ]);

        // Si hay errores de validación, retornar con los errores
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar si ya existe una atención para el turno
        $atencionExistente = Atencion::where('turno_id', $request->turno_id)->first();

        if ($atencionExistente) {
            return response()->json([
                'message' => 'Ya existe una atención para este turno. Solo se puede actualizar.',
                'atencion' => $atencionExistente,
            ], 409); // Código 409: Conflicto
        }

        // Verificar que el turno exista y obtener el paciente asociado
        $turno = Turno::with('paciente')->find($request->turno_id);

        // Verificar si el turno existe
        if (!$turno) {
            return response()->json(['message' => 'Turno no encontrado'], 404);
        }

        // Verificar si el turno tiene un paciente asignado
        if (!$turno->paciente) {
            return response()->json(['message' => 'Turno aún sin paciente asignado'], 404);
        }

        // Obtener los datos personales del paciente
        $datoPersonal = $turno->paciente->dato_personal;
        if (!$datoPersonal) {
            return response()->json(['message' => 'Datos personales del paciente no encontrados'], 404);
        }

        // Calcular edad y sexo
        $edad = Carbon::parse($datoPersonal->fecha_nacimiento)->age;
        $sexo = $datoPersonal->sexo;

        // Realizar cálculos de atención
        $alturaMetros = $request->altura / 100; // Convertir altura de cm a metros
        $imc = $request->peso / ($alturaMetros ** 2);

        // Calcular TMB usando la fórmula de Harris-Benedict según el sexo
        $tmb = $sexo === 'Masculino'
            ? 88.362 + (13.397 * $request->peso) + (4.799 * $request->altura) - (5.677 * $edad)
            : 447.593 + (9.247 * $request->peso) + (3.098 * $request->altura) - (4.330 * $edad);

        $cinturaTalla = $request->cintura / $request->altura;
        $cinturaCadera = $request->cintura / $request->cadera;

        // Porcentaje de grasa corporal
        $porcentajeGrasa = $sexo === 'Masculino'
            ? (1.2 * $imc) + (0.23 * $edad) - 16.2
            : (1.2 * $imc) + (0.23 * $edad) - 5.4;

        // Complexión ósea usando la circunferencia de la muñeca
        $complexionHueso = $request->altura / $request->circunferencia_muneca;

        // Crear la atención para el paciente asignado al turno
        $atencion = new Atencion([
            'turno_id' => $request->turno_id,
            'altura' => $request->altura,
            'peso' => $request->peso,
            'cintura' => $request->cintura,
            'cadera' => $request->cadera,
            'circunferencia_muneca' => $request->circunferencia_muneca,
            'circunferencia_cuello' => $request->circunferencia_cuello,
            'actividad_fisica' => $request->actividad_fisica,
            'imc' => $imc,
            'tmb' => $tmb,
            'cintura_talla' => $cinturaTalla,
            'cintura_cadera' => $cinturaCadera,
            'porcentaje_grasa' => $porcentajeGrasa,
            'complexion_hueso' => $complexionHueso,
            'observacion' => $request->observacion,
        ]);

        // Guardar la atención en la base de datos
        $atencion->save();

        // Actualizar estado del turno a "terminado"
        $turno->estado_id = 2;
        $turno->save();


        return response()->json([
            'message' => 'Atención creada con éxito para el paciente',
            'atencion' => $atencion,
        ], 201);
    }


    public function actualizar(Request $request, $id)
    {
        $usuario = Auth::user();

        // Validar que el usuario autenticado tenga el rol de 'Nutricionista'
        if ($usuario->rol_id !== 2) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        // Validación de los datos usando Validator
        $validator = Validator::make($request->all(), [
            'turno_id' => 'sometimes|exists:turno,id',
            'altura' => 'sometimes|numeric|min:0',
            'peso' => 'sometimes|numeric|min:0',
            'cintura' => 'sometimes|numeric|min:0',
            'cadera' => 'sometimes|numeric|min:0',
            'circunferencia_muneca' => 'sometimes|numeric|min:0',
            'circunferencia_cuello' => 'sometimes|numeric|min:0',
            'actividad_fisica' => 'sometimes|numeric|min:0|max:5',
            'observacion' => 'sometimes|string',
        ]);

        // Si hay errores de validación, retornar con los errores
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buscar la atención que se desea actualizar
        $atencion = Atencion::find($id);

        // Verificar si la atención existe
        if (!$atencion) {
            return response()->json(['message' => 'Atención no encontrada'], 404);
        }

        // Verificar que el turno exista y obtener el paciente asociado
        $turno = Turno::with('Paciente')->find($request->turno_id);

        // Verificar si el turno existe
        if (!$turno) {
            return response()->json(['message' => 'Turno no encontrado'], 404);
        }

        // Verificar si el turno tiene un paciente asignado
        if (!$turno->paciente) {
            return response()->json(['message' => 'Turno aún sin paciente asignado'], 404);
        }

        // Obtener los datos personales del paciente
        $datoPersonal = $turno->paciente->dato_personal;
        if (!$datoPersonal) {
            return response()->json(['message' => 'Datos personales del paciente no encontrados'], 404);
        }

        // Calcular edad y sexo
        $edad = Carbon::parse($datoPersonal->fecha_nacimiento)->age;
        $sexo = $datoPersonal->sexo;

        // Realizar cálculos de atención
        $alturaMetros = $request->altura / 100; // Convertir altura de cm a metros
        $imc = $request->peso / ($alturaMetros ** 2);

        // Calcular TMB usando la fórmula de Harris-Benedict según el sexo
        $tmb = $sexo === 'Masculino'
            ? 88.362 + (13.397 * $request->peso) + (4.799 * $request->altura) - (5.677 * $edad)
            : 447.593 + (9.247 * $request->peso) + (3.098 * $request->altura) - (4.330 * $edad);

        $cinturaTalla = $request->cintura / $request->altura;
        $cinturaCadera = $request->cintura / $request->cadera;

        // Porcentaje de grasa corporal
        $porcentajeGrasa = $sexo === 'Masculino'
            ? (1.2 * $imc) + (0.23 * $edad) - 16.2
            : (1.2 * $imc) + (0.23 * $edad) - 5.4;

        // Complexión ósea usando la circunferencia de la muñeca
        $complexionHueso = $request->altura / $request->circunferencia_muneca;

        // Actualizar los datos de la atención
        $atencion->turno_id = $request->turno_id;
        $atencion->altura = $request->altura;
        $atencion->peso = $request->peso;
        $atencion->cintura = $request->cintura;
        $atencion->cadera = $request->cadera;
        $atencion->circunferencia_muneca = $request->circunferencia_muneca;
        $atencion->circunferencia_cuello = $request->circunferencia_cuello;
        $atencion->actividad_fisica = $request->actividad_fisica;
        $atencion->imc = $imc;
        $atencion->tmb = $tmb;
        $atencion->cintura_talla = $cinturaTalla;
        $atencion->cintura_cadera = $cinturaCadera;
        $atencion->porcentaje_grasa = $porcentajeGrasa;
        $atencion->complexion_hueso = $complexionHueso;
        $atencion->observacion = $request->observacion;

        // Guardar los cambios en la base de datos
        $atencion->save();
        if ($atencion->wasChanged()) {
            $turno->estado_id = 2;
            $turno->save();
        }
        return response()->json([
            'message' => 'Atención actualizada con éxito',
            'atencion' => $atencion,
        ], 200);
    }

    public function verificarAtencion(Request $request)
    {
        $turnoId = $request->query('turno_id');
        $atencion = Atencion::where('turno_id', $turnoId)->first();

        if ($atencion) {
            return response()->json(['atencion' => $atencion], 200);
        }

        return response()->json(['atencion' => null], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminar($id)
    {
        $usuario = Auth::user();

        // Validar que el usuario autenticado tenga el rol de 'Nutricionista'
        if ($usuario->rol_id !== 2) { // Asegúrate de que 2 es el ID correcto para 'Nutricionista'
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        // Buscar la atención por ID
        $atencion = Atencion::find($id);

        // Verificar si la atención existe
        if (!$atencion) {
            return response()->json(['message' => 'Atención no encontrada'], 404);
        }

        // Eliminar la atención
        $atencion->delete();

        return response()->json(['message' => 'Atención eliminada con éxito'], 200);
    }
}