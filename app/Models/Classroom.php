<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
  use SoftDeletes;

  protected $fillable = ['name', 'level', 'homeroom_teacher_id', 'capacity', 'academic_year'];

  public function homeroomTeacher(): BelongsTo
  {
    return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
  }

  public function students(): HasMany
  {
    return $this->hasMany(Student::class);
  }

  public function assignments(): HasMany
  {
    return $this->hasMany(Assignment::class);
  }

  public function examinations(): HasMany
  {
    return $this->hasMany(Examination::class);
  }
}
