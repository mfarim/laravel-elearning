<?php

namespace App\Livewire\Student;

use App\Models\LearningMaterial;
use App\Models\MaterialView;
use Livewire\Component;

class MaterialDetail extends Component
{
  public int $materialId;

  public function mount(int $materialId): void
  {
    $this->materialId = $materialId;

    // Record view
    $studentId = auth()->user()->student?->id;
    if ($studentId) {
      MaterialView::firstOrCreate(
        ['learning_material_id' => $materialId, 'student_id' => $studentId],
        ['viewed_at' => now()]
      );
    }
  }

  public function getYoutubeId(?string $url): ?string
  {
    if (!$url)
      return null;
    preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
    return $m[1] ?? null;
  }

  public function render()
  {
    $material = LearningMaterial::with(['subject', 'teacher.user'])->findOrFail($this->materialId);

    return view('livewire.student.material-detail', [
      'material' => $material,
      'youtubeId' => $this->getYoutubeId($material->file_url),
    ])->layout('components.layouts.student', ['title' => $material->title]);
  }
}
