<?php

namespace App\Livewire\Admin;

use App\Models\Classroom;
use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;

class ClassroomIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $name = '';
    public int $level = 10;
    public string $homeroom_teacher_id = '';
    public int $capacity = 30;
    public string $academic_year = '2025/2026';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'level' => 'required|integer|min:1|max:12',
            'homeroom_teacher_id' => 'nullable|exists:teachers,id',
            'capacity' => 'required|integer|min:1',
            'academic_year' => 'required|string',
        ];
    }

    public function create(): void
    {
        $this->reset(['name', 'homeroom_teacher_id', 'editingId']);
        $this->level = 10;
        $this->capacity = 30;
        $this->academic_year = '2025/2026';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $c = Classroom::findOrFail($id);
        $this->editingId = $id;
        $this->name = $c->name;
        $this->level = $c->level;
        $this->homeroom_teacher_id = (string)($c->homeroom_teacher_id ?? '');
        $this->capacity = $c->capacity;
        $this->academic_year = $c->academic_year;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'name' => $this->name, 'level' => $this->level,
            'homeroom_teacher_id' => $this->homeroom_teacher_id ?: null,
            'capacity' => $this->capacity, 'academic_year' => $this->academic_year,
        ];
        if ($this->editingId) {
            Classroom::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Data kelas berhasil diperbarui.');
        } else {
            Classroom::create($data);
            session()->flash('success', 'Data kelas berhasil ditambahkan.');
        }
        $this->showForm = false;
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        Classroom::findOrFail($this->deletingId)->delete();
        $this->showDeleteModal = false;
        session()->flash('success', 'Data kelas berhasil dihapus.');
    }

    public function render()
    {
        $classrooms = Classroom::with(['homeroomTeacher.user'])
            ->withCount('students')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('level')->orderBy('name')->paginate(10);

        return view('livewire.admin.classroom-index', [
            'classrooms' => $classrooms,
            'teachers' => Teacher::with('user')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Manajemen Kelas']);
    }
}
