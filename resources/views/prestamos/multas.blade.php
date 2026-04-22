@extends('layouts.admin')

@section('page-title', 'Multas y sanciones')

@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/lectores/penalizaciones.css') }}?v={{ filemtime(public_path('css/lectores/penalizaciones.css')) }}" rel="stylesheet" />
    <style>
        :root{
            --unamad-primary:#0b3c6f;
            --unamad-primary-2:#145da0;
            --unamad-accent:#f2b705;
            --unamad-soft:#eef5fc;
            --unamad-border:#d8e4f2;
            --unamad-text:#17324d;
            --unamad-muted:#6b7c93;
            --unamad-success:#188754;
            --unamad-success-soft:#eaf7f0;
            --unamad-danger:#b54708;
            --unamad-danger-soft:#fff4e8;
            --panel-shadow:0 16px 40px rgba(11,60,111,.08);
            --card-shadow:0 12px 28px rgba(15,40,81,.08);
            --radius-xl:24px;
            --radius-lg:18px;
            --radius-md:14px;
        }

        .admin-section{
            display:grid;
            gap:1.25rem;
        }

        .admin-breadcrumb{
            display:flex;
            align-items:center;
            gap:.5rem;
            color:var(--unamad-muted);
            font-size:.93rem;
            font-weight:500;
            padding:.15rem .15rem 0;
        }

        .admin-breadcrumb__current{
            color:var(--unamad-primary);
            font-weight:700;
        }

        .admin-panel{
            border:1px solid rgba(11,60,111,.08);
            border-radius:var(--radius-xl);
            background:
                radial-gradient(circle at top right, rgba(20,93,160,.08), transparent 32%),
                linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,251,255,.98));
            box-shadow:var(--panel-shadow);
            overflow:hidden;
        }

        .admin-panel__header{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:1rem;
            padding:1.5rem 1.5rem 1rem;
            border-bottom:1px solid rgba(11,60,111,.08);
            background:linear-gradient(135deg, rgba(11,60,111,.04), rgba(20,93,160,.02));
        }

        .admin-panel__title{
            margin:0;
            color:var(--unamad-primary);
            font-size:1.55rem;
            font-weight:800;
            letter-spacing:-.02em;
        }

        .admin-panel__copy{
            margin:.45rem 0 0;
            color:var(--unamad-muted);
            max-width:760px;
            line-height:1.6;
        }

        .admin-actions{
            display:flex;
            align-items:center;
            gap:.75rem;
            flex-wrap:wrap;
        }

        .admin-btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.55rem;
            border:0;
            border-radius:14px;
            padding:.9rem 1.15rem;
            font-weight:700;
            text-decoration:none;
            transition:.2s ease;
            box-shadow:0 8px 18px rgba(11,60,111,.15);
        }

        .admin-btn--primary{
            color:#fff;
            background:linear-gradient(135deg, var(--unamad-primary), var(--unamad-primary-2));
        }

        .admin-btn--primary:hover{
            transform:translateY(-1px);
            box-shadow:0 12px 24px rgba(11,60,111,.22);
        }

        .penalty-overview{
            display:grid;
            grid-template-columns:repeat(3, minmax(0,1fr));
            gap:1rem;
            padding:1.25rem 1.5rem 0;
        }

        .penalty-stat{
            position:relative;
            overflow:hidden;
            border:1px solid var(--unamad-border);
            border-radius:20px;
            background:linear-gradient(180deg,#fff,#f7fbff);
            padding:1.15rem 1.15rem 1.05rem;
            box-shadow:var(--card-shadow);
        }

        .penalty-stat::after{
            content:"";
            position:absolute;
            inset:auto -10px -10px auto;
            width:72px;
            height:72px;
            border-radius:50%;
            background:radial-gradient(circle, rgba(20,93,160,.12), transparent 68%);
        }

        .penalty-stat__label{
            display:block;
            color:var(--unamad-muted);
            font-size:.92rem;
            font-weight:600;
            margin-bottom:.45rem;
        }

        .penalty-stat__value{
            color:var(--unamad-primary);
            font-size:1.9rem;
            line-height:1;
            font-weight:800;
            letter-spacing:-.03em;
        }

        .penalty-filters{
            margin:1.25rem 1.5rem 0;
            border:1px solid var(--unamad-border);
            border-radius:22px;
            background:#fff;
            padding:1rem;
            box-shadow:var(--card-shadow);
        }

        .penalty-filters__grid{
            display:grid;
            grid-template-columns:repeat(5, minmax(0,1fr));
            gap:1rem;
            align-items:end;
        }

        .penalty-field{
            display:grid;
            gap:.42rem;
        }

        .penalty-field--wide{
            grid-column:span 2;
        }

        .penalty-field label{
            font-size:.88rem;
            font-weight:700;
            color:var(--unamad-text);
            margin:0;
        }

        .penalty-field input,
        .penalty-field select,
        .penalty-field textarea,
        .form-control,
        .form-select{
            border:1px solid #cfdceb;
            border-radius:14px;
            min-height:48px;
            padding:.75rem .95rem;
            box-shadow:none;
            transition:border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
            color:var(--unamad-text);
            background:#fff;
        }

        .penalty-field input:focus,
        .penalty-field select:focus,
        .penalty-field textarea:focus,
        .form-control:focus,
        .form-select:focus{
            border-color:rgba(20,93,160,.55);
            box-shadow:0 0 0 .2rem rgba(20,93,160,.12);
        }

        .penalty-actions{
            display:flex;
            gap:.75rem;
            align-items:center;
            justify-content:flex-end;
            flex-wrap:wrap;
        }

        .btn{
            border-radius:14px;
            font-weight:700;
            padding:.75rem 1rem;
            box-shadow:none !important;
        }

        .btn-primary{
            background:linear-gradient(135deg, var(--unamad-primary), var(--unamad-primary-2));
            border-color:transparent;
        }

        .btn-primary:hover{
            background:linear-gradient(135deg, #0a3663, #114d86);
            border-color:transparent;
        }

        .btn-success{
            background:linear-gradient(135deg, #13824d, #1ca463);
            border-color:transparent;
        }

        .btn-success:hover{
            background:linear-gradient(135deg, #106e41, #188754);
            border-color:transparent;
        }

        .btn-outline-primary{
            border-color:rgba(20,93,160,.28);
            color:var(--unamad-primary);
            background:#fff;
        }

        .btn-outline-primary:hover{
            background:var(--unamad-soft);
            border-color:rgba(20,93,160,.38);
            color:var(--unamad-primary);
        }

        .btn-outline-secondary{
            border-color:#d6dee8;
            color:#58677a;
            background:#fff;
        }

        .btn-outline-secondary:hover{
            background:#f6f8fb;
            color:#3f4d60;
            border-color:#c7d1de;
        }

        .alert{
            margin:1rem 1.5rem 0;
            border-radius:18px !important;
            padding:1rem 1.1rem;
        }

        .penalty-empty{
            margin:1.2rem 1.5rem 1.5rem;
            padding:2.25rem 1.5rem;
            border:1px dashed #c8d9eb;
            border-radius:22px;
            text-align:center;
            color:var(--unamad-muted);
            background:linear-gradient(180deg,#fcfdff,#f5f9fd);
            font-weight:600;
        }

        .penalty-list{
            display:grid;
            gap:1rem;
            padding:1.2rem 1.5rem 1.5rem;
        }

        .penalty-card{
            position:relative;
            border:1px solid rgba(11,60,111,.08);
            border-radius:24px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.98), rgba(246,250,255,.98));
            box-shadow:var(--card-shadow);
            padding:1.25rem;
            overflow:hidden;
        }

        .penalty-card::before{
            content:"";
            position:absolute;
            inset:0 auto 0 0;
            width:5px;
            background:linear-gradient(180deg, var(--unamad-primary), var(--unamad-accent));
        }

        .penalty-card__head{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:1rem;
            margin-bottom:.9rem;
        }

        .penalty-card__title{
            font-size:1.12rem;
            font-weight:800;
            color:var(--unamad-primary);
            line-height:1.3;
        }

        .penalty-card__meta{
            color:var(--unamad-muted);
            font-size:.94rem;
            margin-top:.25rem;
        }

        .penalty-card__badges{
            display:flex;
            flex-wrap:wrap;
            gap:.5rem;
            justify-content:flex-end;
        }

        .penalty-badge{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:.45rem .78rem;
            border-radius:999px;
            font-size:.78rem;
            font-weight:800;
            letter-spacing:.02em;
            border:1px solid transparent;
            white-space:nowrap;
        }

        .penalty-badge.is-active{
            background:var(--unamad-success-soft);
            color:var(--unamad-success);
            border-color:rgba(24,135,84,.18);
        }

        .penalty-badge.is-closed{
            background:var(--unamad-danger-soft);
            color:var(--unamad-danger);
            border-color:rgba(181,71,8,.12);
        }

        .penalty-badge.is-neutral{
            background:#eef4fb;
            color:var(--unamad-primary);
            border-color:rgba(11,60,111,.1);
        }

        .penalty-card__book{
            display:flex;
            align-items:center;
            gap:.55rem;
            margin-bottom:1rem;
            padding:.85rem 1rem;
            border-radius:16px;
            background:linear-gradient(135deg, rgba(11,60,111,.05), rgba(20,93,160,.08));
            color:var(--unamad-text);
            font-weight:700;
        }

        .penalty-card__book::before{
            content:"📘";
            font-size:1rem;
        }

        .penalty-card__grid{
            display:grid;
            grid-template-columns:repeat(3, minmax(0,1fr));
            gap:.85rem;
        }

        .penalty-card__item{
            border:1px solid #e1ebf6;
            border-radius:18px;
            background:#fff;
            padding:.9rem 1rem;
            min-height:88px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            gap:.25rem;
        }

        .penalty-card__item span{
            color:var(--unamad-muted);
            font-size:.8rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:.04em;
        }

        .penalty-card__item strong{
            color:var(--unamad-text);
            font-size:.98rem;
            font-weight:800;
            line-height:1.4;
            word-break:break-word;
        }

        .penalty-card__notes{
            margin-top:1rem;
            border:1px solid #e2ebf5;
            border-radius:18px;
            background:#fbfdff;
            padding:1rem 1rem .2rem;
        }

        .penalty-card__notes p{
            margin:0 0 .8rem;
            color:var(--unamad-text);
            line-height:1.65;
        }

        .penalty-card__notes strong{
            color:var(--unamad-primary);
        }

        .modal-content{
            border:none;
            border-radius:26px;
            overflow:hidden;
            box-shadow:0 24px 60px rgba(12,33,65,.18);
        }

        .modal-header{
            padding:1.15rem 1.3rem;
            border-bottom:1px solid rgba(11,60,111,.08);
            background:linear-gradient(135deg, rgba(11,60,111,.05), rgba(20,93,160,.03));
        }

        .modal-title{
            color:var(--unamad-primary);
            font-weight:800;
            margin:0;
        }

        .modal-body{
            padding:1.3rem;
            background:#fff;
        }

        .modal-footer{
            padding:1rem 1.3rem 1.3rem;
            border-top:1px solid rgba(11,60,111,.08);
            background:#fbfdff;
        }

        .admin-modal-section{
            border:1px solid #e3ecf6;
            border-radius:22px;
            background:linear-gradient(180deg,#ffffff,#f9fbff);
            padding:1rem;
        }

        .form-label{
            font-weight:700;
            color:var(--unamad-text);
            margin-bottom:.45rem;
        }

        textarea.form-control{
            min-height:120px;
            resize:vertical;
        }

        .pagination{
            gap:.45rem;
        }

        .page-link{
            border-radius:12px !important;
            border:1px solid #d8e3ef;
            color:var(--unamad-primary);
            padding:.6rem .9rem;
        }

        .page-item.active .page-link{
            background:linear-gradient(135deg, var(--unamad-primary), var(--unamad-primary-2));
            border-color:transparent;
        }

        .select2-container--default .select2-selection--single{
            border:1px solid #cfdceb;
            border-radius:14px;
            min-height:48px;
            display:flex;
            align-items:center;
            padding:0 .3rem;
        }

        .select2-container .select2-selection--single .select2-selection__rendered{
            color:var(--unamad-text);
            line-height:46px;
            padding-left:.65rem;
            padding-right:2rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow{
            height:46px;
            right:8px;
        }

        .select2-dropdown{
            border:1px solid #d7e3ef;
            border-radius:14px;
            overflow:hidden;
            box-shadow:0 12px 30px rgba(15,40,81,.12);
        }

        .select2-search--dropdown .select2-search__field{
            border:1px solid #d6e0ec;
            border-radius:10px;
            padding:.55rem .75rem;
        }

        @media (max-width: 1199.98px){
            .penalty-filters__grid{
                grid-template-columns:repeat(3, minmax(0,1fr));
            }

            .penalty-field--wide{
                grid-column:span 3;
            }

            .penalty-card__grid{
                grid-template-columns:repeat(2, minmax(0,1fr));
            }
        }

        @media (max-width: 991.98px){
            .admin-panel__header{
                flex-direction:column;
            }

            .penalty-overview{
                grid-template-columns:1fr;
            }

            .penalty-filters__grid{
                grid-template-columns:repeat(2, minmax(0,1fr));
            }

            .penalty-field--wide{
                grid-column:span 2;
            }
        }

        @media (max-width: 767.98px){
            .admin-panel__header,
            .penalty-overview,
            .penalty-list{
                padding-left:1rem;
                padding-right:1rem;
            }

            .penalty-filters{
                margin-left:1rem;
                margin-right:1rem;
                padding:.9rem;
            }

            .alert{
                margin-left:1rem;
                margin-right:1rem;
            }

            .penalty-filters__grid,
            .penalty-card__grid{
                grid-template-columns:1fr;
            }

            .penalty-field--wide{
                grid-column:span 1;
            }

            .penalty-card{
                padding:1rem;
            }

            .penalty-card__head{
                flex-direction:column;
            }

            .penalty-card__badges{
                justify-content:flex-start;
            }

            .penalty-actions{
                justify-content:stretch;
            }

            .penalty-actions .btn{
                flex:1 1 auto;
            }
        }
    </style>
@endsection

@section('js')
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script>
        $(function () {
            const lectoresUrl = @json(route('prestamos.multas.lectores'));

            function configurarSelectLector(selector, dropdownParent) {
                const $select = $(selector);

                if (!$select.length || typeof $select.select2 !== 'function') {
                    return;
                }

                $select.select2({
                    width: '100%',
                    language: 'es',
                    allowClear: true,
                    placeholder: $select.data('placeholder') || 'Buscar lector por nombre o DNI',
                    dropdownParent: dropdownParent ? $(dropdownParent) : $(document.body),
                    ajax: {
                        url: lectoresUrl,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term || ''
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.results || []
                            };
                        }
                    }
                });
            }

            configurarSelectLector('#filtro_lector_id');
            configurarSelectLector('#nueva_lector_id', '#modalNuevaSancion');

            @if($errors->any())
                const modalNuevaSancion = document.getElementById('modalNuevaSancion');
                if (modalNuevaSancion && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(modalNuevaSancion).show();
                }
            @endif
        });
    </script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Prestamos</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Multas</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Sanciones registradas</h2>
                <p class="admin-panel__copy">
                    Consulta todas las sanciones, revisa el detalle de cada caso y levanta sanciones activas cuando corresponda.
                </p>
            </div>

            <div class="admin-actions">
                <button type="button" class="admin-btn admin-btn--primary" data-bs-toggle="modal" data-bs-target="#modalNuevaSancion">
                    <span>＋</span>
                    <span>Nueva sancion</span>
                </button>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success rounded-4 border-0 mb-0">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger rounded-4 border-0 mb-0">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="penalty-overview">
            <article class="penalty-stat">
                <span class="penalty-stat__label">Total registradas</span>
                <strong class="penalty-stat__value">{{ number_format($resumen['total']) }}</strong>
            </article>
            <article class="penalty-stat">
                <span class="penalty-stat__label">Activas</span>
                <strong class="penalty-stat__value">{{ number_format($resumen['activas']) }}</strong>
            </article>
            <article class="penalty-stat">
                <span class="penalty-stat__label">Cerradas</span>
                <strong class="penalty-stat__value">{{ number_format($resumen['cerradas']) }}</strong>
            </article>
        </section>

        <section class="admin-modal-section penalty-filters">
            <form method="GET" class="penalty-filters__grid">
                <div class="penalty-field penalty-field--wide">
                    <label for="filtro_lector_id">Lector</label>
                    <select id="filtro_lector_id" name="lector_id" data-placeholder="Buscar por nombre o DNI">
                        @if($lectorFiltro)
                            <option value="{{ $lectorFiltro->id }}" selected>
                                {{ $lectorFiltro->name }}{{ $lectorFiltro->persona?->dni ? ' - DNI ' . $lectorFiltro->persona->dni : '' }} - {{ $lectorFiltro->email }}
                            </option>
                        @endif
                    </select>
                </div>

                <div class="penalty-field">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="1" @selected(request('estado') === '1')>Activa</option>
                        <option value="2" @selected(request('estado') === '2')>Cerrada</option>
                    </select>
                </div>

                <div class="penalty-field">
                    <label for="tipo">Tipo</label>
                    <input type="text" id="tipo" name="tipo" value="{{ request('tipo') }}" placeholder="Tardanza, deterioro, reserva...">
                </div>

                <div class="penalty-field">
                    <label for="fecha_desde">Fecha desde</label>
                    <input type="date" id="fecha_desde" name="fecha_desde" value="{{ request('fecha_desde') }}">
                </div>

                <div class="penalty-field">
                    <label for="fecha_hasta">Fecha hasta</label>
                    <input type="date" id="fecha_hasta" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                </div>

                <div class="penalty-actions">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="{{ url('/prestamos/multas') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </section>

        @if($sanciones->isEmpty())
            <section class="penalty-empty">
                No se encontraron sanciones con los filtros aplicados.
            </section>
        @else
            <section class="penalty-list">
                @foreach($sanciones as $sancion)
                    @php
                        $esActiva = (int) ($sancion->estado ?? 0) === 1;
                        $estadoTexto = $esActiva ? 'Activa' : 'Cerrada';
                        $estadoClase = $esActiva ? 'is-active' : 'is-closed';
                        $tipoTexto = $sancion->tipoSancion?->nombre ?? $sancion->tipo ?? '-';
                        $referenciaPrestamo = $sancion->prestamo;
                        $referenciaReserva = $sancion->reservacion;
                        $libroRelacionado = $referenciaPrestamo?->ejemplar?->libro?->titulo
                            ?? $referenciaReserva?->ejemplar?->libro?->titulo
                            ?? 'Sin libro relacionado';
                        $bibliotecaRelacionada = $referenciaPrestamo?->ejemplar?->biblioteca?->nombre
                            ?? $referenciaReserva?->ejemplar?->biblioteca?->nombre
                            ?? 'Biblioteca no disponible';
                        $origen = $referenciaPrestamo ? 'Prestamo' : ($referenciaReserva ? 'Reservacion' : 'Registro manual');
                        $duracionTexto = $sancion->duracion ? $sancion->duracion . ' dia' . ((int) $sancion->duracion === 1 ? '' : 's') : '-';
                    @endphp

                    <article class="penalty-card">
                        <div class="penalty-card__head">
                            <div>
                                <div class="penalty-card__title">
                                    {{ $sancion->motivo ?: ($sancion->tipo ?: 'Sancion registrada') }}
                                </div>
                                <div class="penalty-card__meta">
                                    {{ $sancion->usuario->name ?? 'Lector no disponible' }} · {{ $bibliotecaRelacionada }}
                                </div>
                            </div>

                            <div class="penalty-card__badges">
                                <span class="penalty-badge {{ $estadoClase }}">{{ $estadoTexto }}</span>
                                <span class="penalty-badge is-neutral">{{ $origen }}</span>
                            </div>
                        </div>

                        <div class="penalty-card__book">{{ $libroRelacionado }}</div>

                        <div class="penalty-card__grid">
                            <div class="penalty-card__item">
                                <span>Tipo</span>
                                <strong>{{ $tipoTexto }}</strong>
                            </div>
                            <div class="penalty-card__item">
                                <span>Codigo de pago</span>
                                <strong>{{ $sancion->codigo_pago ?: '-' }}</strong>
                            </div>
                            <div class="penalty-card__item">
                                <span>Fecha inicio</span>
                                <strong>{{ $sancion->fecha_inicio?->format('d/m/Y') ?? '-' }}</strong>
                            </div>
                            <div class="penalty-card__item">
                                <span>Fecha fin</span>
                                <strong>{{ $sancion->fecha_fin?->format('d/m/Y') ?? '-' }}</strong>
                            </div>
                            <div class="penalty-card__item">
                                <span>Duracion</span>
                                <strong>{{ $duracionTexto }}</strong>
                            </div>
                            <div class="penalty-card__item">
                                <span>Registrado por</span>
                                <strong>{{ $sancion->bibliotecario->name ?? '-' }}</strong>
                            </div>
                        </div>

                        @if($sancion->observaciones || $sancion->detalles_termino)
                            <div class="penalty-card__notes">
                                @if($sancion->observaciones)
                                    <p><strong>Observaciones:</strong> {{ $sancion->observaciones }}</p>
                                @endif
                                @if($sancion->detalles_termino)
                                    <p><strong>Detalle de cierre:</strong> {{ $sancion->detalles_termino }}</p>
                                @endif
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mt-3 justify-content-end">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalSancion{{ $sancion->id }}">
                                Ver sancion
                            </button>

                            @if($esActiva)
                                <form method="POST" action="{{ route('prestamos.multas.levantar', $sancion) }}">
                                    @csrf
                                    <input type="hidden" name="detalles_termino" value="Sancion levantada manualmente.">
                                    <button type="submit" class="btn btn-success">
                                        Levantar sancion
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>

                    <div class="modal fade" id="modalSancion{{ $sancion->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h5 class="modal-title">Sancion #{{ $sancion->id }}</h5>
                                        <small class="text-muted">{{ $sancion->usuario->name ?? 'Lector no disponible' }}</small>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="penalty-card__grid">
                                        <div class="penalty-card__item">
                                            <span>Estado</span>
                                            <strong>{{ $estadoTexto }}</strong>
                                        </div>
                                        <div class="penalty-card__item">
                                            <span>Origen</span>
                                            <strong>{{ $origen }}</strong>
                                        </div>
                                        <div class="penalty-card__item">
                                            <span>Biblioteca</span>
                                            <strong>{{ $bibliotecaRelacionada }}</strong>
                                        </div>
                                        <div class="penalty-card__item">
                                            <span>Libro</span>
                                            <strong>{{ $libroRelacionado }}</strong>
                                        </div>
                                        <div class="penalty-card__item">
                                            <span>Fecha inicio</span>
                                            <strong>{{ $sancion->fecha_inicio?->format('d/m/Y') ?? '-' }}</strong>
                                        </div>
                                        <div class="penalty-card__item">
                                            <span>Fecha fin</span>
                                            <strong>{{ $sancion->fecha_fin?->format('d/m/Y') ?? '-' }}</strong>
                                        </div>
                                    </div>

                                    <div class="penalty-card__notes">
                                        <p><strong>Motivo:</strong> {{ $sancion->motivo ?: '-' }}</p>
                                        <p><strong>Tipo:</strong> {{ $tipoTexto }}</p>
                                        <p><strong>Codigo de pago:</strong> {{ $sancion->codigo_pago ?: '-' }}</p>
                                        <p><strong>Observaciones:</strong> {{ $sancion->observaciones ?: '-' }}</p>
                                        <p><strong>Detalle de cierre:</strong> {{ $sancion->detalles_termino ?: '-' }}</p>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </section>

            <div class="px-4 pb-4">
                {{ $sanciones->links() }}
            </div>
        @endif
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalNuevaSancion" tabindex="-1" aria-labelledby="modalNuevaSancionTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('prestamos.multas.nueva') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-semibold" id="modalNuevaSancionTitle">Nueva sancion</h5>
                    <p class="text-muted mb-0">Registra una sancion manual seleccionando lector, tipo y periodo de aplicacion.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="admin-modal-section">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nueva_lector_id" class="form-label">Lector</label>
                            <select id="nueva_lector_id" name="lector_id" class="form-select" data-placeholder="Buscar por nombre o DNI" required>
                                @if($lectorFormulario)
                                    <option value="{{ $lectorFormulario->id }}" selected>
                                        {{ $lectorFormulario->name }}{{ $lectorFormulario->persona?->dni ? ' - DNI ' . $lectorFormulario->persona->dni : '' }} - {{ $lectorFormulario->email }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="tipo_sancion_id" class="form-label">Tipo de sancion</label>
                            <select id="tipo_sancion_id" name="tipo_sancion_id" class="form-select" required>
                                <option value="">Seleccione un tipo</option>
                                @foreach($tiposSancion as $tipoSancion)
                                    <option value="{{ $tipoSancion->id }}" @selected(old('tipo_sancion_id') == $tipoSancion->id)>
                                        {{ $tipoSancion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio', now()->toDateString()) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_fin" class="form-label">Fecha fin</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ old('fecha_fin') }}" required>
                        </div>

                        <div class="col-12">
                            <label for="motivo" class="form-label">Motivo</label>
                            <textarea id="motivo" name="motivo" class="form-control" rows="4" required placeholder="Describe el motivo de la sancion">{{ old('motivo') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4">Guardar sancion</button>
            </div>
        </form>
    </div>
</div>
@endsection