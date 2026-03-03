<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
  protected $fillable = [
    'assignment_id',
    'student_id',
    'file_path',
    'notes',
    'score',
    'feedback',
    'status',
    'submitted_at',
    'graded_at',
  ];

  protected function casts(): array
  {
    return [
      'submitted_at' => 'datetime',
      'graded_at' => 'datetime',
    ];
  }

  public function assignment(): BelongsTo
  {
    return $this->belongsTo(Assignment::class);
  }

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }
}
