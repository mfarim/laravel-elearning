<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentDiscussion extends Model
{
  protected $fillable = [
    'assignment_id',
    'user_id',
    'message',
    'parent_id',
  ];

  public function assignment(): BelongsTo
  {
    return $this->belongsTo(Assignment::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(\App\Models\User::class);
  }

  public function parent(): BelongsTo
  {
    return $this->belongsTo(AssignmentDiscussion::class, 'parent_id');
  }

  public function replies(): HasMany
  {
    return $this->hasMany(AssignmentDiscussion::class, 'parent_id')->oldest();
  }
}
