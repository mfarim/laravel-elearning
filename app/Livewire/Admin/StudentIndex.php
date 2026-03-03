<?php

namespace App\Livewire\Admin;

use App\Imports\StudentImport;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class StudentIndex extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterClassroom = '';
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;
    public bool $showImport = false;
    public $importFile;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $phone = '';
    public string $nis = '';
    public string $nisn = '';
    public string $classroom_id = '';
    public string $birth_date = '';
    public string $gender = 'L';
    public string $address = '';

    protected function rules(): array
    {
        $userId = $this->editingId ? Student::find($this->editingId)?->user_id : null;
        return [
            'name' => 'required|string|max:255',
            'email' => $this->editingId ? "required|email|unique:users,email,{$userId}" : 'required|email|unique:users,email',
            'password' => $this->editingId ? 'nullable|min:8' : 'required|min:8',
            'nis' => $this->editingId ? "required|unique:students,nis,{$this->editingId}" : 'required|unique:students,nis',
            'nisn' => 'nullable|string',
            'classroom_id' => 'required|exists:classrooms,id',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:L,P',
            'address' => 'nullable|string',
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterClassroom(): void { $this->resetPage(); }

    public function create(): void
    {
        $this->reset(['name','email','password','phone','nis','nisn','classroom_id','birth_date','gender','address','editingId']);
        $this->gender = 'L';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $student = Student::with('user')->findOrFail($id);
        $this->editingId = $id;
        $this->name = $student->user->name;
        $this->email = $student->user->email;
        $this->phone = $student->user->phone ?? '';
        $this->nis = $student->nis;
        $this->nisn = $student->nisn ?? '';
        $this->classroom_id = (string)$student->classroom_id;
        $this->birth_date = $student->birth_date?->format('Y-m-d') ?? '';
        $this->gender = $student->gender;
        $this->address = $student->address ?? '';
        $this->password = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $student = Student::with('user')->findOrFail($this->editingId);
            $student->user->update([
                'name' => $this->name, 'email' => $this->email, 'phone' => $this->phone ?: null,
                ...(filled($this->password) ? ['password' => Hash::make($this->password)] : []),
            ]);
            $student->update([
                'nis' => $this->nis, 'nisn' => $this->nisn ?: null,
                'classroom_id' => $this->classroom_id, 'birth_date' => $this->birth_date ?: null,
                'gender' => $this->gender, 'address' => $this->address ?: null,
            ]);
            session()->flash('success', 'Data siswa berhasil diperbarui.');
        } else {
            $user = User::create([
                'name' => $this->name, 'email' => $this->email,
                'password' => Hash::make($this->password), 'phone' => $this->phone ?: null, 'is_active' => true,
            ]);
            $user->assignRole('siswa');
            Student::create([
                'user_id' => $user->id, 'nis' => $this->nis, 'nisn' => $this->nisn ?: null,
                'classroom_id' => $this->classroom_id, 'birth_date' => $this->birth_date ?: null,
                'gender' => $this->gender, 'address' => $this->address ?: null,
            ]);
            session()->flash('success', 'Data siswa berhasil ditambahkan.');
        }
        $this->showForm = false;
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        $student = Student::with('user')->findOrFail($this->deletingId);
        $student->user->delete();
        $student->delete();
        $this->showDeleteModal = false;
        session()->flash('success', 'Data siswa berhasil dihapus.');
    }

    public function importExcel(): void
    {
        $this->validate(['importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240']);
        $import = new StudentImport();
        Excel::import($import, $this->importFile->getRealPath());
        $failures = $import->failures();
        $msg = "Berhasil import {$import->importedCount} siswa.";
        if ($failures->count()) {
            $msg .= " {$failures->count()} baris gagal.";
        }
        session()->flash('success', $msg);
        $this->showImport = false;
        $this->importFile = null;
    }

    public function render()
    {
        $students = Student::with(['user', 'classroom'])
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%"))->orWhere('nis', 'like', "%{$this->search}%"))
            ->when($this->filterClassroom, fn ($q) => $q->where('classroom_id', $this->filterClassroom))
            ->latest()->paginate(10);

        return view('livewire.admin.student-index', [
            'students' => $students,
            'classrooms' => Classroom::orderBy('name')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Manajemen Siswa']);
    }
}
