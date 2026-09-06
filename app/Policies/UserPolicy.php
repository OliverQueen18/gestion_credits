<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageAdministration();
    }

    public function view(User $user, User $model): bool
    {
        return $user->canManageAdministration() || $user->is($model);
    }

    public function create(User $user): bool
    {
        return $user->canManageAdministration();
    }

    public function update(User $user, User $model): bool
    {
        return $user->canManageAdministration();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->canManageAdministration() && ! $user->is($model);
    }
}
