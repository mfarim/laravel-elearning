<?php

use App\Http\Middleware\CheckBlockedUser;
use App\Http\Middleware\RoleMiddleware;
use App\Livewire\Teacher\AssignmentDiscussionTeacher;
use App\Livewire\Teacher\AssignmentIndex;
use App\Livewire\Teacher\AssignmentSubmissions;
use App\Livewire\Teacher\Dashboard;
use App\Livewire\Teacher\ExamIndex;
use App\Livewire\Teacher\ExamGrading;
use App\Livewire\Teacher\ExamMonitor;
use App\Livewire\Teacher\ExamPrint;
use App\Livewire\Teacher\MaterialIndex;
use App\Livewire\Teacher\MaterialViews;
use App\Livewire\Teacher\QuestionIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', CheckBlockedUser::class, RoleMiddleware::class . ':guru'])
  ->prefix('teacher')
  ->name('teacher.')
  ->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/materials', MaterialIndex::class)->name('materials.index');
    Route::get('/materials/{materialId}/views', MaterialViews::class)->name('materials.views');
    Route::get('/assignments', AssignmentIndex::class)->name('assignments.index');
    Route::get('/assignments/{assignmentId}/submissions', AssignmentSubmissions::class)->name('assignments.submissions');
    Route::get('/assignments/{assignmentId}/discussion', AssignmentDiscussionTeacher::class)->name('assignments.discussion');
    Route::get('/exams', ExamIndex::class)->name('exams.index');
    Route::get('/exams/{examination}/monitor', ExamMonitor::class)->name('exams.monitor');
    Route::get('/exams/{examination}/print', ExamPrint::class)->name('exams.print');
    Route::get('/exams/{examination}/grade/{attempt}', ExamGrading::class)->name('exams.grade');
    Route::get('/questions', QuestionIndex::class)->name('questions.index');
  });
