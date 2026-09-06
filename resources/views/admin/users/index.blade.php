<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Utilisateurs</h1>
    </x-slot>

    <div class="space-y-4">
        <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-800 text-white text-xs font-semibold uppercase rounded-md">Nouvel utilisateur</a>
        <div class="bg-white border border-slate-200 rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Nom</th>
                        <th class="px-3 py-2">Identifiant</th>
                        <th class="px-3 py-2">E-mail</th>
                        <th class="px-3 py-2">Téléphone</th>
                        <th class="px-3 py-2">Rôle</th>
                        <th class="px-3 py-2">Statut</th>
                        <th class="px-3 py-2">Dernière connexion</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-3 py-2">{{ $user->name }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $user->username }}</td>
                            <td class="px-3 py-2">{{ $user->email }}</td>
                            <td class="px-3 py-2">{{ $user->telephone ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $user->role->label() }}</td>
                            <td class="px-3 py-2">
                                <span class="px-2 py-0.5 rounded text-xs {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-3 py-2">{{ $user->last_login_at?->format('d/m/Y H:i') ?: '—' }}</td>
                            <td class="px-3 py-2"><a href="{{ route('users.edit', $user) }}" class="text-brand-700 hover:underline">Modifier</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>
</x-app-layout>
