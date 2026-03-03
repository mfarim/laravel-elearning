<?php

namespace App\Livewire\Teacher;

use App\Models\Classroom;
use App\Models\Examination;
use App\Models\Subject;
use Livewire\Component;
use Livewire\WithPagination;

class ExamIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $title = '';
    public string $description = '';
    public string $type = 'quiz';
    public string $subject_id = '';
    public string $classroom_id = '';
    public int $duration_minutes = 60;
    public int $passing_score = 75;
    public string $start_at = '';
    public string $end_at = '';
    public bool $shuffle_questions = false;
    public bool $shuffle_options = false;
    public bool $show_result = false;
    public bool $allow_retry = false;
    public string $status = 'draft';

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|in:quiz,uts,uas,praktik,tryout',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'duration_minutes' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'status' => 'required|in:draft,published,closed',
        ];
    }

    public function create(): void
    {
        $this->reset(['title','description','subject_id','classroom_id','editingId']);
        $this->type = 'quiz'; $this->duration_minutes = 60; $this->passing_score = 75;
        $this->shuffle_questions = false; $this->shuffle_options = false;
        $this->show_result = false; $this->allow_retry = false; $this->status = 'draft';
        $this->start_at = now()->addDay()->format('Y-m-d\TH:i');
        $this->end_at = now()->addDay()->addHours(2)->format('Y-m-d\TH:i');
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $e = Examination::where('teacher_id', auth()->user()->teacher->id)->findOrFail($id);
        $this->editingId = $id;
        $this->title = $e->title; $this->description = $e->description ?? '';
        $this->type = $e->type; $this->subject_id = (string)$e->subject_id;
        $this->classroom_id = (string)$e->classroom_id;
        $this->duration_minutes = $e->duration_minutes; $this->passing_score = $e->passing_score;
        $this->start_at = $e->start_at->format('Y-m-d\TH:i');
        $this->end_at = $e->end_at->format('Y-m-d\TH:i');
        $this->shuffle_questions = $e->shuffle_questions; $this->shuffle_options = $e->shuffle_options;
        $this->show_result = $e->show_result; $this->allow_retry = $e->allow_retry;
        $this->status = $e->status;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'title' => $this->title, 'description' => $this->description ?: null,
            'type' => $this->type, 'subject_id' => $this->subject_id,
            'classroom_id' => $this->classroom_id, 'teacher_id' => auth()->user()->teacher->id,
            'duration_minutes' => $this->duration_minutes, 'passing_score' => $this->passing_score,
            'start_at' => $this->start_at, 'end_at' => $this->end_at,
            'shuffle_questions' => $this->shuffle_questions, 'shuffle_options' => $this->shuffle_options,
            'show_result' => $this->show_result, 'allow_retry' => $this->allow_retry,
            'status' => $this->status,
        ];
        if ($this->editingId) {
            Examination::where('teacher_id', auth()->user()->teacher->id)->findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Ujian berhasil diperbarui.');
        } else {
            Examination::create($data);
            session()->flash('success', 'Ujian berhasil ditambahkan.');
        }
        $this->showForm = false;
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }
    public function delete(): void { Examination::where('teacher_id', auth()->user()->teacher->id)->findOrFail($this->deletingId)->delete(); $this->showDeleteModal = false; session()->flash('success', 'Ujian berhasil dihapus.'); }

    public function render()
    {
        $teacherId = auth()->user()->teacher?->id;
        return view('livewire.teacher.exam-index', [
            'exams' => Examination::with(['subject','classroom'])->withCount(['questions','attempts'])
                ->where('teacher_id', $teacherId)
                ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->latest()->paginate(10),
            'subjects' => Subject::orderBy('name')->get(),
            'classrooms' => Classroom::orderBy('name')->get(),
        ])->layout('components.layouts.teacher', ['title' => 'Ujian CBT']);
    }
}
