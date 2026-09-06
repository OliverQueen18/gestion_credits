<?php

namespace App\Policies;

use App\Models\Operation;
use App\Models\User;

class OperationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Operation $operation): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->canPerformFinancialOperations();
    }

    public function update(User $user, Operation $operation): bool
    {
        return false;
    }

    public function delete(User $user, Operation $operation): bool
    {
        return false;
    }

    public function correct(User $user, Operation $operation): bool
    {
        return $user->canPerformFinancialOperations() && ! $operation->est_annulee;
    }
}
