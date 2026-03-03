<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
  public function start(int $userId)
  {
    $admin = Auth::user();
    if (!$admin->hasRole('admin')) {
      abort(403);
    }

    $target = \App\Models\User::findOrFail($userId);
    session()->put('impersonating_admin_id', $admin->id);
    Auth::login($target);

    if ($target->hasRole('guru')) {
      return redirect('/teacher');
    } elseif ($target->hasRole('siswa')) {
      return redirect('/student');
    }

    return redirect('/');
  }

  public function stop()
  {
    $adminId = session()->pull('impersonating_admin_id');
    if ($adminId) {
      Auth::loginUsingId($adminId);
    }
    return redirect('/admin');
  }
}
