<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ejemplar;
use App\Models\Libro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class EjemplarController extends Controller
{
    public function listar(Request $request)
    {
        $query = Ejemplar::with(['compra_detalle.compra', 'biblioteca'])
            ->where('libro_id', $request->id);

        if ($request->has('biblioteca_id')) {
            if ($request->biblioteca_id === null || $request->biblioteca_id === '') {
                $query->whereNull('biblioteca_id');
            } elseif ($request->biblioteca_id != -1) {
                $query->where('biblioteca_id', $request->biblioteca_id);
            }
        }

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];

            $query->where(function ($q) use ($search) {
                $q->where('codigo_interno', 'like', "%{$search}%")
                    ->orWhere('codigo_dewey', 'like', "%{$search}%")
                    ->orWhere('codigo_ant', 'like', "%{$search}%")
                    ->orWhere('siaf', 'like', "%{$search}%")
                    ->orWhereHas('biblioteca', function ($bibliotecaQuery) use ($search) {
                        $bibliotecaQuery->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        return DataTables::of($query)
            ->addColumn('estado_value', function ($row) {
                return (int) $row->estado;
            })
            ->addColumn('biblioteca', function ($row) {
                if (!$row->biblioteca) {
                    return '<span class="exemplars-table__library exemplars-table__library--empty">Sin biblioteca</span>';
                }

                return '<span class="exemplars-table__library">' . e($row->biblioteca->nombre) . '</span>';
            })
            ->editColumn('estado', function ($row) {
                $label = match ((int) $row->estado) {
                    0 => 'Prestado',
                    1 => 'Disponible',
                    2 => 'Reservado',
                    default => 'Sin definir',
                };

                $modifier = match ((int) $row->estado) {
                    0 => 'warning',
                    1 => 'success',
                    2 => 'danger',
                    default => 'neutral',
                };

                return '<span class="exemplars-status exemplars-status--' . $modifier . '">' . $label . '</span>';
            })
            ->addColumn('acciones', function ($row) {
                return '<button type="button" onclick="actualizarEjemplar(' . $row->id . ')" class="btn btn-sm btn-primary editarEjemplar">Actualizar</button>';
            })
            ->rawColumns(['biblioteca', 'estado', 'acciones'])
            ->make(true);
    }

    public function guardar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1',
            'libro_id' => 'required|exists:libros,id',
            'biblioteca_id' => 'required|exists:bibliotecas,id',
            'siaf' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $libro = Libro::findOrFail($request->libro_id);
            $ultimoCodigo = Ejemplar::where('libro_id', $request->libro_id)->max('codigo_interno') ?: 0;

            for ($i = 1; $i <= $request->cantidad; $i++) {
                Ejemplar::create([
                    'libro_id' => $request->libro_id,
                    'biblioteca_id' => $request->biblioteca_id,
                    'codigo_interno' => $ultimoCodigo + $i,
                    'tipo' => 'ej.',
                    'siaf' => $request->siaf,
                    'codigo_dewey' => $libro->codigo_dewey . $libro->codigo,
                    'estado' => 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ejemplares agregados correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function actualizar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:1|exists:ejemplares,id',
            'biblioteca_id' => 'required|exists:bibliotecas,id',
            'siaf' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $ejemplar = Ejemplar::findOrFail($request->id);
            $ejemplar->biblioteca_id = $request->biblioteca_id;
            $ejemplar->siaf = $request->siaf;
            $ejemplar->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ejemplar actualizado correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar ejemplar',
            ], 500);
        }
    }

    public function enviarBiblioteca(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ejemplares' => 'required|array|min:1',
            'ejemplares.*' => 'integer|exists:ejemplares,id',
            'biblioteca_id' => 'required|exists:bibliotecas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        Ejemplar::whereIn('id', $request->ejemplares)->update([
            'biblioteca_id' => $request->biblioteca_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ejemplares movidos correctamente',
        ]);
    }
}
