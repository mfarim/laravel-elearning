<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningMaterial extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'subject_id',
    'teacher_id',
    'classroom_id',
    'title',
    'description',
    'type',
    'file_path',
    'file_url',
    'content',
    'is_published',
  ];

  protected function casts(): array
  {
    return ['is_published' => 'boolean'];
  }

  public function subject(): BelongsTo
  {
    return $this->belongsTo(Subject::class);
  }

  public function teacher(): BelongsTo
  {
    return $this->belongsTo(Teacher::class);
  }

  public function classroom(): BelongsTo
  {
    return $this->belongsTo(Classroom::class);
  }

  public function views(): \Illuminate\Database\Eloquent\Relations\HasMany
  {
    return $this->hasMany(MaterialView::class, 'learning_material_id');
  }
}
