<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialView extends Model
{
  protected $fillable = [
    'learning_material_id',
    'student_id',
    'viewed_at',
  ];

  protected function casts(): array
  {
    return ['viewed_at' => 'datetime'];
  }

  public function material(): BelongsTo
  {
    return $this->belongsTo(LearningMaterial::class, 'learning_material_id');
  }

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }
}
