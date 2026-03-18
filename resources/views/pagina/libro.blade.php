<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Libro - Biblioteca UNAMAD</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link" href="#">Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Actividades</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Quiénes somos</a></li>
                </ul>
                <form class="d-flex">
                    <input class="form-control me-2" placeholder="Buscar...">
                    <button class="btn btn-primary">🔍</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- DETALLE DEL LIBRO -->
    <div class="container my-5">
       
        <div class="row">
            <!-- Portada -->
            <div class="col-md-4 text-center">
                <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f" 
                     class="img-fluid rounded shadow-lg mb-3" alt="Portada del libro">
                <span class="badge bg-success">Disponible</span>
            </div>
            <!-- Información -->
            <div class="col-md-8">
                <h2 class="fw-bold">Cien años de soledad</h2>
                <p class="text-muted fs-5">Gabriel García Márquez</p>
                <p><span class="badge bg-info">Editorial Sudamericana</span> 
                   <span class="badge bg-secondary">1967</span> 
                   <span class="badge bg-warning">ISBN: 978-84-376-0494-7</span></p>
                <p><strong>Categoría:</strong> Novela, Realismo mágico</p>
                <p><strong>Resumen:</strong> Una obra maestra del realismo mágico que narra la historia de la familia Buendía en el mítico pueblo de Macondo.</p>

                <!-- Calificación -->
                <div class="stars mb-3 fs-4 text-warning">
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star-half-alt"></i>
                    <i class="fa-regular fa-star"></i>
                    <span class="ms-2 text-dark fs-6">(3.5/5)</span>
                </div>
            </div>
        </div>

        <!-- COMENTARIOS -->
        <div class="mt-5">
            <h4>💬 Opiniones de lectores</h4>
            <div class="list-group">
                <!-- Comentario 1 -->
                <div class="list-group-item">
                    <strong>Ana López</strong> <span class="text-muted small">(12/03/2026)</span>
                    <div class="text-warning">
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star-half-alt"></i>
                        <i class="fa-regular fa-star"></i>
                        <span class="ms-2">(3.5/5)</span>
                    </div>
                    <p>Un libro fascinante, lleno de simbolismo y personajes inolvidables.</p>
                </div>
                <!-- Comentario 2 -->
                <div class="list-group-item">
                    <strong>Carlos Pérez</strong> <span class="text-muted small">(13/03/2026)</span>
                    <div class="text-warning">
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <span class="ms-2">(3/5)</span>
                    </div>
                    <p>Al principio cuesta engancharse, pero luego es imposible soltarlo.</p>
                </div>
                <!-- Comentario 3 -->
                <div class="list-group-item">
                    <strong>María Torres</strong> <span class="text-muted small">(14/03/2026)</span>
                    <div class="text-warning">
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <span class="ms-2">(4/5)</span>
                    </div>
                    <p>Una obra que marcó mi vida.</p>
                </div>
                <!-- Comentario 4 -->
                <div class="list-group-item">
                    <strong>José Ramírez</strong> <span class="text-muted small">(15/03/2026)</span>
                    <div class="text-warning">
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star-half-alt"></i>
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <span class="ms-2">(2.5/5)</span>
                    </div>
                    <p>El realismo mágico en su máxima expresión.</p>
                </div>
                <!-- Comentario 5 -->
                <div class="list-group-item">
                    <strong>Laura Fernández</strong> <span class="text-muted small">(16/03/2026)</span>
                    <div class="text-warning">
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <span class="ms-2">(5/5)</span>
                    </div>
                    <p>Recomendado para quienes aman la literatura latinoamericana.</p>
                </div>
            </div>

            <!-- Paginación -->
            <nav aria-label="Comentarios">
                <ul class="pagination justify-content-center mt-3">
                    <li class="page-item disabled"><a class="page-link">Anterior</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Siguiente</a></li>
                </ul>
            </nav>
        </div>


        <!-- LIBROS SIMILARES -->
        <div class="mt-5">
            <h4>📖 Libros similares</h4>
            <div id="carouselSimilares" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <div class="card h-100 shadow-sm">
                                    <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794" class="card-img-top">
                                    <div class="card-body">
                                        <h6 class="card-title">La casa de los espíritus</h6>
                                        <a href="/1/libro" class="btn btn-sm btn-outline-primary w-100">Ver detalle</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100 shadow-sm">
                                    <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66" class="card-img-top">
                                    <div class="card-body">
                                        <h6 class="card-title">Pedro Páramo</h6>
                                        <a href="/2/libro" class="btn btn-sm btn-outline-primary w-100">Ver detalle</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100 shadow-sm">
                                    <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f" class="card-img-top">
                                    <div class="card-body">
                                        <h6 class="card-title">El otoño del patriarca</h6>
                                        <a href="/3/libro" class="btn btn-sm btn-outline-primary w-100">Ver detalle</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Podrías añadir más carousel-item si hay más recomendaciones -->
                </div>
                <button class="carousel-control-prev" data-bs-target="#carouselSimilares" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" data-bs-target="#carouselSimilares" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('js')
</body>
</html>
