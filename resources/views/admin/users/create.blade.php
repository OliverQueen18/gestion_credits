<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Nouvel utilisateur</h1>
    </x-slot>

    <form method="POST" action="{{ route('users.store') }}" class="max-w-xl bg-white border border-slate-200 rounded-lg p-6 space-y-4">
        @csrf
        @include('admin.users._form', ['user' => null])
        <x-primary-button>Créer</x-primary-button>
    </form>
</x-app-layout>
