<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name') }}</title>
        @include('partials.favicon')
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-brand-50/40 text-slate-800">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
            @include('layouts.sidebar')

            <div class="flex-1 min-w-0">
                <header class="bg-white border-b border-brand-100">
                    <div class="flex items-center justify-between px-4 py-3 lg:px-8">
                        <div class="flex items-center gap-3">
                            <button type="button" class="lg:hidden text-slate-600" @click="sidebarOpen = true" aria-label="Ouvrir le menu">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>
                            <div>
                                @isset($header)
                                    {{ $header }}
                                @else
                                    <h1 class="text-lg font-semibold text-slate-800">{{ $title ?? 'Tableau de bord' }}</h1>
                                @endisset
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-xs font-medium">{{ auth()->user()->role->label() }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="text-slate-500 hover:text-slate-800">Déconnexion</button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="p-4 lg:p-8">
                    @if (session('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-4 rounded-md bg-accent-50 border border-accent-200 text-accent-700 px-4 py-3 text-sm">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                            {{ session('error') }}
                        </div>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
