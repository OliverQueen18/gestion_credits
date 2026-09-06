<div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-brand-950/40 lg:hidden" @click="sidebarOpen = false"></div>

<aside
    class="fixed inset-y-0 left-0 z-40 w-64 bg-brand-900 text-slate-100 transform transition-transform lg:static lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="px-4 py-3 border-b border-brand-800">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/favicon-gestion-credit.png') }}" alt="" class="h-10 w-10 rounded-lg shadow-sm shrink-0">
            <span class="leading-tight">
                <span class="block font-semibold tracking-wide">Gestion de Crédit</span>
                <span class="block text-[10px] uppercase tracking-wider text-brand-200">Vos clients, notre priorité</span>
            </span>
        </a>
    </div>

    <nav class="p-3 space-y-4 text-sm overflow-y-auto h-[calc(100vh-4.5rem)]">
        <div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">
                Tableau de bord
            </a>
        </div>

        <div>
            <p class="px-3 mb-1 text-[11px] uppercase tracking-wider text-brand-300">Portefeuille</p>
            <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Clients</a>
            <a href="{{ route('operations.index') }}" class="{{ request()->routeIs('operations.*') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Opérations</a>
            <a href="{{ route('credits.index') }}" class="{{ request()->routeIs('credits.*') ? 'bg-brand-700 text-white border-l-4 border-gold-400' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Crédits</a>
            <a href="{{ route('remboursements.index') }}" class="{{ request()->routeIs('remboursements.*') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Remboursements</a>
        </div>

        <div>
            <p class="px-3 mb-1 text-[11px] uppercase tracking-wider text-brand-300">Rapports</p>
            <a href="{{ route('rapports.portefeuille') }}" class="{{ request()->routeIs('rapports.portefeuille') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Situation portefeuille</a>
            <a href="{{ route('rapports.debiteurs') }}" class="{{ request()->routeIs('rapports.debiteurs') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Situation client</a>
            <a href="{{ route('rapports.historique') }}" class="{{ request()->routeIs('rapports.historique') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Historique</a>
            <a href="{{ route('rapports.mensuel') }}" class="{{ request()->routeIs('rapports.mensuel') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Rapports mensuels</a>
        </div>

        @can('manage-administration')
            <div>
                <p class="px-3 mb-1 text-[11px] uppercase tracking-wider text-brand-300">Administration</p>
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Utilisateurs</a>
                <a href="{{ route('type-operations.index') }}" class="{{ request()->routeIs('type-operations.*') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Types d’opérations</a>
                <a href="{{ route('audit-logs.index') }}" class="{{ request()->routeIs('audit-logs.*') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Journal d’audit</a>
                <a href="{{ route('settings.edit') }}" class="{{ request()->routeIs('settings.*') ? 'bg-brand-700 text-white border-l-4 border-accent-500' : 'text-brand-100 hover:bg-brand-800/80 border-l-4 border-transparent' }} flex px-3 py-2 rounded-md">Paramètres</a>
            </div>
        @endcan
    </nav>
</aside>
