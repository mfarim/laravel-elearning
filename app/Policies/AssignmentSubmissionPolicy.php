<?php

namespace App\Policies;

use App\Models\AssignmentSubmission;
use App\Models\User;

class AssignmentSubmissionPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AssignmentSubmission $submission): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $submission->assignment?->teacher_id === (int) $user->teacher?->id;
        }

        if ($user->hasRole('siswa')) {
            return (int) $submission->student_id === (int) $user->student?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can grade the submission.
     */
    public function grade(User $user, AssignmentSubmission $submission): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('guru')) {
            return (int) $submission->assignment?->teacher_id === (int) $user->teacher?->id;
        }

        return false;
    }
}
