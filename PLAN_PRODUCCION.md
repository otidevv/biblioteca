# Plan de Configuración para Producción
**Sistema de Biblioteca UNAMAD**
**Fecha:** 2026-05-15
**Evaluado por:** Claude Code

---

## Resumen ejecutivo

El sistema está funcionando pero tiene configuraciones de desarrollo activas que representan riesgos de seguridad y estabilidad en producción. Se identificaron **5 problemas críticos**, **3 moderados** y **3 menores**.

---

## FASE 1 — Crítico (hacer antes de exponer a usuarios)

### 1.1 Cambiar variables de entorno en `.env`

| Variable | Valor actual | Valor correcto |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_URL` | `http://localhost` | `https://biblioteca.unamad.edu.pe` |
| `LOG_LEVEL` | `debug` | `error` |
| `LOG_CHANNEL` | `stack` | `daily` |

**Riesgo actual:** `APP_DEBUG=true` expone stack traces con código fuente, variables y credenciales ante cualquier error visible al usuario.

**Pasos:**
1. Abrir `.env`
2. Aplicar los cambios de la tabla
3. Limpiar caché de configuración:
   ```
   php artisan config:clear
   php artisan config:cache
   ```

---

### 1.2 Corregir PHP para producción

Archivo: `C:\php83\php.ini`

| Directiva | Valor actual | Valor correcto |
|---|---|---|
| `display_errors` | `On` | `Off` |
| `expose_php` | `On` | `Off` |
| `max_execution_time` | `0` (sin límite) | `30` |

**Pasos:**
1. Abrir `C:\php83\php.ini`
2. Localizar y cambiar cada directiva
3. Reiniciar Apache:
   ```
   httpd -k restart
   ```

---

### 1.3 Forzar redirect HTTP → HTTPS

Archivo: `C:\Apache24\conf\extra\httpd-vhosts.conf`

**Configuración actual (insegura):**
```apache
<VirtualHost *:80>
    ServerName 192.168.254.48
    DocumentRoot "C:/Apache24/htdocs/sistema-biblioteca/public"
    ...
</VirtualHost>
```

**Configuración correcta:**
```apache
<VirtualHost *:80>
    ServerName biblioteca.unamad.edu.pe
    Redirect permanent / https://biblioteca.unamad.edu.pe/
</VirtualHost>
```

**Pasos:**
1. Editar `httpd-vhosts.conf` con la configuración correcta
2. Verificar configuración: `httpd -t`
3. Reiniciar Apache: `httpd -k restart`

---

## FASE 2 — Moderado (hacer en los próximos días)

### 2.1 Compilar caché de Laravel para producción

Estos comandos aceleran la aplicación y deben ejecutarse después de cualquier deploy:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

> **Nota:** Con caché activo, los cambios en `.env` o rutas no se aplican hasta volver a correr estos comandos.

---

### 2.2 Activar verificación SSL de la API externa

Archivo: `.env`, línea `EXTERNAL_API_VERIFY_SSL`

```
EXTERNAL_API_VERIFY_SSL=true
```

**Riesgo actual:** La comunicación con `daa-documentos.unamad.edu.pe:8081` no valida el certificado del servidor, lo que permite ataques man-in-the-middle.

**Prerequisito:** Confirmar que el certificado SSL de la API externa es válido y de una CA reconocida.

---

### 2.3 Activar cifrado de sesiones

Archivo: `.env`

```
SESSION_ENCRYPT=true
```

Las sesiones se almacenan en la base de datos (`SESSION_DRIVER=database`). Cifrarlas protege los datos si alguien accede directamente a la tabla `sessions`.

---

## FASE 3 — Menor (mantenimiento)

### 3.1 Limpiar archivos de debug del proyecto

Los siguientes archivos existen en la raíz del proyecto y deben eliminarse:

| Archivo | Motivo |
|---|---|
| `debug_import.php` | Archivo de debug, no tiene uso en producción |
| `debug_probe.php` | Archivo de debug, no tiene uso en producción |
| `biblioteca-2.sql` | Dump de base de datos, riesgo si se filtra |
| `AUDITORIA_CUMPLIMIENTO_2026-03-31.md` | Documento interno, no pertenece al repo |

> Estos archivos no son accesibles via HTTP (DocumentRoot apunta a `/public`), pero representan riesgo si hay acceso al sistema de archivos del servidor.

```bash
# Desde la raíz del proyecto
git rm debug_import.php debug_probe.php
# biblioteca-2.sql y el MD moverlos fuera del proyecto o agregar al .gitignore
```

---

### 3.2 Rotación del log de Laravel

El log actual pesa **15 MB** y crecerá sin límite. Con `LOG_CHANNEL=daily` (ya incluido en Fase 1) se generará un archivo por día. Adicionalmente, limpiar el log actual:

```bash
# Vaciar el log actual (no eliminar el archivo)
echo "" > storage/logs/laravel.log
```

---

### 3.3 Renovación del certificado SSL

| Campo | Valor |
|---|---|
| Válido desde | 07/04/2026 |
| **Vence** | **22/10/2026** |
| Tiempo restante | ~5 meses |

Programar renovación para la **primera semana de octubre 2026**. El certificado está en:
- `C:\Apache24\ssl\server.crt`
- `C:\Apache24\ssl\private.key`
- `C:\Apache24\ssl\chain.crt`

---

## Checklist de verificación post-cambios

Después de aplicar todos los cambios, verificar:

- [ ] Acceder a `http://biblioteca.unamad.edu.pe` redirige automáticamente a `https://`
- [ ] Provocar un error 404 muestra página genérica (no stack trace)
- [ ] Las cabeceras HTTP no muestran `X-Powered-By: PHP/8.x`
- [ ] Login de usuarios funciona correctamente
- [ ] Gestión de libros y autores funciona
- [ ] Los correos electrónicos se envían correctamente
- [ ] El API externo de estudiantes responde correctamente
- [ ] `php artisan route:list` ejecuta sin errores
- [ ] No hay errores en `storage/logs/laravel.log`

---

## Estado actual por categoría

| Categoría | Estado |
|---|---|
| DocumentRoot apunta a `/public` | OK |
| SSL/TLS configurado (TLS 1.2+) | OK |
| HSTS activo | OK |
| Directory listing desactivado (`-Indexes`) | OK |
| `.env` en `.gitignore` | OK |
| `BCRYPT_ROUNDS=12` | OK |
| `SESSION_DRIVER=database` | OK |
| `APP_DEBUG=false` | OK |
| `APP_ENV=production` | OK |
| `APP_URL` correcto | OK |
| `display_errors=Off` en PHP | OK |
| Redirect HTTP→HTTPS | OK |
| Caché de Laravel compilado | OK |
| `EXTERNAL_API_VERIFY_SSL=true` | OK |
| `SESSION_ENCRYPT=true` | OK |
| Archivos debug eliminados | OK (no existían) |
| Log rotación diaria | OK |
| Renovación SSL (oct 2026) | **PROGRAMAR** |
