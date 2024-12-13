<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Rol;
use App\Models\Turno;
use App\Models\DatoPersonal;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id';
    protected $fillable = [
        'rol_id',
        'usuario_id',
        'nombre',
        'email',
        'password'
    ];
    // Atributos que deben estar ocultos para arrays
    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function dato_personal()
    {
        return $this->hasOne(DatoPersonal::class, 'usuario_id');
    }
    
    public function turno()
    {
        return $this->hasMany(Turno::class, 'usuario_id'); // Cambiar a 'id' si la PK de usuario es 'id'
    }
}
