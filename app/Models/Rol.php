<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table='rol';

    protected $fillable=[
        'rol_id','nombre_rol','descripcion'];

    public function usuario(){
        return $this->HasMany(Usuario::class,'usuario_id');
    }
}
