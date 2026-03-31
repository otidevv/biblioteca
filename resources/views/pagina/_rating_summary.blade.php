@php
    $ratingClass = $ratingClass ?? '';
    $ratingSize = $ratingSize ?? '1rem';
@endphp

<x-rating-stars
    :rating="$libro->rating_promedio"
    :count="$libro->comentarios_count"
    :size="$ratingSize"
    :class="$ratingClass" />
