<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReglaSancion;
use App\Models\TipoSancion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TipoSancionController extends Controller
{
    public function listar(Request $request)
    {
        $query = TipoSancion::query()->latest('id');

        return DataTables::eloquent($query)
            ->addColumn('estado_badge', function ($row) {
                $activo = (bool) $row->estado;

                return '<span class="badge ' . ($activo ? 'bg-success' : 'bg-secondary') . '">' .
                    ($activo ? 'Activa' : 'Inactiva') .
                    '</span>';
            })
            ->addColumn('acciones', function ($row) {
                return '<button class="btn btn-sm btn-primary editarSancionTipo">Editar</button>'
                    . '<button class="btn btn-sm btn-info reglasSancionTipo">Reglas</button>';
            })
            ->rawColumns(['estado_badge', 'acciones'])
            ->make(true);
    }

    public function listarReglas(TipoSancion $tipoSancion)
    {
        $tipoSancion->load(['reglas' => function ($query) {
            $query->latest('id');
        }]);

        return response()->json([
            'success' => true,
            'tipo_sancion' => $tipoSancion,
            'reglas' => $tipoSancion->reglas,
        ]);
    }

    public function nuevo(Request $request)
    {
        $data = $this->validar($request);

        DB::beginTransaction();
        try {
            $tipoSancion = TipoSancion::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de sancion registrado correctamente.',
                'data' => $tipoSancion,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el tipo de sancion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:tipo_sanciones,id',
        ]);

        $data = $this->validar($request, (int) $request->id);

        DB::beginTransaction();
        try {
            $tipoSancion = TipoSancion::findOrFail($request->id);
            $tipoSancion->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de sancion actualizado correctamente.',
                'data' => $tipoSancion,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el tipo de sancion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function guardarRegla(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|exists:reglas_sanciones,id',
            'tipo_sancion_id' => 'required|exists:tipo_sanciones,id',
            'evento' => 'required|string|max:100',
            'dias_desde' => 'nullable|integer|min:0',
            'dias_hasta' => 'nullable|integer|min:0',
            'cantidad_minima' => 'nullable|integer|min:0',
            'cantidad_maxima' => 'nullable|integer|min:0',
            'duracion_dias' => 'nullable|integer|min:0',
            'monto' => 'nullable|numeric|min:0',
            'requiere_aprobacion' => 'nullable|boolean',
            'estado' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            $regla = empty($data['id'])
                ? ReglaSancion::create($data)
                : tap(ReglaSancion::findOrFail($data['id']))->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Regla de sancion guardada correctamente.',
                'data' => $regla->fresh(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la regla de sancion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function validar(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'codigo' => 'required|string|max:100|unique:tipo_sanciones,codigo' . ($id ? ',' . $id : ''),
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'origen_evento' => 'nullable|string|max:100',
            'condicion' => 'nullable|string|max:100',
            'dias_duracion' => 'nullable|integer|min:0',
            'monto' => 'nullable|numeric|min:0',
            'requiere_pago' => 'nullable|boolean',
            'bloquea_prestamos' => 'nullable|boolean',
            'aplica_automaticamente' => 'nullable|boolean',
            'estado' => 'required|boolean',
        ]);
    }
}
