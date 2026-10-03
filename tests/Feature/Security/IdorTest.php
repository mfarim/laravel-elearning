<?php

namespace Tests\Feature\Security;

use App\Livewire\Student\Assignments;
use App\Livewire\Student\ExamStart;
use App\Livewire\Teacher\QuestionIndex;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\ExamAttempt;
use App\Models\Examination;
use App\Models\LearningMaterial;
use App\Models\Question;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IdorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'guru']);
        Role::firstOrCreate(['name' => 'siswa']);
    }

    protected function createTeacher(string $email = 'teacher@test.com'): array
    {
        $user = User::factory()->create(['email' => $email, 'is_active' => true]);
        $user->assignRole('guru');
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'nip' => fake()->unique()->numerify('##########'),
        ]);

        return [$user, $teacher];
    }

    protected function createClassroom(string $name = 'X-A', int $level = 10): Classroom
    {
        return Classroom::create([
            'name' => $name,
            'level' => $level,
            'academic_year' => '2026/2027',
        ]);
    }

    protected function createStudent(int $classroomId, string $email = 'student@test.com'): array
    {
        $user = User::factory()->create(['email' => $email, 'is_active' => true]);
        $user->assignRole('siswa');
        $student = Student::create([
            'user_id' => $user->id,
            'classroom_id' => $classroomId,
            'nis' => fake()->unique()->numerify('#####'),
            'gender' => 'L',
        ]);

        return [$user, $student];
    }

    public function test_teacher_cannot_monitor_another_teachers_exam(): void
    {
        [$t1User, $t1] = $this->createTeacher('t1@test.com');
        [$t2User, $t2] = $this->createTeacher('t2@test.com');

        $classroom = $this->createClassroom('X-A');
        $subject = Subject::create(['name' => 'Matematika', 'code' => 'MTK']);

        $exam = Examination::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'title' => 'Exam Guru 1',
            'type' => 'quiz',
            'duration_minutes' => 60,
            'passing_score' => 75,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
            'status' => 'published',
        ]);

        $this->actingAs($t2User)
            ->get(route('teacher.exams.monitor', $exam))
            ->assertForbidden();
    }

    public function test_teacher_cannot_grade_another_teachers_exam(): void
    {
        [$t1User, $t1] = $this->createTeacher('t1@test.com');
        [$t2User, $t2] = $this->createTeacher('t2@test.com');

        $classroom = $this->createClassroom('X-A');
        $subject = Subject::create(['name' => 'Matematika', 'code' => 'MTK']);
        [$sUser, $student] = $this->createStudent($classroom->id);

        $exam = Examination::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'title' => 'Exam Guru 1',
            'type' => 'quiz',
            'duration_minutes' => 60,
            'passing_score' => 75,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
            'status' => 'published',
        ]);

        $attempt = ExamAttempt::create([
            'examination_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'status' => 'completed',
        ]);

        $this->actingAs($t2User)
            ->get(route('teacher.exams.grade', [$exam, $attempt]))
            ->assertForbidden();
    }

    public function test_teacher_cannot_print_another_teachers_exam(): void
    {
        [$t1User, $t1] = $this->createTeacher('t1@test.com');
        [$t2User, $t2] = $this->createTeacher('t2@test.com');

        $classroom = $this->createClassroom('X-A');
        $subject = Subject::create(['name' => 'Matematika', 'code' => 'MTK']);

        $exam = Examination::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'title' => 'Exam Guru 1',
            'type' => 'quiz',
            'duration_minutes' => 60,
            'passing_score' => 75,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
            'status' => 'published',
        ]);

        $this->actingAs($t2User)
            ->get(route('teacher.exams.print', $exam))
            ->assertForbidden();
    }

    public function test_teacher_cannot_edit_or_delete_another_teachers_question(): void
    {
        [$t1User, $t1] = $this->createTeacher('t1@test.com');
        [$t2User, $t2] = $this->createTeacher('t2@test.com');

        $classroom = $this->createClassroom('X-A');
        $subject = Subject::create(['name' => 'Matematika', 'code' => 'MTK']);

        $exam = Examination::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'title' => 'Exam Guru 1',
            'type' => 'quiz',
            'duration_minutes' => 60,
            'passing_score' => 75,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
            'status' => 'published',
        ]);

        $question = Question::create([
            'examination_id' => $exam->id,
            'question_text' => '1 + 1 = ?',
            'question_type' => 'multiple_choice',
            'options' => ['1', '2', '3', '4'],
            'correct_answer' => 'B',
            'points' => 10,
            'difficulty' => 'easy',
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::actingAs($t2User, 'web')
            ->test(QuestionIndex::class)
            ->call('edit', $question->id);
    }

    public function test_teacher_cannot_delete_another_teachers_question(): void
    {
        [$t1User, $t1] = $this->createTeacher('t1@test.com');
        [$t2User, $t2] = $this->createTeacher('t2@test.com');

        $classroom = $this->createClassroom('X-A');
        $subject = Subject::create(['name' => 'Matematika', 'code' => 'MTK']);

        $exam = Examination::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'title' => 'Exam Guru 1',
            'type' => 'quiz',
            'duration_minutes' => 60,
            'passing_score' => 75,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
            'status' => 'published',
        ]);

        $question = Question::create([
            'examination_id' => $exam->id,
            'question_text' => '1 + 1 = ?',
            'question_type' => 'multiple_choice',
            'options' => ['1', '2', '3', '4'],
            'correct_answer' => 'B',
            'points' => 10,
            'difficulty' => 'easy',
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::actingAs($t2User, 'web')
            ->test(QuestionIndex::class)
            ->set('deletingId', $question->id)
            ->call('delete');
    }

    public function test_teacher_cannot_view_or_grade_another_teachers_assignment_submissions(): void
    {
        [$t1User, $t1] = $this->createTeacher('t1@test.com');
        [$t2User, $t2] = $this->createTeacher('t2@test.com');

        $classroom = $this->createClassroom('X-A');
        $subject = Subject::create(['name' => 'Matematika', 'code' => 'MTK']);

        $assignment = Assignment::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'title' => 'Tugas Guru 1',
            'max_score' => 100,
            'due_date' => now()->addWeek(),
            'status' => 'published',
        ]);

        $this->actingAs($t2User)
            ->get(route('teacher.assignments.submissions', $assignment->id))
            ->assertNotFound();
    }

    public function test_student_cannot_view_unpublished_material_or_material_from_another_classroom(): void
    {
        [$t1User, $t1] = $this->createTeacher('t1@test.com');

        $classA = $this->createClassroom('X-A');
        $classB = $this->createClassroom('X-B');
        $subject = Subject::create(['name' => 'Fisika', 'code' => 'FSK']);

        [$sUser, $student] = $this->createStudent($classA->id, 'student1@test.com');

        // Material for Class B
        $matClassB = LearningMaterial::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classB->id,
            'subject_id' => $subject->id,
            'title' => 'Materi Kelas B',
            'type' => 'document',
            'is_published' => true,
        ]);

        // Draft Material for Class A
        $matDraft = LearningMaterial::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classA->id,
            'subject_id' => $subject->id,
            'title' => 'Materi Draft Kelas A',
            'type' => 'document',
            'is_published' => false,
        ]);

        $this->actingAs($sUser)
            ->get(route('student.materials.detail', $matClassB->id))
            ->assertForbidden();

        $this->actingAs($sUser)
            ->get(route('student.materials.detail', $matDraft->id))
            ->assertForbidden();
    }

    public function test_student_cannot_submit_assignment_of_another_classroom_or_after_due_date(): void
    {
        [$t1User, $t1] = $this->createTeacher('t1@test.com');

        $classA = $this->createClassroom('X-A');
        $classB = $this->createClassroom('X-B');
        $subject = Subject::create(['name' => 'Biologi', 'code' => 'BIO']);

        [$sUser, $student] = $this->createStudent($classA->id, 'student1@test.com');

        $assignClassB = Assignment::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classB->id,
            'subject_id' => $subject->id,
            'title' => 'Tugas Kelas B',
            'max_score' => 100,
            'due_date' => now()->addWeek(),
            'status' => 'published',
        ]);

        $assignExpired = Assignment::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classA->id,
            'subject_id' => $subject->id,
            'title' => 'Tugas Kedaluwarsa',
            'max_score' => 100,
            'due_date' => now()->subDay(),
            'allow_late_submission' => false,
            'status' => 'published',
        ]);

        Livewire::actingAs($sUser, 'web')
            ->test(Assignments::class)
            ->call('openSubmit', $assignClassB->id)
            ->assertForbidden();

        Livewire::actingAs($sUser, 'web')
            ->test(Assignments::class)
            ->call('openSubmit', $assignExpired->id)
            ->assertForbidden();
    }

    public function test_student_cannot_save_answers_to_questions_from_different_exam(): void
    {
        [$t1User, $t1] = $this->createTeacher('t1@test.com');

        $classA = $this->createClassroom('X-A');
        $subject = Subject::create(['name' => 'Kimia', 'code' => 'KIM']);

        [$sUser, $student] = $this->createStudent($classA->id, 'student1@test.com');

        $exam1 = Examination::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classA->id,
            'subject_id' => $subject->id,
            'title' => 'Kimia Exam 1',
            'type' => 'quiz',
            'duration_minutes' => 60,
            'passing_score' => 75,
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'status' => 'published',
        ]);

        $exam2 = Examination::create([
            'teacher_id' => $t1->id,
            'classroom_id' => $classA->id,
            'subject_id' => $subject->id,
            'title' => 'Kimia Exam 2',
            'type' => 'quiz',
            'duration_minutes' => 60,
            'passing_score' => 75,
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'status' => 'published',
        ]);

        $qExam2 = Question::create([
            'examination_id' => $exam2->id,
            'question_text' => 'Soal dari Exam 2',
            'question_type' => 'multiple_choice',
            'options' => ['A', 'B'],
            'correct_answer' => 'A',
            'points' => 10,
            'difficulty' => 'easy',
        ]);

        $attempt = ExamAttempt::create([
            'examination_id' => $exam1->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        // Attempting to submit answer to question from exam2 while taking exam1
        Livewire::actingAs($sUser, 'web')
            ->test(ExamStart::class, ['examination' => $exam1])
            ->call('saveAnswer', $qExam2->id, 'A');

        $this->assertDatabaseMissing('exam_answers', [
            'exam_attempt_id' => $attempt->id,
            'question_id' => $qExam2->id,
        ]);
    }

    public function test_admin_cannot_impersonate_another_admin(): void
    {
        $admin1 = User::factory()->create(['email' => 'admin1@test.com', 'is_active' => true]);
        $admin1->assignRole('admin');

        $admin2 = User::factory()->create(['email' => 'admin2@test.com', 'is_active' => true]);
        $admin2->assignRole('admin');

        $this->actingAs($admin1)
            ->post(route('admin.impersonate.start', $admin2->id))
            ->assertForbidden();
    }
}
