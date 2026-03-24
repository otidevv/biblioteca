<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Biblioteca</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet" />
<style>
:root{
    --verde:#1b8f3a;
    --verde-oscuro:#0f5c25;
    --amarillo:#f4c300;
}

body{background:#f4f6f9}

/* BOTON MENU */
.menu-btn{
    position:fixed;
    top:10px;
    left:10px;
    z-index:3000;
}

/* SIDEBAR */
.sidebar{
    width:240px;
    height:100vh;
    position:fixed;
    top:0;
    left:0;
    background:var(--verde-oscuro);
    color:#fff;
    z-index:2000;
    transition:0.3s;
}

.sidebar a{
    display:block;
    padding:12px;
    color:#ccc;
    text-decoration:none;
}

.sidebar a:hover{
    background:var(--verde);
    color:#fff;
}

/* OVERLAY */
.overlay{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
    z-index:1500;
    display:none;
}

.overlay.active{
    display:block;
}

/* CONTENT */
.content{
    margin-left:240px;
    padding:20px;
    position:relative;
    z-index:1;
}

/* HERO */
.hero{
    position:relative;
    background:url('/img/banner1.png') center/cover;
    height:300px;
    border-radius:12px;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
}

/* CARDS */
.card-hover{
    transition:0.3s;
    cursor:pointer;
}
.card-hover:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 20px rgba(0,0,0,0.2);
}
.stars i{
    color:#f4c150 !important; /* 🔥 fuerza el dorado */
    font-size:14px;
}
<style>
    /* HERO */
    .hero{
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                    url('/img/banner1.png') center/cover;
        height:260px;
        border-radius:12px;
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        flex-direction:column;
    }

    /* CARD LIBRO */
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

    /* IMAGEN LIBRO */
    .libro-img{
        width:100%;
        height:300px;
        object-fit:contain;
        background:#f8f9fa;
        padding:8px;
        transition:0.3s;
    }

    /* HOVER IMAGEN */
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

    /* TARJETAS BIBLIOTECAS */
    .card-hover{
        transition:0.3s;
        cursor:pointer;
    }
    .card-hover:hover{
        transform:translateY(-5px);
        box-shadow:0 10px 20px rgba(0,0,0,0.2);
    }
    .rating{
    display:flex;
    flex-direction: row-reverse;
    justify-content:flex-start;
    }

    .rating input{
        display:none;
    }

    .rating label{
        font-size:25px;
        color:#ccc;
        cursor:pointer;
        transition:0.2s;
    }

    .rating input:checked ~ label{
        color:#ffc107;
    }

    .rating label:hover,
    .rating label:hover ~ label{
        color:#ffc107;
    }
/* RESPONSIVE */
@media(max-width:768px){

    .sidebar{
        left:-240px;
    }

    .sidebar.active{
        left:0;
    }

    .content{
        margin-left:0;
    }
}
</style>

</head>

<body>

<!-- BOTON MENU -->
<button class="btn btn-success menu-btn d-md-none" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<!-- SIDEBAR -->
<div class="sidebar p-3" id="sidebar">

    <h5 class="text-warning">📚 Biblioteca</h5>

    <a href="{{ route('home') }}">🏠 Inicio</a>
    <a href="{{ route('catalogo') }}">📚 Catalogo</a>
    <a href="{{ route('evento') }}">📦 eventos</a>
    @auth
    <a href="{{ route('mis.reservas') }}">📦 Mis Reservaciones</a>
    <a href="{{ route('prestamos') }}">🔄 Préstamos</a>
    @endauth

</div>

<!-- OVERLAY -->
<div id="overlay" class="overlay" onclick="toggleSidebar()"></div>

<!-- CONTENIDO -->
<div class="content">

    <!-- TOP BAR -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h5 class="mb-0">Sistema de Biblioteca</h5>

        <div>
            @auth
                <span class="me-2">{{ Auth::user()->name }}</span>

                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-danger">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                </a>
            @endauth
        </div>

    </div>

    @yield('content')

</div>

<!-- ALERTAS -->
<div id="mensaje_container" class="fixed top-4 right-4 z-50 space-y-2"></div>

<!-- MODALES -->
@yield('modal')
<script>
function toggleSidebar(){
    let sidebar = document.getElementById('sidebar');
    let overlay = document.getElementById('overlay');

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}
</script>
<script src="{{ asset('js/jquery-3.6.3.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
<script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
<script src="{{ asset('js/admin.js') }}"></script>
@yield('js')

</body>
</html>