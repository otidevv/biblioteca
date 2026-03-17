<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Detalle del Libro</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        /* COLORES */
        :root {
            --primary: #d6336c;
            --secondary: #0d6efd;
            --bg: #f4f6f9;
        }

        body {
            background: var(--bg);
            font-family: 'Segoe UI', sans-serif;
        }

        /* CONTENEDOR */
        .book-detail {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-top: 20px;
        }

        /* PORTADA */
        .book-cover {
            width: 100%;
            border-radius: 10px;
        }

        /* ESTRELLAS */
        .stars {
            color: #ffc107;
            font-size: 18px;
        }

        /* BOTONES */
        .btn-main {
            background: var(--secondary);
            color: white;
        }

        .btn-main:hover {
            background: #0b5ed7;
        }

        /* LIBROS RELACIONADOS */
        .book-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: 0.3s;
        }

        .book-card:hover {
            transform: scale(1.05);
        }

        .book-small {
            height: 150px;
            object-fit: cover;
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="book-detail">

            <div class="row">

                <!-- PORTADA -->
                <div class="col-md-4">
                    <img src="https://via.placeholder.com/300x450" class="book-cover">
                </div>

                <!-- INFORMACIÓN -->
                <div class="col-md-8">

                    <h3>Programación Web Avanzada</h3>
                    <p class="text-muted">Autor: Juan Pérez</p>

                    <div class="stars mb-2">
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star-half-alt"></i>
                        <span>(4.5)</span>
                    </div>

                    <p>
                        Este libro aborda el desarrollo web moderno utilizando tecnologías como Laravel, Vue.js y APIs
                        REST...
                    </p>

                    <hr>

                    <ul>
                        <li><b>ISBN:</b> 123-456-789</li>
                        <li><b>Año:</b> 2024</li>
                        <li><b>Categoría:</b> Tecnología</li>
                        <li><b>Disponibles:</b> 5 ejemplares</li>
                    </ul>

                    <div class="mt-3">
                        <button class="btn btn-main me-2">📖 Leer</button>
                        <button class="btn btn-success me-2">📥 Descargar</button>
                        <button class="btn btn-warning">📚 Reservar</button>
                    </div>

                </div>

            </div>

        </div>

        <!-- ⭐ CALIFICAR -->
        <div class="book-detail mt-3">

            <h5>Calificar este libro</h5>

            <div class="stars">
                <i class="fa fa-star" onclick="calificar(1)"></i>
                <i class="fa fa-star" onclick="calificar(2)"></i>
                <i class="fa fa-star" onclick="calificar(3)"></i>
                <i class="fa fa-star" onclick="calificar(4)"></i>
                <i class="fa fa-star" onclick="calificar(5)"></i>
            </div>

        </div>

        <!-- 📚 RELACIONADOS -->
        <div class="book-detail mt-3">

            <h5>Libros relacionados</h5>

            <div class="row g-3">

                <div class="col-6 col-md-3">
                    <div class="card book-card">
                        <img src="https://via.placeholder.com/200x150" class="book-small">
                        <div class="card-body">
                            <p>Laravel Básico</p>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card book-card">
                        <img src="https://via.placeholder.com/200x150" class="book-small">
                        <div class="card-body">
                            <p>JavaScript Moderno</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script>

        /* ⭐ CALIFICAR */
        function calificar(valor) {
            alert("Calificaste con " + valor + " estrellas");
        }

    </script>

</body>

</html>