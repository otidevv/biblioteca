@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Otras bibliotecas')
@section('meta_description', 'Directorio de enlaces oficiales a otras bibliotecas para ampliar la consulta académica y bibliográfica.')

@section('content')
<style>
.external-libraries-page {
    display: grid;
    gap: 1.5rem;
}

.external-libraries-hero {
    position: relative;
    overflow: hidden;
    padding: 1.75rem;
    border-radius: 1.5rem;
    border: 1px solid rgba(24, 77, 59, 0.1);
    background:
        radial-gradient(circle at top right, rgba(216, 177, 92, 0.2), transparent 28%),
        linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(240, 247, 244, 0.92));
}

.external-libraries-hero::after {
    content: "";
    position: absolute;
    inset: auto -40px -40px auto;
    width: 180px;
    height: 180px;
    background: radial-gradient(circle, rgba(47, 122, 93, 0.14), transparent 68%);
    pointer-events: none;
}

.external-libraries-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.9rem;
    padding: 0.35rem 0.8rem;
    border-radius: 999px;
    background: rgba(24, 77, 59, 0.08);
    color: #1d5a46;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.external-libraries-hero h2 {
    margin: 0 0 0.7rem;
    font-size: clamp(1.8rem, 2.4vw, 2.5rem);
    font-weight: 800;
    color: #143529;
}

.external-libraries-hero p {
    max-width: 760px;
    margin: 0;
    color: #526761;
    line-height: 1.7;
}

.external-libraries-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
}

.external-library-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    height: 100%;
    padding: 1.25rem;
    border-radius: 1.35rem;
    border: 1px solid rgba(24, 77, 59, 0.09);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(243, 248, 245, 0.88));
    box-shadow: 0 14px 34px rgba(24, 77, 59, 0.08);
}

.external-library-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.8rem;
}

.external-library-card__icon {
    width: 52px;
    height: 52px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(24, 77, 59, 0.12), rgba(216, 177, 92, 0.22));
    color: #175a45;
    font-size: 1.35rem;
}

.external-library-card__badge {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    background: rgba(24, 77, 59, 0.08);
    color: #1f5f4b;
    font-size: 0.74rem;
    font-weight: 700;
}

.external-library-card h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #173d2f;
}

.external-library-card small {
    color: #6c8078;
    font-weight: 600;
}

.external-library-card p {
    margin: 0;
    color: #5b7069;
    line-height: 1.65;
}

.external-library-card__link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: fit-content;
    margin-top: auto;
    padding: 0.8rem 1rem;
    border-radius: 0.95rem;
    text-decoration: none;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #1a7357, #0f4e3a);
}

.external-library-card__link:hover {
    color: #fff;
    background: linear-gradient(135deg, #23815f, #12553f);
}

body.library-dark .external-libraries-hero {
    border-color: rgba(255, 255, 255, 0.08);
    background:
        radial-gradient(circle at top right, rgba(216, 177, 92, 0.12), transparent 28%),
        linear-gradient(135deg, rgba(16, 29, 24, 0.94), rgba(11, 20, 16, 0.92));
}

body.library-dark .external-libraries-eyebrow,
body.library-dark .external-library-card__badge {
    background: rgba(255, 255, 255, 0.08);
    color: #f2cf82;
}

body.library-dark .external-libraries-hero h2,
body.library-dark .external-library-card h3 {
    color: #f8fafc;
}

body.library-dark .external-libraries-hero p,
body.library-dark .external-library-card p,
body.library-dark .external-library-card small {
    color: #adc0b7;
}

body.library-dark .external-library-card {
    border-color: rgba(255, 255, 255, 0.08);
    background: linear-gradient(180deg, rgba(18, 30, 24, 0.94), rgba(11, 20, 16, 0.9));
    box-shadow: 0 18px 36px rgba(0, 0, 0, 0.28);
}

body.library-dark .external-library-card__icon {
    background: linear-gradient(135deg, rgba(242, 207, 130, 0.16), rgba(94, 234, 212, 0.14));
    color: #f2cf82;
}

@media (max-width: 576px) {
    .external-libraries-hero {
        padding: 1.25rem;
    }
}
</style>

<div class="external-libraries-page">
    <section class="external-libraries-hero">
        <span class="external-libraries-eyebrow">
            <i class="bi bi-link-45deg"></i>
            Directorio externo
        </span>
        <h2>Links de otras bibliotecas para consulta</h2>
        <p>
            Reune accesos directos a bibliotecas e instituciones con portales oficiales de consulta.
            Puedes usar estos enlaces para ampliar tu busqueda bibliografica y revisar otros catalogos.
        </p>
    </section>

    <section class="external-libraries-grid">
        @foreach($bibliotecasExternas as $biblioteca)
            <article class="external-library-card">
                <div class="external-library-card__top">
                    <span class="external-library-card__icon">
                        <i class="bi {{ $biblioteca['icono'] }}"></i>
                    </span>
                    <span class="external-library-card__badge">{{ $biblioteca['etiqueta'] }}</span>
                </div>

                <div>
                    <h3>{{ $biblioteca['nombre'] }}</h3>
                    <small>{{ $biblioteca['institucion'] }}</small>
                </div>

                <p>{{ $biblioteca['descripcion'] }}</p>

                <a href="{{ $biblioteca['url'] }}" target="_blank" rel="noopener noreferrer" class="external-library-card__link">
                    Visitar sitio
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </article>
        @endforeach
    </section>
</div>
@endsection
