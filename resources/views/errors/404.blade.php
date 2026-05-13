@extends('layouts.admin')
@section('page-title', 'Página no encontrada · 404')

@section('content')
@include('errors._shell', [
    'code'       => '404',
    'gradient'   => 'from-violet-700 via-purple-600 to-blue-700',
    'glowColor'  => 'rgba(139,92,246,0.22)',
    'icon'       => 'bi-compass',
    'eyebrowBg'  => 'bg-violet-50 dark:bg-violet-950/40',
    'eyebrowTxt' => 'text-violet-600 dark:text-violet-400',
    'eyebrow'    => 'Recurso no encontrado',
    'title'      => 'Página no encontrada',
    'copy'       => 'La página que buscas no existe, fue eliminada o la dirección cambió. Verifica la URL e intenta de nuevo.',
    'actions'    => [
        ['href' => route('administracion.index'), 'label' => 'Volver al inicio', 'icon' => 'bi-house-fill',  'primary' => true],
        ['href' => 'javascript:history.back()',   'label' => 'Página anterior',  'icon' => 'bi-arrow-left', 'primary' => false],
    ],
    'support' => false,
])
@endsection
