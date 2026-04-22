<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>@yield('title', 'Biblioteca UNAMAD')</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="@yield('meta_description', 'Biblioteca UNAMAD: consulta catálogos, revisa disponibilidad de libros y gestiona reservas y préstamos en línea.')">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet">
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@yield('css')

<style>
:root {
    --library-forest: #184d3b;
    --library-forest-deep: #0f3025;
    --library-leaf: #2f7a5d;
    --library-gold: #d8b15c;
    --library-cream: #f7f1e4;
    --library-ink: #1f2c27;
    --library-mist: #edf2ef;
    --library-card: rgba(255, 255, 255, 0.82);
    --library-border: rgba(24, 77, 59, 0.12);
    --library-shadow: 0 24px 60px rgba(18, 39, 31, 0.12);
    --sidebar-width: 280px;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    color: var(--library-ink);
    background:
        radial-gradient(circle at top left, rgba(216, 177, 92, 0.22), transparent 28%),
        radial-gradient(circle at bottom right, rgba(47, 122, 93, 0.2), transparent 24%),
        linear-gradient(180deg, #f8f4ea 0%, #eef3ef 48%, #e7efe9 100%);
    font-family: "Segoe UI", "Trebuchet MS", sans-serif;
}

.library-shell {
    min-height: 100vh;
}

.library-sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    width: var(--sidebar-width);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    background:
        linear-gradient(180deg, rgba(15, 48, 37, 0.98) 0%, rgba(24, 77, 59, 0.96) 52%, rgba(47, 122, 93, 0.94) 100%);
    color: #fff;
    box-shadow: 18px 0 50px rgba(10, 26, 20, 0.18);
    z-index: 1040;
    transition: transform 0.28s ease;
    isolation: isolate;
}

.library-sidebar::after {
    content: "";
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at top left, rgba(255, 255, 255, 0.08), transparent 24%),
        linear-gradient(180deg, transparent 0%, rgba(255, 255, 255, 0.04) 100%);
    pointer-events: none;
    z-index: -1;
}

.library-brand {
    padding: 1.1rem 1.15rem;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 1.4rem;
    background: rgba(255, 255, 255, 0.06);
    backdrop-filter: blur(10px);
}

.library-brand-header {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    margin-bottom: 0.95rem;
}

.library-brand-mark {
    width: 64px;
    height: 64px;
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 18px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.96);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.28),
        0 14px 28px rgba(0, 0, 0, 0.18);
}

.library-brand-mark::before {
    content: "";
    position: absolute;
    inset: 6px;
    border-radius: 14px;
    border: 1px solid rgba(24, 77, 59, 0.12);
}

.library-brand-logo {
    position: relative;
    z-index: 1;
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 0.45rem;
}

.library-brand-copy {
    min-width: 0;
}

.library-brand-copy h1 {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 800;
    letter-spacing: 0.05em;
}

.library-brand-copy small {
    display: block;
    margin-top: 0.18rem;
    color: rgba(255, 255, 255, 0.76);
    font-size: 0.72rem;
    line-height: 1.35;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.library-brand p {
    margin: 0;
    color: rgba(255, 255, 255, 0.72);
    font-size: 0.92rem;
    line-height: 1.5;
}

.library-nav {
    display: grid;
    gap: 0.5rem;
}

.library-nav-link {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.9rem 1rem;
    border-radius: 1rem;
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease;
}

.library-nav-link:focus-visible,
.library-login-btn:focus-visible,
.library-logout-btn:focus-visible,
.library-menu-btn:focus-visible {
    outline: 3px solid rgba(242, 207, 130, 0.9);
    outline-offset: 3px;
}

.library-nav-icon {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 14px;
    font-size: 1.1rem;
    color: #fff;
    background: linear-gradient(135deg, rgba(216, 177, 92, 0.34), rgba(255, 255, 255, 0.08));
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
    transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}

.library-nav-link.nav-home .library-nav-icon {
    background: linear-gradient(135deg, rgba(72, 166, 120, 0.95), rgba(20, 86, 63, 0.95));
}

.library-nav-link.nav-catalog .library-nav-icon {
    background: linear-gradient(135deg, rgba(56, 126, 201, 0.95), rgba(28, 74, 135, 0.95));
}

.library-nav-link.nav-events .library-nav-icon {
    background: linear-gradient(135deg, rgba(223, 150, 63, 0.95), rgba(180, 90, 28, 0.95));
}

.library-nav-link.nav-libraries .library-nav-icon {
    background: linear-gradient(135deg, rgba(26, 127, 110, 0.95), rgba(17, 94, 89, 0.95));
}

.library-nav-link.nav-reservations .library-nav-icon {
    background: linear-gradient(135deg, rgba(167, 92, 214, 0.95), rgba(108, 47, 149, 0.95));
}

.library-nav-link.nav-loans .library-nav-icon {
    background: linear-gradient(135deg, rgba(228, 91, 109, 0.95), rgba(151, 34, 61, 0.95));
}

.library-nav-text {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
}

.library-nav-text strong {
    font-size: 0.95rem;
    font-weight: 700;
}

.library-nav-text small {
    color: rgba(255, 255, 255, 0.62);
    font-size: 0.76rem;
    margin-top: 0.15rem;
}

.library-nav-link:hover,
.library-nav-link.is-active {
    color: #fff;
    background: rgba(255, 255, 255, 0.12);
    transform: translateX(4px);
}

.library-nav-link:hover .library-nav-icon,
.library-nav-link.is-active .library-nav-icon {
    transform: scale(1.08);
    box-shadow:
        0 12px 24px rgba(8, 18, 14, 0.24),
        inset 0 1px 0 rgba(255, 255, 255, 0.22);
}

.library-nav-link.nav-home:hover .library-nav-icon,
.library-nav-link.nav-home.is-active .library-nav-icon {
    background: linear-gradient(135deg, #7fe0a8, #1d7f59);
}

.library-nav-link.nav-catalog:hover .library-nav-icon,
.library-nav-link.nav-catalog.is-active .library-nav-icon {
    background: linear-gradient(135deg, #74c0ff, #2f6fd4);
}

.library-nav-link.nav-events:hover .library-nav-icon,
.library-nav-link.nav-events.is-active .library-nav-icon {
    background: linear-gradient(135deg, #ffd37a, #d97a25);
}

.library-nav-link.nav-libraries:hover .library-nav-icon,
.library-nav-link.nav-libraries.is-active .library-nav-icon {
    background: linear-gradient(135deg, #59d0c1, #16796f);
}

.library-nav-link.nav-reservations:hover .library-nav-icon,
.library-nav-link.nav-reservations.is-active .library-nav-icon {
    background: linear-gradient(135deg, #d6a7ff, #8a47c7);
}

.library-nav-link.nav-loans:hover .library-nav-icon,
.library-nav-link.nav-loans.is-active .library-nav-icon {
    background: linear-gradient(135deg, #ff9aa9, #d64566);
}

.library-nav-link.is-active .library-nav-text small,
.library-nav-link:hover .library-nav-text small {
    color: rgba(255, 255, 255, 0.82);
}

.library-sidebar-footer {
    margin-top: auto;
    padding: 1rem 1.05rem;
    border-radius: 1.2rem;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.library-sidebar-footer small {
    color: rgba(255, 255, 255, 0.74);
}

.library-main {
    min-height: 100vh;
    margin-left: var(--sidebar-width);
    padding: 1.35rem;
}

.library-topbar {
    position: sticky;
    top: 0;
    z-index: 1030;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.35rem;
    padding: 0.95rem 1.15rem;
    border: 1px solid rgba(255, 255, 255, 0.55);
    border-radius: 1.4rem;
    background:
        linear-gradient(135deg, rgba(255, 255, 255, 0.82), rgba(248, 244, 234, 0.92)),
        rgba(255, 251, 244, 0.82);
    backdrop-filter: blur(14px);
    box-shadow: 0 10px 35px rgba(24, 77, 59, 0.08);
}

.library-topbar-title {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    min-width: 0;
}

.library-topbar-title-badge {
    width: 54px;
    height: 54px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 16px;
    overflow: hidden;
    padding: 0.35rem;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(241, 246, 242, 0.94));
    border: 1px solid rgba(24, 77, 59, 0.1);
    box-shadow: 0 10px 24px rgba(24, 77, 59, 0.14);
}

.library-topbar-title-badge img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.library-topbar-title-copy {
    padding: 0.35rem 0.4rem 0.35rem 0;
}

.library-topbar-title-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin-bottom: 0.2rem;
    padding: 0.22rem 0.58rem;
    border-radius: 999px;
    background: rgba(24, 77, 59, 0.08);
    color: #2a5a49;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.library-topbar-title h5 {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 800;
}

.library-topbar-title span {
    display: block;
    color: #5a6d66;
    font-size: 0.88rem;
}

.library-topbar-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.library-user-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.9rem;
    border-radius: 999px;
    background: rgba(24, 77, 59, 0.08);
    color: var(--library-forest);
    font-weight: 600;
    border: 1px solid rgba(24, 77, 59, 0.08);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
}

.library-user-chip.dropdown-toggle::after {
    margin-left: 0.15rem;
}

.library-user-chip:hover,
.library-user-chip:focus {
    color: var(--library-forest);
    background: rgba(24, 77, 59, 0.12);
}

.library-user-chip i {
    color: var(--library-gold);
}

.library-login-btn,
.library-logout-btn,
.library-menu-btn,
.library-alert-btn,
.library-theme-btn {
    border: 0;
    border-radius: 999px;
    font-weight: 600;
}

.library-login-btn {
    color: #16392d;
    background: linear-gradient(135deg, #efd08c, #f7e7bb);
}

.library-logout-btn {
    color: #fff;
    background: linear-gradient(135deg, #205842, #123529);
}

.library-menu-btn {
    width: 44px;
    height: 44px;
    display: none;
    align-items: center;
    justify-content: center;
    color: #fff;
    background: linear-gradient(135deg, var(--library-leaf), var(--library-forest-deep));
    box-shadow: 0 12px 30px rgba(24, 77, 59, 0.2);
}

.library-theme-btn {
    width: 46px;
    height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--library-forest);
    background: rgba(24, 77, 59, 0.08);
    border: 1px solid rgba(24, 77, 59, 0.08);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
}

.library-theme-btn i {
    font-size: 1rem;
}

.library-alert-dropdown {
    position: relative;
}

.library-alert-btn {
    width: 46px;
    height: 46px;
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--library-forest);
    background: rgba(24, 77, 59, 0.08);
    border: 1px solid rgba(24, 77, 59, 0.08);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
}

.library-alert-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    min-width: 20px;
    height: 20px;
    padding: 0 0.3rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: linear-gradient(135deg, #d86b52, #b1392f);
    color: #fff;
    font-size: 0.68rem;
    font-weight: 800;
    box-shadow: 0 10px 20px rgba(177, 57, 47, 0.22);
}

.library-alert-menu {
    width: min(380px, calc(100vw - 2rem));
    padding: 0.75rem;
    border: 1px solid rgba(24, 77, 59, 0.08);
    border-radius: 1.1rem;
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 24px 50px rgba(24, 77, 59, 0.16);
}

.library-alert-menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    padding: 0.2rem 0.25rem 0.7rem;
    border-bottom: 1px solid rgba(24, 77, 59, 0.08);
}

.library-alert-menu-header strong {
    color: #173d2f;
}

.library-alert-list {
    display: grid;
    gap: 0.65rem;
    margin-top: 0.75rem;
}

.library-alert-item {
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr);
    gap: 0.75rem;
    padding: 0.8rem;
    border-radius: 1rem;
    text-decoration: none;
    color: inherit;
    background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(244,248,245,.94));
    border: 1px solid rgba(24, 77, 59, 0.06);
}

.library-alert-item:hover {
    background: rgba(24, 77, 59, 0.05);
}

.library-alert-item--button {
    width: 100%;
    border: 0;
    text-align: left;
}

.library-alert-icon {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(24, 77, 59, 0.08);
    color: #1d654c;
}

.library-alert-copy strong {
    display: block;
    color: #173d2f;
    font-size: 0.92rem;
}

.library-alert-copy p {
    margin: 0.2rem 0 0;
    color: #62756d;
    font-size: 0.84rem;
    line-height: 1.55;
}

.library-alert-copy small {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    margin-top: 0.35rem;
    color: #7a8a84;
}

.library-content {
    position: relative;
    overflow: hidden;
    padding: 1.4rem;
    border: 1px solid rgba(255, 255, 255, 0.45);
    border-radius: 1.8rem;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.72), rgba(255, 255, 255, 0.6));
    box-shadow: var(--library-shadow);
}

.library-content::before {
    content: "";
    position: absolute;
    inset: 0 auto auto 0;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(216, 177, 92, 0.12), transparent 68%);
    pointer-events: none;
}

.library-footer {
    margin-top: 1.2rem;
    padding: 1.15rem 1.3rem;
    border-radius: 1.4rem;
    border: 1px solid rgba(255, 255, 255, 0.45);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.72), rgba(249, 246, 238, 0.82));
    box-shadow: 0 12px 30px rgba(24, 77, 59, 0.07);
}

.library-footer-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr;
    gap: 1.1rem;
    align-items: start;
}

.library-footer h6 {
    margin-bottom: 0.55rem;
    color: #173d2f;
    font-weight: 800;
}

.library-footer p,
.library-footer small,
.library-footer a {
    color: #61746d;
}

.library-footer a {
    text-decoration: none;
}

.library-footer a:hover {
    color: #1f674d;
}

.library-footer-list {
    display: grid;
    gap: 0.45rem;
}

.library-footer-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding-top: 0.95rem;
    margin-top: 0.95rem;
    border-top: 1px solid rgba(24, 77, 59, 0.1);
    color: #6a7b74;
    font-size: 0.88rem;
}

.hero,
.card,
.modal-content {
    border-radius: 1.25rem;
}

.card,
.table,
.modal-content {
    border-color: var(--library-border);
}

.card {
    background: var(--library-card);
    box-shadow: 0 14px 36px rgba(24, 77, 59, 0.08);
}

.book-card,
.card-hover {
    overflow: hidden;
    transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.book-card:hover,
.card-hover:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px rgba(24, 77, 59, 0.16);
}

.libro-img {
    width: 100%;
    height: 300px;
    object-fit: contain;
    background: linear-gradient(180deg, #fbfaf5, #f2f4ef);
    padding: 12px;
    transition: transform 0.25s ease;
}

.book-card:hover .libro-img {
    transform: scale(1.04);
}

.btn-libro {
    color: #fff;
    border: 0;
    border-radius: 0.9rem;
    background: linear-gradient(135deg, var(--library-leaf), var(--library-forest-deep));
}

.btn-libro:hover {
    color: #fff;
    background: linear-gradient(135deg, #2c8564, #0d2f23);
}

.stars i {
    color: #d5a942 !important;
    font-size: 14px;
}

.rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-start;
}

.rating input {
    display: none;
}

.rating label {
    font-size: 25px;
    color: #ccc;
    cursor: pointer;
    transition: color 0.2s ease;
}

.rating input:checked ~ label,
.rating label:hover,
.rating label:hover ~ label {
    color: #ffc107;
}

.overlay {
    position: fixed;
    inset: 0;
    display: none;
    background: rgba(9, 21, 17, 0.4);
    backdrop-filter: blur(4px);
    z-index: 1035;
}

.overlay.active {
    display: block;
}

#mensaje_container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    bottom: auto;
    left: auto;
    z-index: 1060;
    width: min(420px, calc(100vw - 2rem));
    max-width: calc(100vw - 2rem);
    height: auto;
    display: grid;
    gap: 0.75rem;
    pointer-events: none;
}

#mensaje_container .notificacion {
    max-height: calc(100vh - 2rem);
    pointer-events: auto;
    overflow: hidden;
}

@media (max-width: 576px) {
    #mensaje_container {
        left: 1rem;
        right: 1rem;
        width: auto;
        max-width: none;
    }
}

body.library-dark {
    color: #e7f0ea;
    background:
        radial-gradient(circle at top left, rgba(216, 177, 92, 0.12), transparent 22%),
        radial-gradient(circle at 85% 12%, rgba(66, 153, 120, 0.14), transparent 18%),
        radial-gradient(circle at bottom right, rgba(47, 122, 93, 0.14), transparent 22%),
        linear-gradient(180deg, #08110e 0%, #0d1714 38%, #111d19 100%);
}

body.library-dark .library-sidebar {
    background:
        radial-gradient(circle at top left, rgba(250, 204, 102, 0.12), transparent 22%),
        linear-gradient(180deg, rgba(7, 16, 13, 0.98) 0%, rgba(10, 23, 19, 0.97) 55%, rgba(15, 33, 27, 0.96) 100%);
    box-shadow: 18px 0 52px rgba(0, 0, 0, 0.34);
}

body.library-dark .library-brand,
body.library-dark .library-sidebar-footer {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.08);
}

body.library-dark .library-brand-copy small,
body.library-dark .library-brand p,
body.library-dark .library-sidebar-footer small,
body.library-dark .library-nav-text small {
    color: rgba(220, 231, 226, 0.68);
}

body.library-dark .library-nav-link {
    color: rgba(233, 241, 236, 0.8);
}

body.library-dark .library-nav-link:hover,
body.library-dark .library-nav-link.is-active {
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
}

body.library-dark .library-nav-link.is-active .library-nav-text small,
body.library-dark .library-nav-link:hover .library-nav-text small {
    color: rgba(244, 248, 246, 0.82);
}

body.library-dark .library-topbar,
body.library-dark .library-content,
body.library-dark .library-footer {
    border-color: rgba(255, 255, 255, 0.08);
    background:
        linear-gradient(180deg, rgba(18, 30, 24, 0.9), rgba(11, 20, 16, 0.88)),
        rgba(14, 24, 20, 0.88);
    box-shadow:
        0 18px 42px rgba(0, 0, 0, 0.34),
        inset 0 1px 0 rgba(255, 255, 255, 0.03);
}

body.library-dark .library-content::before {
    background: radial-gradient(circle, rgba(216, 177, 92, 0.1), transparent 68%);
}

body.library-dark .library-topbar-title h5,
body.library-dark .library-content h1,
body.library-dark .library-content h2,
body.library-dark .library-content h3,
body.library-dark .library-content h4,
body.library-dark .library-content h5,
body.library-dark .library-content h6,
body.library-dark .library-footer h6,
body.library-dark .library-alert-copy strong,
body.library-dark .library-alert-menu-header strong,
body.library-dark .library-user-chip,
body.library-dark .library-theme-btn,
body.library-dark .library-alert-btn {
    color: #f8fafc;
}

body.library-dark .library-topbar-title span,
body.library-dark .library-topbar-title-kicker,
body.library-dark .library-content p,
body.library-dark .library-content small,
body.library-dark .library-footer p,
body.library-dark .library-footer small,
body.library-dark .library-footer a,
body.library-dark .library-alert-copy p,
body.library-dark .library-alert-copy small,
body.library-dark .text-muted {
    color: #a8bbb1 !important;
}

body.library-dark .library-user-chip,
body.library-dark .library-theme-btn,
body.library-dark .library-alert-btn {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.05));
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

body.library-dark .library-alert-menu,
body.library-dark .dropdown-menu,
body.library-dark .card,
body.library-dark .modal-content,
body.library-dark .table,
body.library-dark .alert,
body.library-dark .home-stat-card,
body.library-dark .home-library-card,
body.library-dark .home-book-card,
body.library-dark .home-activity-card {
    color: #e5efe9;
    border-color: rgba(255, 255, 255, 0.08);
    background:
        linear-gradient(180deg, rgba(22, 35, 29, 0.98), rgba(13, 22, 18, 0.95)),
        rgba(16, 27, 22, 0.96);
    box-shadow:
        0 16px 36px rgba(0, 0, 0, 0.28),
        inset 0 1px 0 rgba(255, 255, 255, 0.03);
}

body.library-dark .library-alert-item {
    background: linear-gradient(180deg, rgba(26, 40, 33, 0.96), rgba(15, 24, 20, 0.94));
    border-color: rgba(255, 255, 255, 0.06);
}

body.library-dark .library-alert-item:hover,
body.library-dark .home-library-card:hover,
body.library-dark .home-book-card:hover,
body.library-dark .home-activity-card:hover {
    background:
        linear-gradient(180deg, rgba(29, 45, 37, 0.98), rgba(17, 28, 23, 0.95)),
        rgba(21, 34, 28, 0.96);
    box-shadow: 0 22px 46px rgba(0, 0, 0, 0.34);
}

body.library-dark .form-control,
body.library-dark .form-select,
body.library-dark .input-group-text,
body.library-dark input,
body.library-dark select,
body.library-dark textarea {
    color: #f8fafc;
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.1);
}

body.library-dark .table > :not(caption) > * > * {
    color: #e5efe9;
    background-color: transparent;
    border-bottom-color: rgba(255, 255, 255, 0.08);
}

body.library-dark .home-library-card h5,
body.library-dark .home-book-card h6,
body.library-dark .home-section-title,
body.library-dark .home-stat-card h3,
body.library-dark .home-activity-card h6 {
    color: #f8fafc;
}

body.library-dark .home-library-card p,
body.library-dark .home-section-subtitle,
body.library-dark .home-book-authors,
body.library-dark .home-book-meta small,
body.library-dark .home-stat-card p,
body.library-dark .home-activity-copy,
body.library-dark .home-activity-date {
    color: #a8bbb1;
}

body.library-dark .home-search-card {
    background: rgba(12, 21, 18, 0.6);
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

body.library-dark .home-search-card .input-group-text,
body.library-dark .home-search-card .form-control {
    background: rgba(244, 248, 246, 0.94);
    color: #10211a;
}

body.library-dark .home-link,
body.library-dark .home-library-footer,
body.library-dark .home-activity-footer,
body.library-dark .report-center__download,
body.library-dark a {
    color: #9be7c5;
}

body.library-dark .home-library-badge,
body.library-dark .home-activity-badge,
body.library-dark .home-book-tag,
body.library-dark .library-topbar-title-kicker {
    background: rgba(255, 255, 255, 0.08);
    color: #f2cf82;
}

body.library-dark .home-book-button,
body.library-dark .btn-libro,
body.library-dark .library-login-btn {
    color: #0c1713;
    background: linear-gradient(135deg, #f0d58f, #cfa850);
}

body.library-dark .library-logout-btn {
    color: #f8fafc;
    background: linear-gradient(135deg, #1b5b43, #0f3528);
}

body.library-dark .library-footer-bottom {
    border-top-color: rgba(255, 255, 255, 0.08);
}

body.library-dark .overlay.active {
    background: rgba(2, 7, 5, 0.58);
}

@media (max-width: 991.98px) {
    .library-sidebar {
        transform: translateX(-100%);
    }

    .library-sidebar.active {
        transform: translateX(0);
    }

    .library-main {
        margin-left: 0;
        padding: 1rem;
    }

    .library-menu-btn {
        display: inline-flex;
    }

    .library-topbar {
        padding: 0.9rem 1rem;
    }

    .library-content {
        padding: 1rem;
        border-radius: 1.4rem;
    }
}

@media (max-width: 575.98px) {
    .library-topbar {
        flex-direction: column;
        align-items: stretch;
        gap: 0.85rem;
    }

    .library-topbar-title {
        width: 100%;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .library-topbar-title-copy {
        min-width: 0;
        flex: 1 1 auto;
        padding-right: 0;
    }

    .library-topbar-title h5 {
        font-size: 1rem;
        line-height: 1.3;
    }

    .library-topbar-title span {
        font-size: 0.82rem;
        line-height: 1.35;
    }

    .library-topbar-actions {
        width: 100%;
        justify-content: flex-start;
    }

    .library-user-chip,
    .library-login-btn,
    .library-logout-btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 991.98px) {
    .library-footer-grid {
        grid-template-columns: 1fr;
    }

    .library-footer-bottom {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
</head>

<body>
<script>
(function () {
    const enabled = localStorage.getItem('library-dark-mode') === 'true';
    document.documentElement.classList.toggle('library-dark', enabled);
    document.documentElement.classList.toggle('dark-mode', enabled);
    document.body.classList.toggle('library-dark', enabled);
    document.body.classList.toggle('dark-mode', enabled);
})();
</script>
@php($user = Auth::user())
<?php
    $topbarMeta = [
        'kicker' => 'UNAMAD',
        'icon' => 'bi-mortarboard-fill',
        'title' => 'Sistema de Biblioteca',
        'subtitle' => 'Navegación central para catálogo, reservas y consultas.',
    ];

    if (request()->routeIs('home')) {
        $topbarMeta = [
            'kicker' => 'Portada institucional',
            'icon' => 'bi-house-heart-fill',
            'title' => 'Sistema de Biblioteca',
            'subtitle' => 'Explora novedades, bibliotecas y accesos principales desde una portada clara.',
        ];
    } elseif (request()->routeIs('catalogo')) {
        $topbarMeta = [
            'kicker' => 'Consulta bibliografica',
            'icon' => 'bi-collection-fill',
            'title' => 'Catálogo institucional',
            'subtitle' => 'Busca libros, filtra resultados y revisa valoraciones antes de consultar el detalle.',
        ];
    } elseif (request()->routeIs('biblioteca.show')) {
        $topbarMeta = [
            'kicker' => 'Sede bibliotecaria',
            'icon' => 'bi-buildings-fill',
            'title' => 'Biblioteca y disponibilidad',
            'subtitle' => 'Consulta la coleccion disponible por sede y encuentra ejemplares con mayor rapidez.',
        ];
    } elseif (request()->routeIs('libro.show')) {
        $topbarMeta = [
            'kicker' => 'Ficha bibliografica',
            'icon' => 'bi-journal-bookmark-fill',
            'title' => 'Detalle del libro',
            'subtitle' => 'Revisa autores, disponibilidad, comentarios y reserva el ejemplar que necesitas.',
        ];
    } elseif (request()->routeIs('mis.reservas')) {
        $topbarMeta = [
            'kicker' => 'Seguimiento personal',
            'icon' => 'bi-bookmark-star-fill',
            'title' => 'Mis reservas',
            'subtitle' => 'Monitorea tus solicitudes activas y mantente al tanto de su estado.',
        ];
    } elseif (request()->routeIs('prestamos')) {
        $topbarMeta = [
            'kicker' => 'Control de movimientos',
            'icon' => 'bi-arrow-left-right',
            'title' => 'Prestamos y movimientos',
            'subtitle' => 'Consulta el flujo de préstamos y devoluciones desde una vista centralizada.',
        ];
    } elseif (request()->routeIs('evento')) {
        $topbarMeta = [
            'kicker' => 'Agenda cultural',
            'icon' => 'bi-stars',
            'title' => 'Eventos y actividades',
            'subtitle' => 'Accede a novedades, actividades y espacios de participacion de la biblioteca.',
        ];
    } elseif (request()->routeIs('otras.bibliotecas')) {
        $topbarMeta = [
            'kicker' => 'Consulta externa',
            'icon' => 'bi-link-45deg',
            'title' => 'Otras bibliotecas',
            'subtitle' => 'Explora enlaces oficiales de otras bibliotecas para ampliar tu consulta.',
        ];
    }
?>

<a href="#contenido-principal" class="visually-hidden-focusable position-absolute top-0 start-0 m-3 p-2 rounded bg-white text-dark">
    Saltar al contenido principal
</a>

<div class="library-shell">
    <aside class="library-sidebar" id="sidebar" aria-label="Menu principal">
        <div class="library-brand">
            <div class="library-brand-header">
                <div class="library-brand-mark">
                    <img src="{{ asset('img/logo_unamad.png') }}" alt="Logo UNAMAD" class="library-brand-logo">
                </div>
                <div class="library-brand-copy">
                    <h1>Biblioteca UNAMAD</h1>
                    <small>Universidad Nacional Amazónica de Madre de Dios</small>
                </div>
            </div>
            <p>Explora catálogos, reservas y préstamos desde una experiencia más clara y ordenada.</p>
        </div>

        <nav class="library-nav" aria-label="Navegación principal">
            <a href="{{ route('home') }}" class="library-nav-link nav-home {{ request()->routeIs('home') ? 'is-active' : '' }}">
                <span class="library-nav-icon">
                    <i class="bi bi-house-heart-fill"></i>
                </span>
                <span class="library-nav-text">
                    <strong>Inicio</strong>
                    <small>Portada principal</small>
                </span>
            </a>
            <a href="{{ route('catalogo') }}" class="library-nav-link nav-catalog {{ request()->routeIs('catalogo') ? 'is-active' : '' }}">
                <span class="library-nav-icon">
                    <i class="bi bi-collection-fill"></i>
                </span>
                <span class="library-nav-text">
                    <strong>Catálogo</strong>
                    <small>Búsqueda de libros</small>
                </span>
            </a>
            <a href="{{ route('evento') }}" class="library-nav-link nav-events {{ request()->routeIs('evento') ? 'is-active' : '' }}">
                <span class="library-nav-icon">
                    <i class="bi bi-stars"></i>
                </span>
                <span class="library-nav-text">
                    <strong>Eventos</strong>
                    <small>Novedades y agenda</small>
                </span>
            </a>
            <a href="{{ route('otras.bibliotecas') }}" class="library-nav-link nav-libraries {{ request()->routeIs('otras.bibliotecas') ? 'is-active' : '' }}">
                <span class="library-nav-icon">
                    <i class="bi bi-link-45deg"></i>
                </span>
                <span class="library-nav-text">
                    <strong>Otras bibliotecas</strong>
                    <small>Links de consulta</small>
                </span>
            </a>
            @auth
            <a href="{{ route('mis.reservas') }}" class="library-nav-link nav-reservations {{ request()->routeIs('mis.reservas') ? 'is-active' : '' }}">
                <span class="library-nav-icon">
                    <i class="bi bi-bookmark-star-fill"></i>
                </span>
                <span class="library-nav-text">
                    <strong>Mis Reservas</strong>
                    <small>Solicitudes activas</small>
                </span>
            </a>
            <a href="{{ route('prestamos') }}" class="library-nav-link nav-loans {{ request()->routeIs('prestamos') ? 'is-active' : '' }}">
                <span class="library-nav-icon">
                    <i class="bi bi-arrow-left-right"></i>
                </span>
                <span class="library-nav-text">
                    <strong>Préstamos</strong>
                    <small>Control de movimientos</small>
                </span>
            </a>
            @endauth
        </nav>

        <div class="library-sidebar-footer">
            <strong class="d-block mb-1">Ambiente de lectura</strong>
            <small>Un espacio pensado para consultar libros, revisar disponibilidad y gestionar movimientos sin perder contexto.</small>
        </div>
    </aside>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <main class="library-main" id="contenido-principal">
        <header class="library-topbar">
            <div class="library-topbar-title">
                <button type="button" class="library-menu-btn" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <div class="library-topbar-title-badge">
                    <img src="{{ asset('img/logo_unamad.png') }}" alt="Logo UNAMAD">
                </div>
                <div class="library-topbar-title-copy">
                    <small class="library-topbar-title-kicker">
                        <i class="bi {{ $topbarMeta['icon'] }}"></i>
                        {{ $topbarMeta['kicker'] }}
                    </small>
                    <h5>{{ $topbarMeta['title'] }}</h5>
                    <span>{{ $topbarMeta['subtitle'] }}</span>
                </div>
            </div>

            <div class="library-topbar-actions">
                <button type="button" class="btn library-theme-btn" aria-label="Alternar modo oscuro" onclick="toggleLibraryTheme()">
                    <i class="bi bi-moon-stars-fill" id="libraryThemeIcon"></i>
                </button>

                <div class="dropdown library-alert-dropdown">
                    <button class="btn library-alert-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Ver mensajes y avisos">
                        <i class="bi bi-bell-fill"></i>
                        @if(isset($libraryAlerts) && $libraryAlerts->isNotEmpty())
                            <span class="library-alert-badge">{{ min($libraryAlerts->count(), 9) }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end library-alert-menu">
                        <div class="library-alert-menu-header">
                            <div>
                                <strong>Actividades y noticias</strong>
                                <small class="text-muted">Avisos, mensajes y novedades de biblioteca</small>
                            </div>
                        </div>

                        @if(isset($libraryAlerts) && $libraryAlerts->isNotEmpty())
                            <div class="library-alert-list">
                                @foreach($libraryAlerts as $alert)
                                    <a href="{{ $alert->url }}" class="library-alert-item">
                                        <span class="library-alert-icon">
                                            <i class="bi {{ $alert->icono }}"></i>
                                        </span>
                                        <span class="library-alert-copy">
                                            <strong>{{ $alert->titulo }}</strong>
                                            <p>{{ $alert->contenido }}</p>
                                            @if($alert->meta)
                                                <small><i class="bi bi-calendar3"></i>{{ $alert->meta }}</small>
                                            @endif
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="pt-3 px-2 pb-1 text-muted small">
                                No hay mensajes o avisos publicados por el momento.
                            </div>
                        @endif
                    </div>
                </div>

                @auth
                <div class="dropdown">
                    <button class="btn library-user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        {{ $user->name }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-end library-alert-menu">
                        <div class="library-alert-menu-header">
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                        </div>
                        <a href="{{ route('perfil.edit', ['layout' => 'library']) }}" class="library-alert-item">
                            <span class="library-alert-icon">
                                <i class="bi bi-person-gear"></i>
                            </span>
                            <span class="library-alert-copy">
                                <strong>Mi perfil</strong>
                                <p>Ver y actualizar mis datos personales.</p>
                            </span>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="d-block">
                            @csrf
                            <button type="submit" class="library-alert-item library-alert-item--button">
                                <span class="library-alert-icon">
                                    <i class="bi bi-box-arrow-right"></i>
                                </span>
                                <span class="library-alert-copy">
                                    <strong>Cerrar sesión</strong>
                                    <p>Salir del sistema de biblioteca.</p>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <a href="{{ route('login', request()->routeIs('libro.show') ? ['redirect' => url()->current()] : []) }}" class="btn library-login-btn">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    Iniciar sesión
                </a>
                @endauth
            </div>
        </header>

        <section class="library-content">
            @yield('content')
        </section>

        <footer class="library-footer" aria-label="Pie de página institucional">
            <div class="library-footer-grid">
                <div>
                    <h6>Biblioteca UNAMAD</h6>
                    <p class="mb-2">
                        Plataforma de consulta para explorar catálogos, revisar disponibilidad bibliográfica
                        y gestionar reservas y préstamos en la Universidad Nacional Amazónica de Madre de Dios.
                    </p>
                    <small>Madre de Dios, Perú</small>
                </div>

                <div>
                    <h6>Enlaces rápidos</h6>
                    <div class="library-footer-list">
                        <a href="{{ route('home') }}">Inicio</a>
                        <a href="{{ route('catalogo') }}">Catálogo</a>
                        <a href="{{ route('evento') }}">Eventos</a>
                        <a href="{{ route('otras.bibliotecas') }}">Otras bibliotecas</a>
                        @auth
                        <a href="{{ route('mis.reservas') }}">Mis reservas</a>
                        @endauth
                    </div>
                </div>

                <div>
                    <h6>Contacto</h6>
                    <div class="library-footer-list">
                        <span><i class="bi bi-geo-alt me-2"></i>Universidad Nacional Amazónica de Madre de Dios</span>
                        <span><i class="bi bi-envelope me-2"></i>biblioteca@unamad.edu.pe</span>
                        <span><i class="bi bi-clock me-2"></i>Consulta digital disponible todo el día</span>
                    </div>
                </div>
            </div>

            <div class="library-footer-bottom">
                <span>&copy; {{ now()->year }} Biblioteca UNAMAD. Todos los derechos reservados.</span>
                <span>Diseñado para una experiencia de consulta clara, accesible e institucional.</span>
            </div>
        </footer>
    </main>
</div>

<div id="mensaje_container"></div>

@yield('modal')

<script>
function toggleLibraryTheme() {
    const root = document.documentElement;
    const body = document.body;
    const nextValue = !body.classList.contains('library-dark');

    root.classList.toggle('library-dark', nextValue);
    root.classList.toggle('dark-mode', nextValue);
    body.classList.toggle('library-dark', nextValue);
    body.classList.toggle('dark-mode', nextValue);
    localStorage.setItem('library-dark-mode', nextValue ? 'true' : 'false');

    const icon = document.getElementById('libraryThemeIcon');
    if (icon) {
        icon.className = nextValue ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const enabled = document.body.classList.contains('library-dark');
    const icon = document.getElementById('libraryThemeIcon');
    if (icon) {
        icon.className = enabled ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    }
});

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}
</script>
<script src="{{ asset('js/jquery-3.6.3.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
<script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
<script src="{{ asset('js/admin.js') }}"></script>
@yield('js')

</body>
</html>
