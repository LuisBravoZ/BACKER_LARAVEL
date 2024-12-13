<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Atencion;

class Turno extends Model
{
    use HasFactory;

    protected $table = 'turno';

    protected $fillable = [

        'estado_id',
        'paciente_id',
        'nutricionista_id',
        'fecha',
        'hora'
    ];

    public function atencion()
    {
        return $this->hasMany(Atencion::class, 'atencion_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'paciente_id');
    }


    public function turnable()
    {
        return $this->morphTo();
    }

    // Relación con el modelo Paciente (o Usuario si usas autenticación)
    public function paciente()
    {
        return $this->belongsTo(Usuario::class, 'paciente_id'); // Cambia User por Paciente si tienes un modelo específico
    }

    // Relación con el modelo Nutricionista
    public function nutricionista()
    {
        return $this->belongsTo(Usuario::class, 'nutricionista_id'); // Cambia User por Nutricionista si tienes un modelo específico
    }
}
