<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
  use SoftDeletes;

  protected $fillable = ['user_id', 'classroom_id', 'nis', 'nisn', 'birth_date', 'gender', 'address', 'photo'];

  protected function casts(): array
  {
    return [
      'birth_date' => 'date',
    ];
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function classroom(): BelongsTo
  {
    return $this->belongsTo(Classroom::class);
  }

  public function assignmentSubmissions(): HasMany
  {
    return $this->hasMany(AssignmentSubmission::class);
  }

  public function examAttempts(): HasMany
  {
    return $this->hasMany(ExamAttempt::class);
  }
}
