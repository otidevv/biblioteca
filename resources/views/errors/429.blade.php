@extends('layouts.admin')
@section('page-title', 'Demasiadas solicitudes · 429')

@section('content')
@include('errors._shell', [
    'code'       => '429',
    'gradient'   => 'from-orange-600 via-red-500 to-rose-600',
    'glowColor'  => 'rgba(239,68,68,0.22)',
    'icon'       => 'bi-speedometer2',
    'eyebrowBg'  => 'bg-orange-50 dark:bg-orange-950/40',
    'eyebrowTxt' => 'text-orange-600 dark:text-orange-400',
    'eyebrow'    => 'Límite de solicitudes',
    'title'      => 'Demasiadas solicitudes',
    'copy'       => 'Has realizado demasiadas solicitudes en poco tiempo. Espera unos segundos y vuelve a intentarlo.',
    'actions'    => [
        ['href' => route('administracion.index'),  'label' => 'Volver al inicio', 'icon' => 'bi-house-fill',         'primary' => true],
        ['href' => 'javascript:location.reload()', 'label' => 'Reintentar',       'icon' => 'bi-arrow-clockwise',    'primary' => false],
    ],
    'support' => false,
])
@endsection
