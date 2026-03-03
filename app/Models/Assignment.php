<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'subject_id',
    'classroom_id',
    'teacher_id',
    'title',
    'description',
    'instructions',
    'attachment',
    'max_score',
    'due_date',
    'allow_late_submission',
    'status',
  ];

  protected function casts(): array
  {
    return [
      'due_date' => 'datetime',
      'allow_late_submission' => 'boolean',
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

  public function submissions(): HasMany
  {
    return $this->hasMany(AssignmentSubmission::class);
  }

  public function discussions(): HasMany
  {
    return $this->hasMany(AssignmentDiscussion::class);
  }
}
