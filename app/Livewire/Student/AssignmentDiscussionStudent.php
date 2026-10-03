<?php

namespace App\Livewire\Student;

use App\Events\DiscussionMessageSent;
use App\Models\Assignment;
use App\Models\AssignmentDiscussion;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class AssignmentDiscussionStudent extends Component
{
  #[Locked]
  public int $assignmentId;
  public string $message = '';
  public ?int $replyingTo = null;

  public function mount(int $assignmentId): void
  {
    $assignment = Assignment::findOrFail($assignmentId);
    $this->authorize('discuss', $assignment);

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
    $assignment = Assignment::findOrFail($this->assignmentId);
    $this->authorize('discuss', $assignment);

    $parentValid = null;
    if ($this->replyingTo) {
      $parentValid = AssignmentDiscussion::where('assignment_id', $this->assignmentId)
        ->where('id', $this->replyingTo)
        ->value('id');
    }

    $discussion = AssignmentDiscussion::create([
      'assignment_id' => $this->assignmentId,
      'user_id' => auth()->id(),
      'message' => $this->message,
      'parent_id' => $parentValid,
    ]);

    broadcast(new DiscussionMessageSent($this->assignmentId, $discussion->id))->toOthers();

    $this->message = '';
    $this->replyingTo = null;
  }

  public function reply(int $id): void
  {
    $exists = AssignmentDiscussion::where('assignment_id', $this->assignmentId)->where('id', $id)->exists();
    if ($exists) {
      $this->replyingTo = $id;
    }
  }

  public function cancelReply(): void
  {
    $this->replyingTo = null;
  }

  public function render()
  {
    $assignment = Assignment::with(['subject'])->findOrFail($this->assignmentId);
    $discussions = AssignmentDiscussion::with(['user', 'replies.user'])
      ->where('assignment_id', $this->assignmentId)
      ->whereNull('parent_id')
      ->latest()
      ->get();

    return view('livewire.student.assignment-discussion-student', [
      'assignment' => $assignment,
      'discussions' => $discussions,
    ])->layout('components.layouts.student', ['title' => 'Diskusi — ' . $assignment->title]);
  }
}
