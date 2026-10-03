<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
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
    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $assignment->teacher_id === (int) $user->teacher?->id;
        }

        if ($user->hasRole('siswa')) {
            return $assignment->status === 'published'
                && (int) $assignment->classroom_id === (int) $user->student?->classroom_id;
        }

        return false;
    }

    /**
     * Determine whether the user can view submissions of the assignment.
     */
    public function viewSubmissions(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $assignment->teacher_id === (int) $user->teacher?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can grade the assignment submissions.
     */
    public function grade(User $user, Assignment $assignment): bool
    {
        return $this->viewSubmissions($user, $assignment);
    }

    /**
     * Determine whether the user can participate in the discussion of the assignment.
     */
    public function discuss(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $assignment->teacher_id === (int) $user->teacher?->id;
        }

        if ($user->hasRole('siswa')) {
            return (int) $assignment->classroom_id === (int) $user->student?->classroom_id;
        }

        return false;
    }

    /**
     * Determine whether a student can submit work to the assignment.
     */
    public function submit(User $user, Assignment $assignment): bool
    {
        if (!$user->hasRole('siswa')) {
            return false;
        }

        $student = $user->student;
        if (!$student || (int) $assignment->classroom_id !== (int) $student->classroom_id) {
            return false;
        }

        if ($assignment->status !== 'published') {
            return false;
        }

        if (!$assignment->allow_late_submission && now()->gt($assignment->due_date)) {
            return false;
        }

        return true;
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
    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $assignment->teacher_id === (int) $user->teacher?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }
}
