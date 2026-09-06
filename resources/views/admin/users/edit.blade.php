<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Modifier {{ $user->name }}</h1>
    </x-slot>

    <form method="POST" action="{{ route('users.update', $user) }}" class="max-w-xl bg-white border border-slate-200 rounded-lg p-6 space-y-4">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['user' => $user])
        <x-primary-button>Enregistrer</x-primary-button>
    </form>
</x-app-layout>
