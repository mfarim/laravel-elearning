<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
  public function start(Request $request, int $userId)
  {
    $admin = Auth::user();
    if (!$admin || !$admin->hasRole('admin')) {
      abort(403, 'Akses ditolak.');
    }

    $target = \App\Models\User::findOrFail($userId);

    // Prevent impersonating another admin or oneself
    if ($target->hasRole('admin') || $target->id === $admin->id) {
      abort(403, 'Tidak dapat melakukan impersonasi sesama admin atau akun sendiri.');
    }

    session()->put('impersonating_admin_id', $admin->id);
    session()->regenerate();
    Auth::login($target);

    if ($target->hasRole('guru')) {
      return redirect('/teacher');
    } elseif ($target->hasRole('siswa')) {
      return redirect('/student');
    }

    return redirect('/');
  }

  public function stop(Request $request)
  {
    $adminId = session()->pull('impersonating_admin_id');
    if ($adminId) {
      $admin = \App\Models\User::where('id', $adminId)
        ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
        ->first();

      if ($admin) {
        Auth::login($admin);
        session()->regenerate();
      }
    }
    return redirect('/admin');
  }
}
