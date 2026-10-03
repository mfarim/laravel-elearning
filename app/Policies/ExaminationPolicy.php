<?php

namespace App\Policies;

use App\Models\ExamAttempt;
use App\Models\Examination;
use App\Models\User;

class ExaminationPolicy
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
    public function view(User $user, Examination $examination): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $examination->teacher_id === (int) $user->teacher?->id;
        }

        if ($user->hasRole('siswa')) {
            return $examination->status === 'published'
                && (int) $examination->classroom_id === (int) $user->student?->classroom_id;
        }

        return false;
    }

    /**
     * Determine whether the user can monitor the examination.
     */
    public function monitor(User $user, Examination $examination): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $examination->teacher_id === (int) $user->teacher?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can print the examination.
     */
    public function print(User $user, Examination $examination): bool
    {
        return $this->monitor($user, $examination);
    }

    /**
     * Determine whether the user can grade the examination attempt.
     */
    public function grade(User $user, Examination $examination, ?ExamAttempt $attempt = null): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            $isOwner = (int) $examination->teacher_id === (int) $user->teacher?->id;
            if (!$isOwner) {
                return false;
            }

            if ($attempt !== null && (int) $attempt->examination_id !== (int) $examination->id) {
                return false;
            }

            return true;
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
    public function update(User $user, Examination $examination): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $examination->teacher_id === (int) $user->teacher?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Examination $examination): bool
    {
        return $this->update($user, $examination);
    }
}
