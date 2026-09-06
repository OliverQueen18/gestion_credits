@if (!empty($impression))
    <x-print-layout title="Clients débiteurs">
        @include('rapports._debiteurs-body')
    </x-print-layout>
@else
    <x-app-layout>
        <x-slot name="header">
            <h1 class="text-lg font-semibold text-slate-800">Clients débiteurs</h1>
        </x-slot>
        <div class="space-y-4">
            <x-export-buttons route="rapports.debiteurs" />
            <div class="bg-white border border-slate-200 rounded-lg overflow-x-auto">
                @include('rapports._debiteurs-body')
            </div>
        </div>
    </x-app-layout>
@endif
