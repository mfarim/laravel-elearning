<?php

namespace App\Livewire\Admin;

use App\Models\Announcement;
use Livewire\Component;
use Livewire\WithPagination;

class AnnouncementIndex extends Component
{
  use WithPagination;

  public bool $showForm = false;
  public bool $showDeleteModal = false;
  public ?int $editingId = null;
  public ?int $deletingId = null;

  public string $title = '';
  public string $content = '';
  public string $target = 'all';
  public bool $is_published = false;

  protected function rules(): array
  {
    return [
      'title' => 'required|string|max:255',
      'content' => 'required|string',
      'target' => 'required|in:all,teacher,student',
      'is_published' => 'boolean',
    ];
  }

  public function create(): void
  {
    $this->reset(['title', 'content', 'editingId']);
    $this->target = 'all';
    $this->is_published = false;
    $this->showForm = true;
  }

  public function edit(int $id): void
  {
    $a = Announcement::findOrFail($id);
    $this->editingId = $id;
    $this->title = $a->title;
    $this->content = $a->content;
    $this->target = $a->target;
    $this->is_published = $a->is_published;
    $this->showForm = true;
  }

  public function save(): void
  {
    $this->validate();
    $data = [
      'title' => $this->title,
      'content' => $this->content,
      'target' => $this->target,
      'is_published' => $this->is_published,
      'published_at' => $this->is_published ? now() : null,
    ];
    if ($this->editingId) {
      Announcement::findOrFail($this->editingId)->update($data);
      session()->flash('success', 'Pengumuman berhasil diperbarui.');
    } else {
      Announcement::create($data);
      session()->flash('success', 'Pengumuman berhasil ditambahkan.');
    }
    $this->showForm = false;
  }

  public function confirmDelete(int $id): void
  {
    $this->deletingId = $id;
    $this->showDeleteModal = true;
  }

  public function delete(): void
  {
    Announcement::findOrFail($this->deletingId)->delete();
    $this->showDeleteModal = false;
    session()->flash('success', 'Pengumuman berhasil dihapus.');
  }

  public function render()
  {
    return view('livewire.admin.announcement-index', [
      'announcements' => Announcement::latest()->paginate(10),
    ])->layout('components.layouts.admin', ['title' => 'Pengumuman']);
  }
}
