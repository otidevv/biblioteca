# Manual de Codificacion Bibliografica

## 1. Objetivo

Este manual describe la logica de codificacion bibliografica usada actualmente en el sistema de biblioteca para:

- clasificar libros con Dewey,
- generar el codigo Cutter/topografico,
- construir el codigo topografico completo,
- y mantener consistencia entre `libros` y `ejemplares`.

Aplica tanto al registro manual de libros como a los procesos masivos de actualizacion.

## 2. Componentes del codigo

La codificacion bibliografica del sistema se compone de dos partes:

### 2.1. Codigo Dewey

Representa la clasificacion tematica principal del libro.

Ejemplo:

- `511` = Matematicas

En la base de datos se guarda en:

- `libros.codigo_dewey`

La fuente principal de clasificacion es:

- tabla `deweys`
- archivo [database/data/dewey.json](/d:/proyectos/sistema-biblioteca/database/data/dewey.json)
- tabla `dewey_aprendizajes`

## 2.2. Codigo topografico o Cutter local

Es el codigo complementario que identifica al autor/obra dentro del mismo Dewey.

En la base de datos se guarda en:

- `libros.codigo`

## 2.3. Codigo topografico completo

Es la concatenacion de:

- `libros.codigo_dewey`
- `libros.codigo`

Ejemplo:

- Dewey: `511`
- Codigo: `69`
- Codigo topografico completo: `51169`

Actualmente, al actualizar codigos topograficos, este valor completo se copia a:

- `ejemplares.codigo_dewey`

## 3. Regla general de generacion del codigo

La generacion del codigo sigue este orden:

1. Codigo Cutter por apellido del autor principal.
2. Si el Cutter coincide con otro libro dentro del mismo `codigo_dewey`, se agrega la primera letra del nombre.
3. Si sigue coincidiendo, se agrega la primera letra del titulo.
4. Si sigue coincidiendo, se agrega la edicion.
5. Si aun existe colision por la restriccion unica del sistema, se agrega un sufijo numerico.

## 4. Autor principal

Cuando un libro tiene varios autores, el sistema toma como autor principal al primero segun este orden:

1. `apellidos`
2. `nombres`
3. `id`

Esto se usa tanto en:

- generacion en formulario de libros
- actualizacion masiva de codigos topograficos

## 5. Regla del Cutter

El sistema obtiene el Cutter desde la tabla:

- `codido_cutters`

La busqueda se realiza con las tres primeras letras normalizadas del apellido principal.

Ejemplo:

- Apellido: `CLEMENTE`
- Raiz: `CLE`
- Cutter: `69`

Si no encuentra un registro exacto, intenta encontrar uno por prefijo.
Si tampoco encuentra coincidencia, usa la raiz normalizada como fallback.

## 6. Orden actual de desempate

### 6.1. Primer nivel: Cutter

Se genera con el apellido principal.

Ejemplo:

- `CLEMENTE` -> `69`

### 6.2. Segundo nivel: inicial del nombre

Si dentro del mismo `codigo_dewey` ya existe otro libro con el mismo Cutter, se agrega la primera letra del nombre.

Ejemplo:

- `CLEMENTE, MIGUEL` -> `69M`
- `CLEMENTE, MARIO` -> `69M`

Si ambos siguen coincidiendo, se pasa al siguiente nivel.

### 6.3. Tercer nivel: inicial del titulo

Si el Cutter y la inicial del nombre siguen colisionando, se agrega la primera letra del titulo.

Ejemplo:

- `69MF`
- `69MP`

### 6.4. Cuarto nivel: edicion

Si aun existe colision, se agrega la edicion.

Reglas de edicion:

- si la edicion contiene numeros, se usan esos numeros
- si no contiene numeros, se usan hasta 2 caracteres normalizados

Ejemplos:

- `2a ed.` -> `2`
- `3ra` -> `3`
- `rev.` -> `RE`

### 6.5. Quinto nivel: sufijo numerico

Si aun asi el codigo resultante sigue ocupado, el sistema agrega un sufijo numerico incremental:

- `69`
- `692`
- `693`

Este ultimo paso existe porque `libros.codigo` es unico en la tabla.

## 7. Ejemplos practicos

### Caso 1: sin colision

- Dewey: `511`
- Autor principal: `CLEMENTE, MIGUEL`
- Cutter: `69`
- Codigo final: `69`
- Codigo topografico completo: `51169`

### Caso 2: mismo Cutter en el mismo Dewey

- Libro A: `CLEMENTE, MIGUEL`
- Libro B: `CASTILLO, MARIO`

Si ambos producen el mismo Cutter dentro del mismo Dewey, entra la inicial del nombre.

Ejemplo:

- `69M`

### Caso 3: mismo Cutter y misma inicial del nombre

- `CLEMENTE, MIGUEL`
- `CLEMENTE, MARCOS`

Si ambos coinciden en Cutter e inicial del nombre, entra la inicial del titulo.

Ejemplo:

- `69MF`
- `69MP`

### Caso 4: mismo Cutter, misma inicial de nombre y misma inicial de titulo

Si todavia coinciden, entra la edicion.

Ejemplo:

- `69MF2`
- `69MF3`

## 8. Campos de base de datos involucrados

### Libros

- `libros.codigo_dewey`
- `libros.codigo`
- `libros.titulo`
- `libros.edicion`

### Autores

- `autores.apellidos`
- `autores.nombres`

### Relacion libro-autor

- `autor_libros`

### Ejemplares

- `ejemplares.codigo_dewey`
- `ejemplares.codigo_ant`
- `ejemplares.codigo_interno`

## 9. Flujo en el sistema

### Registro manual de un libro

Cuando se crea o edita un libro desde el formulario:

1. el sistema sugiere un Dewey segun el titulo,
2. el catalogador selecciona o confirma la clasificacion,
3. el sistema genera el codigo segun Cutter y reglas de desempate,
4. al guardar, se actualiza el libro.

Endpoints involucrados:

- `/api/inventario/libros/sugerir-dewey`
- `/api/inventario/libros/generar-codigo`

### Actualizacion masiva

Para recalcular codigos topograficos de todos los libros ya registrados:

- `GET /actualizarCodigosTopograficos`

Este proceso:

1. recorre libros con `codigo_dewey`,
2. identifica autor principal,
3. genera `libros.codigo`,
4. y actualiza `ejemplares.codigo_dewey` con el codigo topografico completo.

## 10. Relacion entre libro y ejemplar

En el estado actual del sistema:

- `libros.codigo_dewey` contiene el Dewey puro
- `libros.codigo` contiene el codigo Cutter/local
- `ejemplares.codigo_dewey` contiene el codigo topografico completo

Por eso puede verse:

- en `libros`: `511`
- en `libros.codigo`: `69`
- en `ejemplares.codigo_dewey`: `51169`

## 11. Recomendaciones de uso

- Verificar siempre el autor principal antes de confirmar el codigo.
- Confirmar que el Dewey sugerido corresponda al contenido principal del libro.
- Revisar la edicion solo cuando exista colision real.
- Evitar modificar manualmente `ejemplares.codigo_dewey` sin recalcular el codigo del libro.

## 12. Archivos clave del proyecto

La logica principal se encuentra en:

- [app/Http/Controllers/SincronizarController.php](/d:/proyectos/sistema-biblioteca/app/Http/Controllers/SincronizarController.php)
- [app/Http/Controllers/Api/LibroController.php](/d:/proyectos/sistema-biblioteca/app/Http/Controllers/Api/LibroController.php)
- [resources/views/administracion/libros_nuevo.blade.php](/d:/proyectos/sistema-biblioteca/resources/views/administracion/libros_nuevo.blade.php)
- [database/data/dewey.json](/d:/proyectos/sistema-biblioteca/database/data/dewey.json)

## 13. Observacion importante

El sistema actualmente garantiza unicidad final en `libros.codigo` a nivel global, no solo dentro del mismo Dewey.

Eso significa que, si un codigo base ya esta ocupado, se puede agregar un sufijo numerico aunque bibliograficamente no sea el escenario ideal. Si en el futuro se cambia la restriccion de unicidad para trabajar por contexto bibliografico, este manual debera actualizarse.
