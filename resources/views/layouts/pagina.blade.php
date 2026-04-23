<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Catálogo de Libros - Biblioteca UNAMAD</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('img/logo_unamad.png') }}">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- TU CSS -->
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/pagina/index.css') }}" rel="stylesheet" />
    @yield('css')
</head>
<body>
    @php($logoUnamad = asset('img/logo_unamad.png'))
    <!-- TOPBAR -->
    <div class="topbar bg-dark text-white py-2">
        <div class="container d-flex justify-content-between align-items-center">
            <div><b>📚 BIBLIOTECA UNAMAD</b></div>
            
            <div class="d-flex align-items-center gap-3">
                @auth
                    <span class="small">👤 {{ Auth::user()->name }}</span>
                    <a href="{{ url('/perfil') }}" class="btn btn-sm btn-outline-light">
                        Perfil
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger">
                            Cerrar sesión
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">
                        Iniciar sesión
                    </a>
                @endauth
            </div>
        </div>
    </div>


    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Biblioteca</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">☰</button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Actividades</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Mis Prestamos</a></li>
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
    <!-- CATÁLOGO DE LIBROS -->
    <div class="container my-5">
        @yield('content') 
        @livewireScripts       
    </div>

    <!-- Pie de página -->
   <footer class="bg-dark text-light pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row">
            <!-- Información institucional -->
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold">Universidad Nacional Amazónica de Madre de Dios</h5>
                <p class="small">
                    📍 Av. Jorge Chávez s/n, Puerto Maldonado, Madre de Dios, Perú<br>
                    🎓 Formación académica con excelencia y compromiso social.
                </p>
            </div>

            <!-- Contacto -->
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold">Contacto</h5>
                <p class="small mb-1"><i class="fa fa-phone"></i> (082) 123456</p>
                <p class="small mb-1"><i class="fa fa-envelope"></i> info@unamad.edu.pe</p>
                <p class="small"><i class="fa fa-clock"></i> Lunes - Viernes: 8:00 am - 6:00 pm</p>
            </div>

            <!-- Enlaces y redes sociales -->
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold">Enlaces útiles</h5>
                <ul class="list-unstyled small">
                    <li><a href="#" class="text-light text-decoration-none">Portal Académico</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Biblioteca Virtual</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Admisión</a></li>
                </ul>
                <div class="mt-3">
                    <a href="#" class="text-light me-3"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>
        </div>

        <hr class="border-light">
        <div class="text-center">
            <small>© 2026 Universidad Nacional Amazónica de Madre de Dios - Todos los derechos reservados</small>
        </div>
    </div>
</footer>

    <!-- Bootstrap JS -->
    <script src="https://unpkg.com/heroicons@2.0.18/24/outline/index.js" defer></script>
    <script src="{{ asset('js/jquery-3.6.3.min.js') }}"></script>
    <script src="{{ asset('lib/tabler/js/tabler.js') }}"></script>
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('js/admin.js') }}"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('js')
</body>
</html>
