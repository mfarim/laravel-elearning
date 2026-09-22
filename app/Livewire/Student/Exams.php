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
      ? Examination::with(['subject', 'attempts' => fn($q) => $q->where('student_id', $studentId)->latest()])
        ->where('classroom_id', $classroomId)
        ->where('status', 'published')
        ->where('end_at', '>', now())
        ->where(function ($query) use ($studentId) {
          $query->where('allow_retry', true)
            ->orWhereDoesntHave('attempts', function ($q) use ($studentId) {
              $q->where('student_id', $studentId)
                ->whereIn('status', ['completed', 'needs_grading', 'force_finished']);
            })
            ->orWhereHas('attempts', function ($q) use ($studentId) {
              $q->where('student_id', $studentId)
                ->where('status', 'in_progress');
            });
        })
        ->orderBy('start_at')
        ->get()
      : collect();

    $pastAttempts = $studentId
      ? ExamAttempt::with('examination.subject')
        ->where('student_id', $studentId)
        ->whereIn('status', ['completed', 'needs_grading', 'force_finished'])
        ->latest()->take(10)->get()
      : collect();

    return view('livewire.student.exams', compact('upcomingExams', 'pastAttempts', 'studentId'))
      ->layout('components.layouts.student', ['title' => 'Ujian']);
  }
}
