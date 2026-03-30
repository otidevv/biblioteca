<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido al Sistema de Biblioteca</title>
</head>
<body style="margin:0; padding:24px; background:#f4f7fb; font-family:Arial, Helvetica, sans-serif; color:#17212b;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:20px; overflow:hidden; border:1px solid #dbe4ee;">
        <div style="padding:28px 32px; background:linear-gradient(135deg, #0f766e 0%, #164e63 100%); color:#ffffff;">
            <div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; opacity:.88; font-weight:700;">Sistema de Biblioteca</div>
            <h1 style="margin:10px 0 0; font-size:28px; line-height:1.1;">Bienvenido, {{ $nombre }}</h1>
        </div>

        <div style="padding:28px 32px;">
            <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
                Tu cuenta de lector ha sido creada correctamente. Ya puedes ingresar al sistema de biblioteca y consultar tus reservas, préstamos y actividad lectora.
            </p>

            <div style="margin:20px 0; padding:18px 20px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0;">
                <div style="font-size:13px; color:#64748b; margin-bottom:6px;">Usuario</div>
                <div style="font-size:16px; font-weight:700; margin-bottom:14px;">{{ $correo }}</div>

                <div style="font-size:13px; color:#64748b; margin-bottom:6px;">Contraseña temporal</div>
                <div style="font-size:16px; font-weight:700;">{{ $passwordTemporal }}</div>
            </div>

            <p style="margin:0 0 14px; font-size:14px; line-height:1.7; color:#475569;">
                Te recomendamos ingresar lo antes posible y cambiar tu contraseña por una personal.
            </p>

            <p style="margin:0; font-size:14px; line-height:1.7; color:#475569;">
                Gracias por formar parte de nuestra comunidad lectora. Esperamos que disfrutes el servicio.
            </p>
        </div>
    </div>
</body>
</html>
