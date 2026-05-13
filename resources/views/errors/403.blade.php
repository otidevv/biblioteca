@extends('layouts.admin')
@section('page-title', 'Acceso no autorizado · 403')
@section('content')
@include('errors._shell', [
    'code'       => '403',
    'gradient'   => 'from-teal-700 via-teal-600 to-blue-700',
    'glowColor'  => 'rgba(20,184,166,0.25)',
    'icon'       => 'bi-shield-lock-fill',
    'eyebrowBg'  => 'bg-red-50 dark:bg-red-950/40',
    'eyebrowTxt' => 'text-red-600 dark:text-red-400',
    'eyebrow'    => 'Acceso restringido',
    'title'      => 'No autorizado',
    'copy'       => 'No tienes permisos para acceder a esta sección del sistema. Si crees que esto es un error, contacta al administrador.',
    'actions'    => [
        ['href' => route('administracion.index'), 'label' => 'Volver al inicio',  'icon' => 'bi-house-fill',  'primary' => true],
        ['href' => 'javascript:history.back()',   'label' => 'Página anterior',   'icon' => 'bi-arrow-left', 'primary' => false],
    ],
    'support' => false,
])
@endsection
