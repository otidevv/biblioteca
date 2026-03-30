@props([
    'rating' => 0,
    'count' => 0,
    'showText' => true,
    'size' => '0.9rem',
])

@php
    $ratingValue = max(0, min(5, (float) $rating));
    $roundedRating = round($ratingValue * 2) / 2;
@endphp

<div {{ $attributes->merge(['class' => 'd-inline-flex align-items-center gap-2']) }}>
    <span class="d-inline-flex align-items-center" aria-label="Calificacion {{ number_format($ratingValue, 1) }} de 5">
        @for($i = 1; $i <= 5; $i++)
            @if($roundedRating >= $i)
                <i class="bi bi-star-fill text-warning" style="font-size: {{ $size }};"></i>
            @elseif($roundedRating >= ($i - 0.5))
                <i class="bi bi-star-half text-warning" style="font-size: {{ $size }};"></i>
            @else
                <i class="bi bi-star text-warning" style="font-size: {{ $size }};"></i>
            @endif
        @endfor
    </span>

    @if($showText)
        <small class="text-muted">
            {{ $count > 0 ? number_format($ratingValue, 1) . '/5' : 'Sin calificaciones' }}
            @if($count > 0)
                ({{ $count }})
            @endif
        </small>
    @endif
</div>
