<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Atencion extends Model
{
    use HasFactory;

    protected $table = 'atencion';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'turno_id',
       /*  'edad',
        'sexo', */
        'altura',
        'peso',
        'cintura',
        'cadera',
        'circunferencia_muneca',
        'circunferencia_cuello',
        'actividad_fisica',
        'imc',
        'tmb',
        'cintura_talla',
        'cintura_cadera',
        'porcentaje_grasa',
        'complexion_hueso',
        'observacion',
    ];

    /**
     * Relación con el modelo Turno.
     */
    public function usuario() {
        return $this->belongsTo(Usuario::class, 'usuario_id'); // Aquí se asume que 'usuario_id' es la clave foránea en atencion
    }
    
    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }
}
