<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
  use SoftDeletes;

  protected $fillable = ['user_id', 'nip', 'address', 'photo'];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function subjects(): HasMany
  {
    return $this->hasMany(Subject::class);
  }

  public function classrooms(): HasMany
  {
    return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
  }

  public function learningMaterials(): HasMany
  {
    return $this->hasMany(LearningMaterial::class);
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
