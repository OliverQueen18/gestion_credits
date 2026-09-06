<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'users' => User::query()->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            ...$request->safe()->except(['password', 'password_confirmation']),
            'password' => $request->string('password'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->audit->log('creation_utilisateur', $user, null, $user->only(['name', 'username', 'email', 'telephone', 'role']));

        return redirect()->route('users.index')->with('success', 'Utilisateur créé.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $avant = $user->only(['name', 'username', 'email', 'telephone', 'role', 'is_active']);
        $data = $request->safe()->except(['password', 'password_confirmation']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->filled('password')) {
            $data['password'] = $request->string('password');
        }

        $user->update($data);
        $this->audit->log('modification_utilisateur', $user, $avant, $user->only(['name', 'username', 'email', 'telephone', 'role', 'is_active']));

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }
}
