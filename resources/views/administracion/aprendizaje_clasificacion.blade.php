@extends('layouts.admin')

@section('page-title', 'Aprendizaje Dewey y Cutter')

@section('css')
    <link href="{{ asset('css/administracion/aprendizaje_clasificacion.css') }}?v={{ filemtime(public_path('css/administracion/aprendizaje_clasificacion.css')) }}" rel="stylesheet" />
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span>Manuales</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Aprendizaje Dewey y Cutter</span>
    </div>

    <section class="admin-panel learning-hero">
        <div class="learning-hero__copy">
            <span class="learning-hero__eyebrow">Guia de uso</span>
            <h2 class="admin-panel__title">Como el sistema mejora sus sugerencias al registrar libros</h2>
            <p class="admin-panel__copy">
                Cuando registras libros, el sistema va recordando decisiones correctas para ayudarte en registros futuros.
                Asi puede sugerir mejor la clasificacion del libro y el codigo relacionado con el autor, sin que tengas que empezar desde cero cada vez.
            </p>

            <div class="learning-hero__actions">
                <a href="{{ url('/administracion/libros_nuevo') }}" class="admin-btn admin-btn--primary">Ir a nuevo libro</a>
                <a href="{{ route('manual.codificacion') }}" class="admin-btn admin-btn--ghost">Ver guia completa</a>
            </div>
        </div>

        <div class="learning-hero__grid">
            <article class="learning-mini-card">
                <span class="learning-mini-card__label">Clasificacion</span>
                <strong>Recuerda titulos parecidos</strong>
                <small>Si varios libros similares se clasifican igual, la siguiente sugerencia sera mas cercana.</small>
            </article>
            <article class="learning-mini-card">
                <span class="learning-mini-card__label">Codigo del autor</span>
                <strong>Recuerda apellidos ya usados</strong>
                <small>Si un autor o apellido parecido ya fue registrado, el sistema intenta seguir el mismo criterio.</small>
            </article>
            <article class="learning-mini-card">
                <span class="learning-mini-card__label">Resultado</span>
                <strong>Menos trabajo repetido</strong>
                <small>Mientras mas registros correctos se guarden, mas rapido y consistente sera el registro futuro.</small>
            </article>
        </div>
    </section>

    <section class="learning-grid learning-grid--diagram">
        <article class="admin-panel learning-card learning-card--diagram">
            <div class="learning-card__head">
                <span class="learning-card__icon"><i class="bi bi-bezier2"></i></span>
                <div>
                    <h3 class="learning-card__title">Grafico de como mejora la sugerencia</h3>
                    <p class="learning-card__copy">Este diagrama muestra de forma simple como el sistema toma un dato, lo recuerda y lo usa de nuevo mas adelante.</p>
                </div>
            </div>

            <div class="learning-diagram">
                <article class="learning-diagram__lane learning-diagram__lane--dewey">
                    <div class="learning-diagram__header">
                        <span class="learning-diagram__badge">Clasificacion</span>
                        <strong>Mejora a partir del titulo del libro</strong>
                    </div>

                    <div class="learning-diagram__flow">
                        <div class="learning-diagram__node">
                            <span class="learning-diagram__node-label">Entrada</span>
                            <strong>Titulo del libro</strong>
                            <small>El sistema observa de que trata el libro</small>
                        </div>
                        <span class="learning-diagram__arrow"><i class="bi bi-arrow-right"></i></span>
                        <div class="learning-diagram__node">
                            <span class="learning-diagram__node-label">Revision</span>
                            <strong>Compara con registros anteriores</strong>
                            <small>Busca parecidos con libros ya clasificados</small>
                        </div>
                        <span class="learning-diagram__arrow"><i class="bi bi-arrow-right"></i></span>
                        <div class="learning-diagram__node">
                            <span class="learning-diagram__node-label">Recuerdo</span>
                            <strong>Guarda el criterio correcto</strong>
                            <small>Si la clasificacion fue buena, la usara como referencia</small>
                        </div>
                    </div>

                    <div class="learning-diagram__result">
                        <span class="learning-diagram__result-label">Proxima vez</span>
                        <p>Cuando llegue un titulo parecido, la sugerencia saldra mas afinada y mas rapido.</p>
                    </div>
                </article>

                <article class="learning-diagram__lane learning-diagram__lane--cutter">
                    <div class="learning-diagram__header">
                        <span class="learning-diagram__badge">Codigo del autor</span>
                        <strong>Mejora a partir del apellido principal</strong>
                    </div>

                    <div class="learning-diagram__flow">
                        <div class="learning-diagram__node">
                            <span class="learning-diagram__node-label">Entrada</span>
                            <strong>Apellido principal</strong>
                            <small>Se toma del autor elegido en el formulario</small>
                        </div>
                        <span class="learning-diagram__arrow"><i class="bi bi-arrow-right"></i></span>
                        <div class="learning-diagram__node">
                            <span class="learning-diagram__node-label">Revision</span>
                            <strong>Busca como se resolvio antes</strong>
                            <small>Revisa si ese apellido ya fue trabajado en otros libros</small>
                        </div>
                        <span class="learning-diagram__arrow"><i class="bi bi-arrow-right"></i></span>
                        <div class="learning-diagram__node">
                            <span class="learning-diagram__node-label">Recuerdo</span>
                            <strong>Conserva el patron usado</strong>
                            <small>Si el codigo fue correcto, intenta repetir ese criterio</small>
                        </div>
                    </div>

                    <div class="learning-diagram__result">
                        <span class="learning-diagram__result-label">Proxima vez</span>
                        <p>Si vuelve a aparecer un apellido parecido, el sistema intentara sugerir el mismo estilo de codigo.</p>
                    </div>
                </article>
            </div>
        </article>
    </section>

    <section class="learning-grid">
        <article class="admin-panel learning-card">
            <div class="learning-card__head">
                <span class="learning-card__icon"><i class="bi bi-journal-bookmark-fill"></i></span>
                <div>
                    <h3 class="learning-card__title">Como mejora la clasificacion del libro</h3>
                    <p class="learning-card__copy">El sistema usa el titulo y aprende de los registros que ustedes ya validaron antes.</p>
                </div>
            </div>

            <div class="learning-steps">
                <div class="learning-step">
                    <strong>1. Analiza el titulo</strong>
                    <p>Lee el titulo e identifica las palabras mas importantes.</p>
                </div>
                <div class="learning-step">
                    <strong>2. Busca coincidencias</strong>
                    <p>Revisa si ya existen libros parecidos o clasificaciones similares.</p>
                </div>
                <div class="learning-step">
                    <strong>3. Te propone una opcion</strong>
                    <p>Muestra una sugerencia basada en lo que ya aprendio del sistema.</p>
                </div>
                <div class="learning-step">
                    <strong>4. Aprende al guardar</strong>
                    <p>Cuando confirmas y guardas, esa decision ayuda a mejorar sugerencias futuras.</p>
                </div>
            </div>
        </article>

        <article class="admin-panel learning-card">
            <div class="learning-card__head">
                <span class="learning-card__icon"><i class="bi bi-person-vcard-fill"></i></span>
                <div>
                    <h3 class="learning-card__title">Como mejora el codigo relacionado con el autor</h3>
                    <p class="learning-card__copy">El sistema toma como referencia el apellido principal del autor y recuerda como se resolvio antes.</p>
                </div>
            </div>

            <div class="learning-steps">
                <div class="learning-step">
                    <strong>1. Identifica el autor principal</strong>
                    <p>Toma el autor principal seleccionado en el registro.</p>
                </div>
                <div class="learning-step">
                    <strong>2. Revisa casos parecidos</strong>
                    <p>Busca si ese apellido ya fue usado antes en otros libros.</p>
                </div>
                <div class="learning-step">
                    <strong>3. Propone un codigo</strong>
                    <p>Si ya tiene una referencia, intenta seguir el mismo criterio.</p>
                </div>
                <div class="learning-step">
                    <strong>4. Aprende al confirmar el codigo</strong>
                    <p>Cuando guardas el libro, recuerda esa decision para los siguientes registros.</p>
                </div>
            </div>
        </article>
    </section>

    <section class="learning-grid learning-grid--comparison">
        <article class="admin-panel learning-card">
            <div class="learning-card__head">
                <span class="learning-card__icon"><i class="bi bi-arrow-left-right"></i></span>
                <div>
                    <h3 class="learning-card__title">Que aprende cada parte del sistema</h3>
                    <p class="learning-card__copy">Las dos sugerencias mejoran con el uso, pero se fijan en datos diferentes.</p>
                </div>
            </div>

            <div class="learning-compare">
                <div class="learning-compare__item">
                    <span class="learning-compare__label">Clasificacion</span>
                    <strong>Aprende por el tema del libro</strong>
                    <p>Se apoya en el titulo para sugerir la clasificacion mas adecuada.</p>
                </div>
                <div class="learning-compare__item">
                    <span class="learning-compare__label">Codigo del autor</span>
                    <strong>Aprende por el apellido</strong>
                    <p>Se apoya en el autor principal para sugerir un codigo mas consistente.</p>
                </div>
            </div>
        </article>

        <article class="admin-panel learning-card">
            <div class="learning-card__head">
                <span class="learning-card__icon"><i class="bi bi-diagram-3-fill"></i></span>
                <div>
                    <h3 class="learning-card__title">Ejemplo practico</h3>
                    <p class="learning-card__copy">Este ejemplo muestra por que el sistema se vuelve mas util mientras mas se usa correctamente.</p>
                </div>
            </div>

            <div class="learning-example">
                <div class="learning-example__item">
                    <strong>Titulo nuevo</strong>
                    <p>`Algoritmos y estructuras de datos`</p>
                </div>
                <div class="learning-example__item">
                    <strong>Autor principal</strong>
                    <p>`Garcia Perez`</p>
                </div>
                <div class="learning-example__item">
                    <strong>Lo que recuerda el sistema</strong>
                    <p>Que ese tipo de titulo se clasifica parecido y que ese apellido ya tuvo un criterio de codigo valido.</p>
                </div>
                <div class="learning-example__item">
                    <strong>Resultado</strong>
                    <p>La siguiente vez la sugerencia sera mas cercana a lo que el equipo ya considero correcto.</p>
                </div>
            </div>
        </article>
    </section>

    <section class="admin-panel learning-timeline">
        <div class="learning-card__head">
                <span class="learning-card__icon"><i class="bi bi-clock-history"></i></span>
                <div>
                    <h3 class="learning-card__title">Paso a paso dentro de nuevo libro</h3>
                    <p class="learning-card__copy">Esta es la secuencia real que vive la persona que registra o corrige un libro.</p>
                </div>
            </div>

        <div class="learning-timeline__steps">
            <div class="learning-timeline__step">
                <span>01</span>
                <strong>Escribe el titulo</strong>
                <p>El sistema intenta sugerir una clasificacion automaticamente.</p>
            </div>
            <div class="learning-timeline__step">
                <span>02</span>
                <strong>Selecciona autor y clasificacion</strong>
                <p>Se genera un codigo sugerido para completar el registro.</p>
            </div>
            <div class="learning-timeline__step">
                <span>03</span>
                <strong>El catalogador ajusta si hace falta</strong>
                <p>Si corrige la sugerencia, esa decision tambien ayuda al sistema a mejorar.</p>
            </div>
            <div class="learning-timeline__step">
                <span>04</span>
                <strong>Se guarda el libro</strong>
                <p>El sistema recuerda esa experiencia y la usa en registros futuros.</p>
            </div>
        </div>
    </section>

    <section class="learning-grid">
        <article class="admin-panel learning-card">
            <div class="learning-card__head">
                <span class="learning-card__icon"><i class="bi bi-stars"></i></span>
                <div>
                    <h3 class="learning-card__title">Beneficios operativos</h3>
                </div>
            </div>

            <ul class="learning-list">
                <li>Reduce tiempo de catalogacion en registros repetitivos.</li>
                <li>Hace mas consistente la clasificacion entre distintos usuarios.</li>
                <li>Permite que las correcciones humanas mejoren el sistema, en vez de perderse.</li>
                <li>Ayuda a mantener continuidad cuando la coleccion va creciendo.</li>
            </ul>
        </article>

        <article class="admin-panel learning-card">
            <div class="learning-card__head">
                <span class="learning-card__icon"><i class="bi bi-exclamation-circle-fill"></i></span>
                <div>
                    <h3 class="learning-card__title">Importante para el usuario</h3>
                </div>
            </div>

            <ul class="learning-list">
                <li>Las sugerencias ayudan, pero la decision final sigue siendo del usuario.</li>
                <li>Si se guarda una clasificacion equivocada, esa referencia tambien puede influir despues.</li>
                <li>Por eso es importante revisar antes de confirmar el registro.</li>
                <li>Si el sistema todavia no tiene experiencia suficiente, seguira usando reglas base para apoyar la sugerencia.</li>
            </ul>
        </article>
    </section>
</div>
@endsection
