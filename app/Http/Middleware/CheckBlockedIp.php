<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIp
{
  public function handle(Request $request, Closure $next): Response
  {
    $blocked = BlockedIp::where('ip_address', $request->ip())
      ->where('is_active', true)
      ->where(function ($query) {
        $query->whereNull('expires_at')
          ->orWhere('expires_at', '>', now());
      })
      ->first();

    if ($blocked) {
      abort(403, 'IP Anda telah diblokir. Alasan: ' . $blocked->reason);
    }

    return $next($request);
  }
}
