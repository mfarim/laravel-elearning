<?php

namespace App\Livewire\Admin;

use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
  public function render()
  {
    return view('livewire.admin.dashboard', [
      'totalTeachers' => Teacher::count(),
      'totalStudents' => Student::count(),
      'totalClassrooms' => Classroom::count(),
      'totalSubjects' => Subject::count(),
      'totalUsers' => User::count(),
      'recentAnnouncements' => Announcement::where('is_published', true)
        ->latest()->take(5)->get(),
      'activeExams' => ExamAttempt::where('status', 'in_progress')->count(),
    ])->layout('components.layouts.admin', ['title' => 'Dashboard']);
  }
}
