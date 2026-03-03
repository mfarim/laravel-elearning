<?php

namespace App\Livewire\Student;

use App\Models\LearningMaterial;
use Livewire\Component;
use Livewire\WithPagination;

class Materials extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $classroomId = auth()->user()->student?->classroom_id;

        return view('livewire.student.materials', [
            'materials' => LearningMaterial::with('subject')
                ->where('is_published', true)
                ->where(fn ($q) => $q->where('classroom_id', $classroomId)->orWhereNull('classroom_id'))
                ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->latest()->paginate(10),
        ])->layout('components.layouts.student', ['title' => 'Materi']);
    }
}
