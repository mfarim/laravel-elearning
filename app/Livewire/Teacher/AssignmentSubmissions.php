<?php

namespace App\Livewire\Teacher;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use Livewire\Component;

class AssignmentSubmissions extends Component
{
  public int $assignmentId;
  public ?int $gradingId = null;
  public int $score = 0;
  public string $feedback = '';

  public function mount(int $assignmentId): void
  {
    $this->assignmentId = $assignmentId;
  }

  public function startGrade(int $submissionId): void
  {
    $sub = AssignmentSubmission::findOrFail($submissionId);
    $this->gradingId = $submissionId;
    $this->score = $sub->score ?? 0;
    $this->feedback = $sub->feedback ?? '';
  }

  public function saveGrade(): void
  {
    $assignment = Assignment::findOrFail($this->assignmentId);
    $this->validate([
      'score' => "required|integer|min:0|max:{$assignment->max_score}",
      'feedback' => 'nullable|string|max:2000',
    ]);

    $sub = AssignmentSubmission::findOrFail($this->gradingId);
    $sub->update([
      'score' => $this->score,
      'feedback' => $this->feedback ?: null,
      'status' => 'graded',
      'graded_at' => now(),
    ]);

    $this->gradingId = null;
    session()->flash('success', 'Nilai berhasil disimpan.');
  }

  public function render()
  {
    $assignment = Assignment::with(['subject', 'classroom.students.user'])->findOrFail($this->assignmentId);
    $submissions = AssignmentSubmission::with('student.user')
      ->where('assignment_id', $this->assignmentId)
      ->get()
      ->keyBy('student_id');

    $students = $assignment->classroom->students ?? collect();

    return view('livewire.teacher.assignment-submissions', [
      'assignment' => $assignment,
      'submissions' => $submissions,
      'students' => $students,
    ])->layout('components.layouts.teacher', ['title' => 'Submissions — ' . $assignment->title]);
  }
}
