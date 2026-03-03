<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
  protected $fillable = [
    'examination_id',
    'student_id',
    'started_at',
    'finished_at',
    'score',
    'is_passed',
    'is_force_finished',
    'attempt_number',
    'status',
  ];

  protected function casts(): array
  {
    return [
      'started_at' => 'datetime',
      'finished_at' => 'datetime',
      'is_passed' => 'boolean',
      'is_force_finished' => 'boolean',
    ];
  }

  public function examination(): BelongsTo
  {
    return $this->belongsTo(Examination::class);
  }

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }

  public function answers(): HasMany
  {
    return $this->hasMany(ExamAnswer::class);
  }
}
