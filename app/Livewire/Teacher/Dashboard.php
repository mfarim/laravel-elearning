<?php

namespace App\Livewire\Teacher;

use App\Models\Assignment;
use App\Models\Examination;
use App\Models\LearningMaterial;
use Livewire\Component;

class Dashboard extends Component
{
  public function render()
  {
    $teacher = auth()->user()->teacher;
    $teacherId = $teacher?->id;

    return view('livewire.teacher.dashboard', [
      'totalMaterials' => $teacherId ? LearningMaterial::where('teacher_id', $teacherId)->count() : 0,
      'totalAssignments' => $teacherId ? Assignment::where('teacher_id', $teacherId)->count() : 0,
      'totalExams' => $teacherId ? Examination::where('teacher_id', $teacherId)->count() : 0,
      'upcomingExams' => $teacherId ? Examination::where('teacher_id', $teacherId)->where('start_at', '>', now())->orderBy('start_at')->take(5)->get() : collect(),
      'pendingAssignments' => $teacherId ? Assignment::where('teacher_id', $teacherId)->where('status', 'published')->where('due_date', '>', now())->withCount('submissions')->take(5)->get() : collect(),
    ])->layout('components.layouts.teacher', ['title' => 'Dashboard Guru']);
  }
}
