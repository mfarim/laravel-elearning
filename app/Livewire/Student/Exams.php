<?php

namespace App\Livewire\Student;

use App\Models\ExamAttempt;
use App\Models\Examination;
use Livewire\Component;

class Exams extends Component
{
  public function render()
  {
    $student = auth()->user()->student;
    $classroomId = $student?->classroom_id;
    $studentId = $student?->id;

    $upcomingExams = $classroomId
      ? Examination::with('subject')
        ->where('classroom_id', $classroomId)
        ->where('status', 'published')
        ->where('end_at', '>', now())
        ->orderBy('start_at')
        ->get()
      : collect();

    $pastAttempts = $studentId
      ? ExamAttempt::with('examination.subject')
        ->where('student_id', $studentId)
        ->where('status', 'completed')
        ->latest()->take(10)->get()
      : collect();

    return view('livewire.student.exams', compact('upcomingExams', 'pastAttempts', 'studentId'))
      ->layout('components.layouts.student', ['title' => 'Ujian']);
  }
}
