<?php

namespace App\Livewire\Teacher;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Subject;
use Livewire\Component;
use Livewire\WithPagination;

class AssignmentIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $title = '';
    public string $description = '';
    public string $instructions = '';
    public string $subject_id = '';
    public string $classroom_id = '';
    public int $max_score = 100;
    public string $due_date = '';
    public bool $allow_late_submission = false;
    public string $status = 'draft';

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'max_score' => 'required|integer|min:1',
            'due_date' => 'required|date',
            'status' => 'required|in:draft,published,closed',
        ];
    }

    public function create(): void
    {
        $this->reset(['title','description','instructions','subject_id','classroom_id','editingId']);
        $this->max_score = 100; $this->status = 'draft'; $this->allow_late_submission = false;
        $this->due_date = now()->addWeek()->format('Y-m-d\TH:i');
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $a = Assignment::where('teacher_id', auth()->user()->teacher->id)->findOrFail($id);
        $this->editingId = $id;
        $this->title = $a->title; $this->description = $a->description ?? '';
        $this->instructions = $a->instructions ?? '';
        $this->subject_id = (string)$a->subject_id; $this->classroom_id = (string)$a->classroom_id;
        $this->max_score = $a->max_score; $this->due_date = $a->due_date->format('Y-m-d\TH:i');
        $this->allow_late_submission = $a->allow_late_submission; $this->status = $a->status;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'title' => $this->title, 'description' => $this->description ?: null,
            'instructions' => $this->instructions ?: null, 'subject_id' => $this->subject_id,
            'classroom_id' => $this->classroom_id, 'teacher_id' => auth()->user()->teacher->id,
            'max_score' => $this->max_score, 'due_date' => $this->due_date,
            'allow_late_submission' => $this->allow_late_submission, 'status' => $this->status,
        ];
        if ($this->editingId) {
            Assignment::where('teacher_id', auth()->user()->teacher->id)->findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Tugas berhasil diperbarui.');
        } else {
            Assignment::create($data);
            session()->flash('success', 'Tugas berhasil ditambahkan.');
        }
        $this->showForm = false;
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }
    public function delete(): void { Assignment::where('teacher_id', auth()->user()->teacher->id)->findOrFail($this->deletingId)->delete(); $this->showDeleteModal = false; session()->flash('success', 'Tugas berhasil dihapus.'); }

    public function render()
    {
        $teacherId = auth()->user()->teacher?->id;
        return view('livewire.teacher.assignment-index', [
            'assignments' => Assignment::with(['subject','classroom'])->withCount('submissions')
                ->where('teacher_id', $teacherId)
                ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->latest()->paginate(10),
            'subjects' => Subject::orderBy('name')->get(),
            'classrooms' => Classroom::orderBy('name')->get(),
        ])->layout('components.layouts.teacher', ['title' => 'Penugasan']);
    }
}
