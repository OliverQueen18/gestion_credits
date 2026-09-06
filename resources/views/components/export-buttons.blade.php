@props(['route', 'params' => []])

<div class="flex flex-wrap gap-2">
    <a href="{{ route($route, array_merge($params, request()->except('export', 'print') + ['export' => 'excel'])) }}" class="inline-flex items-center px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold uppercase rounded-md">Excel</a>
    <a href="{{ route($route, array_merge($params, request()->except('export', 'print') + ['export' => 'pdf'])) }}" class="inline-flex items-center px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold uppercase rounded-md">PDF</a>
    <a href="{{ route($route, array_merge($params, request()->except('export', 'print') + ['print' => 1])) }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold uppercase rounded-md">Imprimer</a>
</div>
