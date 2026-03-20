<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Biblioteca</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{background:#f4f6f9}

/* SIDEBAR */
.sidebar{
    width:240px;
    height:100vh;
    position:fixed;
    background:#1f2a3a;
    color:#fff;
}

.sidebar a{
    display:block;
    padding:12px;
    color:#ccc;
    text-decoration:none;
}

.sidebar a:hover{
    background:#2d3f5f;
    color:#fff;
}

/* CONTENT */
.content{
    margin-left:240px;
    padding:20px;
}

/* HERO */
.hero{
    background:url('/img/biblioteca.jpg') center/cover;
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
</style>

</head>

<body>

<div class="sidebar p-3">
    <h5>📚 Biblioteca</h5>

    <a href="{{ route('home') }}">🏠 Inicio</a>
    <a href="#">📚 Libros</a>
    <a href="#">📦 Ejemplares</a>
    <a href="#">🔄 Préstamos</a>
</div>

<div class="content">

    <div class="d-flex justify-content-between mb-3">
        <h5>Sistema de Biblioteca</h5>
        <i class="bi bi-person-circle"></i>
    </div>

    @yield('content')

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>