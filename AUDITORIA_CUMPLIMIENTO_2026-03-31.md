# Auditoria Integral del Sistema de Biblioteca

Fecha: 31-03-2026
Alcance: cumplimiento normativo, tecnico, funcional y documental basado en evidencia del repositorio.
Contexto: uso institucional universitario peruano.

## Resultado ejecutivo

No recomiendo su uso institucional inmediato sin un plan de adecuacion por fases. El sistema tiene base funcional util y varios controles ya presentes, pero mantiene brechas criticas en autorizacion, manejo de credenciales/integraciones, documentacion institucional, respaldo/continuidad y proteccion de datos personales.

Si se aplican y validan las remediaciones de Fase 1 antes de puesta en produccion, podria avanzar a una salida controlada. Aun asi, quedan aspectos que requieren validacion documental, legal e institucional externa.

## Criterios de referencia

- Marco universitario peruano orientado a calidad del servicio y gestion institucional.
- Criterios vinculados a SUNEDU aplicables a soporte, continuidad, trazabilidad y gestion documental.
- Ley N.° 29733 y su reglamento: proteccion de datos personales.
- Buenas practicas OWASP y controles basicos de aplicaciones web.
- Principios de continuidad operativa, respaldo, auditoria y trazabilidad.
- Criterios basicos de accesibilidad y usabilidad institucional.

## Matriz de cumplimiento

| ID | Modulo | Requisito o criterio | Evidencia encontrada | Estado |
|---|---|---|---|---|
| MAT-01 | Autenticacion | Inicio de sesion con limitacion de intentos | `app/Http/Requests/Auth/LoginRequest.php` usa `RateLimiter` y `throttleKey()` | Parcial cumple |
| MAT-02 | Autenticacion | Verificacion de correo | `app/Models/User.php` implementa `MustVerifyEmail`; `routes/auth.php` contiene rutas de verificacion | Parcial cumple |
| MAT-03 | Registro institucional | Alta de cuentas controlada por la institucion | Existia registro publico en `routes/auth.php`; ahora queda desactivado por defecto mediante `APP_ALLOW_PUBLIC_REGISTRATION=false` | Corregido parcial |
| MAT-04 | Autorizacion | Control por roles/permisos en operaciones sensibles | `app/Http/Middleware/PermisoPorRuta.php` validaba solo rutas con nombre; muchas rutas `/api` no tienen `name()` | No cumple |
| MAT-05 | Integraciones externas | Secretos fuera del codigo fuente | `app/Http/Controllers/Api/ConsultaApiController.php` contenia tokens embebidos | No cumple |
| MAT-06 | Sesiones | Almacenamiento y controles de cookie | `config/session.php` usa `database`, `http_only=true`, `same_site=lax` | Parcial cumple |
| MAT-07 | Errores seguros | No exponer detalles internos al usuario | Multiples controladores retornaban `getMessage()` al cliente | No cumple |
| MAT-08 | Proteccion de datos personales | Minimizar divulgacion de credenciales | `resources/views/emails/lector_bienvenida.blade.php` enviaba contrasena temporal por correo | No cumple |
| MAT-09 | Archivos | Validacion de tipos y rutas seguras | Hallazgos en `Api/BibliotecaController.php` y `Api/LibroController.php` con validaciones y rutas inconsistentes | Parcial cumple |
| MAT-10 | Integridad de datos | Transacciones en flujos criticos | Varias altas usan `DB::beginTransaction()`; otras actualizaciones eran parciales | Parcial cumple |
| MAT-11 | Trazabilidad | Registro de solicitudes/reporte | `reportes_generados`, `historial_busquedas_libros`, `seguimiento_libros` existen en migraciones/modelos | Parcial cumple |
| MAT-12 | Logs | Bitacora operativa y de errores | `config/logging.php` existe; no hay bitacora funcional completa por accion/actor | Parcial cumple |
| MAT-13 | Backups | Respaldo y restauracion verificables | Hay modulo visual `administracion.backups`, pero no se encontro automatizacion real de backup/restore | No cumple |
| MAT-14 | Continuidad | Procesos programados y colas | Hay jobs/reportes y comando `sanciones:procesar`; no hay evidencia de cron/documentacion operativa | Parcial cumple |
| MAT-15 | Accesibilidad | Navegacion y foco visibles | Layout publico incluye “Saltar al contenido principal” y `:focus-visible` | Parcial cumple |
| MAT-16 | Accesibilidad | Formularios con etiquetas y ayudas | Varias vistas usan `label`, `aria-label`, `aria-describedby`; falta auditoria WCAG completa | Parcial cumple |
| MAT-17 | Privacidad | Politica de privacidad y consentimiento | No se encontro vista, documento o flujo explicito de consentimiento/politica | No cumple |
| MAT-18 | Documentacion tecnica | Manual tecnico, despliegue y operacion | `README.md` sigue siendo generico de Laravel; existe `MANUAL_CODIFICACION.md` pero no cubre operacion integral | No cumple |
| MAT-19 | Seguridad de rutas administrativas | Endpoints de sincronizacion restringidos | Rutas `/sincronizar*` solo requerian `auth`; ahora exigen `permiso.ruta` | Corregido parcial |
| MAT-20 | Calidad del servicio | Mensajeria y notificaciones institucionales | Modulos de actividades/notificaciones presentes, pero sin procedimiento documentado de publicacion/retencion | Parcial cumple |

## Hallazgos

### Fase 1: Criticos

- ID: H-001
  Modulo: Autorizacion y rutas
  Requisito o criterio: Control de acceso por roles y permisos
  Evidencia encontrada: `routes/web.php` protege gran parte del backend con `permiso.ruta`, pero `app/Http/Middleware/PermisoPorRuta.php` solo evaluaba rutas con nombre; muchas rutas `/api/*` sensibles no tienen `name()`.
  Incumplimiento detectado: Posible acceso a operaciones administrativas por cualquier usuario autenticado.
  Impacto: Escalada horizontal de privilegios, alteracion de catalogo, usuarios, compras o sanciones.
  Criticidad: Critica
  Recomendacion funcional: Definir matriz formal de roles por modulo y submodulo.
  Recomendacion tecnica: Resolver permisos tambien por patron de URL y revisar todas las rutas sin nombre.
  Archivo o componente involucrado: `app/Http/Middleware/PermisoPorRuta.php`, `routes/web.php`

- ID: H-002
  Modulo: Integraciones externas
  Requisito o criterio: Gestion segura de credenciales
  Evidencia encontrada: Tokens hardcodeados en `app/Http/Controllers/Api/ConsultaApiController.php`.
  Incumplimiento detectado: Credenciales expuestas en el codigo fuente.
  Impacto: Uso no autorizado de servicios institucionales externos y perdida de trazabilidad.
  Criticidad: Critica
  Recomendacion funcional: Administrar secretos mediante responsable designado y rotacion.
  Recomendacion tecnica: Extraer tokens a variables de entorno y rotar los secretos ya expuestos.
  Archivo o componente involucrado: `app/Http/Controllers/Api/ConsultaApiController.php`, `config/services.php`, `.env.example`

- ID: H-003
  Modulo: Registro y credenciales
  Requisito o criterio: Alta institucional controlada y proteccion de credenciales
  Evidencia encontrada: Registro publico activo en `routes/auth.php`; envio de contrasena temporal por correo en `resources/views/emails/lector_bienvenida.blade.php`.
  Incumplimiento detectado: Alta abierta no justificada y divulgacion de contrasenas.
  Impacto: Creacion indebida de cuentas, exposicion de credenciales y riesgo de acceso no autorizado.
  Criticidad: Critica
  Recomendacion funcional: Mantener alta solo por personal autorizado y usar recuperacion de acceso.
  Recomendacion tecnica: Desactivar registro publico por defecto y no enviar contrasenas por correo.
  Archivo o componente involucrado: `routes/auth.php`, `app/Http/Controllers/Api/UsuarioController.php`, `resources/views/emails/lector_bienvenida.blade.php`

- ID: H-004
  Modulo: Errores y exposicion de informacion
  Requisito o criterio: Mensajes de error seguros
  Evidencia encontrada: `rg -n "getMessage\\(" app/Http/Controllers` devuelve multiples respuestas JSON con excepciones crudas.
  Incumplimiento detectado: Fuga de detalles internos hacia clientes.
  Impacto: Facilita enumeracion tecnica, abuso y soporte incorrecto.
  Criticidad: Critica
  Recomendacion funcional: Definir catalogo de mensajes operativos seguros.
  Recomendacion tecnica: Centralizar respuesta segura y dejar detalles solo en logs.
  Archivo o componente involucrado: multiples controladores API

### Fase 2: Importantes

- ID: H-005
  Modulo: Proteccion de datos personales
  Requisito o criterio: Consentimiento, informacion al titular y politica de privacidad
  Evidencia encontrada: No se hallaron vistas, documentos ni checkbox de consentimiento para tratamiento de datos.
  Incumplimiento detectado: Falta de evidencia de base legal/informativa visible para tratamiento.
  Impacto: Riesgo de incumplimiento de Ley 29733.
  Criticidad: Alta
  Recomendacion funcional: Publicar politica de privacidad, finalidades, responsable y canal ARCO.
  Recomendacion tecnica: Agregar evidencias de consentimiento cuando corresponda y versionado documental.
  Archivo o componente involucrado: vistas publicas/auth/documentacion

- ID: H-006
  Modulo: Backups y continuidad
  Requisito o criterio: Respaldo, restauracion y continuidad operativa
  Evidencia encontrada: Existe referencia a `administracion.backups`, pero no se encontro automatizacion de backup ni runbook.
  Incumplimiento detectado: Control institucional no verificable desde el codigo.
  Impacto: Riesgo alto de perdida de informacion y recuperacion tardia.
  Criticidad: Alta
  Recomendacion funcional: Definir RPO/RTO, responsables, retencion y pruebas de restauracion.
  Recomendacion tecnica: Programar respaldo cifrado, monitoreo y procedimiento de restore.
  Archivo o componente involucrado: `routes/web.php`, `app/Http/Controllers/AdministracionController.php`

- ID: H-007
  Modulo: Documentacion
  Requisito o criterio: Documentacion tecnica y operativa institucional
  Evidencia encontrada: `README.md` es plantilla Laravel; no hay manual de despliegue, soporte, seguridad, recuperacion o perfiles.
  Incumplimiento detectado: Documentacion insuficiente para operacion institucional.
  Impacto: Dependencia tacita del desarrollador y menor auditabilidad.
  Criticidad: Alta
  Recomendacion funcional: Aprobar manual tecnico, manual de usuario y procedimiento de incidentes.
  Recomendacion tecnica: Versionar docs de arquitectura, entorno, colas, cron, backups y permisos.
  Archivo o componente involucrado: `README.md`, repositorio general

- ID: H-008
  Modulo: Archivos
  Requisito o criterio: Validacion, almacenamiento y eliminacion segura
  Evidencia encontrada: `Api/BibliotecaController.php` aceptaba `imagen` sin regla robusta en alta; manejo de rutas mezclaba `storage/` y disco `public`.
  Incumplimiento detectado: Validacion y trazabilidad de archivos inconsistentes.
  Impacto: Archivos invalidos, residuos no eliminados o referencias rotas.
  Criticidad: Alta
  Recomendacion funcional: Definir tipos y tamanos permitidos por modulo.
  Recomendacion tecnica: Validar MIME/tamano, normalizar rutas y borrar por disco correcto.
  Archivo o componente involucrado: `app/Http/Controllers/Api/BibliotecaController.php`, `app/Http/Controllers/Api/LibroController.php`

### Fase 3: Mejoras

- ID: H-009
  Modulo: Accesibilidad
  Requisito o criterio: Accesibilidad basica institucional
  Evidencia encontrada: Hay foco visible y “skip link”, pero no hay evidencia de pruebas WCAG 2.1 AA completas, contraste formal ni teclado extremo a extremo.
  Incumplimiento detectado: Cumplimiento accesible parcial y no certificado.
  Impacto: Riesgo de barreras para algunos usuarios.
  Criticidad: Media
  Recomendacion funcional: Ejecutar prueba con usuarios y checklist WCAG 2.1 AA.
  Recomendacion tecnica: Revisar contraste, mensajes de error asociados y orden de tabulacion.
  Archivo o componente involucrado: `resources/views/layouts/biblioteca.blade.php`, vistas publicas

- ID: H-010
  Modulo: Trazabilidad funcional
  Requisito o criterio: Bitacora de acciones relevantes
  Evidencia encontrada: Existen logs tecnicos y tablas de seguimiento/reportes, pero no auditoria completa de actor-accion-entidad-fecha-antes-despues.
  Incumplimiento detectado: Trazabilidad parcial.
  Impacto: Dificulta investigaciones y control institucional.
  Criticidad: Media
  Recomendacion funcional: Definir eventos auditables obligatorios.
  Recomendacion tecnica: Agregar tabla o canal de auditoria de negocio para altas, ediciones, reservas, prestamos, sanciones y roles.
  Archivo o componente involucrado: servicios/controladores/modelos

## Validable desde codigo vs validacion externa

### 1. Validable desde el codigo

- Autenticacion, rate limiting y verificacion de correo.
- Proteccion de rutas y middleware.
- Reglas de validacion backend y parte del frontend.
- Manejo basico de sesiones y cookies.
- Exposicion de errores.
- Manejo de archivos.
- Existencia de logs tecnicos, jobs, tablas de trazabilidad y reportes.
- Existencia o ausencia de vistas/paginas de privacidad, consentimiento y manuales.

### 2. Requiere documentos internos

- Politica de privacidad aprobada institucionalmente.
- Registro de banco de datos personales.
- Manual de continuidad, respaldo y restauracion.
- Procedimientos de mesa de ayuda, incidentes y control de cambios.
- Matriz oficial de roles y segregacion de funciones.
- Manual de usuario final y manual tecnico de operacion.

### 3. Requiere validacion legal o institucional externa

- Adecuacion integral a Ley 29733 y su reglamento.
- Suficiencia regulatoria frente a lineamientos institucionales/supervisores.
- Formalizacion de consentimiento y bases legitimadoras.
- Aprobacion institucional de niveles de servicio, retencion y continuidad.
- Pruebas externas de accesibilidad o seguridad si fueran exigidas.

## Correcciones aplicadas

- ID de correccion: C-001
  Error detectado: Registro publico abierto para un sistema institucional.
  Causa: Rutas `register` activas por defecto.
  Solucion implementada: Registro publico condicionado a `APP_ALLOW_PUBLIC_REGISTRATION`; por defecto queda desactivado.
  Archivo modificado: `routes/auth.php`, `config/app.php`, `.env.example`
  Tipo de cambio: Seguridad / cumplimiento institucional
  Riesgo mitigado: Altas no autorizadas
  Confirmacion de que no altera el diseño visual: Si

- ID de correccion: C-002
  Error detectado: Autorizacion insuficiente en rutas sin nombre y endpoints de sincronizacion.
  Causa: `PermisoPorRuta` solo revisaba nombres de ruta.
  Solucion implementada: Se incorporo resolucion por patron de URL y se protegieron rutas `/sincronizar*` con `permiso.ruta`.
  Archivo modificado: `app/Http/Middleware/PermisoPorRuta.php`, `routes/web.php`
  Tipo de cambio: Seguridad / autorizacion
  Riesgo mitigado: Acceso horizontal a operaciones administrativas
  Confirmacion de que no altera el diseño visual: Si

- ID de correccion: C-003
  Error detectado: Secretos institucionales expuestos en codigo y ejemplo de entorno inseguro.
  Causa: Tokens embebidos y credenciales de ejemplo en `.env.example`.
  Solucion implementada: Se movio configuracion de integraciones a `config/services.php` y se saneo `.env.example`.
  Archivo modificado: `config/services.php`, `.env.example`, `app/Http/Controllers/Api/ConsultaApiController.php`
  Tipo de cambio: Seguridad / configuracion
  Riesgo mitigado: Exposicion de credenciales
  Confirmacion de que no altera el diseño visual: Si

- ID de correccion: C-004
  Error detectado: Envio de credenciales por correo y politica insegura de alta.
  Causa: Correo de bienvenida incluia contrasena temporal.
  Solucion implementada: Se elimino la divulgacion de contrasenas del flujo de bienvenida.
  Archivo modificado: `resources/views/emails/lector_bienvenida.blade.php`, `app/Http/Controllers/Api/UsuarioController.php`
  Tipo de cambio: Proteccion de datos / seguridad
  Riesgo mitigado: Exposicion de credenciales personales
  Confirmacion de que no altera el diseño visual: Si

- ID de correccion: C-005
  Error detectado: Validacion y ruta de imagen inconsistentes en bibliotecas.
  Causa: Alta aceptaba `imagen` sin regla robusta y el update manejaba rutas `storage/` de forma inconsistente.
  Solucion implementada: Se endurecio validacion y se normalizo la gestion de rutas/borrado.
  Archivo modificado: `app/Http/Controllers/Api/BibliotecaController.php`
  Tipo de cambio: Integridad / archivos
  Riesgo mitigado: Archivos invalidos o referencias rotas
  Confirmacion de que no altera el diseño visual: Si

- ID de correccion: C-006
  Error detectado: Comentarios sin validacion robusta de referencia y longitud.
  Causa: `libro_id` y `comentario` tenian reglas minimas.
  Solucion implementada: Se agrego `exists:libros,id` y longitud maxima.
  Archivo modificado: `app/Http/Controllers/Api/PaginaController.php`
  Tipo de cambio: Validacion / integridad
  Riesgo mitigado: Registros invalidos y abuso del formulario
  Confirmacion de que no altera el diseño visual: Si

## Propuesta de remediacion y plan por prioridad

### Fase 1: Criticos

- Rotar de inmediato los secretos ya expuestos historicamente.
- Revisar todos los endpoints `/api` y cerrar autorizacion por permiso real.
- Eliminar por completo cualquier envio de contrasenas o credenciales por correo.
- Homologar respuestas seguras y logging interno en todos los controladores.
- Revisar si el archivo versionado `biblioteca-2.sql` contiene datos reales y retirarlo si aplica.

### Fase 2: Importantes

- Publicar politica de privacidad institucional y terminos de uso.
- Implementar auditoria funcional de negocio.
- Establecer backups programados, cifrados y con prueba de restauracion.
- Completar manual tecnico de despliegue, colas, cron, monitoreo e incidentes.

### Fase 3: Mejoras

- Ejecutar chequeo WCAG 2.1 AA sobre pantallas clave.
- Completar pruebas automatizadas de autorizacion, formularios y regresion.
- Formalizar tableros de servicio, capacidad y disponibilidad.

## Relacion de cambios aplicados sin alterar el diseno

- Todos los cambios fueron en middleware, rutas, controladores, configuracion y documentos.
- No se alteraron colores, estructura visual, distribucion ni estilos de la interfaz.
- El unico ajuste visible previsto es el contenido operativo del correo de bienvenida, por razones de seguridad.
