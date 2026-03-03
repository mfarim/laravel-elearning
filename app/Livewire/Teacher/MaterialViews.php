<?php

namespace App\Livewire\Teacher;

use App\Models\LearningMaterial;
use App\Models\MaterialView;
use App\Models\Student;
use Livewire\Component;

class MaterialViews extends Component
{
    public int $materialId;

    public function mount(int $materialId): void
    {
        $this->materialId = $materialId;
    }

    public function render()
    {
        $material = LearningMaterial::with(['subject', 'classroom'])->findOrFail($this->materialId);
        $classroomId = $material->classroom_id;

        $students = Student::with('user')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select('students.*')
            ->when($classroomId, fn ($q) => $q->where('students.classroom_id', $classroomId))
            ->orderBy('users.name')
            ->get();

        $viewedStudentIds = MaterialView::where('learning_material_id', $this->materialId)
            ->pluck('viewed_at', 'student_id')
            ->toArray();

        return view('livewire.teacher.material-views', [
            'material' => $material,
            'students' => $students,
            'viewedStudentIds' => $viewedStudentIds,
        ])->layout('components.layouts.teacher', ['title' => 'Dilihat — ' . $material->title]);
    }
}
