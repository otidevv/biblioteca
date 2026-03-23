<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Biblioteca</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

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
    <a href="#">📚 Libros</a>
    <a href="#">📦 Ejemplares</a>
    <a href="#">🔄 Préstamos</a>

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

<script>
function toggleSidebar(){
    let sidebar = document.getElementById('sidebar');
    let overlay = document.getElementById('overlay');

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>