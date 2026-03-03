<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examination extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'subject_id',
    'classroom_id',
    'teacher_id',
    'title',
    'description',
    'type',
    'duration_minutes',
    'passing_score',
    'start_at',
    'end_at',
    'exam_date',
    'total_questions',
    'shuffle_questions',
    'shuffle_options',
    'show_result',
    'exam_package',
    'allow_retry',
    'status',
  ];

  protected function casts(): array
  {
    return [
      'start_at' => 'datetime',
      'end_at' => 'datetime',
      'exam_date' => 'date',
      'shuffle_questions' => 'boolean',
      'shuffle_options' => 'boolean',
      'show_result' => 'boolean',
      'allow_retry' => 'boolean',
    ];
  }

  public function subject(): BelongsTo
  {
    return $this->belongsTo(Subject::class);
  }

  public function classroom(): BelongsTo
  {
    return $this->belongsTo(Classroom::class);
  }

  public function teacher(): BelongsTo
  {
    return $this->belongsTo(Teacher::class);
  }

  public function questions(): HasMany
  {
    return $this->hasMany(Question::class);
  }

  public function attempts(): HasMany
  {
    return $this->hasMany(ExamAttempt::class);
  }
}
