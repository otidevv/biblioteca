<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>
</head>
<body style="margin:0; padding:0; background:#eef2f7; font-family:'Segoe UI', Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td align="center">

<table width="600" style="background:#ffffff; margin:40px auto; border-radius:12px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.08);">

    <!-- Header -->
    <tr>
        <td style="background:linear-gradient(135deg,#0b3c6f,#145da0); padding:25px; text-align:center;">
            <img src="{{ asset('img/logo_unamad.png') }}" alt="UNAMAD" style="height:65px;">
            <p style="color:#ffffff; margin:10px 0 0; font-size:14px; letter-spacing:1px;">
                SISTEMA DE BIBLIOTECA
            </p>
        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style="padding:35px 30px; color:#333;">

            <h2 style="margin-top:0; color:#0b3c6f;">
                Hola, {{ $user->name ?? 'usuario' }}
            </h2>

            <p style="font-size:15px; line-height:1.6;">
                Se solicitó restablecer la contraseña de su cuenta en el 
                <strong style="color:#0b3c6f;">Sistema de Biblioteca UNAMAD</strong>.
            </p>

            <!-- Caja informativa -->
            <div style="background:#f4f8fc; border-left:4px solid #0b3c6f; padding:12px 15px; margin:20px 0; border-radius:6px;">
                <p style="margin:0; font-size:14px;">
                    Este enlace es válido por <strong>10 minutos</strong>.
                </p>
            </div>

            <!-- Botón -->
            <div style="text-align:center; margin:35px 0;">
                <a href="{{ $url }}" 
                   style="
                        background:linear-gradient(135deg,#0b3c6f,#145da0);
                        color:#ffffff;
                        padding:14px 30px;
                        text-decoration:none;
                        border-radius:8px;
                        font-weight:bold;
                        font-size:15px;
                        display:inline-block;
                        box-shadow:0 6px 15px rgba(11,60,111,0.3);
                   ">
                    Restablecer contraseña
                </a>
            </div>

            <p style="font-size:13px; color:#666; line-height:1.5;">
                Si no solicitó este cambio, puede ignorar este mensaje sin realizar ninguna acción.
            </p>

        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style="background:#0b3c6f; text-align:center; padding:18px; color:#ffffff; font-size:12px;">
            © {{ date('Y') }} Universidad Nacional Amazónica de Madre de Dios<br>
            Sistema de Biblioteca
        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>