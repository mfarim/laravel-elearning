<?php

namespace App\Livewire\Teacher;

use App\Models\Examination;
use App\Models\Question;
use Livewire\Component;
use Livewire\WithPagination;

class QuestionIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterExam = '';
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $examination_id = '';
    public string $question_text = '';
    public string $question_type = 'multiple_choice';
    public array $options = ['', '', '', '', ''];
    public string $correct_answer = '';
    public string $explanation = '';
    public int $points = 1;
    public string $difficulty = 'medium';

    protected function rules(): array
    {
        return [
            'examination_id' => 'required|exists:examinations,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,short_answer,essay',
            'correct_answer' => 'nullable|string',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
        ];
    }

    public function mount(): void
    {
        if (request()->has('exam_id')) {
            $this->filterExam = (string)request()->get('exam_id');
        }
    }

    public function create(): void
    {
        $this->reset(['question_text','correct_answer','explanation','editingId']);
        $this->examination_id = $this->filterExam;
        $this->question_type = 'multiple_choice';
        $this->options = ['', '', '', '', ''];
        $this->points = 1; $this->difficulty = 'medium';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $q = Question::findOrFail($id);
        $this->editingId = $id;
        $this->examination_id = (string)$q->examination_id;
        $this->question_text = $q->question_text;
        $this->question_type = $q->question_type;
        $this->options = $q->options ?? ['', '', '', '', ''];
        $this->correct_answer = $q->correct_answer ?? '';
        $this->explanation = $q->explanation ?? '';
        $this->points = $q->points;
        $this->difficulty = $q->difficulty;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'examination_id' => $this->examination_id, 'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'options' => $this->question_type === 'multiple_choice' ? array_filter($this->options) : null,
            'correct_answer' => $this->correct_answer ?: null,
            'explanation' => $this->explanation ?: null,
            'points' => $this->points, 'difficulty' => $this->difficulty,
        ];
        if ($this->editingId) {
            Question::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Soal berhasil diperbarui.');
        } else {
            Question::create($data);
            session()->flash('success', 'Soal berhasil ditambahkan.');
        }
        $this->showForm = false;
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }
    public function delete(): void { Question::findOrFail($this->deletingId)->delete(); $this->showDeleteModal = false; session()->flash('success', 'Soal berhasil dihapus.'); }

    public function render()
    {
        $teacherId = auth()->user()->teacher?->id;
        return view('livewire.teacher.question-index', [
            'questions' => Question::with('examination')
                ->whereHas('examination', fn ($q) => $q->where('teacher_id', $teacherId))
                ->when($this->search, fn ($q) => $q->where('question_text', 'like', "%{$this->search}%"))
                ->when($this->filterExam, fn ($q) => $q->where('examination_id', $this->filterExam))
                ->latest()->paginate(10),
            'exams' => Examination::where('teacher_id', $teacherId)->orderBy('title')->get(),
        ])->layout('components.layouts.teacher', ['title' => 'Bank Soal']);
    }
}
