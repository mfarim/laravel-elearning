<?php

namespace App\Livewire\Teacher;

use App\Models\Examination;
use App\Models\Student;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ExamPrint extends Component
{
  #[Locked]
  public Examination $examination;
  public string $printType = 'kartu';

  public function mount(Examination $examination): void
  {
    $this->authorize('print', $examination);
    $this->examination = $examination;
  }

  public function render()
  {
    $students = Student::with('user')
      ->where('classroom_id', $this->examination->classroom_id)
      ->orderBy('nis')
      ->get();

    return view('livewire.teacher.exam-print', [
      'students' => $students,
      'exam' => $this->examination,
    ])->layout('components.layouts.teacher', ['title' => 'Print: ' . $this->examination->title]);
  }
}
