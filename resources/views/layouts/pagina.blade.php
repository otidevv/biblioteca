<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Libros - Biblioteca UNAMAD</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- TU CSS -->
    <link href="{{ asset('css/pagina/index.css') }}" rel="stylesheet" />
    @yield('css')
</head>
<body>
    <!-- TOPBAR -->
    <div class="topbar bg-dark text-white py-2">
        <div class="container d-flex justify-content-between">
            <div><b>📚 BIBLIOTECA UNAMAD</b></div>
            <div>UNAMAD | ADMIN</div>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Biblioteca</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">☰</button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Actividades</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Quiénes somos</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CAROUSEL CABECERA -->
    <div id="carouselHeader" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f" class="d-block w-100" alt="Biblioteca">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Bienvenido al Catálogo</h5>
                    <p>Explora nuestra colección de libros destacados</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66" class="d-block w-100" alt="Lectura">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Encuentra tu próxima lectura</h5>
                    <p>Autores clásicos y contemporáneos</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794" class="d-block w-100" alt="Libros">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Biblioteca UNAMAD</h5>
                    <p>Conocimiento al alcance de todos</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselHeader" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselHeader" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <!-- BUSCADOR FUERA DEL BANNER -->
    <div class="search-section bg-light py-4">
        <div class="container">
            <form class="row g-2">
                <div class="col-12 col-md-3">
                    <select class="form-select form-select-lg">
                        <option value="titulo">Título del libro</option>
                        <option value="autor">Autor</option>
                        <option value="editorial">Editorial</option>
                        <option value="materia">Materia</option>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <input class="form-control form-control-lg" placeholder="Escribe tu búsqueda...">
                </div>
                <div class="col-12 col-md-3">
                    <button class="btn btn-primary w-100 btn-lg">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- CATÁLOGO DE LIBROS -->
    <div class="container my-5">
        @yield('content')        
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('js')
</body>
</html>
