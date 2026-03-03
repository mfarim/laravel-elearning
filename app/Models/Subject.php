<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
  use SoftDeletes;

  protected $fillable = ['name', 'code', 'description', 'teacher_id', 'credits'];

  public function teacher(): BelongsTo
  {
    return $this->belongsTo(Teacher::class);
  }
}
