<?php

namespace App\Http\Middleware;

use App\Models\BlockedUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedUser
{
  public function handle(Request $request, Closure $next): Response
  {
    if ($user = $request->user()) {
      $blocked = BlockedUser::where('user_id', $user->id)
        ->where('is_active', true)
        ->where(function ($query) {
          $query->whereNull('expires_at')
            ->orWhere('expires_at', '>', now());
        })
        ->first();

      if ($blocked) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
          ->withErrors(['email' => 'Akun Anda telah diblokir. Alasan: ' . $blocked->reason]);
      }
    }

    return $next($request);
  }
}
