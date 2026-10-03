<?php

namespace App\Policies;

use App\Models\ExamAttempt;
use App\Models\User;

class ExamAttemptPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExamAttempt $attempt): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $attempt->examination?->teacher_id === (int) $user->teacher?->id;
        }

        if ($user->hasRole('siswa')) {
            return (int) $attempt->student_id === (int) $user->student?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can grade the attempt.
     */
    public function grade(User $user, ExamAttempt $attempt): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $attempt->examination?->teacher_id === (int) $user->teacher?->id;
        }

        return false;
    }
}
