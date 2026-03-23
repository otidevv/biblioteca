@forelse($comentarios as $c)
<div class="comentario mb-2">

    <div class="d-flex justify-content-between">
        {{ $c->usuario->name ?? 'Usuario anónimo' }}
        <small>{{ $c->created_at->diffForHumans() }}</small>
    </div>

    <!-- ⭐ estrellas -->
    <div class="mb-1">
        @for($i=1; $i<=5; $i++)
            @if($i <= $c->calificacion)
                <i class="fa-solid fa-star text-warning"></i>
            @else
                <i class="fa-regular fa-star text-warning"></i>
            @endif
        @endfor
    </div>

    <p class="mb-0">{{ $c->comentario }}</p>

</div>
@empty
<div class="alert alert-info">
    No hay comentarios aún
</div>
@endforelse