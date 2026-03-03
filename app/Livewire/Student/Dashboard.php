<?php

namespace App\Livewire\Student;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Examination;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $student = auth()->user()->student;
        $classroomId = $student?->classroom_id;

        return view('livewire.student.dashboard', [
            'student' => $student,
            'upcomingExams' => $classroomId
                ? Examination::where('classroom_id', $classroomId)->where('status', 'published')->where('start_at', '>', now())->orderBy('start_at')->take(3)->get()
                : collect(),
            'activeAssignments' => $classroomId
                ? Assignment::where('classroom_id', $classroomId)->where('status', 'published')->where('due_date', '>', now())->orderBy('due_date')->take(5)->get()
                : collect(),
            'announcements' => Announcement::where('is_published', true)->where(fn ($q) => $q->where('target', 'all')->orWhere('target', 'student'))->latest()->take(3)->get(),
        ])->layout('components.layouts.student', ['title' => 'Home']);
    }
}
