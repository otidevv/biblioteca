@extends('layouts.biblioteca')

@section('title', 'Mi perfil | Biblioteca UNAMAD')

@section('css')
    <link href="{{ asset('css/profile/library.css') }}?v={{ filemtime(public_path('css/profile/library.css')) }}" rel="stylesheet" />
@endsection

@section('content')
<section class="library-profile">
    <div class="library-profile__hero">
        <div>
            <span class="library-profile__eyebrow">Cuenta de usuario</span>
            <h2 class="library-profile__title">Mi perfil</h2>
            <p class="library-profile__copy">Administra tu informacion personal, actualiza tu foto y refuerza la seguridad de tu acceso a la biblioteca.</p>
        </div>
    </div>

    <div class="library-profile__layout">
        <aside class="library-profile__summary">
            <div class="library-profile__avatar-shell">
                @if(optional($user->persona)->foto)
                    <img src="{{ asset('storage/' . $user->persona->foto) }}" alt="Foto de perfil" class="library-profile__avatar">
                @else
                    <div class="library-profile__avatar library-profile__avatar--placeholder">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="library-profile__name">{{ $user->name }}</div>
            <div class="library-profile__email">{{ $user->email }}</div>
            <div class="library-profile__chips">
                <span class="library-profile__chip">{{ $user->tipo_usuario ?: 'Usuario' }}</span>
                <span class="library-profile__chip {{ (int) ($user->estado ?? 0) === 1 ? 'is-active' : 'is-inactive' }}">
                    {{ (int) ($user->estado ?? 0) === 1 ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </aside>

        <div class="library-profile__forms">
            <section class="library-profile__card">
                <div class="library-profile__card-head">
                    <h3>Informacion personal</h3>
                    <p>Actualiza el nombre, correo y foto que acompanian tu cuenta.</p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="layout" value="{{ $profileLayout }}">

                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" readonly disabled>
                        <small class="text-muted d-block mt-2">El correo de acceso no se puede modificar desde esta pantalla.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Foto de perfil</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <small class="text-muted d-block mt-2">Formato sugerido: JPG o PNG, hasta 3 MB.</small>
                        @error('foto')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex align-items-center gap-3 flex-wrap">
                        <button type="submit" class="btn library-profile__btn-primary">Guardar cambios</button>
                        @if (session('status') === 'profile-updated')
                            <span class="text-success fw-semibold">Perfil actualizado correctamente.</span>
                        @endif
                    </div>
                </form>
            </section>

            <section class="library-profile__card">
                <div class="library-profile__card-head">
                    <h3>Seguridad</h3>
                    <p>Cambia tu contrasena para proteger el acceso a tus reservas y prestamos.</p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-4">
                        <label class="form-label">Contrasena actual</label>
                        <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nueva contrasena</label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Confirmar contrasena</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                        @error('password_confirmation', 'updatePassword')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex align-items-center gap-3 flex-wrap">
                        <button type="submit" class="btn library-profile__btn-secondary">Actualizar contrasena</button>
                        @if (session('status') === 'password-updated')
                            <span class="text-success fw-semibold">Contrasena actualizada correctamente.</span>
                        @endif
                    </div>
                </form>
            </section>
        </div>
    </div>
</section>
@endsection
