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
    $student = auth()->user()?->student;

    if (!$student || $this->examination->classroom_id !== $student->classroom_id) {
      session()->flash('error', 'Anda tidak memiliki akses ke ujian ini.');
      $this->redirect(route('student.exams'), navigate: true);
      return;
    }

    if ($this->examination->status !== 'published') {
      session()->flash('error', 'Ujian belum dipublikasikan atau sudah ditutup.');
      $this->redirect(route('student.exams'), navigate: true);
      return;
    }

    if (now()->lt($this->examination->start_at)) {
      session()->flash('error', 'Ujian belum dimulai. Jadwal mulai: ' . $this->examination->start_at->format('d M Y H:i'));
      $this->redirect(route('student.exams'), navigate: true);
      return;
    }

    if (now()->gt($this->examination->end_at)) {
      session()->flash('error', 'Waktu ujian telah berakhir pada ' . $this->examination->end_at->format('d M Y H:i'));
      $this->redirect(route('student.exams'), navigate: true);
      return;
    }

    // Check existing in_progress attempt
    $this->attempt = ExamAttempt::where('examination_id', $this->examination->id)
      ->where('student_id', $student->id)
      ->where('status', 'in_progress')
      ->first();

    if ($this->attempt) {
      $durationDeadline = $this->attempt->started_at->copy()->addMinutes($this->examination->duration_minutes);
      $deadline = $durationDeadline->lt($this->examination->end_at) ? $durationDeadline : $this->examination->end_at;

      if (now()->gte($deadline)) {
        $this->finishExam();
        return;
      }

      $this->examStarted = true;
      $this->loadAnswers();
      return;
    }

    // Check if student has already completed attempts and retry is not allowed
    $finishedAttemptsCount = ExamAttempt::where('examination_id', $this->examination->id)
      ->where('student_id', $student->id)
      ->whereIn('status', ['completed', 'needs_grading', 'force_finished'])
      ->count();

    if ($finishedAttemptsCount > 0 && !$this->examination->allow_retry) {
      session()->flash('error', 'Anda sudah menyelesaikan ujian ini dan tidak dapat mengulang.');
      $this->redirect(route('student.exams'), navigate: true);
      return;
    }
  }

  public function startExam(): void
  {
    $student = auth()->user()?->student;

    if (!$student || $this->examination->classroom_id !== $student->classroom_id) {
      session()->flash('error', 'Anda tidak memiliki akses ke ujian ini.');
      $this->redirect(route('student.exams'), navigate: true);
      return;
    }

    if ($this->examination->status !== 'published') {
      session()->flash('error', 'Ujian belum dipublikasikan atau sudah ditutup.');
      $this->redirect(route('student.exams'), navigate: true);
      return;
    }

    if (now()->lt($this->examination->start_at) || now()->gt($this->examination->end_at)) {
      session()->flash('error', 'Waktu pelaksanaan ujian tidak valid atau telah berakhir.');
      $this->redirect(route('student.exams'), navigate: true);
      return;
    }

    // Resume existing attempt if present
    $existing = ExamAttempt::where('examination_id', $this->examination->id)
      ->where('student_id', $student->id)
      ->where('status', 'in_progress')
      ->first();

    if ($existing) {
      $this->attempt = $existing;
      $this->examStarted = true;
      $this->loadAnswers();
      return;
    }

    // Verify retry allowance
    $finishedAttemptsCount = ExamAttempt::where('examination_id', $this->examination->id)
      ->where('student_id', $student->id)
      ->whereIn('status', ['completed', 'needs_grading', 'force_finished'])
      ->count();

    if ($finishedAttemptsCount > 0 && !$this->examination->allow_retry) {
      session()->flash('error', 'Anda sudah menyelesaikan ujian ini dan tidak dapat mengulang.');
      $this->redirect(route('student.exams'), navigate: true);
      return;
    }

    $attemptNumber = ExamAttempt::where('examination_id', $this->examination->id)
      ->where('student_id', $student->id)
      ->count() + 1;

    $this->attempt = ExamAttempt::create([
      'examination_id' => $this->examination->id,
      'student_id' => $student->id,
      'started_at' => now(),
      'attempt_number' => $attemptNumber,
      'status' => 'in_progress',
    ]);

    $this->examStarted = true;
    $this->loadAnswers();
  }

  public function loadAnswers(): void
  {
    if (!$this->attempt) return;

    $existingAnswers = ExamAnswer::where('exam_attempt_id', $this->attempt->id)
      ->pluck('answer_text', 'question_id')
      ->toArray();
    $this->answers = $existingAnswers;
  }

  public function saveAnswer(int $questionId, string $answer): void
  {
    if (!$this->attempt) return;

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

  public function finishExam(): void
  {
    if (!$this->attempt) {
      return;
    }

    $questions = $this->examination->questions;
    $totalPoints = 0;
    $earnedPoints = 0;
    $hasEssay = false;

    foreach ($questions as $q) {
      $totalPoints += $q->points;
      $answer = $this->answers[$q->id] ?? null;

      if ($q->question_type === 'essay') {
        $hasEssay = true;
        ExamAnswer::updateOrCreate(
          ['exam_attempt_id' => $this->attempt->id, 'question_id' => $q->id],
          [
            'answer_text' => $answer,
            'answered_at' => ($answer !== null && $answer !== '') ? now() : null,
            'points_earned' => 0,
            'is_correct' => null,
          ]
        );
      } elseif ($q->question_type === 'multiple_choice') {
        $isCorrect = ($answer !== null && $answer !== '') && (strtoupper(trim($answer)) === strtoupper(trim($q->correct_answer ?? '')));
        $pointsEarned = $isCorrect ? $q->points : 0;
        ExamAnswer::updateOrCreate(
          ['exam_attempt_id' => $this->attempt->id, 'question_id' => $q->id],
          [
            'answer_text' => $answer,
            'answered_at' => ($answer !== null && $answer !== '') ? now() : null,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
          ]
        );
        if ($isCorrect) {
          $earnedPoints += $q->points;
        }
      }
    }

    if ($hasEssay) {
      $status = 'needs_grading';
      $score = null;
      $isPassed = false;
      $message = "Ujian selesai! Soal essay akan dinilai oleh guru.";
    } else {
      $status = 'completed';
      $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;
      $isPassed = $score >= $this->examination->passing_score;
      $message = "Ujian selesai! Nilai Anda: {$score}";
    }

    $this->attempt->update([
      'finished_at' => now(),
      'score' => $score,
      'is_passed' => $isPassed,
      'status' => $status,
    ]);

    session()->flash('success', $message);
    $this->redirect(route('student.exams'), navigate: true);
  }

  public function render()
  {
    $questions = $this->examination->questions()->orderBy('id')->get();
    $currentQuestion = $questions[$this->currentIndex] ?? null;
    $answeredCount = count(array_filter($this->answers, fn($ans) => $ans !== null && $ans !== ''));

    // Calculate remaining seconds for timer
    $remainingSeconds = 0;
    if ($this->attempt && $this->attempt->started_at) {
      $durationDeadline = $this->attempt->started_at->copy()->addMinutes($this->examination->duration_minutes);
      $deadline = $durationDeadline->lt($this->examination->end_at) ? $durationDeadline : $this->examination->end_at;
      $remainingSeconds = max(0, $deadline->timestamp - now()->timestamp);
    }

    return view('livewire.student.exam-start', compact('questions', 'currentQuestion', 'answeredCount', 'remainingSeconds'))
      ->layout('components.layouts.student-exam', ['title' => $this->examination->title]);
  }
}
