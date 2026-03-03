<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'examination_id',
    'question_text',
    'question_type',
    'options',
    'correct_answer',
    'audio_path',
    'image_path',
    'explanation',
    'points',
    'difficulty',
  ];

  protected function casts(): array
  {
    return [
      'options' => 'array',
    ];
  }

  public function examination(): BelongsTo
  {
    return $this->belongsTo(Examination::class);
  }
}
