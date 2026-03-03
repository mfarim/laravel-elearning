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

    // Guru yang mengajar mata pelajaran terkait
    if ($user->hasRole('guru')) {
        return true;
    }

    // Siswa yang berada di kelas assignment
    if ($user->hasRole('siswa') && $assignment->classroom) {
        return $assignment->classroom->students()->where('user_id', $user->id)->exists();
    }

    return false;
});
