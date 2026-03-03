<?php

namespace App\Livewire\Student;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Assignments extends Component
{
  use WithPagination, WithFileUploads;

  public bool $showSubmit = false;
  public ?int $submittingId = null;
  public string $notes = '';
  public $submission_file;

  protected function rules(): array
  {
    return ['submission_file' => 'required|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip,rar', 'notes' => 'nullable|string'];
  }

  public function openSubmit(int $assignmentId): void
  {
    $this->submittingId = $assignmentId;
    $this->notes = '';
    $this->submission_file = null;
    $this->showSubmit = true;
  }

  public function submit(): void
  {
    $this->validate();
    $studentId = auth()->user()->student->id;
    $filePath = $this->submission_file->store('submissions', 'public');
    AssignmentSubmission::create([
      'assignment_id' => $this->submittingId,
      'student_id' => $studentId,
      'file_path' => $filePath,
      'notes' => $this->notes ?: null,
      'status' => 'submitted',
      'submitted_at' => now(),
    ]);
    $this->showSubmit = false;
    session()->flash('success', 'Tugas berhasil dikumpulkan!');
  }

  public function render()
  {
    $student = auth()->user()->student;
    $classroomId = $student?->classroom_id;
    $studentId = $student?->id;

    return view('livewire.student.assignments', [
      'assignments' => Assignment::with('subject')
        ->where('classroom_id', $classroomId)
        ->where('status', 'published')
        ->latest()->paginate(10),
      'submittedIds' => $studentId ? AssignmentSubmission::where('student_id', $studentId)->pluck('assignment_id')->toArray() : [],
    ])->layout('components.layouts.student', ['title' => 'Tugas']);
  }
}
