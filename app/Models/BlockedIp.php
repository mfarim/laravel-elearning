<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedIp extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'ip_address',
    'reason',
    'description',
    'blocked_by',
    'blocked_at',
    'expires_at',
    'is_active',
  ];

  protected function casts(): array
  {
    return [
      'blocked_at' => 'datetime',
      'expires_at' => 'datetime',
      'is_active' => 'boolean',
    ];
  }

  public function blocker(): BelongsTo
  {
    return $this->belongsTo(User::class, 'blocked_by');
  }

  public function isExpired(): bool
  {
    return $this->expires_at && $this->expires_at->isPast();
  }
}
