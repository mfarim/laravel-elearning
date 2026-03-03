<?php

namespace App\Livewire\Teacher;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Examination;
use Livewire\Component;

class ExamGrading extends Component
{
  public Examination $examination;
  public ExamAttempt $attempt;
  public array $grades = [];

  public function mount(Examination $examination, ExamAttempt $attempt): void
  {
    $this->examination = $examination;
    $this->attempt = $attempt;

    // Load existing grades for essay answers
    $essayAnswers = ExamAnswer::where('exam_attempt_id', $attempt->id)
      ->whereHas('question', fn($q) => $q->where('question_type', 'essay'))
      ->get();

    foreach ($essayAnswers as $answer) {
      $this->grades[$answer->question_id] = [
        'points' => $answer->points_earned ?? 0,
        'feedback' => $answer->feedback ?? '',
      ];
    }
  }

  public function saveGrades(): void
  {
    $essayQuestions = $this->examination->questions()
      ->where('question_type', 'essay')
      ->get();

    foreach ($essayQuestions as $question) {
      $grade = $this->grades[$question->id] ?? null;
      if (!$grade) continue;

      $points = max(0, min($question->points, (int)($grade['points'] ?? 0)));

      ExamAnswer::where('exam_attempt_id', $this->attempt->id)
        ->where('question_id', $question->id)
        ->update([
          'points_earned' => $points,
          'feedback' => $grade['feedback'] ?? null,
          'is_correct' => $points > 0,
        ]);
    }

    // Recalculate total score
    $totalPoints = $this->examination->questions->sum('points');
    $earnedPoints = ExamAnswer::where('exam_attempt_id', $this->attempt->id)
      ->sum('points_earned');

    $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;

    $this->attempt->update([
      'score' => $score,
      'is_passed' => $score >= $this->examination->passing_score,
      'status' => 'completed',
    ]);

    session()->flash('success', "Penilaian essay berhasil disimpan. Nilai akhir: {$score}");
    $this->redirect(route('teacher.exams.monitor', $this->examination), navigate: true);
  }

  public function render()
  {
    $essayQuestions = $this->examination->questions()
      ->where('question_type', 'essay')
      ->get();

    $essayAnswers = ExamAnswer::where('exam_attempt_id', $this->attempt->id)
      ->whereIn('question_id', $essayQuestions->pluck('id'))
      ->get()
      ->keyBy('question_id');

    // PG score (already auto-graded)
    $pgQuestions = $this->examination->questions()->where('question_type', 'multiple_choice')->get();
    $pgEarned = ExamAnswer::where('exam_attempt_id', $this->attempt->id)
      ->whereIn('question_id', $pgQuestions->pluck('id'))
      ->sum('points_earned');
    $pgTotal = $pgQuestions->sum('points');

    return view('livewire.teacher.exam-grading', [
      'essayQuestions' => $essayQuestions,
      'essayAnswers' => $essayAnswers,
      'student' => $this->attempt->student,
      'pgEarned' => $pgEarned,
      'pgTotal' => $pgTotal,
    ])->layout('components.layouts.teacher', ['title' => 'Nilai Essay: ' . $this->examination->title]);
  }
}
