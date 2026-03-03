<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Examination;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ExaminationSeeder extends Seeder
{
  public function run(): void
  {
    $teachers = Teacher::all();
    $subjects = Subject::all();
    $classrooms = Classroom::all();

    // Create 3 exams
    $exams = [
      [
        'title' => 'Quiz Matematika - Aljabar',
        'type' => 'quiz',
        'duration_minutes' => 30,
        'passing_score' => 70,
        'shuffle_questions' => true,
        'shuffle_options' => true,
        'status' => 'published',
      ],
      [
        'title' => 'UTS Bahasa Inggris',
        'type' => 'uts',
        'duration_minutes' => 60,
        'passing_score' => 75,
        'shuffle_questions' => false,
        'shuffle_options' => true,
        'status' => 'published',
      ],
      [
        'title' => 'Tryout Fisika',
        'type' => 'tryout',
        'duration_minutes' => 90,
        'passing_score' => 65,
        'shuffle_questions' => true,
        'shuffle_options' => true,
        'show_result' => true,
        'allow_retry' => true,
        'status' => 'published',
      ],
    ];

    $questionBanks = [
      // Math questions
      [
        ['text' => 'Berapakah hasil dari 3x + 5 = 20, maka x = ?', 'options' => ['3', '4', '5', '6', '7'], 'answer' => 'C', 'difficulty' => 'easy'],
        ['text' => 'Jika f(x) = 2x² + 3x - 1, maka f(2) = ?', 'options' => ['9', '13', '11', '15', '7'], 'answer' => 'B', 'difficulty' => 'medium'],
        ['text' => 'Determinan matriks [[2,3],[1,4]] adalah?', 'options' => ['5', '8', '11', '3', '7'], 'answer' => 'A', 'difficulty' => 'medium'],
        ['text' => 'Limit x→0 dari sin(x)/x = ?', 'options' => ['0', '1', '∞', '-1', 'Tidak ada'], 'answer' => 'B', 'difficulty' => 'hard'],
        ['text' => 'Turunan dari f(x) = x³ + 2x adalah?', 'options' => ['3x² + 2', '3x + 2', 'x² + 2', '3x²', 'x³'], 'answer' => 'A', 'difficulty' => 'medium'],
      ],
      // English questions
      [
        ['text' => 'Choose the correct form: She ___ to school every day.', 'options' => ['go', 'goes', 'going', 'gone', 'went'], 'answer' => 'B', 'difficulty' => 'easy'],
        ['text' => 'The opposite of "generous" is:', 'options' => ['kind', 'selfish', 'brave', 'honest', 'wise'], 'answer' => 'B', 'difficulty' => 'easy'],
        ['text' => 'Which sentence uses past perfect correctly?', 'options' => ['I had eaten before he came', 'I have eaten yesterday', 'I was ate lunch', 'He did went home', 'She has go home'], 'answer' => 'A', 'difficulty' => 'medium'],
        ['text' => '"Running out of time" is an example of:', 'options' => ['Simile', 'Metaphor', 'Idiom', 'Hyperbole', 'Irony'], 'answer' => 'C', 'difficulty' => 'medium'],
        ['text' => 'The synonym of "ameliorate" is:', 'options' => ['worsen', 'improve', 'maintain', 'destroy', 'ignore'], 'answer' => 'B', 'difficulty' => 'hard'],
      ],
      // Physics questions
      [
        ['text' => 'Satuan gaya dalam SI adalah?', 'options' => ['Joule', 'Newton', 'Watt', 'Pascal', 'Hertz'], 'answer' => 'B', 'difficulty' => 'easy'],
        ['text' => 'Hukum Newton ke-2 menyatakan:', 'options' => ['F = ma', 'E = mc²', 'V = IR', 'P = W/t', 'F = kx'], 'answer' => 'A', 'difficulty' => 'easy'],
        ['text' => 'Energi kinetik benda bermassa 2 kg dengan kecepatan 3 m/s:', 'options' => ['6 J', '9 J', '12 J', '18 J', '3 J'], 'answer' => 'B', 'difficulty' => 'medium'],
        ['text' => 'Resultan gaya 3N dan 4N yang saling tegak lurus:', 'options' => ['5 N', '7 N', '1 N', '12 N', '3.5 N'], 'answer' => 'A', 'difficulty' => 'medium'],
        ['text' => 'Periode bandul sederhana bergantung pada:', 'options' => ['massa', 'panjang tali', 'amplitudo', 'warna bandul', 'bentuk bandul'], 'answer' => 'B', 'difficulty' => 'hard'],
      ],
    ];

    foreach ($exams as $i => $e) {
      $exam = Examination::create([
        ...$e,
        'subject_id' => $subjects[$i % $subjects->count()]->id,
        'teacher_id' => $teachers[$i % $teachers->count()]->id,
        'classroom_id' => $classrooms[0]->id, // All for X IPA 1
        'start_at' => now()->addHours(rand(1, 48)),
        'end_at' => now()->addHours(rand(49, 168)),
        'show_result' => $e['show_result'] ?? false,
        'allow_retry' => $e['allow_retry'] ?? false,
      ]);

      // Create questions for this exam
      foreach ($questionBanks[$i] as $j => $q) {
        Question::create([
          'examination_id' => $exam->id,
          'question_text' => $q['text'],
          'question_type' => 'multiple_choice',
          'options' => $q['options'],
          'correct_answer' => $q['answer'],
          'points' => $q['difficulty'] === 'easy' ? 2 : ($q['difficulty'] === 'hard' ? 4 : 3),
          'difficulty' => $q['difficulty'],
          'explanation' => 'Pembahasan soal nomor ' . ($j + 1),
        ]);
      }
    }
  }
}
