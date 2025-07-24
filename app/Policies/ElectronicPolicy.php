<?php

namespace App\Policies;

use App\Models\Electronic;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ElectronicPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_electronic');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Electronic $electronic): bool
    {
        return $user->can('view_electronic');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_electronic');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Electronic $electronic): bool
    {
        return $user->can('update_electronic');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Electronic $electronic): bool
    {
        return $user->can('delete_electronic');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_electronic');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Electronic $electronic): bool
    {
        return $user->can('force_delete_electronic');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_electronic');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Electronic $electronic): bool
    {
        return $user->can('restore_electronic');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_electronic');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Electronic $electronic): bool
    {
        return $user->can('replicate_electronic');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_electronic');
    }
}
