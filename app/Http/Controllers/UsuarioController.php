<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    // Obtener todos los usuarios excepto el administrador
    public function obtenerUsuarios()
    {
        $admin = Auth::user(); // Obtiene al usuario autenticado (administrador)
    
        // Verificar si hay un usuario autenticado
        if (!$admin) {
            return response()->json(['message' => 'No autenticado'], 401);
        }
    
        // Obtener todos los usuarios excepto el admin
        $usuarios = Usuario::where('id', '!=', $admin->id)
            ->whereIn('rol_id', [2, 3]) // Solo nutricionistas (rol_id = 2) y pacientes (rol_id = 3)
            ->select('id', 'nombre', 'email', 'rol_id')
            ->get();
    
        return response()->json($usuarios);
    }
    


    // Obtener todos los nutricionistas
    public function obtenerNutricionistas()
    {
        // Filtrar usuarios con rol_id = 2 (Nutricionistas)
        $nutricionistas = Usuario::where('rol_id', 2)
            ->select('id', 'nombre', 'email') // Selecciona solo los campos necesarios
            ->get();

        if ($nutricionistas->isEmpty()) {
            return response()->json(['message' => 'No hay nutricionistas disponibles'], 404);
        }

        return response()->json($nutricionistas, 200);
    }

    // Registro único de un usuario
    public function registro_unico($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        return response()->json($usuario);
    }

    // Registrar un usuario
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required',
            'email' => 'required|email|unique:usuario',
            'password' => 'required',
            'rol_id' => 'sometimes|exists:rol,id' // 'sometimes' porque el rol por defecto es 3 (Paciente)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al crear usuario',
                'errors' => $validator->errors(),
            ], 400);
        }

        $rol_id = $request->input('rol_id', 3); // Por defecto el rol es Paciente (3)

        $usuario = Usuario::create([
            'rol_id' => $rol_id,
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Encriptar contraseña
        ]);

        return response()->json([
            'usuario' => $usuario,
            'message' => 'Usuario creado exitosamente',
        ], 201);
    }

    // Actualizar un usuario
    // Actualizar un usuario
    public function update(Request $request, $id)
    {
        $usuario = Usuario::find($id);
        $admin = Auth::user(); // Obtiene el administrador autenticado

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // No permitir que el admin se edite a sí mismo
        if ($usuario->id === $admin->id) {
            return response()->json(['message' => 'No puedes editarte a ti mismo'], 403);
         }

        $validator = Validator::make($request->all(), [
            'rol_id' => 'sometimes|exists:rol,id',
            'nombre' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:usuario,email,' . $usuario->id,
            'password' => 'sometimes|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Actualizar el usuario
        $usuario->update([
            'rol_id' => $request->rol_id ?? $usuario->rol_id,
            'nombre' => $request->nombre ?? $usuario->nombre,
            'email' => $request->email ?? $usuario->email,
            'password' => $request->password ? bcrypt($request->password) : $usuario->password,
        ]);

        return response()->json(['message' => 'Usuario actualizado correctamente', 'usuario' => $usuario]);
}


    // Eliminar un usuario
    public function destroy($id)
    {
        $usuario = Usuario::find($id);
        $admin = Auth::user(); // Obtiene el administrador autenticado

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // No permitir que el admin se elimine a sí mismo
        if ($usuario->id === $admin->id) {
            return response()->json(['message' => 'No puedes eliminarte a ti mismo'], 403);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }


    // Login de usuario
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials)) {
        $usuario = Auth::user(); // Obtiene el usuario autenticado
        $token = $usuario->createToken('API TOKEN')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'user' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'rol_id' => $usuario->rol_id, // Incluye el rol
            ],
            'token' => $token,
        ], 200);
    }

    return response()->json(['message' => 'Credenciales incorrectas'], 401);
}


    // Logout del usuario
    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->delete(); // Eliminar todos los tokens del usuario autenticado

        return response()->json(['message' => 'Cierre de sesión exitoso'], 200);
    }
}
