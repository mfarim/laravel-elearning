<?php

namespace App\Livewire\Admin;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $phone = '';
    public string $nip = '';
    public string $address = '';

    protected function rules(): array
    {
        $emailRule = $this->editingId
            ? "required|email|unique:users,email,{$this->getUser()?->id}"
            : 'required|email|unique:users,email';
        $nipRule = $this->editingId
            ? "required|unique:teachers,nip,{$this->editingId}"
            : 'required|unique:teachers,nip';

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => $this->editingId ? 'nullable|min:8' : 'required|min:8',
            'phone' => 'nullable|string',
            'nip' => $nipRule,
            'address' => 'nullable|string',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['name', 'email', 'password', 'phone', 'nip', 'address', 'editingId']);
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        $this->editingId = $id;
        $this->name = $teacher->user->name;
        $this->email = $teacher->user->email;
        $this->phone = $teacher->user->phone ?? '';
        $this->nip = $teacher->nip;
        $this->address = $teacher->address ?? '';
        $this->password = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $teacher = Teacher::with('user')->findOrFail($this->editingId);
            $teacher->user->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone ?: null,
                ...(filled($this->password) ? ['password' => Hash::make($this->password)] : []),
            ]);
            $teacher->update([
                'nip' => $this->nip,
                'address' => $this->address ?: null,
            ]);
            session()->flash('success', 'Data guru berhasil diperbarui.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'phone' => $this->phone ?: null,
                'is_active' => true,
            ]);
            $user->assignRole('guru');
            Teacher::create([
                'user_id' => $user->id,
                'nip' => $this->nip,
                'address' => $this->address ?: null,
            ]);
            session()->flash('success', 'Data guru berhasil ditambahkan.');
        }

        $this->showForm = false;
        $this->reset(['name', 'email', 'password', 'phone', 'nip', 'address', 'editingId']);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $teacher = Teacher::with('user')->findOrFail($this->deletingId);
        $teacher->user->delete();
        $teacher->delete();
        $this->showDeleteModal = false;
        $this->deletingId = null;
        session()->flash('success', 'Data guru berhasil dihapus.');
    }

    private function getUser(): ?User
    {
        return $this->editingId ? Teacher::find($this->editingId)?->user : null;
    }

    public function render()
    {
        $teachers = Teacher::with('user')
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%"))->orWhere('nip', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.teacher-index', compact('teachers'))
            ->layout('components.layouts.admin', ['title' => 'Manajemen Guru']);
    }
}
