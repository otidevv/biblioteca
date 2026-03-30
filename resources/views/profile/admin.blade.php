@extends('layouts.admin')

@section('page-title', 'Perfil')

@section('css')
    <link href="{{ asset('css/profile/edit.css') }}?v={{ filemtime(public_path('css/profile/edit.css')) }}" rel="stylesheet" />
@endsection

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
                <h2 class="admin-panel__title">Mi perfil</h2>
                <p class="admin-panel__copy">Actualiza tus datos de acceso, tu foto de perfil y la seguridad de tu cuenta institucional.</p>
            </div>
        </div>

        <div class="profile-layout">
            <aside class="profile-summary">
                <div class="profile-summary__avatar-shell">
                    @if(optional($user->persona)->foto)
                        <img src="{{ asset('storage/' . $user->persona->foto) }}" alt="Foto de perfil" class="profile-summary__avatar">
                    @else
                        <div class="profile-summary__avatar profile-summary__avatar--placeholder">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="profile-summary__name">{{ $user->name }}</div>
                <div class="profile-summary__email">{{ $user->email }}</div>
                <div class="profile-summary__chips">
                    <span class="profile-chip">{{ $user->tipo_usuario ?: 'Usuario' }}</span>
                    <span class="profile-chip {{ (int) ($user->estado ?? 0) === 1 ? 'is-active' : 'is-inactive' }}">
                        {{ (int) ($user->estado ?? 0) === 1 ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </aside>

            <div class="profile-forms">
                <section class="admin-modal-section profile-card">
                    <div class="profile-card__head">
                        <div>
                            <h3>Informacion de la cuenta</h3>
                            <p>Cambia tu nombre, correo y fotografia visible en el panel.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="layout" value="{{ $profileLayout }}">

                        <div class="col-md-6 form-group">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" readonly disabled>
                            <small class="text-muted d-block mt-2">El correo de acceso no se puede modificar desde esta pantalla.</small>
                        </div>

                        <div class="col-md-12 form-group">
                            <label class="form-label">Foto de perfil</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted d-block mt-2">Formato sugerido: JPG o PNG, hasta 3 MB.</small>
                            @error('foto')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex align-items-center gap-3 flex-wrap">
                            <button type="submit" class="btn btn-success px-4">Guardar cambios</button>
                            @if (session('status') === 'profile-updated')
                                <span class="text-success fw-semibold">Perfil actualizado correctamente.</span>
                            @endif
                        </div>
                    </form>
                </section>

                <section class="admin-modal-section profile-card">
                    <div class="profile-card__head">
                        <div>
                            <h3>Seguridad</h3>
                            <p>Actualiza tu contrasena para mantener segura tu sesion administrativa.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-md-4 form-group">
                            <label class="form-label">Contrasena actual</label>
                            <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                            @error('current_password', 'updatePassword')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label class="form-label">Nueva contrasena</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                            @error('password', 'updatePassword')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label class="form-label">Confirmar contrasena</label>
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                            @error('password_confirmation', 'updatePassword')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex align-items-center gap-3 flex-wrap">
                            <button type="submit" class="btn btn-primary px-4">Actualizar contrasena</button>
                            @if (session('status') === 'password-updated')
                                <span class="text-success fw-semibold">Contrasena actualizada correctamente.</span>
                            @endif
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </section>
</div>
@endsection
