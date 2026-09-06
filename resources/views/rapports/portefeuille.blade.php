@if (!empty($impression))
    <x-print-layout title="Situation du portefeuille">
        @include('rapports._portefeuille-body')
    </x-print-layout>
@else
    <x-app-layout>
        <x-slot name="header">
            <h1 class="text-lg font-semibold text-slate-800">Situation du portefeuille</h1>
        </x-slot>
        <div class="space-y-4">
            <x-export-buttons route="rapports.portefeuille" />
            <div class="bg-white border border-slate-200 rounded-lg p-6">
                @include('rapports._portefeuille-body')
            </div>
        </div>
    </x-app-layout>
@endif
