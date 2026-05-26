@php
    $ratingClass  = $ratingClass ?? '';
    $ratingSize   = $ratingSize  ?? '1rem';
    $ratingValue  = max(0, min(5, (float) ($libro->rating_promedio ?? 0)));
    $totalComents = (int) ($libro->comentarios_count ?? 0);

    $dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    if ($libro->relationLoaded('comentarios')) {
        foreach ($libro->comentarios as $c) {
            $cal = max(1, min(5, (int) ($c->calificacion ?? 0)));
            if ($cal > 0) $dist[$cal]++;
        }
    }
@endphp

<div class="book-rating-summary {{ $ratingClass }}">
    {{-- Puntuación global --}}
    <div class="book-rating-score">
        <div class="book-rating-number">
            {{ $totalComents > 0 ? number_format($ratingValue, 1) : '—' }}
        </div>
        <div class="book-rating-stars-row">
            <x-rating-stars
                :rating="$libro->rating_promedio"
                :count="0"
                :showText="false"
                size="{{ $ratingSize }}" />
        </div>
        <div class="book-rating-count">
            {{ $totalComents }} valoración{{ $totalComents !== 1 ? 'es' : '' }}
        </div>
    </div>

    {{-- Distribución por estrellas --}}
    @if($totalComents > 0)
        <div class="book-rating-dist">
            @foreach([5, 4, 3, 2, 1] as $stars)
                @php
                    $cnt = $dist[$stars];
                    $pct = $totalComents > 0 ? round(($cnt / $totalComents) * 100) : 0;
                @endphp
                <div class="book-rating-dist-row">
                    <span class="book-rating-dist-label">{{ $stars }}</span>
                    <i class="bi bi-star-fill book-rating-dist-star"></i>
                    <div class="book-rating-dist-bar" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="book-rating-dist-fill" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="book-rating-dist-count">{{ $cnt }}</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="book-rating-no-data">
            <i class="bi bi-chat-square-dots"></i>
            <span>Sé el primero en valorar este libro</span>
        </div>
    @endif
</div>
