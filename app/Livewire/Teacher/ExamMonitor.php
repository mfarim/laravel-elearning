<?php

namespace App\Livewire\Teacher;

use App\Models\ExamAttempt;
use App\Models\Examination;
use App\Models\Student;
use Livewire\Component;

class ExamMonitor extends Component
{
    public Examination $examination;

    public function mount(Examination $examination): void
    {
        $this->examination = $examination;
    }

    public function render()
    {
        $classroomId = $this->examination->classroom_id;
        $totalQuestions = $this->examination->questions()->count();

        // All students in the classroom
        $students = Student::with('user')
            ->where('classroom_id', $classroomId)
            ->get();

        // All attempts for this exam
        $attempts = ExamAttempt::with('answers')
            ->where('examination_id', $this->examination->id)
            ->get()
            ->keyBy('student_id');

        $monitorData = $students->map(function ($student) use ($attempts, $totalQuestions) {
            $attempt = $attempts->get($student->id);
            $answeredCount = $attempt ? $attempt->answers->count() : 0;

            return (object)[
                'student' => $student,
                'attempt' => $attempt,
                'status' => !$attempt ? 'belum_mulai' : ($attempt->status === 'completed' ? 'selesai' : ($attempt->status === 'needs_grading' ? 'perlu_dinilai' : 'mengerjakan')),
                'answered' => $answeredCount,
                'total' => $totalQuestions,
                'progress' => $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100) : 0,
                'score' => $attempt?->score,
                'is_passed' => $attempt?->is_passed,
                'started_at' => $attempt?->started_at,
                'finished_at' => $attempt?->finished_at,
                'violations' => $attempt?->violations ?? 0,
            ];
        })->sortBy(fn ($m) => ['mengerjakan' => 0, 'belum_mulai' => 1, 'selesai' => 2][$m->status]);

        $stats = (object)[
            'total' => $students->count(),
            'not_started' => $monitorData->where('status', 'belum_mulai')->count(),
            'in_progress' => $monitorData->where('status', 'mengerjakan')->count(),
            'completed' => $monitorData->where('status', 'selesai')->count(),
            'avg_score' => $monitorData->where('status', 'selesai')->avg('score') ?? 0,
            'passed' => $monitorData->where('is_passed', true)->count(),
        ];

        return view('livewire.teacher.exam-monitor', compact('monitorData', 'stats', 'totalQuestions'))
            ->layout('components.layouts.teacher', ['title' => 'Monitor: ' . $this->examination->title]);
    }
}
