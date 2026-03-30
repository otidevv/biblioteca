@extends('layouts.admin')

@section('page-title', 'Perfil de usuario')

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Perfil</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Configuracion de perfil</h2>
                <p class="admin-panel__copy">Actualiza tus datos de acceso y mantén segura tu cuenta institucional.</p>
            </div>
        </div>

        <div class="admin-modal-section">
            @include('profile.partials.update-profile-information-form')
        </div>
        <div class="admin-modal-section mt-4">
            @include('profile.partials.update-password-form')
        </div>
        <div class="admin-modal-section mt-4">
            @include('profile.partials.delete-user-form')
        </div>
    </section>
</div>
@endsection
