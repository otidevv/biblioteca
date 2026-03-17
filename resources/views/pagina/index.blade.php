<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Biblioteca UNAMAD</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        /* 🎨 COLORES */
        :root {
            --primary: #d6336c;
            --secondary: #0d6efd;
            --bg: #f4f6f9;
        }

        /* GENERAL */
        body {
            background: var(--bg);
            font-family: 'Segoe UI', sans-serif;
        }

        /* TOPBAR */
        .topbar {
            background: var(--primary);
            color: white;
            padding: 8px 0;
            font-size: 14px;
        }

        /* NAVBAR */
        .navbar {
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* BANNER */
        .banner {
            position: relative;
        }

        .carousel-item {
            height: 300px;
        }

        .carousel-item img {
            height: 300px;
            object-fit: cover;
            filter: brightness(0.6);
        }

        @media(min-width:768px) {
            .carousel-item {
                height: 400px;
            }

            .carousel-item img {
                height: 400px;
            }
        }

        /* BUSCADOR */
        .banner-search {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 700px;
        }

        .search-box {
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* TARJETAS */
        .book-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: 0.3s;
            height: 100%;
        }

        .book-card:hover {
            transform: translateY(-5px);
        }

        .book-cover {
            height: 220px;
            object-fit: cover;
        }

        /* BOTONES */
        .btn-libro {
            background: var(--secondary);
            color: white;
        }

        .btn-libro:hover {
            background: #0b5ed7;
        }

        /* ESTRELLAS */
        .stars {
            color: #ffc107;
            font-size: 14px;
        }

        .section-title {
            color: var(--primary);
            font-weight: 700;
            margin-top: 30px;
        }
    </style>

</head>

<body>

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="container d-flex justify-content-between">
            <div><b>📚 BIBLIOTECA UNAMAD</b></div>
            <div>UNAMAD | ADMIN</div>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">

            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
                ☰
            </button>

            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Actividades</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Quiénes somos</a></li>
                </ul>

                <div class="d-flex mt-2 mt-lg-0">
                    <input class="form-control me-2" placeholder="Buscar...">
                    <button class="btn btn-primary">🔍</button>
                </div>
            </div>

        </div>
    </nav>

    <!-- BANNER -->
    <div class="banner">

        <div id="carouselBanner" class="carousel slide" data-bs-ride="carousel">

            <div class="carousel-inner">

                <div class="carousel-item active">
                    <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f" class="w-100">
                </div>

                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66" class="w-100">
                </div>

                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794" class="w-100">
                </div>

            </div>

            <button class="carousel-control-prev" data-bs-target="#carouselBanner" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>

            <button class="carousel-control-next" data-bs-target="#carouselBanner" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>

        </div>

        <!-- BUSCADOR CENTRADO -->
        <div class="banner-search">
            <div class="search-box">

                <div class="row g-2">
                    <div class="col-12 col-md-8">
                        <input class="form-control form-control-lg" placeholder="Buscar libro, autor o tema...">
                    </div>

                    <div class="col-12 col-md-4">
                        <button class="btn btn-libro w-100 btn-lg">Buscar</button>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- LIBROS -->
    <div class="container">

        <h4 class="section-title">📚 Libros Destacados</h4>

        <div class="row g-4" id="contenedorLibros"></div>

    </div>

    <script>

        /* 📚 LIBROS DE EJEMPLO */
        const libros = [
            "Programación Web", "Base de Datos", "IA", "Redes",
            "Algoritmos", "Ciberseguridad", "Laravel", "Python",
            "Machine Learning", "Big Data", "Cloud Computing", "DevOps"
        ];

        const contenedor = document.getElementById('contenedorLibros');

        libros.forEach(t => {
            contenedor.innerHTML += `
                <div class="col-12 col-md-4 col-lg-3">
                <div class="card book-card">

                <img src="https://via.placeholder.com/200x300" class="book-cover">

                <div class="card-body">
                <h6>${t}</h6>
                <p class="text-muted">Autor</p>

                <div class="stars">
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
                <i class="fa fa-star-half-alt"></i>
                <i class="fa-regular fa-star"></i>
                </div>

                <button class="btn btn-libro w-100 mt-2 btn-sm">Ver detalle</button>
                </div>

                </div>
                </div>
            `;
        });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>