<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ejemplar extends Model
{
    public const ESTADO_PRESTADO = 0;
    public const ESTADO_DISPONIBLE = 1;
    public const ESTADO_RESERVADO = 2;
    public const ESTADO_TRASLADO_PENDIENTE = 3;

    public const TRASLADO_NINGUNO = 0;
    public const TRASLADO_PENDIENTE = 1;

    protected $table = 'ejemplares';

    protected $fillable = [
        'codigo_ant',
        'adquisicion',
        'siaf',
        'tipo',
        'codigo_dewey',
        'codigo_interno',
        'libro_id',
        'biblioteca_id',
        'traslado_origen_biblioteca_id',
        'traslado_destino_biblioteca_id',
        'estado',
        'estado_traslado',
        'compra_detalle_id',
    ];

    public function libro()
    {
        return $this->belongsTo(Libro::class, 'libro_id');
    }
    public function compra_detalle()
    {
        return $this->belongsTo(Compra_detalle::class,'compra_detalle_id');
    }
    public function biblioteca()
    {
        return $this->belongsTo(Biblioteca::class, 'biblioteca_id');
    }

    public function trasladoOrigenBiblioteca()
    {
        return $this->belongsTo(Biblioteca::class, 'traslado_origen_biblioteca_id');
    }

    public function trasladoDestinoBiblioteca()
    {
        return $this->belongsTo(Biblioteca::class, 'traslado_destino_biblioteca_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoEjemplar::class, 'ejemplar_id');
    }

    public static function siguienteCodigoInternoParaLibro(int $libroId): int
    {
        $maxCodigoInterno = static::where('libro_id', $libroId)->max('codigo_interno');

        return $maxCodigoInterno ? $maxCodigoInterno + 1 : 1;
    }

    public static function crearDesdeImportacion(Libro $libro, int $bibliotecaId, int $codigoInterno): self
    {
        return static::create([
            'codigo_ant' => $libro->codigo_ant,
            'adquisicion' => null,
            'siaf' => null,
            'tipo' => 'ej.',
            'codigo_dewey' => (string) ($libro->codigo_dewey ?? '') . (string) ($libro->codigo ?? ''),
            'codigo_interno' => $codigoInterno,
            'libro_id' => $libro->id,
            'biblioteca_id' => $bibliotecaId,
            'estado' => self::ESTADO_DISPONIBLE,
            'estado_traslado' => self::TRASLADO_NINGUNO,
            'compra_detalle_id' => null,
        ]);
    }
}
