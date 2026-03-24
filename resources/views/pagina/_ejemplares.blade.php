@foreach($libro->ejemplares as $e)
<div class="col-6 col-md-3">

    <div class="card card-hover p-3 text-center h-100">

        <h6 class="fw-bold">{{ $e->codigo }}</h6>

        <small class="text-muted">{{ $e->biblioteca->nombre }}</small>

        <div class="mt-2">
            @if($e->estado == '1')
                <span class="badge bg-success">Disponible</span>
            @else
                <span class="badge bg-danger">Prestado</span>
            @endif
        </div>

    </div>

</div>
@endforeach