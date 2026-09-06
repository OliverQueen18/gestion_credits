<?php

namespace App\Policies;

use App\Models\TypeOperation;
use App\Models\User;

class TypeOperationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, TypeOperation $typeOperation): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->canManageAdministration();
    }

    public function update(User $user, TypeOperation $typeOperation): bool
    {
        return $user->canManageAdministration();
    }

    public function delete(User $user, TypeOperation $typeOperation): bool
    {
        return $user->canManageAdministration();
    }
}
