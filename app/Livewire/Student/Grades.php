<?php

namespace App\Livewire\Student;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Examination;
use App\Models\ExamAttempt;
use App\Models\LearningMaterial;
use App\Models\Subject;
use Livewire\Component;

class Grades extends Component
{
    public function render()
    {
        $student = auth()->user()->student;
        $classroomId = $student->classroom_id;

        // Get subject IDs from tables that reference classroom_id
        $subjectIds = collect()
            ->merge(LearningMaterial::where('classroom_id', $classroomId)->pluck('subject_id'))
            ->merge(Examination::where('classroom_id', $classroomId)->pluck('subject_id'))
            ->merge(Assignment::where('classroom_id', $classroomId)->pluck('subject_id'))
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)->get();

        $gradesData = $subjects->map(function ($subject) use ($student) {
            // Exam scores for this subject
            $examAttempts = ExamAttempt::whereHas('examination', fn ($q) => $q->where('subject_id', $subject->id))
                ->where('student_id', $student->id)
                ->where('status', 'completed')
                ->get();

            $avgExamScore = $examAttempts->avg('score') ?? 0;
            $totalExams = $examAttempts->count();
            $passedExams = $examAttempts->where('is_passed', true)->count();

            // Assignment scores for this subject
            $submissions = AssignmentSubmission::whereHas('assignment', fn ($q) => $q->where('subject_id', $subject->id))
                ->where('student_id', $student->id)
                ->whereNotNull('score')
                ->get();

            $avgAssignmentScore = $submissions->avg('score') ?? 0;
            $totalAssignments = $submissions->count();

            // Overall
            $scores = collect();
            if ($totalExams > 0) $scores->push($avgExamScore);
            if ($totalAssignments > 0) $scores->push($avgAssignmentScore);
            $overall = $scores->count() > 0 ? round($scores->avg()) : 0;

            return (object)[
                'subject' => $subject,
                'avg_exam' => round($avgExamScore),
                'avg_assignment' => round($avgAssignmentScore),
                'total_exams' => $totalExams,
                'passed_exams' => $passedExams,
                'total_assignments' => $totalAssignments,
                'overall' => $overall,
            ];
        });

        // Overall GPA
        $overallAvg = $gradesData->where('overall', '>', 0)->avg('overall') ?? 0;
        $totalExams = ExamAttempt::where('student_id', $student->id)->where('status', 'completed')->count();
        $totalPassed = ExamAttempt::where('student_id', $student->id)->where('is_passed', true)->count();

        return view('livewire.student.grades', [
            'gradesData' => $gradesData,
            'overallAvg' => round($overallAvg),
            'totalExams' => $totalExams,
            'totalPassed' => $totalPassed,
        ])->layout('components.layouts.student', ['title' => 'Nilai & Progress']);
    }
}
