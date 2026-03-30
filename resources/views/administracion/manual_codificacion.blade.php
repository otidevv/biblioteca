@extends('layouts.admin')

@section('page-title', 'Manual de codificacion')

@section('css')
    <link href="{{ asset('css/administracion/manual_codificacion.css') }}?v={{ filemtime(public_path('css/administracion/manual_codificacion.css')) }}" rel="stylesheet" />
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span>Manuales</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Codificacion bibliografica</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Manual de codificacion bibliografica</h2>
                <p class="admin-panel__copy">Guia operativa para clasificacion Dewey, codigo Cutter y construccion del codigo topografico.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ url('/administracion/libros_nuevo') }}" class="btn btn-outline-secondary">Volver a nuevo libro</a>
            </div>
        </div>

        <section class="manual-doc__hero">
            <div class="manual-doc__hero-copy">
                <span class="manual-doc__eyebrow">Documento tecnico</span>
                <h3 class="manual-doc__headline">Referencia operativa para catalogacion y construccion del codigo topografico</h3>
                <p class="manual-doc__summary">Usa esta guia para entender como el sistema sugiere Dewey, calcula el Cutter, resuelve colisiones y arma el codigo final que se replica en libros y ejemplares.</p>
            </div>
            <div class="manual-doc__meta">
                <div class="manual-doc__meta-card">
                    <span class="manual-doc__meta-label">Uso principal</span>
                    <strong>Registro y revision de libros</strong>
                </div>
                <div class="manual-doc__meta-card">
                    <span class="manual-doc__meta-label">Ambito</span>
                    <strong>Catalogacion interna</strong>
                </div>
            </div>
        </section>

        <section class="manual-doc__highlights">
            <article class="manual-doc__highlight">
                <span class="manual-doc__highlight-icon"><i class="bi bi-123"></i></span>
                <div>
                    <h4>Orden de desempate</h4>
                    <p>Cutter, nombre, título, edición y luego sufijo numérico si todavía existe colisión.</p>
                </div>
            </article>
            <article class="manual-doc__highlight">
                <span class="manual-doc__highlight-icon"><i class="bi bi-journal-bookmark"></i></span>
                <div>
                    <h4>Código completo</h4>
                    <p>El codigo topografico final concatena `codigo_dewey` del libro y `codigo` local.</p>
                </div>
            </article>
            <article class="manual-doc__highlight">
                <span class="manual-doc__highlight-icon"><i class="bi bi-diagram-3"></i></span>
                <div>
                    <h4>Fuentes del sistema</h4>
                    <p>La lógica toma datos de Dewey, Cutter, autores, relaciones y actualización topográfica masiva.</p>
                </div>
            </article>
        </section>

        <article class="admin-modal-section manual-doc__content">
            <div class="manual-doc__body markdown-body">
                {!! $contenido !!}
            </div>
        </article>
    </section>
</div>
@endsection
