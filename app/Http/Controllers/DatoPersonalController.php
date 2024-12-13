<?php

namespace App\Http\Controllers;

use App\Models\DatoPersonal;
use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatoPersonalController extends Controller
{
    // Listar datos personales del usuario autenticado
    public function index()
    {
        $usuario = Auth::user(); // Obtener el usuario autenticado
        if ($usuario) {
            $datoPersonal = $usuario->dato_personal; // Relación con DatoPersonal
            return response()->json($datoPersonal);
        }
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    // Mostrar un dato personal específico
    public function show($id)
    {
        $datoPersonal = DatoPersonal::find($id);
        if (!$datoPersonal) {
            return response()->json(['message' => 'Dato personal no encontrado'], 404);
        }
        return response()->json($datoPersonal);
    }

    // Agregar o crear datos personales para el usuario autenticado
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ci' => 'required|string|max:20',
            'telefono' => 'required|string|max:15',
            'fecha_nacimiento' => 'required|date',
            'ciudad' => 'required|string|max:100',
            'sexo' => 'required|string|in:Masculino,Femenino',
        ]);

        // Comprobar si la validación falla
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Crear nuevo DatoPersonal
        $datoPersonal = new DatoPersonal([
            'ci' => $request->ci,
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'ciudad' => $request->ciudad,
            'sexo' => $request->sexo,
        ]);

        // Guardar el dato personal relacionado con el usuario
        $usuario->dato_personal()->save($datoPersonal);

        return response()->json([
            'message' => 'Datos personales guardados con éxito',
            'datoPersonal' => $datoPersonal,
        ], 201);
    }


    // Actualizar datos personales del usuario autenticado
    public function update(Request $request)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Verificar si el usuario tiene datos personales
        if (!$usuario->dato_personal) {
            // Si no existen datos personales, llama a la función store
            return $this->store($request);
        }

        // Validar los datos entrantes
        $validator = Validator::make($request->all(), [
            'ci' => 'sometimes|string|max:20',
            'telefono' => 'sometimes|string|max:15',
            'fecha_nacimiento' => 'sometimes|date',
            'ciudad' => 'sometimes|string|max:100',
            'sexo' => 'sometimes|string|in:Masculino,Femenino',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al actualizar datos personales',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Actualizar los datos personales relacionados
        $usuario->dato_personal->update($request->all());
        $datoPersonal = $usuario->dato_personal;
        return response()->json([
            'message' => 'Datos personales actualizados con éxito',
            'Datos nuevos' => $datoPersonal,
        ]);
    }


    // Eliminar los datos personales del usuario autenticado
    public function destroy()
    {
        $usuario = Auth::user();
        if (!$usuario || !$usuario->dato_personal) {
            return response()->json(['message' => 'Datos personales no encontrados'], 404);
        }

        $usuario->dato_personal->delete(); // Eliminar los datos personales
        return response()->json(['message' => 'Datos personales eliminados con éxito']);
    }
}
