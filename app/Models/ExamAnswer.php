<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
  protected $fillable = [
    'exam_attempt_id',
    'question_id',
    'answer_text',
    'is_correct',
    'points_earned',
    'feedback',
    'answered_at',
  ];

  protected function casts(): array
  {
    return [
      'is_correct' => 'boolean',
      'answered_at' => 'datetime',
    ];
  }

  public function attempt(): BelongsTo
  {
    return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
  }

  public function question(): BelongsTo
  {
    return $this->belongsTo(Question::class);
  }
}
