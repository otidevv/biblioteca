<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Ejemplar;
use App\Models\Usuario_rol_biblioteca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventarioController extends Controller
{
    //
    public function listarCompras(Request $request)
    {
       
        $query = Compra::get();

        return DataTables::of($query)
            ->addColumn('acciones', function($row) {
                $btns = '<button class="btn btn-sm btn-primary me-1 editarCompra">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                </button>';
                $btns .= '<button class="btn btn-sm btn-danger me-1 eliminarCompra">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7h16" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1
                    0 0 1 1 1v3" /><path d="M10 12l4 4m0 -4l-4 4" /></svg>  
                </button>';
                return $btns;   
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }
    public function nuevo(Request $request)
    {
        $request->validate([
            // COMPRAS
            'codigo'            => 'required|string|max:20|unique:compras,codigo',
            'nombre'            => 'required|string|max:150',
            'direccion'         => 'nullable|string|max:255',
            'descripcion'       => 'nullable|string|max:500',
        ]);
        //return $request;    
        DB::beginTransaction();

        try {

            /** =========================
             *  COMPRAS
             *  ========================= */
            $compra = Compra::create([
                'codigo'        => $request->codigo,
                'nombre'        => $request->nombre,
                'direccion'     => $request->direccion,
                'descripcion'   => $request->descripcion,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Compra registrada correctamente',
                'data' => $compra
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar compra',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function edit(Request $request)
    {
        $request->validate([
            // COMPRAS
            'id'                => 'required|exists:compras,id',
            'codigo'            => 'required|string|max:20|unique:compras,codigo,'.$request->id,
            'nombre'            => 'required|string|max:150',
            'direccion'         => 'nullable|string|max:255',
            'descripcion'       => 'nullable|string|max:500',
        ]);
        DB::beginTransaction();
        try {

            /** =========================
             *  COMPRAS 
             *  ========================= */
            $compra = Compra::where('id', $request->id)->first();
            $compra->update([
                'codigo'        => $request->codigo,
                'nombre'        => $request->nombre,
                'direccion'     => $request->direccion,
                'descripcion'   => $request->descripcion,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Compra actualizada correctamente',
                'data' => $compra
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar compra',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:compras,id',
        ]);
        $compra = Compra::where('id', $request->id)->first();
        $compra->delete();

        return response()->json([
            'success' => true,
            'message' => 'Compra eliminada correctamente',
        ], 200);
    }

    public function listarInventarioFisico(Request $request)
    {
        $usuario = Auth::user();
        $asignaciones = Usuario_rol_biblioteca::query()
            ->where('user_id', $usuario->id)
            ->where('estado', 1)
            ->pluck('biblioteca_id')
            ->unique()
            ->values();

        $bibliotecasAsignadas = $asignaciones
            ->filter(fn ($id) => !is_null($id))
            ->map(fn ($id) => (int) $id)
            ->values();
        $accesoGlobal = $bibliotecasAsignadas->isEmpty() && $asignaciones->contains(null);

        $query = Ejemplar::query()
            ->leftJoin('libros', 'libros.id', '=', 'ejemplares.libro_id')
            ->leftJoin('tipo_registros', 'tipo_registros.id', '=', 'libros.tipo_registro_id')
            ->leftJoin('bibliotecas', 'bibliotecas.id', '=', 'ejemplares.biblioteca_id')
            ->selectRaw('
                ejemplares.libro_id,
                ejemplares.biblioteca_id,
                libros.titulo,
                libros.imagen,
                libros.codigo_dewey,
                libros.codigo,
                libros.codigo_ant,
                tipo_registros.nombre as tipo_registro,
                bibliotecas.nombre as biblioteca_nombre,
                COUNT(ejemplares.id) as total_ejemplares,
                SUM(CASE WHEN ejemplares.estado = 1 THEN 1 ELSE 0 END) as disponibles,
                SUM(CASE WHEN ejemplares.estado = 0 THEN 1 ELSE 0 END) as prestados,
                SUM(CASE WHEN ejemplares.estado = 2 THEN 1 ELSE 0 END) as reservados
            ')
            ->groupBy(
                'ejemplares.libro_id',
                'ejemplares.biblioteca_id',
                'libros.titulo',
                'libros.imagen',
                'libros.codigo_dewey',
                'libros.codigo',
                'libros.codigo_ant',
                'tipo_registros.nombre',
                'bibliotecas.nombre'
            );

        if ($accesoGlobal) {
            if ($request->filled('biblioteca_id')) {
                if ((string) $request->biblioteca_id === 'sin_biblioteca') {
                    $query->whereNull('ejemplares.biblioteca_id');
                } else {
                    $query->where('ejemplares.biblioteca_id', (int) $request->biblioteca_id);
                }
            }
        } elseif ($bibliotecasAsignadas->isNotEmpty()) {
            $query->whereIn('ejemplares.biblioteca_id', $bibliotecasAsignadas->all());
        } else {
            $query->whereRaw('1 = 0');
        }

        return DataTables::of($query)
            ->editColumn('imagen', function ($row) {
                if (!$row->imagen) {
                    return '<div class="physical-inventory__cover physical-inventory__cover--empty"><i class="bi bi-journal"></i></div>';
                }

                $ruta = str_starts_with($row->imagen, 'http')
                    ? $row->imagen
                    : asset(ltrim($row->imagen, '/'));

                return '<img src="' . e($ruta) . '" alt="Portada" class="physical-inventory__cover-image">';
            })
            ->addColumn('codigo_catalogo', function ($row) {
                $codigo = trim((string) (($row->codigo_dewey ?? '') . ($row->codigo ?? '')));

                if ($codigo !== '') {
                    return '<div class="physical-inventory__code"><strong>' . e($codigo) . '</strong><small>Codigo catalografico</small></div>';
                }

                if (!empty($row->codigo_ant)) {
                    return '<div class="physical-inventory__code"><strong>' . e($row->codigo_ant) . '</strong><small>Codigo anterior</small></div>';
                }

                return '<div class="physical-inventory__code"><strong>Sin codigo</strong><small>Pendiente</small></div>';
            })
            ->editColumn('titulo', function ($row) {
                return '<div class="physical-inventory__book"><strong>' . e($row->titulo ?? 'Sin titulo') . '</strong><small>' . e($row->tipo_registro ?? 'Sin tipo de registro') . '</small></div>';
            })
            ->addColumn('biblioteca', function ($row) {
                if ($row->biblioteca_nombre) {
                    return '<span class="physical-inventory__library">' . e($row->biblioteca_nombre) . '</span>';
                }

                return '<span class="physical-inventory__library physical-inventory__library--empty">Sin biblioteca</span>';
            })
            ->addColumn('resumen_estado', function ($row) {
                return '
                    <div class="physical-inventory__status-grid">
                        <div class="physical-inventory__metric physical-inventory__metric--total">
                            <span class="physical-inventory__metric-label">Total</span>
                            <strong class="physical-inventory__metric-value">' . (int) $row->total_ejemplares . '</strong>
                        </div>
                        <div class="physical-inventory__metric physical-inventory__metric--available">
                            <span class="physical-inventory__metric-label">Disponibles</span>
                            <strong class="physical-inventory__metric-value">' . (int) $row->disponibles . '</strong>
                        </div>
                        <div class="physical-inventory__metric physical-inventory__metric--loaned">
                            <span class="physical-inventory__metric-label">Prestados</span>
                            <strong class="physical-inventory__metric-value">' . (int) $row->prestados . '</strong>
                        </div>
                        <div class="physical-inventory__metric physical-inventory__metric--reserved">
                            <span class="physical-inventory__metric-label">Reservados</span>
                            <strong class="physical-inventory__metric-value">' . (int) $row->reservados . '</strong>
                        </div>
                    </div>
                ';
            })
            ->addColumn('acciones', function ($row) {
                $url = url('administracion/ejemplares/' . $row->libro_id);
                $url .= $row->biblioteca_id ? '?biblioteca_id=' . $row->biblioteca_id : '';

                return '<a href="' . e($url) . '" class="btn btn-sm btn-primary">Ver ejemplares</a>';
            })
            ->rawColumns(['imagen', 'codigo_catalogo', 'titulo', 'biblioteca', 'resumen_estado', 'acciones'])
            ->make(true);
    }
}
