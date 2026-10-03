<?php

namespace App\Policies;

use App\Models\LearningMaterial;
use App\Models\User;

class LearningMaterialPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('guru') || $user->hasRole('siswa');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LearningMaterial $material): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $material->teacher_id === (int) $user->teacher?->id;
        }

        if ($user->hasRole('siswa')) {
            if (!$material->is_published) {
                return false;
            }

            return $material->classroom_id === null
                || (int) $material->classroom_id === (int) $user->student?->classroom_id;
        }

        return false;
    }

    /**
     * Determine whether the user can view statistics/views of the material.
     */
    public function viewStats(User $user, LearningMaterial $material): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $material->teacher_id === (int) $user->teacher?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('guru');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LearningMaterial $material): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $material->teacher_id === (int) $user->teacher?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LearningMaterial $material): bool
    {
        return $this->update($user, $material);
    }
}
