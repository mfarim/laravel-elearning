<?php

namespace App\Livewire\Teacher;

use App\Models\Classroom;
use App\Models\LearningMaterial;
use App\Models\Subject;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MaterialIndex extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $title = '';
    public string $description = '';
    public string $type = 'document';
    public string $subject_id = '';
    public string $classroom_id = '';
    public string $content = '';
    public string $file_url = '';
    public bool $is_published = false;
    public $file_upload;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|in:document,video,text,link,audio',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'file_url' => 'nullable|url',
            'is_published' => 'boolean',
            'file_upload' => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,mp4,mp3,wav,zip,rar',
        ];
    }

    public function create(): void
    {
        $this->reset(['title','description','type','subject_id','classroom_id','content','file_url','is_published','editingId','file_upload']);
        $this->type = 'document';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $m = LearningMaterial::where('teacher_id', auth()->user()->teacher->id)->findOrFail($id);
        $this->editingId = $id;
        $this->title = $m->title;
        $this->description = $m->description ?? '';
        $this->type = $m->type;
        $this->subject_id = (string)$m->subject_id;
        $this->classroom_id = (string)($m->classroom_id ?? '');
        $this->content = $m->content ?? '';
        $this->file_url = $m->file_url ?? '';
        $this->is_published = $m->is_published;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();
        $teacherId = auth()->user()->teacher->id;
        $data = [
            'title' => $this->title, 'description' => $this->description ?: null,
            'type' => $this->type, 'subject_id' => $this->subject_id,
            'classroom_id' => $this->classroom_id ?: null, 'content' => $this->content ?: null,
            'file_url' => $this->file_url ?: null, 'is_published' => $this->is_published,
            'teacher_id' => $teacherId,
        ];
        if ($this->file_upload) {
            $data['file_path'] = $this->file_upload->store('materials', 'public');
        }
        if ($this->editingId) {
            LearningMaterial::where('teacher_id', auth()->user()->teacher->id)->findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Materi berhasil diperbarui.');
        } else {
            LearningMaterial::create($data);
            session()->flash('success', 'Materi berhasil ditambahkan.');
        }
        $this->showForm = false;
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }
    public function delete(): void { LearningMaterial::where('teacher_id', auth()->user()->teacher->id)->findOrFail($this->deletingId)->delete(); $this->showDeleteModal = false; session()->flash('success', 'Materi berhasil dihapus.'); }

    public function render()
    {
        $teacherId = auth()->user()->teacher?->id;
        return view('livewire.teacher.material-index', [
            'materials' => LearningMaterial::with(['subject', 'classroom'])->withCount('views')
                ->where('teacher_id', $teacherId)
                ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->latest()->paginate(10),
            'subjects' => Subject::orderBy('name')->get(),
            'classrooms' => Classroom::orderBy('name')->get(),
        ])->layout('components.layouts.teacher', ['title' => 'Materi Pembelajaran']);
    }
}
