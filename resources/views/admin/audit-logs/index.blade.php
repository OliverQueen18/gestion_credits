<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Journal d’audit</h1>
    </x-slot>

    <div class="space-y-4">
        <form method="GET" class="bg-white border border-slate-200 rounded-lg p-4 grid md:grid-cols-4 gap-3 text-sm">
            <select name="action" class="rounded-md border-slate-300">
                <option value="">Toutes les actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
            <select name="user_id" class="rounded-md border-slate-300">
                <option value="">Tous les utilisateurs</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <input type="date" name="du" value="{{ request('du') }}" class="rounded-md border-slate-300">
            <input type="date" name="au" value="{{ request('au') }}" class="rounded-md border-slate-300">
            <div class="md:col-span-4">
                <x-primary-button>Filtrer</x-primary-button>
            </div>
        </form>

        <div class="bg-white border border-slate-200 rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2">Utilisateur</th>
                        <th class="px-3 py-2">Action</th>
                        <th class="px-3 py-2">Modèle</th>
                        <th class="px-3 py-2">Détail</th>
                        <th class="px-3 py-2">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($logs as $log)
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2">{{ $log->user?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $log->action }}</td>
                            <td class="px-3 py-2">{{ $log->model ? class_basename($log->model).' '.$log->model_id : '—' }}</td>
                            <td class="px-3 py-2 text-xs max-w-md">
                                @if ($log->old_values || $log->new_values)
                                    <details>
                                        <summary class="cursor-pointer text-brand-700">Valeurs</summary>
                                        <pre class="mt-1 whitespace-pre-wrap">{{ json_encode(['avant' => $log->old_values, 'apres' => $log->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $log->ip }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $logs->links() }}</div>
    </div>
</x-app-layout>
