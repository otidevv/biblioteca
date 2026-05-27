@extends('layouts.biblioteca')

@section('title', 'Mi perfil | Biblioteca UNAMAD')

@section('css')
    <link href="{{ asset('css/profile/library.css') }}?v={{ filemtime(public_path('css/profile/library.css')) }}" rel="stylesheet" />
@endsection

@section('js')
<script>
(function () {
    // ── Avatar click → abre el selector de archivo ──────────────────────
    const avatarBtn = document.getElementById('avatarTrigger');
    const fotoInput = document.getElementById('fotoInput');
    const fileLabel = document.getElementById('fotoNombre');

    if (avatarBtn && fotoInput) {
        avatarBtn.addEventListener('click', function () { fotoInput.click(); });
    }

    if (fotoInput) {
        fotoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            // Preview inmediato
            const reader = new FileReader();
            reader.onload = function (ev) {
                const img = document.getElementById('avatarImg');
                const ph  = document.getElementById('avatarPlaceholder');
                if (img) { img.src = ev.target.result; img.classList.remove('lp-hidden'); }
                if (ph)  { ph.classList.add('lp-hidden'); }
                document.getElementById('avatarNewBadge').classList.remove('lp-hidden');
            };
            reader.readAsDataURL(file);

            if (fileLabel) {
                fileLabel.textContent = file.name.length > 34
                    ? file.name.substring(0, 31) + '…'
                    : file.name;
                fileLabel.closest('.lp-file-info').classList.remove('lp-hidden');
            }
        });
    }

    // ── Mostrar / ocultar contraseña ─────────────────────────────────────
    document.querySelectorAll('.lp-eye').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (!input) return;
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            this.querySelector('i').className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });

    // ── Fuerza de contraseña ─────────────────────────────────────────────
    const newPass = document.getElementById('newPassword');
    if (newPass) {
        newPass.addEventListener('input', function () {
            const v   = this.value;
            let score = 0;
            if (v.length >= 8)        score++;
            if (/[A-Z]/.test(v))      score++;
            if (/[0-9]/.test(v))      score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;

            const bar   = document.getElementById('passBar');
            const label = document.getElementById('passLabel');
            const colors = ['#e5e7eb','#ef4444','#f59e0b','#3b82f6','#10b981'];
            const widths = ['0%','25%','50%','75%','100%'];
            const texts  = ['','Débil','Regular','Buena','Fuerte'];

            if (bar)   { bar.style.width = widths[score]; bar.style.background = colors[score]; }
            if (label) { label.textContent = texts[score]; label.style.color = colors[score]; }
        });
    }

    // ── Coincidencia de contraseña ───────────────────────────────────────
    const passConf = document.getElementById('passConfirm');
    if (passConf && newPass) {
        passConf.addEventListener('input', function () {
            const hint = document.getElementById('passMatchHint');
            if (!hint) return;
            if (this.value === '') { hint.textContent = ''; return; }
            const match = this.value === newPass.value;
            hint.textContent  = match ? 'Las contraseñas coinciden' : 'No coinciden';
            hint.style.color  = match ? '#10b981' : '#ef4444';
        });
    }
})();
</script>
@endsection

@section('content')
<section class="lp">

    {{-- Hero ──────────────────────────────────────────────────────────── --}}
    <header class="lp__hero">
        <div class="lp__hero-icon"><i class="bi bi-person-circle"></i></div>
        <div>
            <span class="lp__eyebrow">Cuenta de usuario</span>
            <h2 class="lp__title">Mi perfil</h2>
            <p class="lp__copy">Gestiona tu información personal, actualiza tu foto y refuerza la seguridad de tu acceso a la biblioteca.</p>
        </div>
    </header>

    <div class="lp__layout">

        {{-- Sidebar ────────────────────────────────────────────────────── --}}
        <aside class="lp__sidebar">

            {{-- Avatar -------------------------------------------------- --}}
            <div class="lp__avatar-wrap">
                <button type="button" id="avatarTrigger" class="lp__avatar-btn" title="Cambiar foto">
                    @if(optional($user->persona)->foto)
                        <img id="avatarImg" src="{{ asset('storage/' . $user->persona->foto) }}" alt="Foto" class="lp__avatar-img">
                        <div id="avatarPlaceholder" class="lp__avatar-ph lp-hidden">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
                    @else
                        <img id="avatarImg" src="" alt="" class="lp__avatar-img lp-hidden">
                        <div id="avatarPlaceholder" class="lp__avatar-ph">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
                    @endif
                    <div class="lp__avatar-overlay">
                        <i class="bi bi-camera-fill"></i>
                        <span>Cambiar</span>
                    </div>
                </button>
                <span id="avatarNewBadge" class="lp__avatar-badge lp-hidden">
                    <i class="bi bi-check-circle-fill"></i> Nueva foto lista
                </span>
            </div>

            {{-- Info ---------------------------------------------------- --}}
            <div class="lp__sidebar-name">{{ $user->name }}</div>
            <div class="lp__sidebar-email">{{ $user->email }}</div>

            <div class="lp__chips">
                <span class="lp__chip lp__chip--role">
                    <i class="bi bi-person-badge"></i>
                    {{ $user->tipo_usuario ?: 'Usuario' }}
                </span>
                <span class="lp__chip {{ (int)($user->estado ?? 0) === 1 ? 'lp__chip--active' : 'lp__chip--inactive' }}">
                    <i class="bi bi-{{ (int)($user->estado ?? 0) === 1 ? 'check-circle-fill' : 'dash-circle-fill' }}"></i>
                    {{ (int)($user->estado ?? 0) === 1 ? 'Activo' : 'Inactivo' }}
                </span>
            </div>

            <div class="lp__sidebar-meta">
                <div class="lp__sidebar-meta-item">
                    <i class="bi bi-calendar3"></i>
                    <span>Miembro desde {{ $user->created_at ? $user->created_at->locale('es')->translatedFormat('M Y') : '—' }}</span>
                </div>
                @if(optional($user->persona)->tipo_persona)
                <div class="lp__sidebar-meta-item">
                    <i class="bi bi-mortarboard"></i>
                    <span>{{ $user->persona->tipo_persona }}</span>
                </div>
                @endif
            </div>

            <p class="lp__avatar-hint">
                <i class="bi bi-info-circle"></i>
                Haz clic en la foto para cambiarla. Los cambios se guardan al presionar <strong>Guardar</strong>.
            </p>
        </aside>

        {{-- Formularios ────────────────────────────────────────────────── --}}
        <div class="lp__forms">

            {{-- Información personal ------------------------------------ --}}
            <section class="lp__card">
                <div class="lp__card-head">
                    <div class="lp__card-icon"><i class="bi bi-person-lines-fill"></i></div>
                    <div>
                        <h3 class="lp__card-title">Información personal</h3>
                        <p class="lp__card-copy">Actualiza el nombre y la foto asociados a tu cuenta.</p>
                    </div>
                </div>

                @if(session('status') === 'profile-updated')
                <div class="lp__alert lp__alert--ok">
                    <i class="bi bi-check-circle-fill"></i>
                    Perfil actualizado correctamente.
                </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="lp__form">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="layout" value="{{ $profileLayout }}">

                    <div class="lp__field">
                        <label class="lp__label" for="profileName">Nombre completo</label>
                        <input id="profileName" type="text" name="name" class="lp__input @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required autocomplete="name">
                        @error('name')
                            <span class="lp__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="lp__field">
                        <label class="lp__label" for="profileEmail">Correo electrónico</label>
                        <input id="profileEmail" type="email" class="lp__input lp__input--readonly"
                               value="{{ $user->email }}" readonly disabled>
                        <span class="lp__hint"><i class="bi bi-lock-fill"></i> El correo de acceso no se puede modificar aquí.</span>
                    </div>

                    {{-- Zona de carga de foto ----------------------------- --}}
                    <div class="lp__field">
                        <label class="lp__label">Foto de perfil</label>
                        <input type="file" id="fotoInput" name="foto" accept="image/*" class="lp-sr-only">

                        <div class="lp__upload-zone" id="uploadZone">
                            <div class="lp__upload-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                            <p class="lp__upload-text">
                                <button type="button" class="lp__upload-link" onclick="document.getElementById('fotoInput').click()">
                                    Selecciona una imagen
                                </button>
                                o arrástrala aquí
                            </p>
                            <p class="lp__upload-hint">JPG, PNG o WEBP · máx. 3 MB</p>
                        </div>

                        <div class="lp-file-info lp-hidden">
                            <i class="bi bi-file-image"></i>
                            <span id="fotoNombre" class="lp__file-name"></span>
                            <button type="button" class="lp__file-clear" onclick="
                                document.getElementById('fotoInput').value='';
                                this.closest('.lp-file-info').classList.add('lp-hidden');
                                document.getElementById('avatarNewBadge').classList.add('lp-hidden');
                            " title="Quitar">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>

                        @error('foto')
                            <span class="lp__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="lp__actions">
                        <button type="submit" class="lp__btn lp__btn--primary">
                            <i class="bi bi-floppy-fill"></i>
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </section>

            {{-- Seguridad ----------------------------------------------- --}}
            <section class="lp__card">
                <div class="lp__card-head">
                    <div class="lp__card-icon lp__card-icon--security"><i class="bi bi-shield-lock-fill"></i></div>
                    <div>
                        <h3 class="lp__card-title">Seguridad</h3>
                        <p class="lp__card-copy">Cambia tu contraseña para proteger el acceso a tus reservas y préstamos.</p>
                    </div>
                </div>

                @if(session('status') === 'password-updated')
                <div class="lp__alert lp__alert--ok">
                    <i class="bi bi-check-circle-fill"></i>
                    Contraseña actualizada correctamente.
                </div>
                @endif

                @if($errors->updatePassword->any())
                <div class="lp__alert lp__alert--error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ $errors->updatePassword->first() }}
                </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="lp__form">
                    @csrf
                    @method('PUT')

                    <div class="lp__field">
                        <label class="lp__label" for="currentPassword">Contraseña actual</label>
                        <div class="lp__input-group">
                            <input id="currentPassword" type="password" name="current_password"
                                   class="lp__input @error('current_password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="current-password">
                            <button type="button" class="lp__eye lp-eye" data-target="currentPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('current_password', 'updatePassword')
                            <span class="lp__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="lp__field">
                        <label class="lp__label" for="newPassword">Nueva contraseña</label>
                        <div class="lp__input-group">
                            <input id="newPassword" type="password" name="password"
                                   class="lp__input @error('password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password">
                            <button type="button" class="lp__eye lp-eye" data-target="newPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="lp__pass-strength">
                            <div class="lp__pass-bar-bg">
                                <div id="passBar" class="lp__pass-bar"></div>
                            </div>
                            <span id="passLabel" class="lp__pass-label"></span>
                        </div>
                        @error('password', 'updatePassword')
                            <span class="lp__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="lp__field">
                        <label class="lp__label" for="passConfirm">Confirmar contraseña</label>
                        <div class="lp__input-group">
                            <input id="passConfirm" type="password" name="password_confirmation"
                                   class="lp__input"
                                   autocomplete="new-password">
                            <button type="button" class="lp__eye lp-eye" data-target="passConfirm">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <span id="passMatchHint" class="lp__hint" style="font-size:.82rem;font-weight:600;"></span>
                    </div>

                    <div class="lp__actions">
                        <button type="submit" class="lp__btn lp__btn--secondary">
                            <i class="bi bi-shield-check"></i>
                            Actualizar contraseña
                        </button>
                    </div>
                </form>
            </section>

        </div>
    </div>
</section>
@endsection
