@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">

    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-6xl font-bold text-gray-800 mb-4">
            ¡Bienvenido a <span class="text-emerald-600">Biblioteca UNAMAD</span>!
        </h1>
        <p class="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto">
            Gestiona libros, usuarios y préstamos de manera eficiente. Explora nuestro catálogo, registra lectores y administra tu biblioteca con facilidad.
        </p>
        <div class="mt-6">
            <a href="#" class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors shadow-lg">
                Explorar Catálogo
            </a>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-6 rounded-xl shadow-lg text-center">
            <h3 class="text-2xl font-bold text-emerald-600">{{ $totalLibros ?? 150 }}</h3>
            <p class="text-gray-600">Libros en Catálogo</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg text-center">
            <h3 class="text-2xl font-bold text-cyan-600">{{ $totalUsuarios ?? 50 }}</h3>
            <p class="text-gray-600">Usuarios Registrados</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg text-center">
            <h3 class="text-2xl font-bold text-blue-600">{{ $prestamosActivos ?? 20 }}</h3>
            <p class="text-gray-600">Préstamos Activos</p>
        </div>
    </div>

    <!-- Módulos Principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="#" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow text-center">
            <div class="text-4xl mb-4">📘</div>
            <h3 class="text-xl font-semibold text-gray-800">Catálogo</h3>
            <p class="text-gray-600">Gestiona libros y material bibliográfico.</p>
        </a>
        <a href="#" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow text-center">
            <div class="text-4xl mb-4">🔄</div>
            <h3 class="text-xl font-semibold text-gray-800">Préstamos</h3>
            <p class="text-gray-600">Registra y administra préstamos.</p>
        </a>
        <a href="#" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow text-center">
            <div class="text-4xl mb-4">👥</div>
            <h3 class="text-xl font-semibold text-gray-800">Lectores</h3>
            <p class="text-gray-600">Gestiona usuarios y lectores.</p>
        </a>
        <a href="#" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow text-center">
            <div class="text-4xl mb-4">📊</div>
            <h3 class="text-xl font-semibold text-gray-800">Reportes</h3>
            <p class="text-gray-600">Visualiza estadísticas y reportes.</p>
        </a>
    </div>

    <!-- Footer -->
    <footer class="mt-12 text-center text-gray-500">
        <p>&copy; 2024 Biblioteca UNAMAD. Desarrollado con Laravel.</p>
    </footer>
</div>
@endsection