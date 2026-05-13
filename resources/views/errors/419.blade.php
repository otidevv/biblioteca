@extends('layouts.admin')
@section('page-title', 'Sesión expirada · 419')

@section('content')
@include('errors._shell', [
    'code'       => '419',
    'gradient'   => 'from-amber-600 via-orange-500 to-yellow-600',
    'glowColor'  => 'rgba(245,158,11,0.25)',
    'icon'       => 'bi-clock-history',
    'eyebrowBg'  => 'bg-amber-50 dark:bg-amber-950/40',
    'eyebrowTxt' => 'text-amber-600 dark:text-amber-400',
    'eyebrow'    => 'Sesión caducada',
    'title'      => 'Sesión expirada',
    'copy'       => 'Tu sesión ha caducado por inactividad. Recarga la página para continuar trabajando con seguridad.',
    'actions'    => [
        ['href' => 'javascript:location.reload()', 'label' => 'Recargar página',  'icon' => 'bi-arrow-clockwise', 'primary' => true],
        ['href' => route('login'),                 'label' => 'Iniciar sesión',    'icon' => 'bi-box-arrow-in-right', 'primary' => false],
    ],
    'support' => false,
])
@endsection
