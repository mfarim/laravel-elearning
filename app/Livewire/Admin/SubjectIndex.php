<?php

namespace App\Livewire\Admin;

use App\Models\Subject;
use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;

class SubjectIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $name = '';
    public string $code = '';
    public string $description = '';
    public string $teacher_id = '';
    public int $credits = 2;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => $this->editingId ? "required|unique:subjects,code,{$this->editingId}" : 'required|unique:subjects,code',
            'description' => 'nullable|string',
            'teacher_id' => 'nullable|exists:teachers,id',
            'credits' => 'required|integer|min:1',
        ];
    }

    public function create(): void
    {
        $this->reset(['name', 'code', 'description', 'teacher_id', 'editingId']);
        $this->credits = 2;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $s = Subject::findOrFail($id);
        $this->editingId = $id;
        $this->name = $s->name;
        $this->code = $s->code;
        $this->description = $s->description ?? '';
        $this->teacher_id = (string)($s->teacher_id ?? '');
        $this->credits = $s->credits;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();
        $data = ['name' => $this->name, 'code' => $this->code, 'description' => $this->description ?: null, 'teacher_id' => $this->teacher_id ?: null, 'credits' => $this->credits];
        if ($this->editingId) {
            Subject::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Mapel berhasil diperbarui.');
        } else {
            Subject::create($data);
            session()->flash('success', 'Mapel berhasil ditambahkan.');
        }
        $this->showForm = false;
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        Subject::findOrFail($this->deletingId)->delete();
        $this->showDeleteModal = false;
        session()->flash('success', 'Mapel berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.subject-index', [
            'subjects' => Subject::with('teacher.user')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%"))
                ->orderBy('name')->paginate(10),
            'teachers' => Teacher::with('user')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Mata Pelajaran']);
    }
}
