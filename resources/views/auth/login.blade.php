<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Biblioteca</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #4a4a4a;
        }
        .login-container img {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            border-radius: 50%;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Imagen de referencia: Icono de biblioteca (libro abierto) -->
        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Icono de Biblioteca">
        <h2>Iniciar Sesión en el Sistema de Biblioteca</h2>
        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf
            <!-- Campo para email (único en la tabla users) -->
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com">
            </div>
            <!-- Campo para password -->
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
            </div>
            <!-- Checkbox para recordar sesión (opcional, usa rememberToken en la tabla) -->
            <div class="form-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Recordarme</label>
            </div>
            <button type="submit" class="btn">Iniciar Sesión</button>
            <div id="errorMessage" class="error" style="display: none;"></div>
        </form>
        <div class="footer">
            <p>¿Olvidaste tu contraseña? <a href="/forgot-password">Recupérala aquí</a></p>
            <p>¿No tienes cuenta? <a href="/register">Regístrate como LECTOR</a></p>
            <!-- Nota: El registro podría crear usuarios con tipo_usuario 'LECTOR' por defecto -->
        </div>
    </div>

    <script>
        // Validación básica en el frontend
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('errorMessage');

            // Validar formato de email básico
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                errorMessage.textContent = 'Por favor, ingresa un correo electrónico válido.';
                errorMessage.style.display = 'block';
                event.preventDefault();
                return;
            }

            // Validar que la contraseña no esté vacía
            if (password.length < 6) {
                errorMessage.textContent = 'La contraseña debe tener al menos 6 caracteres.';
                errorMessage.style.display = 'block';
                event.preventDefault();
                return;
            }

            // Si pasa validación, ocultar error
            errorMessage.style.display = 'none';
        });
    </script>
</body>
</html>