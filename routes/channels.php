<?php

use App\Models\Assignment;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('assignment.{assignmentId}.discussion', function ($user, $assignmentId) {
    $assignment = Assignment::with('classroom')->find($assignmentId);

    if (!$assignment) {
        return false;
    }

    if ($user->hasRole('admin')) {
        return true;
    }

    // Guru yang membuat assignment
    if ($user->hasRole('guru')) {
        return (int) $user->teacher?->id === (int) $assignment->teacher_id;
    }

    // Siswa yang berada di kelas assignment
    if ($user->hasRole('siswa') && $assignment->classroom) {
        return $assignment->classroom->students()->where('user_id', $user->id)->exists();
    }

    return false;
});
