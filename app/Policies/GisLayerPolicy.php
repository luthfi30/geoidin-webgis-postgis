<?php

namespace App\Policies;

use App\Models\User;
use App\Models\GisLayer;
use Illuminate\Auth\Access\HandlesAuthorization;

class GisLayerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_gis::layer');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GisLayer $gisLayer): bool
    {
        return $user->can('view_gis::layer');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_gis::layer');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GisLayer $gisLayer): bool
    {
        return $user->can('update_gis::layer');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GisLayer $gisLayer): bool
    {
        return $user->can('delete_gis::layer');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_gis::layer');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, GisLayer $gisLayer): bool
    {
        return $user->can('force_delete_gis::layer');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_gis::layer');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, GisLayer $gisLayer): bool
    {
        return $user->can('restore_gis::layer');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_gis::layer');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, GisLayer $gisLayer): bool
    {
        return $user->can('replicate_gis::layer');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_gis::layer');
    }
}
