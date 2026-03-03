<?php

use App\Http\Middleware\CheckBlockedUser;
use App\Http\Middleware\RoleMiddleware;
use App\Livewire\Student\AssignmentDiscussionStudent;
use App\Livewire\Student\Assignments;
use App\Livewire\Student\Dashboard;
use App\Livewire\Student\ExamStart;
use App\Livewire\Student\Exams;
use App\Livewire\Student\Grades;
use App\Livewire\Student\MaterialDetail;
use App\Livewire\Student\Materials;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', CheckBlockedUser::class, RoleMiddleware::class . ':siswa'])
  ->prefix('student')
  ->name('student.')
  ->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/materials', Materials::class)->name('materials');
    Route::get('/materials/{materialId}', MaterialDetail::class)->name('materials.detail');
    Route::get('/assignments', Assignments::class)->name('assignments');
    Route::get('/assignments/{assignmentId}/discussion', AssignmentDiscussionStudent::class)->name('assignments.discussion');
    Route::get('/exams', Exams::class)->name('exams');
    Route::get('/exams/{examination}/start', ExamStart::class)->name('exam.start');
    Route::get('/grades', Grades::class)->name('grades');
  });
