@extends('layouts.admin')
@section('page-title', 'Error del servidor · 500')

@section('content')
@include('errors._shell', [
    'code'       => '500',
    'gradient'   => 'from-rose-700 via-red-600 to-pink-700',
    'glowColor'  => 'rgba(225,29,72,0.25)',
    'icon'       => 'bi-exclamation-octagon-fill',
    'eyebrowBg'  => 'bg-rose-50 dark:bg-rose-950/40',
    'eyebrowTxt' => 'text-rose-600 dark:text-rose-400',
    'eyebrow'    => 'Error interno',
    'title'      => 'Error del servidor',
    'copy'       => 'Ocurrió un error inesperado en el servidor. El equipo técnico ha sido notificado automáticamente.',
    'actions'    => [
        ['href' => route('administracion.index'),  'label' => 'Volver al inicio', 'icon' => 'bi-house-fill',      'primary' => true],
        ['href' => 'javascript:location.reload()', 'label' => 'Reintentar',       'icon' => 'bi-arrow-clockwise', 'primary' => false],
    ],
    'support' => true,
])
@endsection
