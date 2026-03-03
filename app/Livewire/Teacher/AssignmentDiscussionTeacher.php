<?php

namespace App\Livewire\Teacher;

use App\Events\DiscussionMessageDeleted;
use App\Events\DiscussionMessageSent;
use App\Models\Assignment;
use App\Models\AssignmentDiscussion;
use Livewire\Component;

class AssignmentDiscussionTeacher extends Component
{
  public int $assignmentId;
  public string $message = '';
  public ?int $replyingTo = null;
  public ?int $deletingMessageId = null;

  public function mount(int $assignmentId): void
  {
    $this->assignmentId = $assignmentId;
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
    AssignmentDiscussion::where('id', $this->deletingMessageId)->delete();

    broadcast(new DiscussionMessageDeleted($this->assignmentId, $this->deletingMessageId))->toOthers();

    $this->deletingMessageId = null;
  }

  public function render()
  {
    $assignment = Assignment::with(['subject', 'classroom'])->findOrFail($this->assignmentId);
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
