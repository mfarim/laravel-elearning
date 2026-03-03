<?php

namespace App\Livewire\Student;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Examination;
use App\Models\Question;
use Livewire\Component;

class ExamStart extends Component
{
  public Examination $examination;
  public ?ExamAttempt $attempt = null;
  public int $currentIndex = 0;
  public array $answers = [];
  public bool $showConfirm = false;
  public bool $examStarted = false;

  public function mount(Examination $examination): void
  {
    $this->examination = $examination;
    $student = auth()->user()->student;

    // Check existing attempt
    $this->attempt = ExamAttempt::where('examination_id', $examination->id)
      ->where('student_id', $student->id)
      ->where('status', 'in_progress')
      ->first();

    if ($this->attempt) {
      $this->examStarted = true;
      $this->loadAnswers();
    }
  }

  public function startExam(): void
  {
    $student = auth()->user()->student;
    $this->attempt = ExamAttempt::create([
      'examination_id' => $this->examination->id,
      'student_id' => $student->id,
      'started_at' => now(),
      'attempt_number' => ExamAttempt::where('examination_id', $this->examination->id)->where('student_id', $student->id)->count() + 1,
      'status' => 'in_progress',
    ]);
    $this->examStarted = true;
    $this->loadAnswers();
  }

  public function loadAnswers(): void
  {
    $existingAnswers = ExamAnswer::where('exam_attempt_id', $this->attempt->id)->pluck('answer_text', 'question_id')->toArray();
    $this->answers = $existingAnswers;
  }

  public function saveAnswer(int $questionId, string $answer): void
  {
    $this->answers[$questionId] = $answer;

    ExamAnswer::updateOrCreate(
      ['exam_attempt_id' => $this->attempt->id, 'question_id' => $questionId],
      ['answer_text' => $answer, 'answered_at' => now()]
    );
  }

  public function goTo(int $index): void
  {
    $this->currentIndex = $index;
  }

  public function next(): void
  {
    $questions = $this->examination->questions;
    if ($this->currentIndex < $questions->count() - 1)
      $this->currentIndex++;
  }

  public function prev(): void
  {
    if ($this->currentIndex > 0)
      $this->currentIndex--;
  }

  public function confirmFinish(): void
  {
    $this->showConfirm = true;
  }

  public function logViolation(): void
  {
    if ($this->attempt) {
      $this->attempt->increment('violations');
    }
  }

  public function autoFinish(): void
  {
    // Called when timer expires
    $this->finishExam();
  }

  public function finishExam()
  {
    $questions = $this->examination->questions;
    $totalPoints = 0;
    $earnedPoints = 0;
    $hasEssay = false;

    foreach ($questions as $q) {
      $totalPoints += $q->points;
      $answer = $this->answers[$q->id] ?? null;

      if ($q->question_type === 'essay') {
        $hasEssay = true;
        // Essay: don't auto-grade, leave points_earned = 0
      } elseif ($answer && $q->question_type === 'multiple_choice') {
        $isCorrect = strtoupper(trim($answer)) === strtoupper(trim($q->correct_answer ?? ''));
        ExamAnswer::where('exam_attempt_id', $this->attempt->id)
          ->where('question_id', $q->id)
          ->update(['is_correct' => $isCorrect, 'points_earned' => $isCorrect ? $q->points : 0]);
        if ($isCorrect)
          $earnedPoints += $q->points;
      }
    }

    // If has essay, calculate score from PG only and set needs_grading
    $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;
    $status = $hasEssay ? 'needs_grading' : 'completed';

    $this->attempt->update([
      'finished_at' => now(),
      'score' => $score,
      'is_passed' => $score >= $this->examination->passing_score,
      'status' => $status,
    ]);

    $message = $hasEssay
      ? "Ujian selesai! Soal essay akan dinilai oleh guru."
      : "Ujian selesai! Nilai Anda: {$score}";

    session()->flash('success', $message);
    $this->redirect(route('student.exams'), navigate: true);
  }

  public function render()
  {
    $questions = $this->examination->questions()->orderBy('id')->get();
    $currentQuestion = $questions[$this->currentIndex] ?? null;
    $answeredCount = count(array_filter($this->answers));

    // Calculate remaining seconds for timer
    $remainingSeconds = 0;
    if ($this->attempt && $this->attempt->started_at) {
      $deadline = $this->attempt->started_at->addMinutes($this->examination->duration_minutes);
      $remainingSeconds = max(0, $deadline->diffInSeconds(now(), false) * -1);
    }

    return view('livewire.student.exam-start', compact('questions', 'currentQuestion', 'answeredCount', 'remainingSeconds'))
      ->layout('components.layouts.student-exam', ['title' => $this->examination->title]);
  }
}
