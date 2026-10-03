<?php

namespace App\Livewire\Teacher;

use App\Events\DiscussionMessageDeleted;
use App\Events\DiscussionMessageSent;
use App\Models\Assignment;
use App\Models\AssignmentDiscussion;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AssignmentDiscussionTeacher extends Component
{
  #[Locked]
  public int $assignmentId;
  public string $message = '';
  public ?int $replyingTo = null;
  public ?int $deletingMessageId = null;

  public function mount(int $assignmentId): void
  {
    $teacherId = auth()->user()->teacher?->id;
    $assignment = Assignment::where('teacher_id', $teacherId)->findOrFail($assignmentId);
    $this->assignmentId = $assignment->id;
  }

  /**
   * @return array<string, string>
   */
  public function getListeners(): array
  {
    return [
      "echo-private:assignment.{$this->assignmentId}.discussion,DiscussionMessageSent" => 'refreshMessages',
      "echo-private:assignment.{$this->assignmentId}.discussion,DiscussionMessageDeleted" => 'refreshMessages',
    ];
  }

  public function refreshMessages(): void
  {
    // Livewire will automatically re-render when this method is called
  }

  public function send(): void
  {
    $this->validate(['message' => 'required|string|max:2000']);

    $discussion = AssignmentDiscussion::create([
      'assignment_id' => $this->assignmentId,
      'user_id' => auth()->id(),
      'message' => $this->message,
      'parent_id' => $this->replyingTo,
    ]);

    broadcast(new DiscussionMessageSent($this->assignmentId, $discussion->id))->toOthers();

    $this->message = '';
    $this->replyingTo = null;
  }

  public function reply(int $id): void
  {
    $this->replyingTo = $id;
  }

  public function cancelReply(): void
  {
    $this->replyingTo = null;
  }

  public function confirmDeleteMessage(int $id): void
  {
    $this->deletingMessageId = $id;
  }

  public function deleteMessage(): void
  {
    $teacherId = auth()->user()->teacher?->id;
    $message = AssignmentDiscussion::where('assignment_id', $this->assignmentId)
      ->whereHas('assignment', fn ($q) => $q->where('teacher_id', $teacherId))
      ->where('id', $this->deletingMessageId)
      ->first();

    if ($message) {
      $deletedId = $message->id;
      $message->delete();
      broadcast(new DiscussionMessageDeleted($this->assignmentId, $deletedId))->toOthers();
    }

    $this->deletingMessageId = null;
  }

  public function render()
  {
    $teacherId = auth()->user()->teacher?->id;
    $assignment = Assignment::with(['subject', 'classroom'])
      ->where('teacher_id', $teacherId)
      ->findOrFail($this->assignmentId);
    $discussions = AssignmentDiscussion::with(['user', 'replies.user'])
      ->where('assignment_id', $this->assignmentId)
      ->whereNull('parent_id')
      ->latest()
      ->get();

    return view('livewire.teacher.assignment-discussion-teacher', [
      'assignment' => $assignment,
      'discussions' => $discussions,
    ])->layout('components.layouts.teacher', ['title' => 'Diskusi — ' . $assignment->title]);
  }
}
