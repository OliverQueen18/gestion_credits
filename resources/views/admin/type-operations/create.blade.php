<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Nouveau type d’opération</h1>
    </x-slot>
    <form method="POST" action="{{ route('type-operations.store') }}" class="max-w-xl bg-white border border-slate-200 rounded-lg p-6 space-y-4">
        @csrf
        @include('admin.type-operations._form', ['type' => null])
        <x-primary-button>Enregistrer</x-primary-button>
    </form>
</x-app-layout>
