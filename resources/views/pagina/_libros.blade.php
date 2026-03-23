<style>
/* CARD */
.book-card{
    border-radius:12px;
    overflow:hidden;
    transition:0.3s;
    cursor:pointer;
}
.book-card:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 20px rgba(0,0,0,0.2);
}

/* IMAGEN */
.libro-img{
    width:100%;
    height:300px; /* 🔥 límite */
    object-fit:contain; /* 🔥 no recorta */
    background:#f8f9fa;
    padding:8px;
    transition:0.3s;
}

/* HOVER ZOOM */
.book-card:hover .libro-img{
    transform:scale(1.05);
}

/* BOTÓN */
.btn-libro{
    background:#2d3f5f;
    color:#fff;
    border-radius:8px;
}
.btn-libro:hover{
    background:#1f2a3a;
}

/* ESTRELLAS */
.stars i{
    color:#f4c150;
}
</style>


<div class="row g-4" id="contenedorLibros">

    @forelse($libros as $libro)

        <div class="col-12 col-sm-6 col-md-4 col-lg-3">

            <div class="card book-card h-100">

                <!-- IMAGEN -->
                <img src="{{ $libro->imagen ?? '/img/libro.png' }}" class="libro-img">

                <div class="card-body d-flex flex-column">

                    <!-- TITULO -->
                    <h6 class="mb-1" data-bs-toggle="tooltip" title="{{ $libro->titulo }}">
                        {{ \Illuminate\Support\Str::limit($libro->titulo, 40) }}
                    </h6>

                    <!-- AUTORES -->
                    <p class="text-muted small mb-2">
                        @foreach($libro->autores as $autor)
                            {{ $autor->nombres }} {{ $autor->apellidos }}@if(!$loop->last), @endif
                        @endforeach
                    </p>

                    <!-- ESTRELLAS -->
                    <div class="stars mb-2">
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star-half-alt"></i>
                        <i class="fa-regular fa-star"></i>
                    </div>

                    <!-- BOTÓN -->
                    <a href="/{{ $libro->id }}/libro"
                       class="btn btn-libro mt-auto btn-sm w-100">
                        Ver detalle
                    </a>

                </div>

            </div>

        </div>

    @empty

        <div class="col-12 text-center">
            <p class="text-muted">No hay libros disponibles</p>
        </div>

    @endforelse

</div>

<!-- PAGINACIÓN -->
<div class="d-flex justify-content-center mt-4">
    {{ $libros->links('vendor.pagination.bootstrap-5') }}
</div>