@extends('layouts.admin')
@section('page-title', 'Servicio no disponible · 503')

@section('content')
@include('errors._shell', [
    'code'       => '503',
    'gradient'   => 'from-slate-700 via-slate-600 to-blue-700',
    'glowColor'  => 'rgba(100,116,139,0.22)',
    'icon'       => 'bi-tools',
    'eyebrowBg'  => 'bg-slate-100 dark:bg-slate-800/60',
    'eyebrowTxt' => 'text-slate-600 dark:text-slate-400',
    'eyebrow'    => 'Mantenimiento',
    'title'      => 'Servicio no disponible',
    'copy'       => isset($exception) && $exception->getMessage()
                        ? $exception->getMessage()
                        : 'El sistema se encuentra temporalmente en mantenimiento. Volveremos pronto.',
    'actions'    => [
        ['href' => 'javascript:location.reload()', 'label' => 'Verificar nuevamente', 'icon' => 'bi-arrow-clockwise', 'primary' => true],
    ],
    'support' => true,
])
@endsection
