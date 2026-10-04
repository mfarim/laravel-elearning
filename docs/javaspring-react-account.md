Ran command: `mkdir -p ../java-spring-react-elearning/backend/src/main/resources/db/migration && ls -la ../java-spring-react-elearning/backend/src/main/resources/db/migration`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/resources/db/migration/V1__initial_schema.sql
-- =============================================================================
-- V1: Initial Database Schema for E-Learning LMS & CBT
-- =============================================================================

-- 1. Roles & Users
CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (name) VALUES ('ROLE_ADMIN'), ('ROLE_TEACHER'), ('ROLE_STUDENT');

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE user_roles (
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, role_id)
);

-- Default Super Admin (password: password)
INSERT INTO users (name, email, password, is_active)
VALUES ('Administrator', 'admin@elearning.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

INSERT INTO user_roles (user_id, role_id)
VALUES (1, (SELECT id FROM roles WHERE name = 'ROLE_ADMIN'));

-- 2. Classrooms
CREATE TABLE classrooms (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    level INT NOT NULL,
    capacity INT NOT NULL DEFAULT 30,
    academic_year VARCHAR(20) NOT NULL,
    homeroom_teacher_id BIGINT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- 3. Teachers & Students Profiles
CREATE TABLE teachers (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    nip VARCHAR(50) NOT NULL UNIQUE,
    address TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Add Foreign Key for classroom homeroom teacher
ALTER TABLE classrooms
    ADD CONSTRAINT fk_classrooms_homeroom_teacher
    FOREIGN KEY (homeroom_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL;

CREATE TABLE students (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    classroom_id BIGINT REFERENCES classrooms(id) ON DELETE SET NULL,
    nis VARCHAR(50) NOT NULL UNIQUE,
    nisn VARCHAR(50),
    birth_date DATE,
    gender VARCHAR(1) NOT NULL CHECK (gender IN ('L', 'P')),
    address TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- 4. Subjects
CREATE TABLE subjects (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    teacher_id BIGINT REFERENCES teachers(id) ON DELETE SET NULL,
    credits INT NOT NULL DEFAULT 2,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- 5. Learning Materials & Views
CREATE TABLE learning_materials (
    id BIGSERIAL PRIMARY KEY,
    teacher_id BIGINT NOT NULL REFERENCES teachers(id) ON DELETE CASCADE,
    subject_id BIGINT NOT NULL REFERENCES subjects(id) ON DELETE CASCADE,
    classroom_id BIGINT REFERENCES classrooms(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(50) NOT NULL CHECK (type IN ('document', 'video', 'text', 'link', 'audio')),
    content TEXT,
    file_url TEXT,
    file_path VARCHAR(255),
    is_published BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE material_views (
    id BIGSERIAL PRIMARY KEY,
    learning_material_id BIGINT NOT NULL REFERENCES learning_materials(id) ON DELETE CASCADE,
    student_id BIGINT NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_material_student UNIQUE (learning_material_id, student_id)
);

-- 6. Assignments, Submissions & Discussions
CREATE TABLE assignments (
    id BIGSERIAL PRIMARY KEY,
    teacher_id BIGINT NOT NULL REFERENCES teachers(id) ON DELETE CASCADE,
    subject_id BIGINT NOT NULL REFERENCES subjects(id) ON DELETE CASCADE,
    classroom_id BIGINT NOT NULL REFERENCES classrooms(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    instructions TEXT,
    max_score INT NOT NULL DEFAULT 100,
    due_date TIMESTAMP NOT NULL,
    allow_late_submission BOOLEAN NOT NULL DEFAULT FALSE,
    status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'closed')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE assignment_submissions (
    id BIGSERIAL PRIMARY KEY,
    assignment_id BIGINT NOT NULL REFERENCES assignments(id) ON DELETE CASCADE,
    student_id BIGINT NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    file_path VARCHAR(255),
    notes TEXT,
    score INT,
    feedback TEXT,
    status VARCHAR(20) NOT NULL DEFAULT 'submitted' CHECK (status IN ('submitted', 'graded', 'late')),
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    graded_at TIMESTAMP,
    CONSTRAINT uq_assignment_student UNIQUE (assignment_id, student_id)
);

CREATE TABLE assignment_discussions (
    id BIGSERIAL PRIMARY KEY,
    assignment_id BIGINT NOT NULL REFERENCES assignments(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    parent_id BIGINT REFERENCES assignment_discussions(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 7. CBT Examinations, Questions, Attempts & Answers
CREATE TABLE examinations (
    id BIGSERIAL PRIMARY KEY,
    teacher_id BIGINT NOT NULL REFERENCES teachers(id) ON DELETE CASCADE,
    subject_id BIGINT NOT NULL REFERENCES subjects(id) ON DELETE CASCADE,
    classroom_id BIGINT NOT NULL REFERENCES classrooms(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(50) NOT NULL DEFAULT 'quiz' CHECK (type IN ('quiz', 'uts', 'uas', 'praktik', 'tryout')),
    duration_minutes INT NOT NULL DEFAULT 60,
    passing_score INT NOT NULL DEFAULT 75,
    start_at TIMESTAMP NOT NULL,
    end_at TIMESTAMP NOT NULL,
    exam_date DATE,
    total_questions INT NOT NULL DEFAULT 0,
    shuffle_questions BOOLEAN NOT NULL DEFAULT FALSE,
    shuffle_options BOOLEAN NOT NULL DEFAULT FALSE,
    show_result BOOLEAN NOT NULL DEFAULT FALSE,
    allow_retry BOOLEAN NOT NULL DEFAULT FALSE,
    status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'closed')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE questions (
    id BIGSERIAL PRIMARY KEY,
    examination_id BIGINT NOT NULL REFERENCES examinations(id) ON DELETE CASCADE,
    question_text TEXT NOT NULL,
    question_type VARCHAR(50) NOT NULL CHECK (question_type IN ('multiple_choice', 'essay', 'short_answer')),
    options JSONB,
    correct_answer VARCHAR(255),
    audio_path VARCHAR(255),
    image_path VARCHAR(255),
    explanation TEXT,
    points INT NOT NULL DEFAULT 1,
    difficulty VARCHAR(20) NOT NULL DEFAULT 'medium' CHECK (difficulty IN ('easy', 'medium', 'hard')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE exam_attempts (
    id BIGSERIAL PRIMARY KEY,
    examination_id BIGINT NOT NULL REFERENCES examinations(id) ON DELETE CASCADE,
    student_id BIGINT NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    attempt_number INT NOT NULL DEFAULT 1,
    score INT,
    is_passed BOOLEAN,
    violations INT NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'in_progress' CHECK (status IN ('in_progress', 'needs_grading', 'completed', 'force_finished')),
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    finished_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE exam_answers (
    id BIGSERIAL PRIMARY KEY,
    exam_attempt_id BIGINT NOT NULL REFERENCES exam_attempts(id) ON DELETE CASCADE,
    question_id BIGINT NOT NULL REFERENCES questions(id) ON DELETE CASCADE,
    answer_text TEXT,
    points_earned INT NOT NULL DEFAULT 0,
    is_correct BOOLEAN,
    feedback TEXT,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_attempt_question UNIQUE (exam_attempt_id, question_id)
);

-- 8. Announcements & Security Logs
CREATE TABLE announcements (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    target VARCHAR(20) NOT NULL DEFAULT 'all' CHECK (target IN ('all', 'teacher', 'student')),
    is_published BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE blocked_ips (
    id BIGSERIAL PRIMARY KEY,
    ip_address VARCHAR(50) NOT NULL UNIQUE,
    reason VARCHAR(255),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    expires_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE blocked_users (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    reason VARCHAR(255),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    expires_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for Optimal Query Performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_students_classroom ON students(classroom_id);
CREATE INDEX idx_teachers_user ON teachers(user_id);
CREATE INDEX idx_examinations_classroom ON examinations(classroom_id);
CREATE INDEX idx_examinations_teacher ON examinations(teacher_id);
CREATE INDEX idx_questions_exam ON questions(examination_id);
CREATE INDEX idx_exam_attempts_exam ON exam_attempts(examination_id);
CREATE INDEX idx_exam_attempts_student ON exam_attempts(student_id);
CREATE INDEX idx_assignments_classroom ON assignments(classroom_id);
CREATE INDEX idx_assignments_teacher ON assignments(teacher_id);
CREATE INDEX idx_learning_materials_classroom ON learning_materials(classroom_id);
EOF`
Ran command: `mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/entity \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/enums \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/exception \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/response \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/config \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/security \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/entity \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/repository \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/entity \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/repository \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/entity \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/repository \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/entity \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/repository \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/entity \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/repository \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/auth/dto \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/auth/service \
  ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/auth/controller`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/entity/BaseEntity.java
package com.elearning.common.entity;

import jakarta.persistence.*;
import lombok.Getter;
import lombok.Setter;

import java.time.LocalDateTime;

@Getter
@Setter
@MappedSuperclass
public abstract class BaseEntity {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "created_at", nullable = false, updatable = false)
    private LocalDateTime createdAt;

    @Column(name = "updated_at", nullable = false)
    private LocalDateTime updatedAt;

    @Column(name = "deleted_at")
    private LocalDateTime deletedAt;

    @PrePersist
    protected void onCreate() {
        this.createdAt = LocalDateTime.now();
        this.updatedAt = LocalDateTime.now();
    }

    @PreUpdate
    protected void onUpdate() {
        this.updatedAt = LocalDateTime.now();
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/response/ApiResponse.java
package com.elearning.common.response;

import com.fasterxml.jackson.annotation.JsonInclude;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
@JsonInclude(JsonInclude.Include.NON_NULL)
public class ApiResponse<T> {

    @Builder.Default
    private boolean success = true;

    private String message;

    private T data;

    @Builder.Default
    private LocalDateTime timestamp = LocalDateTime.now();

    public static <T> ApiResponse<T> ok(T data, String message) {
        return ApiResponse.<T>builder()
                .success(true)
                .message(message)
                .data(data)
                .build();
    }

    public static <T> ApiResponse<T> ok(T data) {
        return ok(data, "Success");
    }

    public static <T> ApiResponse<T> error(String message) {
        return ApiResponse.<T>builder()
                .success(false)
                .message(message)
                .build();
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/enums/RoleType.java
package com.elearning.common.enums;

public enum RoleType {
    ROLE_ADMIN,
    ROLE_TEACHER,
    ROLE_STUDENT
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/enums/Gender.java
package com.elearning.common.enums;

public enum Gender {
    L,
    P
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/enums/ExamType.java
package com.elearning.common.enums;

public enum ExamType {
    quiz,
    uts,
    uas,
    praktik,
    tryout
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/enums/QuestionType.java
package com.elearning.common.enums;

public enum QuestionType {
    multiple_choice,
    essay,
    short_answer
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/enums/AttemptStatus.java
package com.elearning.common.enums;

public enum AttemptStatus {
    in_progress,
    needs_grading,
    completed,
    force_finished
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/enums/Status.java
package com.elearning.common.enums;

public enum Status {
    draft,
    published,
    closed
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/entity/Role.java
package com.elearning.modules.user.entity;

import com.elearning.common.enums.RoleType;
import jakarta.persistence.*;
import lombok.*;

@Entity
@Table(name = "roles")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Role {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Enumerated(EnumType.STRING)
    @Column(length = 50, nullable = false, unique = true)
    private RoleType name;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/entity/User.java
package com.elearning.modules.user.entity;

import com.elearning.common.entity.BaseEntity;
import jakarta.persistence.*;
import lombok.*;

import java.util.HashSet;
import java.util.Set;

@Entity
@Table(name = "users")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class User extends BaseEntity {

    @Column(nullable = false)
    private String name;

    @Column(nullable = false, unique = true)
    private String email;

    @Column(nullable = false)
    private String password;

    private String phone;

    @Builder.Default
    @Column(name = "is_active", nullable = false)
    private boolean active = true;

    @ManyToMany(fetch = FetchType.EAGER)
    @JoinTable(
        name = "user_roles",
        joinColumns = @JoinColumn(name = "user_id"),
        inverseJoinColumns = @JoinColumn(name = "role_id")
    )
    @Builder.Default
    private Set<Role> roles = new HashSet<>();
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/entity/Teacher.java
package com.elearning.modules.user.entity;

import com.elearning.common.entity.BaseEntity;
import jakarta.persistence.*;
import lombok.*;

@Entity
@Table(name = "teachers")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Teacher extends BaseEntity {

    @OneToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false, unique = true)
    private User user;

    @Column(nullable = false, unique = true, length = 50)
    private String nip;

    @Column(columnDefinition = "TEXT")
    private String address;

    private String photo;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/entity/Classroom.java
package com.elearning.modules.academic.entity;

import com.elearning.common.entity.BaseEntity;
import com.elearning.modules.user.entity.Teacher;
import jakarta.persistence.*;
import lombok.*;

@Entity
@Table(name = "classrooms")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Classroom extends BaseEntity {

    @Column(nullable = false, length = 100)
    private String name;

    @Column(nullable = false)
    private Integer level;

    @Column(nullable = false)
    @Builder.Default
    private Integer capacity = 30;

    @Column(name = "academic_year", nullable = false, length = 20)
    private String academicYear;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "homeroom_teacher_id")
    private Teacher homeroomTeacher;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/entity/Student.java
package com.elearning.modules.user.entity;

import com.elearning.common.entity.BaseEntity;
import com.elearning.common.enums.Gender;
import com.elearning.modules.academic.entity.Classroom;
import jakarta.persistence.*;
import lombok.*;

import java.time.LocalDate;

@Entity
@Table(name = "students")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Student extends BaseEntity {

    @OneToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false, unique = true)
    private User user;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "classroom_id")
    private Classroom classroom;

    @Column(nullable = false, unique = true, length = 50)
    private String nis;

    @Column(length = 50)
    private String nisn;

    @Column(name = "birth_date")
    private LocalDate birthDate;

    @Enumerated(EnumType.STRING)
    @Column(length = 1, nullable = false)
    private Gender gender;

    @Column(columnDefinition = "TEXT")
    private String address;

    private String photo;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/entity/Subject.java
package com.elearning.modules.academic.entity;

import com.elearning.common.entity.BaseEntity;
import com.elearning.modules.user.entity.Teacher;
import jakarta.persistence.*;
import lombok.*;

@Entity
@Table(name = "subjects")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Subject extends BaseEntity {

    @Column(nullable = false)
    private String name;

    @Column(nullable = false, unique = true, length = 50)
    private String code;

    @Column(columnDefinition = "TEXT")
    private String description;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "teacher_id")
    private Teacher teacher;

    @Column(nullable = false)
    @Builder.Default
    private Integer credits = 2;
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/entity/Examination.java
package com.elearning.modules.exam.entity;

import com.elearning.common.entity.BaseEntity;
import com.elearning.common.enums.ExamType;
import com.elearning.common.enums.Status;
import com.elearning.modules.academic.entity.Classroom;
import com.elearning.modules.academic.entity.Subject;
import com.elearning.modules.user.entity.Teacher;
import jakarta.persistence.*;
import lombok.*;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

@Entity
@Table(name = "examinations")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Examination extends BaseEntity {

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "teacher_id", nullable = false)
    private Teacher teacher;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "subject_id", nullable = false)
    private Subject subject;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "classroom_id", nullable = false)
    private Classroom classroom;

    @Column(nullable = false)
    private String title;

    @Column(columnDefinition = "TEXT")
    private String description;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false, length = 50)
    @Builder.Default
    private ExamType type = ExamType.quiz;

    @Column(name = "duration_minutes", nullable = false)
    @Builder.Default
    private Integer durationMinutes = 60;

    @Column(name = "passing_score", nullable = false)
    @Builder.Default
    private Integer passingScore = 75;

    @Column(name = "start_at", nullable = false)
    private LocalDateTime startAt;

    @Column(name = "end_at", nullable = false)
    private LocalDateTime endAt;

    @Column(name = "exam_date")
    private LocalDate examDate;

    @Column(name = "total_questions", nullable = false)
    @Builder.Default
    private Integer totalQuestions = 0;

    @Column(name = "shuffle_questions", nullable = false)
    @Builder.Default
    private boolean shuffleQuestions = false;

    @Column(name = "shuffle_options", nullable = false)
    @Builder.Default
    private boolean shuffleOptions = false;

    @Column(name = "show_result", nullable = false)
    @Builder.Default
    private boolean showResult = false;

    @Column(name = "allow_retry", nullable = false)
    @Builder.Default
    private boolean allowRetry = false;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false, length = 20)
    @Builder.Default
    private Status status = Status.draft;

    @OneToMany(mappedBy = "examination", cascade = CascadeType.ALL, orphanRemoval = true)
    @Builder.Default
    private List<Question> questions = new ArrayList<>();
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/entity/Question.java
package com.elearning.modules.exam.entity;

import com.elearning.common.entity.BaseEntity;
import com.elearning.common.enums.Difficulty;
import com.elearning.common.enums.QuestionType;
import com.fasterxml.jackson.annotation.JsonIgnore;
import jakarta.persistence.*;
import lombok.*;
import org.hibernate.annotations.JdbcTypeCode;
import org.hibernate.type.SqlTypes;

import java.util.List;

@Entity
@Table(name = "questions")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Question extends BaseEntity {

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "examination_id", nullable = false)
    private Examination examination;

    @Column(name = "question_text", nullable = false, columnDefinition = "TEXT")
    private String questionText;

    @Enumerated(EnumType.STRING)
    @Column(name = "question_type", nullable = false, length = 50)
    private QuestionType questionType;

    @JdbcTypeCode(SqlTypes.JSON)
    @Column(columnDefinition = "jsonb")
    private List<String> options;

    @JsonIgnore
    @Column(name = "correct_answer")
    private String correctAnswer;

    @Column(name = "audio_path")
    private String audioPath;

    @Column(name = "image_path")
    private String imagePath;

    @JsonIgnore
    @Column(columnDefinition = "TEXT")
    private String explanation;

    @Column(nullable = false)
    @Builder.Default
    private Integer points = 1;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false, length = 20)
    @Builder.Default
    private Difficulty difficulty = Difficulty.medium;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/entity/ExamAttempt.java
package com.elearning.modules.exam.entity;

import com.elearning.common.enums.AttemptStatus;
import com.elearning.modules.user.entity.Student;
import jakarta.persistence.*;
import lombok.*;

import java.time.LocalDateTime;

@Entity
@Table(name = "exam_attempts")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class ExamAttempt {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "examination_id", nullable = false)
    private Examination examination;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "student_id", nullable = false)
    private Student student;

    @Column(name = "attempt_number", nullable = false)
    @Builder.Default
    private Integer attemptNumber = 1;

    private Integer score;

    @Column(name = "is_passed")
    private Boolean passed;

    @Column(nullable = false)
    @Builder.Default
    private Integer violations = 0;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false, length = 20)
    @Builder.Default
    private AttemptStatus status = AttemptStatus.in_progress;

    @Column(name = "started_at", nullable = false)
    private LocalDateTime startedAt;

    @Column(name = "finished_at")
    private LocalDateTime finishedAt;

    @Column(name = "created_at", nullable = false, updatable = false)
    @Builder.Default
    private LocalDateTime createdAt = LocalDateTime.now();

    @Column(name = "updated_at", nullable = false)
    @Builder.Default
    private LocalDateTime updatedAt = LocalDateTime.now();

    @PrePersist
    protected void onCreate() {
        this.createdAt = LocalDateTime.now();
        this.updatedAt = LocalDateTime.now();
    }

    @PreUpdate
    protected void onUpdate() {
        this.updatedAt = LocalDateTime.now();
    }
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/entity/ExamAnswer.java
package com.elearning.modules.exam.entity;

import jakarta.persistence.*;
import lombok.*;

import java.time.LocalDateTime;

@Entity
@Table(name = "exam_answers", uniqueConstraints = {
    @UniqueConstraint(columnNames = {"exam_attempt_id", "question_id"})
})
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class ExamAnswer {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "exam_attempt_id", nullable = false)
    private ExamAttempt examAttempt;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "question_id", nullable = false)
    private Question question;

    @Column(name = "answer_text", columnDefinition = "TEXT")
    private String answerText;

    @Column(name = "points_earned", nullable = false)
    @Builder.Default
    private Integer pointsEarned = 0;

    @Column(name = "is_correct")
    private Boolean correct;

    @Column(columnDefinition = "TEXT")
    private String feedback;

    @Column(name = "answered_at")
    @Builder.Default
    private LocalDateTime answeredAt = LocalDateTime.now();
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/entity/Assignment.java
package com.elearning.modules.assignment.entity;

import com.elearning.common.entity.BaseEntity;
import com.elearning.common.enums.Status;
import com.elearning.modules.academic.entity.Classroom;
import com.elearning.modules.academic.entity.Subject;
import com.elearning.modules.user.entity.Teacher;
import jakarta.persistence.*;
import lombok.*;

import java.time.LocalDateTime;

@Entity
@Table(name = "assignments")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Assignment extends BaseEntity {

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "teacher_id", nullable = false)
    private Teacher teacher;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "subject_id", nullable = false)
    private Subject subject;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "classroom_id", nullable = false)
    private Classroom classroom;

    @Column(nullable = false)
    private String title;

    @Column(columnDefinition = "TEXT")
    private String description;

    @Column(columnDefinition = "TEXT")
    private String instructions;

    @Column(name = "max_score", nullable = false)
    @Builder.Default
    private Integer maxScore = 100;

    @Column(name = "due_date", nullable = false)
    private LocalDateTime dueDate;

    @Column(name = "allow_late_submission", nullable = false)
    @Builder.Default
    private boolean allowLateSubmission = false;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false, length = 20)
    @Builder.Default
    private Status status = Status.draft;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/entity/AssignmentSubmission.java
package com.elearning.modules.assignment.entity;

import com.elearning.modules.user.entity.Student;
import jakarta.persistence.*;
import lombok.*;

import java.time.LocalDateTime;

@Entity
@Table(name = "assignment_submissions", uniqueConstraints = {
    @UniqueConstraint(columnNames = {"assignment_id", "student_id"})
})
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class AssignmentSubmission {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "assignment_id", nullable = false)
    private Assignment assignment;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "student_id", nullable = false)
    private Student student;

    @Column(name = "file_path")
    private String filePath;

    @Column(columnDefinition = "TEXT")
    private String notes;

    private Integer score;

    @Column(columnDefinition = "TEXT")
    private String feedback;

    @Column(nullable = false, length = 20)
    @Builder.Default
    private String status = "submitted";

    @Column(name = "submitted_at", nullable = false)
    @Builder.Default
    private LocalDateTime submittedAt = LocalDateTime.now();

    @Column(name = "graded_at")
    private LocalDateTime gradedAt;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/entity/AssignmentDiscussion.java
package com.elearning.modules.assignment.entity;

import com.elearning.modules.user.entity.User;
import jakarta.persistence.*;
import lombok.*;

import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

@Entity
@Table(name = "assignment_discussions")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class AssignmentDiscussion {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "assignment_id", nullable = false)
    private Assignment assignment;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    private User user;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "parent_id")
    private AssignmentDiscussion parent;

    @OneToMany(mappedBy = "parent", cascade = CascadeType.ALL, orphanRemoval = true)
    @Builder.Default
    private List<AssignmentDiscussion> replies = new ArrayList<>();

    @Column(nullable = false, columnDefinition = "TEXT")
    private String message;

    @Column(name = "created_at", nullable = false, updatable = false)
    @Builder.Default
    private LocalDateTime createdAt = LocalDateTime.now();

    @Column(name = "updated_at", nullable = false)
    @Builder.Default
    private LocalDateTime updatedAt = LocalDateTime.now();

    @PrePersist
    protected void onCreate() {
        this.createdAt = LocalDateTime.now();
        this.updatedAt = LocalDateTime.now();
    }

    @PreUpdate
    protected void onUpdate() {
        this.updatedAt = LocalDateTime.now();
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/entity/LearningMaterial.java
package com.elearning.modules.material.entity;

import com.elearning.common.entity.BaseEntity;
import com.elearning.modules.academic.entity.Classroom;
import com.elearning.modules.academic.entity.Subject;
import com.elearning.modules.user.entity.Teacher;
import jakarta.persistence.*;
import lombok.*;

@Entity
@Table(name = "learning_materials")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class LearningMaterial extends BaseEntity {

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "teacher_id", nullable = false)
    private Teacher teacher;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "subject_id", nullable = false)
    private Subject subject;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "classroom_id")
    private Classroom classroom;

    @Column(nullable = false)
    private String title;

    @Column(columnDefinition = "TEXT")
    private String description;

    @Column(nullable = false, length = 50)
    private String type;

    @Column(columnDefinition = "TEXT")
    private String content;

    @Column(name = "file_url", columnDefinition = "TEXT")
    private String fileUrl;

    @Column(name = "file_path")
    private String filePath;

    @Column(name = "is_published", nullable = false)
    @Builder.Default
    private boolean published = false;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/entity/MaterialView.java
package com.elearning.modules.material.entity;

import com.elearning.modules.user.entity.Student;
import jakarta.persistence.*;
import lombok.*;

import java.time.LocalDateTime;

@Entity
@Table(name = "material_views", uniqueConstraints = {
    @UniqueConstraint(columnNames = {"learning_material_id", "student_id"})
})
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class MaterialView {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "learning_material_id", nullable = false)
    private LearningMaterial learningMaterial;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "student_id", nullable = false)
    private Student student;

    @Column(name = "viewed_at", nullable = false)
    @Builder.Default
    private LocalDateTime viewedAt = LocalDateTime.now();
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/repository/UserRepository.java
package com.elearning.modules.user.repository;

import com.elearning.modules.user.entity.User;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.Optional;

@Repository
public interface UserRepository extends JpaRepository<User, Long> {
    Optional<User> findByEmail(String email);
    boolean existsByEmail(String email);
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/repository/RoleRepository.java
package com.elearning.modules.user.repository;

import com.elearning.common.enums.RoleType;
import com.elearning.modules.user.entity.Role;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.Optional;

@Repository
public interface RoleRepository extends JpaRepository<Role, Long> {
    Optional<Role> findByName(RoleType name);
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/repository/TeacherRepository.java
package com.elearning.modules.user.repository;

import com.elearning.modules.user.entity.Teacher;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.Optional;

@Repository
public interface TeacherRepository extends JpaRepository<Teacher, Long> {
    Optional<Teacher> findByUserId(Long userId);
    boolean existsByNip(String nip);
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/repository/StudentRepository.java
package com.elearning.modules.user.repository;

import com.elearning.modules.user.entity.Student;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface StudentRepository extends JpaRepository<Student, Long> {
    Optional<Student> findByUserId(Long userId);
    List<Student> findByClassroomId(Long classroomId);
    boolean existsByNis(String nis);
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/repository/ClassroomRepository.java
package com.elearning.modules.academic.repository;

import com.elearning.modules.academic.entity.Classroom;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface ClassroomRepository extends JpaRepository<Classroom, Long> {
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/repository/SubjectRepository.java
package com.elearning.modules.academic.repository;

import com.elearning.modules.academic.entity.Subject;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.Optional;

@Repository
public interface SubjectRepository extends JpaRepository<Subject, Long> {
    Optional<Subject> findByCode(String code);
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/repository/ExaminationRepository.java
package com.elearning.modules.exam.repository;

import com.elearning.common.enums.Status;
import com.elearning.modules.exam.entity.Examination;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface ExaminationRepository extends JpaRepository<Examination, Long> {
    List<Examination> findByTeacherId(Long teacherId);
    List<Examination> findByClassroomIdAndStatus(Long classroomId, Status status);
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/repository/QuestionRepository.java
package com.elearning.modules.exam.repository;

import com.elearning.modules.exam.entity.Question;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface QuestionRepository extends JpaRepository<Question, Long> {
    List<Question> findByExaminationIdOrderByIdAsc(Long examinationId);
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/repository/ExamAttemptRepository.java
package com.elearning.modules.exam.repository;

import com.elearning.modules.exam.entity.ExamAttempt;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface ExamAttemptRepository extends JpaRepository<ExamAttempt, Long> {
    Optional<ExamAttempt> findByExaminationIdAndStudentId(Long examinationId, Long studentId);
    List<ExamAttempt> findByExaminationId(Long examinationId);
    List<ExamAttempt> findByStudentId(Long studentId);
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/repository/ExamAnswerRepository.java
package com.elearning.modules.exam.repository;

import com.elearning.modules.exam.entity.ExamAnswer;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface ExamAnswerRepository extends JpaRepository<ExamAnswer, Long> {
    List<ExamAnswer> findByExamAttemptId(Long examAttemptId);
    Optional<ExamAnswer> findByExamAttemptIdAndQuestionId(Long examAttemptId, Long questionId);
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/repository/AssignmentRepository.java
package com.elearning.modules.assignment.repository;

import com.elearning.common.enums.Status;
import com.elearning.modules.assignment.entity.Assignment;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface AssignmentRepository extends JpaRepository<Assignment, Long> {
    List<Assignment> findByTeacherId(Long teacherId);
    List<Assignment> findByClassroomIdAndStatus(Long classroomId, Status status);
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/repository/AssignmentSubmissionRepository.java
package com.elearning.modules.assignment.repository;

import com.elearning.modules.assignment.entity.AssignmentSubmission;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface AssignmentSubmissionRepository extends JpaRepository<AssignmentSubmission, Long> {
    Optional<AssignmentSubmission> findByAssignmentIdAndStudentId(Long assignmentId, Long studentId);
    List<AssignmentSubmission> findByAssignmentId(Long assignmentId);
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/repository/LearningMaterialRepository.java
package com.elearning.modules.material.repository;

import com.elearning.modules.material.entity.LearningMaterial;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface LearningMaterialRepository extends JpaRepository<LearningMaterial, Long> {
    List<LearningMaterial> findByTeacherId(Long teacherId);
    List<LearningMaterial> findByPublishedTrueAndClassroomId(Long classroomId);
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/security/UserPrincipal.java
package com.elearning.security;

import com.elearning.modules.user.entity.User;
import lombok.AllArgsConstructor;
import lombok.Getter;
import org.springframework.security.core.GrantedAuthority;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.security.core.userdetails.UserDetails;

import java.util.Collection;
import java.util.stream.Collectors;

@Getter
@AllArgsConstructor
public class UserPrincipal implements UserDetails {

    private final Long id;
    private final String name;
    private final String email;
    private final String password;
    private final boolean active;
    private final Collection<? extends GrantedAuthority> authorities;
    private final Long impersonatorAdminId;

    public static UserPrincipal create(User user, Long impersonatorAdminId) {
        Collection<GrantedAuthority> authorities = user.getRoles().stream()
                .map(role -> new SimpleGrantedAuthority(role.getName().name()))
                .collect(Collectors.toList());

        return new UserPrincipal(
                user.getId(),
                user.getName(),
                user.getEmail(),
                user.getPassword(),
                user.isActive(),
                authorities,
                impersonatorAdminId
        );
    }

    public static UserPrincipal create(User user) {
        return create(user, null);
    }

    @Override
    public String getUsername() {
        return email;
    }

    @Override
    public boolean isAccountNonExpired() {
        return true;
    }

    @Override
    public boolean isAccountNonLocked() {
        return active;
    }

    @Override
    public boolean isCredentialsNonExpired() {
        return true;
    }

    @Override
    public boolean isEnabled() {
        return active;
    }
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/security/CustomUserDetailsService.java
package com.elearning.security;

import com.elearning.modules.user.entity.User;
import com.elearning.modules.user.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.core.userdetails.UserDetailsService;
import org.springframework.security.core.userdetails.UsernameNotFoundException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
@RequiredArgsConstructor
public class CustomUserDetailsService implements UserDetailsService {

    private final UserRepository userRepository;

    @Override
    @Transactional(readOnly = true)
    public UserDetails loadUserByUsername(String email) throws UsernameNotFoundException {
        User user = userRepository.findByEmail(email)
                .orElseThrow(() -> new UsernameNotFoundException("User not found with email: " + email));

        return UserPrincipal.create(user);
    }

    @Transactional(readOnly = true)
    public UserDetails loadUserById(Long id, Long impersonatorAdminId) {
        User user = userRepository.findById(id)
                .orElseThrow(() -> new UsernameNotFoundException("User not found with id: " + id));

        return UserPrincipal.create(user, impersonatorAdminId);
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/security/JwtProvider.java
package com.elearning.security;

import io.jsonwebtoken.Claims;
import io.jsonwebtoken.JwtException;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.io.Decoders;
import io.jsonwebtoken.security.Keys;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.security.core.GrantedAuthority;
import org.springframework.stereotype.Component;

import javax.crypto.SecretKey;
import java.util.Date;
import java.util.List;
import java.util.stream.Collectors;

@Component
public class JwtProvider {

    private final SecretKey secretKey;
    private final long accessTokenExpirationMs;
    private final long refreshTokenExpirationMs;

    public JwtProvider(
            @Value("${app.jwt.secret}") String secret,
            @Value("${app.jwt.access-token-expiration-ms:900000}") long accessTokenExpirationMs,
            @Value("${app.jwt.refresh-token-expiration-ms:604800000}") long refreshTokenExpirationMs
    ) {
        byte[] keyBytes = Decoders.BASE64.decode(secret);
        this.secretKey = Keys.hmacShaKeyFor(keyBytes);
        this.accessTokenExpirationMs = accessTokenExpirationMs;
        this.refreshTokenExpirationMs = refreshTokenExpirationMs;
    }

    public String generateAccessToken(UserPrincipal userPrincipal) {
        Date now = new Date();
        Date expiryDate = new Date(now.getTime() + accessTokenExpirationMs);

        List<String> roles = userPrincipal.getAuthorities().stream()
                .map(GrantedAuthority::getAuthority)
                .collect(Collectors.toList());

        var builder = Jwts.builder()
                .subject(userPrincipal.getId().toString())
                .claim("email", userPrincipal.getEmail())
                .claim("name", userPrincipal.getName())
                .claim("roles", roles)
                .issuedAt(now)
                .expiration(expiryDate)
                .signWith(secretKey);

        if (userPrincipal.getImpersonatorAdminId() != null) {
            builder.claim("impersonatorAdminId", userPrincipal.getImpersonatorAdminId());
        }

        return builder.compact();
    }

    public String generateRefreshToken(UserPrincipal userPrincipal) {
        Date now = new Date();
        Date expiryDate = new Date(now.getTime() + refreshTokenExpirationMs);

        return Jwts.builder()
                .subject(userPrincipal.getId().toString())
                .issuedAt(now)
                .expiration(expiryDate)
                .signWith(secretKey)
                .compact();
    }

    public Long getUserIdFromToken(String token) {
        Claims claims = getClaims(token);
        return Long.parseLong(claims.getSubject());
    }

    public Long getImpersonatorAdminIdFromToken(String token) {
        Claims claims = getClaims(token);
        Object impId = claims.get("impersonatorAdminId");
        if (impId instanceof Number number) {
            return number.longValue();
        }
        return null;
    }

    public boolean validateToken(String token) {
        try {
            Jwts.parser().verifyWith(secretKey).build().parseSignedClaims(token);
            return true;
        } catch (JwtException | IllegalArgumentException e) {
            return false;
        }
    }

    private Claims getClaims(String token) {
        return Jwts.parser()
                .verifyWith(secretKey)
                .build()
                .parseSignedClaims(token)
                .getPayload();
    }
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/security/JwtAuthenticationFilter.java
package com.elearning.security;

import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import lombok.RequiredArgsConstructor;
import org.springframework.lang.NonNull;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.web.authentication.WebAuthenticationDetailsSource;
import org.springframework.stereotype.Component;
import org.springframework.util.StringUtils;
import org.springframework.web.filter.OncePerRequestFilter;

import java.io.IOException;

@Component
@RequiredArgsConstructor
public class JwtAuthenticationFilter extends OncePerRequestFilter {

    private final JwtProvider jwtProvider;
    private final CustomUserDetailsService userDetailsService;

    @Override
    protected void doFilterInternal(
            @NonNull HttpServletRequest request,
            @NonNull HttpServletResponse response,
            @NonNull FilterChain filterChain
    ) throws ServletException, IOException {
        String token = getJwtFromRequest(request);

        if (StringUtils.hasText(token) && jwtProvider.validateToken(token)) {
            Long userId = jwtProvider.getUserIdFromToken(token);
            Long impersonatorAdminId = jwtProvider.getImpersonatorAdminIdFromToken(token);

            UserDetails userDetails = userDetailsService.loadUserById(userId, impersonatorAdminId);
            UsernamePasswordAuthenticationToken authentication = new UsernamePasswordAuthenticationToken(
                    userDetails,
                    null,
                    userDetails.getAuthorities()
            );
            authentication.setDetails(new WebAuthenticationDetailsSource().buildDetails(request));

            SecurityContextHolder.getContext().setAuthentication(authentication);
        }

        filterChain.doFilter(request, response);
    }

    private String getJwtFromRequest(HttpServletRequest request) {
        String bearerToken = request.getHeader("Authorization");
        if (StringUtils.hasText(bearerToken) && bearerToken.startsWith("Bearer ")) {
            return bearerToken.substring(7);
        }
        return null;
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/config/SecurityConfig.java
package com.elearning.config;

import com.elearning.security.CustomUserDetailsService;
import com.elearning.security.JwtAuthenticationFilter;
import lombok.RequiredArgsConstructor;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.AuthenticationProvider;
import org.springframework.security.authentication.dao.DaoAuthenticationProvider;
import org.springframework.security.config.Customizer;
import org.springframework.security.config.annotation.authentication.configuration.AuthenticationConfiguration;
import org.springframework.security.config.annotation.method.configuration.EnableMethodSecurity;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.config.annotation.web.configurers.AbstractHttpConfigurer;
import org.springframework.security.config.http.SessionCreationPolicy;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.security.web.SecurityFilterChain;
import org.springframework.security.web.authentication.UsernamePasswordAuthenticationFilter;
import org.springframework.web.cors.CorsConfiguration;
import org.springframework.web.cors.CorsConfigurationSource;
import org.springframework.web.cors.UrlBasedCorsConfigurationSource;

import java.util.List;

@Configuration
@EnableWebSecurity
@EnableMethodSecurity
@RequiredArgsConstructor
public class SecurityConfig {

    private final JwtAuthenticationFilter jwtAuthenticationFilter;
    private final CustomUserDetailsService userDetailsService;

    @Value("${app.cors.allowed-origins:http://localhost:3000,http://localhost:5173}")
    private List<String> allowedOrigins;

    @Bean
    public SecurityFilterChain securityFilterChain(HttpSecurity http) throws Exception {
        http
            .cors(cors -> cors.configurationSource(corsConfigurationSource()))
            .csrf(AbstractHttpConfigurer::disable)
            .sessionManagement(session -> session.sessionCreationPolicy(SessionCreationPolicy.STATELESS))
            .authorizeHttpRequests(auth -> auth
                .requestMatchers(
                    "/api/v1/auth/**",
                    "/swagger-ui/**",
                    "/swagger-ui.html",
                    "/api-docs/**",
                    "/v3/api-docs/**",
                    "/ws/**"
                ).permitAll()
                .requestMatchers("/api/v1/admin/**").hasRole("ADMIN")
                .requestMatchers("/api/v1/teacher/**").hasAnyRole("ADMIN", "TEACHER")
                .requestMatchers("/api/v1/student/**").hasAnyRole("ADMIN", "STUDENT")
                .anyRequest().authenticated()
            )
            .authenticationProvider(authenticationProvider())
            .addFilterBefore(jwtAuthenticationFilter, UsernamePasswordAuthenticationFilter.class);

        return http.build();
    }

    @Bean
    public AuthenticationProvider authenticationProvider() {
        DaoAuthenticationProvider authProvider = new DaoAuthenticationProvider();
        authProvider.setUserDetailsService(userDetailsService);
        authProvider.setPasswordEncoder(passwordEncoder());
        return authProvider;
    }

    @Bean
    public AuthenticationManager authenticationManager(AuthenticationConfiguration config) throws Exception {
        return config.getAuthenticationManager();
    }

    @Bean
    public PasswordEncoder passwordEncoder() {
        return new BCryptPasswordEncoder();
    }

    @Bean
    public CorsConfigurationSource corsConfigurationSource() {
        CorsConfiguration configuration = new CorsConfiguration();
        configuration.setAllowedOrigins(allowedOrigins);
        configuration.setAllowedMethods(List.of("GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"));
        configuration.setAllowedHeaders(List.of("Authorization", "Content-Type", "X-Requested-With", "Accept"));
        configuration.setAllowCredentials(true);
        configuration.setMaxAge(3600L);

        UrlBasedCorsConfigurationSource source = new UrlBasedCorsConfigurationSource();
        source.registerCorsConfiguration("/**", configuration);
        return source;
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/auth/dto/LoginRequest.java
package com.elearning.modules.auth.dto;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import lombok.Data;

@Data
public class LoginRequest {
    @NotBlank(message = "Email is required")
    @Email(message = "Invalid email format")
    private String email;

    @NotBlank(message = "Password is required")
    private String password;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/auth/dto/RegisterRequest.java
package com.elearning.modules.auth.dto;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;
import lombok.Data;

@Data
public class RegisterRequest {
    @NotBlank(message = "Name is required")
    private String name;

    @NotBlank(message = "Email is required")
    @Email(message = "Invalid email format")
    private String email;

    @NotBlank(message = "Password is required")
    @Size(min = 8, message = "Password must be at least 8 characters")
    private String password;

    private String phone;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/auth/dto/AuthResponse.java
package com.elearning.modules.auth.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.List;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class AuthResponse {
    private String accessToken;
    private String refreshToken;
    private Long userId;
    private String name;
    private String email;
    private List<String> roles;
    private Long impersonatorAdminId;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/auth/dto/UserProfileResponse.java
package com.elearning.modules.auth.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.List;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class UserProfileResponse {
    private Long id;
    private String name;
    private String email;
    private String phone;
    private List<String> roles;
    private Long teacherId;
    private Long studentId;
    private Long classroomId;
    private String classroomName;
    private boolean impersonated;
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/auth/service/AuthService.java
package com.elearning.modules.auth.service;

import com.elearning.common.enums.RoleType;
import com.elearning.modules.auth.dto.*;
import com.elearning.modules.user.entity.Role;
import com.elearning.modules.user.entity.Student;
import com.elearning.modules.user.entity.Teacher;
import com.elearning.modules.user.entity.User;
import com.elearning.modules.user.repository.RoleRepository;
import com.elearning.modules.user.repository.StudentRepository;
import com.elearning.modules.user.repository.TeacherRepository;
import com.elearning.modules.user.repository.UserRepository;
import com.elearning.security.JwtProvider;
import com.elearning.security.UserPrincipal;
import lombok.RequiredArgsConstructor;
import org.springframework.security.access.AccessDeniedException;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.GrantedAuthority;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;
import java.util.Optional;
import java.util.Set;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
public class AuthService {

    private final AuthenticationManager authenticationManager;
    private final UserRepository userRepository;
    private final RoleRepository roleRepository;
    private final TeacherRepository teacherRepository;
    private final StudentRepository studentRepository;
    private final PasswordEncoder passwordEncoder;
    private final JwtProvider jwtProvider;

    public AuthResponse login(LoginRequest request) {
        Authentication authentication = authenticationManager.authenticate(
                new UsernamePasswordAuthenticationToken(request.getEmail(), request.getPassword())
        );

        UserPrincipal principal = (UserPrincipal) authentication.getPrincipal();
        String accessToken = jwtProvider.generateAccessToken(principal);
        String refreshToken = jwtProvider.generateRefreshToken(principal);

        List<String> roles = principal.getAuthorities().stream()
                .map(GrantedAuthority::getAuthority)
                .collect(Collectors.toList());

        return AuthResponse.builder()
                .accessToken(accessToken)
                .refreshToken(refreshToken)
                .userId(principal.getId())
                .name(principal.getName())
                .email(principal.getEmail())
                .roles(roles)
                .build();
    }

    @Transactional
    public AuthResponse register(RegisterRequest request) {
        if (userRepository.existsByEmail(request.getEmail())) {
            throw new IllegalArgumentException("Email already registered: " + request.getEmail());
        }

        Role studentRole = roleRepository.findByName(RoleType.ROLE_STUDENT)
                .orElseThrow(() -> new IllegalStateException("Default role ROLE_STUDENT not found in database"));

        User user = User.builder()
                .name(request.getName())
                .email(request.getEmail())
                .password(passwordEncoder.encode(request.getPassword()))
                .phone(request.getPhone())
                .active(true)
                .roles(Set.of(studentRole))
                .build();

        user = userRepository.save(user);

        UserPrincipal principal = UserPrincipal.create(user);
        String accessToken = jwtProvider.generateAccessToken(principal);
        String refreshToken = jwtProvider.generateRefreshToken(principal);

        return AuthResponse.builder()
                .accessToken(accessToken)
                .refreshToken(refreshToken)
                .userId(user.getId())
                .name(user.getName())
                .email(user.getEmail())
                .roles(List.of(RoleType.ROLE_STUDENT.name()))
                .build();
    }

    @Transactional(readOnly = true)
    public UserProfileResponse getProfile(UserPrincipal principal) {
        User user = userRepository.findById(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("User not found with id: " + principal.getId()));

        Optional<Teacher> teacher = teacherRepository.findByUserId(user.getId());
        Optional<Student> student = studentRepository.findByUserId(user.getId());

        List<String> roles = user.getRoles().stream()
                .map(role -> role.getName().name())
                .collect(Collectors.toList());

        Long classroomId = student.map(s -> s.getClassroom() != null ? s.getClassroom().getId() : null).orElse(null);
        String classroomName = student.map(s -> s.getClassroom() != null ? s.getClassroom().getName() : null).orElse(null);

        return UserProfileResponse.builder()
                .id(user.getId())
                .name(user.getName())
                .email(user.getEmail())
                .phone(user.getPhone())
                .roles(roles)
                .teacherId(teacher.map(Teacher::getId).orElse(null))
                .studentId(student.map(Student::getId).orElse(null))
                .classroomId(classroomId)
                .classroomName(classroomName)
                .impersonated(principal.getImpersonatorAdminId() != null)
                .build();
    }

    @Transactional(readOnly = true)
    public AuthResponse impersonate(Long targetUserId, UserPrincipal adminPrincipal) {
        User targetUser = userRepository.findById(targetUserId)
                .orElseThrow(() -> new IllegalArgumentException("Target user not found with id: " + targetUserId));

        // Prevent admin from impersonating another admin or themselves
        boolean isTargetAdmin = targetUser.getRoles().stream()
                .anyMatch(r -> r.getName() == RoleType.ROLE_ADMIN);

        if (isTargetAdmin || targetUser.getId().equals(adminPrincipal.getId())) {
            throw new AccessDeniedException("Cannot impersonate another admin or yourself");
        }

        UserPrincipal targetPrincipal = UserPrincipal.create(targetUser, adminPrincipal.getId());
        String accessToken = jwtProvider.generateAccessToken(targetPrincipal);
        String refreshToken = jwtProvider.generateRefreshToken(targetPrincipal);

        List<String> roles = targetPrincipal.getAuthorities().stream()
                .map(GrantedAuthority::getAuthority)
                .collect(Collectors.toList());

        return AuthResponse.builder()
                .accessToken(accessToken)
                .refreshToken(refreshToken)
                .userId(targetUser.getId())
                .name(targetUser.getName())
                .email(targetUser.getEmail())
                .roles(roles)
                .impersonatorAdminId(adminPrincipal.getId())
                .build();
    }

    @Transactional(readOnly = true)
    public AuthResponse stopImpersonate(UserPrincipal currentPrincipal) {
        Long adminId = currentPrincipal.getImpersonatorAdminId();
        if (adminId == null) {
            throw new IllegalArgumentException("You are not currently in an impersonation session");
        }

        User adminUser = userRepository.findById(adminId)
                .orElseThrow(() -> new IllegalArgumentException("Admin user not found with id: " + adminId));

        UserPrincipal adminPrincipal = UserPrincipal.create(adminUser, null);
        String accessToken = jwtProvider.generateAccessToken(adminPrincipal);
        String refreshToken = jwtProvider.generateRefreshToken(adminPrincipal);

        List<String> roles = adminPrincipal.getAuthorities().stream()
                .map(GrantedAuthority::getAuthority)
                .collect(Collectors.toList());

        return AuthResponse.builder()
                .accessToken(accessToken)
                .refreshToken(refreshToken)
                .userId(adminUser.getId())
                .name(adminUser.getName())
                .email(adminUser.getEmail())
                .roles(roles)
                .impersonatorAdminId(null)
                .build();
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/auth/controller/AuthController.java
package com.elearning.modules.auth.controller;

import com.elearning.common.response.ApiResponse;
import com.elearning.modules.auth.dto.*;
import com.elearning.modules.auth.service.AuthService;
import com.elearning.security.UserPrincipal;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.security.SecurityRequirement;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/v1")
@RequiredArgsConstructor
@Tag(name = "Authentication & User Management", description = "Endpoints for user login, registration, profile, and impersonation")
public class AuthController {

    private final AuthService authService;

    @PostMapping("/auth/login")
    @Operation(summary = "User Login", description = "Authenticate with email and password to receive JWT tokens")
    public ResponseEntity<ApiResponse<AuthResponse>> login(@Valid @RequestBody LoginRequest request) {
        AuthResponse response = authService.login(request);
        return ResponseEntity.ok(ApiResponse.ok(response, "Login successful"));
    }

    @PostMapping("/auth/register")
    @Operation(summary = "Student Registration", description = "Register a new student account")
    public ResponseEntity<ApiResponse<AuthResponse>> register(@Valid @RequestBody RegisterRequest request) {
        AuthResponse response = authService.register(request);
        return ResponseEntity.ok(ApiResponse.ok(response, "Registration successful"));
    }

    @GetMapping("/auth/me")
    @Operation(summary = "Get Current Profile", description = "Get authenticated user profile details")
    @SecurityRequirement(name = "Bearer Authentication")
    public ResponseEntity<ApiResponse<UserProfileResponse>> getProfile(@AuthenticationPrincipal UserPrincipal principal) {
        UserProfileResponse profile = authService.getProfile(principal);
        return ResponseEntity.ok(ApiResponse.ok(profile));
    }

    @PostMapping("/admin/impersonate/{userId}")
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Start Impersonation", description = "Admin can impersonate a teacher or student user")
    @SecurityRequirement(name = "Bearer Authentication")
    public ResponseEntity<ApiResponse<AuthResponse>> impersonate(
            @PathVariable Long userId,
            @AuthenticationPrincipal UserPrincipal adminPrincipal
    ) {
        AuthResponse response = authService.impersonate(userId, adminPrincipal);
        return ResponseEntity.ok(ApiResponse.ok(response, "Impersonation started successfully"));
    }

    @PostMapping("/admin/stop-impersonate")
    @Operation(summary = "Stop Impersonation", description = "Revert impersonated session back to administrator")
    @SecurityRequirement(name = "Bearer Authentication")
    public ResponseEntity<ApiResponse<AuthResponse>> stopImpersonate(@AuthenticationPrincipal UserPrincipal currentPrincipal) {
        AuthResponse response = authService.stopImpersonate(currentPrincipal);
        return ResponseEntity.ok(ApiResponse.ok(response, "Returned to admin session"));
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/exception/GlobalExceptionHandler.java
package com.elearning.common.exception;

import com.elearning.common.response.ApiResponse;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.AccessDeniedException;
import org.springframework.security.authentication.BadCredentialsException;
import org.springframework.validation.FieldError;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;

import java.util.HashMap;
import java.util.Map;

@RestControllerAdvice
public class GlobalExceptionHandler {

    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ResponseEntity<ApiResponse<Map<String, String>>> handleValidationExceptions(MethodArgumentNotValidException ex) {
        Map<String, String> errors = new HashMap<>();
        ex.getBindingResult().getAllErrors().forEach((error) -> {
            String fieldName = ((FieldError) error).getField();
            String errorMessage = error.getDefaultMessage();
            errors.put(fieldName, errorMessage);
        });

        ApiResponse<Map<String, String>> response = ApiResponse.<Map<String, String>>builder()
                .success(false)
                .message("Validation failed")
                .data(errors)
                .build();

        return ResponseEntity.status(HttpStatus.BAD_REQUEST).body(response);
    }

    @ExceptionHandler(BadCredentialsException.class)
    public ResponseEntity<ApiResponse<Void>> handleBadCredentials(BadCredentialsException ex) {
        return ResponseEntity.status(HttpStatus.UNAUTHORIZED)
                .body(ApiResponse.error("Invalid email or password"));
    }

    @ExceptionHandler(AccessDeniedException.class)
    public ResponseEntity<ApiResponse<Void>> handleAccessDenied(AccessDeniedException ex) {
        return ResponseEntity.status(HttpStatus.FORBIDDEN)
                .body(ApiResponse.error(ex.getMessage() != null ? ex.getMessage() : "Access denied"));
    }

    @ExceptionHandler(IllegalArgumentException.class)
    public ResponseEntity<ApiResponse<Void>> handleIllegalArgument(IllegalArgumentException ex) {
        return ResponseEntity.status(HttpStatus.BAD_REQUEST)
                .body(ApiResponse.error(ex.getMessage()));
    }

    @ExceptionHandler(Exception.class)
    public ResponseEntity<ApiResponse<Void>> handleGenericException(Exception ex) {
        return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                .body(ApiResponse.error("An unexpected error occurred: " + ex.getMessage()));
    }
}
EOF`
Ran command: `cd ../java-spring-react-elearning && git status`
Ran command: `cd ../java-spring-react-elearning && find backend/src/main/java/com/elearning -type f`
Ran command: `cd ../java-spring-react-elearning && git add . && git commit -m "feat(backend): implement spring security 6, jwt, flyway schema, and core domain entities"`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/Dockerfile
FROM maven:3.9.9-eclipse-temurin-21-alpine AS build
WORKDIR /app
COPY pom.xml .
# Pre-fetch dependencies for caching
RUN mvn dependency:go-offline -B
COPY src ./src
RUN mvn clean package -DskipTests

FROM eclipse-temurin:21-jre-alpine
WORKDIR /app
RUN addgroup -S spring && adduser -S spring -G spring
USER spring:spring
COPY --from=build /app/target/*.jar app.jar
EXPOSE 8080
ENTRYPOINT ["java", "-Djava.security.egd=file:/dev/./urandom", "-jar", "app.jar"]
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/nginx.conf
server {
    listen 80;
    server_name localhost;

    location / {
        root /usr/share/nginx/html;
        index index.html index.htm;
        try_files $uri $uri/ /index.html;
    }

    location /api/ {
        proxy_pass http://backend:8080/api/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location /ws/ {
        proxy_pass http://backend:8080/ws/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/Dockerfile
FROM node:20-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM nginx:alpine
COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
EOF`
Ran command: `cat ../java-spring-react-elearning/docker-compose.yml`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/docker-compose.yml
services:
  postgres:
    image: postgres:16-alpine
    container_name: elearning-postgres
    restart: always
    environment:
      POSTGRES_DB: elearning
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: postgres
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres"]
      interval: 5s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    container_name: elearning-redis
    restart: always
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 5s
      timeout: 5s
      retries: 5

  minio:
    image: minio/minio:latest
    container_name: elearning-minio
    restart: always
    command: server /data --console-address ":9001"
    environment:
      MINIO_ROOT_USER: minioadmin
      MINIO_ROOT_PASSWORD: minioadmin
    ports:
      - "9000:9000"
      - "9001:9001"
    volumes:
      - minio_data:/data

  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: elearning-backend
    restart: always
    environment:
      SPRING_DATASOURCE_URL: jdbc:postgresql://postgres:5432/elearning
      SPRING_DATASOURCE_USERNAME: postgres
      SPRING_DATASOURCE_PASSWORD: postgres
      SPRING_DATA_REDIS_HOST: redis
      SPRING_DATA_REDIS_PORT: 6379
      APP_JWT_SECRET: 404E635266556A586E3272357538782F413F4428472B4B6250645367566B5970
    ports:
      - "8080:8080"
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy

  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    container_name: elearning-frontend
    restart: always
    ports:
      - "80:80"
    depends_on:
      - backend

volumes:
  postgres_data:
  redis_data:
  minio_data:
EOF`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/entity/Classroom.java`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/entity/Subject.java`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/entity/Teacher.java`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/entity/Student.java`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/resources/application.yml`
Ran command: `mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/service
cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/service/FileStorageService.java
package com.elearning.common.service;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.core.io.Resource;
import org.springframework.core.io.UrlResource;
import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;
import org.springframework.web.multipart.MultipartFile;

import java.io.IOException;
import java.net.MalformedURLException;
import java.nio.file.*;
import java.util.Objects;
import java.util.UUID;

@Service
public class FileStorageService {

    private final Path fileStorageLocation;

    public FileStorageService(@Value("${app.storage.upload-dir:./storage/uploads}") String uploadDir) {
        this.fileStorageLocation = Paths.get(uploadDir).toAbsolutePath().normalize();
        try {
            Files.createDirectories(this.fileStorageLocation);
        } catch (Exception ex) {
            throw new RuntimeException("Could not create the directory where the uploaded files will be stored.", ex);
        }
    }

    public String storeFile(MultipartFile file, String subDirectory) {
        String originalFileName = StringUtils.cleanPath(Objects.requireNonNull(file.getOriginalFilename()));
        String extension = "";
        int i = originalFileName.lastIndexOf('.');
        if (i >= 0) {
            extension = originalFileName.substring(i);
        }

        String fileName = UUID.randomUUID() + extension;

        try {
            if (originalFileName.contains("..")) {
                throw new IllegalArgumentException("Filename contains invalid path sequence " + originalFileName);
            }

            Path targetDir = this.fileStorageLocation;
            if (subDirectory != null && !subDirectory.trim().isEmpty()) {
                targetDir = this.fileStorageLocation.resolve(subDirectory).normalize();
                Files.createDirectories(targetDir);
            }

            Path targetLocation = targetDir.resolve(fileName);
            Files.copy(file.getInputStream(), targetLocation, StandardCopyOption.REPLACE_EXISTING);

            return (subDirectory != null && !subDirectory.trim().isEmpty() ? subDirectory + "/" : "") + fileName;
        } catch (IOException ex) {
            throw new RuntimeException("Could not store file " + fileName + ". Please try again!", ex);
        }
    }

    public Resource loadFileAsResource(String filePath) {
        try {
            Path file = this.fileStorageLocation.resolve(filePath).normalize();
            Resource resource = new UrlResource(file.toUri());
            if (resource.exists() && resource.isReadable()) {
                return resource;
            } else {
                throw new RuntimeException("File not found " + filePath);
            }
        } catch (MalformedURLException ex) {
            throw new RuntimeException("File not found " + filePath, ex);
        }
    }

    public void deleteFile(String filePath) {
        if (filePath == null || filePath.trim().isEmpty()) return;
        try {
            Path file = this.fileStorageLocation.resolve(filePath).normalize();
            Files.deleteIfExists(file);
        } catch (IOException ignored) {
        }
    }
}
EOF`
Ran command: `mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/controller
cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/controller/FileController.java
package com.elearning.common.controller;

import com.elearning.common.service.FileStorageService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.servlet.http.HttpServletRequest;
import lombok.RequiredArgsConstructor;
import org.springframework.core.io.Resource;
import org.springframework.http.HttpHeaders;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.io.IOException;

@RestController
@RequestMapping("/api/v1/files")
@RequiredArgsConstructor
@Tag(name = "File Management", description = "Endpoints for downloading and accessing uploaded files")
public class FileController {

    private final FileStorageService fileStorageService;

    @GetMapping("/**")
    @Operation(summary = "Download / View File")
    public ResponseEntity<Resource> downloadFile(HttpServletRequest request) {
        String path = request.getRequestURI();
        String filePath = path.replaceFirst("^/api/v1/files/", "");
        Resource resource = fileStorageService.loadFileAsResource(filePath);

        String contentType = null;
        try {
            contentType = request.getServletContext().getMimeType(resource.getFile().getAbsolutePath());
        } catch (IOException ignored) {}

        if (contentType == null) {
            contentType = "application/octet-stream";
        }

        return ResponseEntity.ok()
                .contentType(MediaType.parseMediaType(contentType))
                .header(HttpHeaders.CONTENT_DISPOSITION, "inline; filename=\"" + resource.getFilename() + "\"")
                .body(resource);
    }
}
EOF`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/config/SecurityConfig.java`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/config/SecurityConfig.java
package com.elearning.config;

import com.elearning.security.CustomUserDetailsService;
import com.elearning.security.JwtAuthenticationFilter;
import lombok.RequiredArgsConstructor;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.AuthenticationProvider;
import org.springframework.security.authentication.dao.DaoAuthenticationProvider;
import org.springframework.security.config.annotation.authentication.configuration.AuthenticationConfiguration;
import org.springframework.security.config.annotation.method.configuration.EnableMethodSecurity;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.config.annotation.web.configurers.AbstractHttpConfigurer;
import org.springframework.security.config.http.SessionCreationPolicy;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.security.web.SecurityFilterChain;
import org.springframework.security.web.authentication.UsernamePasswordAuthenticationFilter;
import org.springframework.web.cors.CorsConfiguration;
import org.springframework.web.cors.CorsConfigurationSource;
import org.springframework.web.cors.UrlBasedCorsConfigurationSource;

import java.util.List;

@Configuration
@EnableWebSecurity
@EnableMethodSecurity
@RequiredArgsConstructor
public class SecurityConfig {

    private final JwtAuthenticationFilter jwtAuthenticationFilter;
    private final CustomUserDetailsService userDetailsService;

    @Value("${app.cors.allowed-origins:http://localhost:3000,http://localhost:5173}")
    private List<String> allowedOrigins;

    @Bean
    public SecurityFilterChain securityFilterChain(HttpSecurity http) throws Exception {
        http
            .cors(cors -> cors.configurationSource(corsConfigurationSource()))
            .csrf(AbstractHttpConfigurer::disable)
            .sessionManagement(session -> session.sessionCreationPolicy(SessionCreationPolicy.STATELESS))
            .authorizeHttpRequests(auth -> auth
                .requestMatchers(
                    "/api/v1/auth/**",
                    "/api/v1/files/**",
                    "/swagger-ui/**",
                    "/swagger-ui.html",
                    "/api-docs/**",
                    "/v3/api-docs/**",
                    "/ws/**"
                ).permitAll()
                .requestMatchers("/api/v1/admin/**").hasRole("ADMIN")
                .requestMatchers("/api/v1/teacher/**").hasAnyRole("ADMIN", "TEACHER")
                .requestMatchers("/api/v1/student/**").hasAnyRole("ADMIN", "STUDENT")
                .anyRequest().authenticated()
            )
            .authenticationProvider(authenticationProvider())
            .addFilterBefore(jwtAuthenticationFilter, UsernamePasswordAuthenticationFilter.class);

        return http.build();
    }

    @Bean
    public AuthenticationProvider authenticationProvider() {
        DaoAuthenticationProvider authProvider = new DaoAuthenticationProvider();
        authProvider.setUserDetailsService(userDetailsService);
        authProvider.setPasswordEncoder(passwordEncoder());
        return authProvider;
    }

    @Bean
    public AuthenticationManager authenticationManager(AuthenticationConfiguration config) throws Exception {
        return config.getAuthenticationManager();
    }

    @Bean
    public PasswordEncoder passwordEncoder() {
        return new BCryptPasswordEncoder();
    }

    @Bean
    public CorsConfigurationSource corsConfigurationSource() {
        CorsConfiguration configuration = new CorsConfiguration();
        configuration.setAllowedOrigins(allowedOrigins);
        configuration.setAllowedMethods(List.of("GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"));
        configuration.setAllowedHeaders(List.of("Authorization", "Content-Type", "X-Requested-With", "Accept"));
        configuration.setAllowCredentials(true);
        configuration.setMaxAge(3600L);

        UrlBasedCorsConfigurationSource source = new UrlBasedCorsConfigurationSource();
        source.registerCorsConfiguration("/**", configuration);
        return source;
    }
}
EOF`
Ran command: `mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/dto
cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/dto/ClassroomDto.java
package com.elearning.modules.academic.dto;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.UUID;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ClassroomDto {
    @NotBlank(message = "Classroom name is required")
    private String name;

    @NotNull(message = "Level is required")
    private Integer level;

    private Integer capacity;

    @NotBlank(message = "Academic year is required")
    private String academicYear;

    private UUID homeroomTeacherId;
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/dto/ClassroomResponse.java
package com.elearning.modules.academic.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;
import java.util.UUID;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ClassroomResponse {
    private UUID id;
    private String name;
    private Integer level;
    private Integer capacity;
    private String academicYear;
    private UUID homeroomTeacherId;
    private String homeroomTeacherName;
    private LocalDateTime createdAt;
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/dto/SubjectDto.java
package com.elearning.modules.academic.dto;

import jakarta.validation.constraints.NotBlank;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.UUID;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class SubjectDto {
    @NotBlank(message = "Subject name is required")
    private String name;

    @NotBlank(message = "Subject code is required")
    private String code;

    private String description;

    private UUID teacherId;

    @Builder.Default
    private Integer credits = 2;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/dto/SubjectResponse.java
package com.elearning.modules.academic.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;
import java.util.UUID;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class SubjectResponse {
    private UUID id;
    private String name;
    private String code;
    private String description;
    private UUID teacherId;
    private String teacherName;
    private Integer credits;
    private LocalDateTime createdAt;
}
EOF`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/repository/ClassroomRepository.java
echo "---"
cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/repository/SubjectRepository.java`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/common/entity/BaseEntity.java`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/dto/ClassroomDto.java
package com.elearning.modules.academic.dto;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ClassroomDto {
    @NotBlank(message = "Classroom name is required")
    private String name;

    @NotNull(message = "Level is required")
    private Integer level;

    private Integer capacity;

    @NotBlank(message = "Academic year is required")
    private String academicYear;

    private Long homeroomTeacherId;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/dto/ClassroomResponse.java
package com.elearning.modules.academic.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ClassroomResponse {
    private Long id;
    private String name;
    private Integer level;
    private Integer capacity;
    private String academicYear;
    private Long homeroomTeacherId;
    private String homeroomTeacherName;
    private LocalDateTime createdAt;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/dto/SubjectDto.java
package com.elearning.modules.academic.dto;

import jakarta.validation.constraints.NotBlank;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class SubjectDto {
    @NotBlank(message = "Subject name is required")
    private String name;

    @NotBlank(message = "Subject code is required")
    private String code;

    private String description;

    private Long teacherId;

    @Builder.Default
    private Integer credits = 2;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/dto/SubjectResponse.java
package com.elearning.modules.academic.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class SubjectResponse {
    private Long id;
    private String name;
    private String code;
    private String description;
    private Long teacherId;
    private String teacherName;
    private Integer credits;
    private LocalDateTime createdAt;
}
EOF`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/repository/TeacherRepository.java`
Ran command: `mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/service
mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/controller

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/service/ClassroomService.java
package com.elearning.modules.academic.service;

import com.elearning.modules.academic.dto.ClassroomDto;
import com.elearning.modules.academic.dto.ClassroomResponse;
import com.elearning.modules.academic.entity.Classroom;
import com.elearning.modules.academic.repository.ClassroomRepository;
import com.elearning.modules.user.entity.Teacher;
import com.elearning.modules.user.repository.TeacherRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
public class ClassroomService {

    private final ClassroomRepository classroomRepository;
    private final TeacherRepository teacherRepository;

    @Transactional(readOnly = true)
    public List<ClassroomResponse> getAllClassrooms() {
        return classroomRepository.findAll().stream()
                .map(this::mapToResponse)
                .collect(Collectors.toList());
    }

    @Transactional(readOnly = true)
    public ClassroomResponse getClassroomById(Long id) {
        Classroom classroom = classroomRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Classroom not found with id: " + id));
        return mapToResponse(classroom);
    }

    @Transactional
    public ClassroomResponse createClassroom(ClassroomDto dto) {
        Teacher teacher = null;
        if (dto.getHomeroomTeacherId() != null) {
            teacher = teacherRepository.findById(dto.getHomeroomTeacherId())
                    .orElseThrow(() -> new IllegalArgumentException("Homeroom teacher not found"));
        }

        Classroom classroom = Classroom.builder()
                .name(dto.getName())
                .level(dto.getLevel())
                .capacity(dto.getCapacity() != null ? dto.getCapacity() : 30)
                .academicYear(dto.getAcademicYear())
                .homeroomTeacher(teacher)
                .build();

        classroom = classroomRepository.save(classroom);
        return mapToResponse(classroom);
    }

    @Transactional
    public ClassroomResponse updateClassroom(Long id, ClassroomDto dto) {
        Classroom classroom = classroomRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Classroom not found with id: " + id));

        classroom.setName(dto.getName());
        classroom.setLevel(dto.getLevel());
        if (dto.getCapacity() != null) {
            classroom.setCapacity(dto.getCapacity());
        }
        classroom.setAcademicYear(dto.getAcademicYear());

        if (dto.getHomeroomTeacherId() != null) {
            Teacher teacher = teacherRepository.findById(dto.getHomeroomTeacherId())
                    .orElseThrow(() -> new IllegalArgumentException("Homeroom teacher not found"));
            classroom.setHomeroomTeacher(teacher);
        } else {
            classroom.setHomeroomTeacher(null);
        }

        return mapToResponse(classroomRepository.save(classroom));
    }

    @Transactional
    public void deleteClassroom(Long id) {
        if (!classroomRepository.existsById(id)) {
            throw new IllegalArgumentException("Classroom not found with id: " + id);
        }
        classroomRepository.deleteById(id);
    }

    private ClassroomResponse mapToResponse(Classroom c) {
        return ClassroomResponse.builder()
                .id(c.getId())
                .name(c.getName())
                .level(c.getLevel())
                .capacity(c.getCapacity())
                .academicYear(c.getAcademicYear())
                .homeroomTeacherId(c.getHomeroomTeacher() != null ? c.getHomeroomTeacher().getId() : null)
                .homeroomTeacherName(c.getHomeroomTeacher() != null && c.getHomeroomTeacher().getUser() != null ? c.getHomeroomTeacher().getUser().getName() : null)
                .createdAt(c.getCreatedAt())
                .build();
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/controller/ClassroomController.java
package com.elearning.modules.academic.controller;

import com.elearning.common.response.ApiResponse;
import com.elearning.modules.academic.dto.ClassroomDto;
import com.elearning.modules.academic.dto.ClassroomResponse;
import com.elearning.modules.academic.service.ClassroomService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/v1/classrooms")
@RequiredArgsConstructor
@Tag(name = "Classroom Management", description = "Endpoints for academic classrooms")
public class ClassroomController {

    private final ClassroomService classroomService;

    @GetMapping
    @Operation(summary = "Get all classrooms")
    public ResponseEntity<ApiResponse<List<ClassroomResponse>>> getAllClassrooms() {
        return ResponseEntity.ok(ApiResponse.success(classroomService.getAllClassrooms(), "Classrooms fetched successfully"));
    }

    @GetMapping("/{id}")
    @Operation(summary = "Get classroom by ID")
    public ResponseEntity<ApiResponse<ClassroomResponse>> getClassroomById(@PathVariable Long id) {
        return ResponseEntity.ok(ApiResponse.success(classroomService.getClassroomById(id), "Classroom details"));
    }

    @PostMapping
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Create a new classroom (Admin only)")
    public ResponseEntity<ApiResponse<ClassroomResponse>> createClassroom(@Valid @RequestBody ClassroomDto dto) {
        ClassroomResponse response = classroomService.createClassroom(dto);
        return ResponseEntity.status(HttpStatus.CREATED).body(ApiResponse.success(response, "Classroom created successfully"));
    }

    @PutMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Update classroom (Admin only)")
    public ResponseEntity<ApiResponse<ClassroomResponse>> updateClassroom(@PathVariable Long id, @Valid @RequestBody ClassroomDto dto) {
        return ResponseEntity.ok(ApiResponse.success(classroomService.updateClassroom(id, dto), "Classroom updated successfully"));
    }

    @DeleteMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Delete classroom (Admin only)")
    public ResponseEntity<ApiResponse<Void>> deleteClassroom(@PathVariable Long id) {
        classroomService.deleteClassroom(id);
        return ResponseEntity.ok(ApiResponse.success(null, "Classroom deleted successfully"));
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/service/SubjectService.java
package com.elearning.modules.academic.service;

import com.elearning.modules.academic.dto.SubjectDto;
import com.elearning.modules.academic.dto.SubjectResponse;
import com.elearning.modules.academic.entity.Subject;
import com.elearning.modules.academic.repository.SubjectRepository;
import com.elearning.modules.user.entity.Teacher;
import com.elearning.modules.user.repository.TeacherRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
public class SubjectService {

    private final SubjectRepository subjectRepository;
    private final TeacherRepository teacherRepository;

    @Transactional(readOnly = true)
    public List<SubjectResponse> getAllSubjects() {
        return subjectRepository.findAll().stream()
                .map(this::mapToResponse)
                .collect(Collectors.toList());
    }

    @Transactional(readOnly = true)
    public SubjectResponse getSubjectById(Long id) {
        Subject subject = subjectRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Subject not found with id: " + id));
        return mapToResponse(subject);
    }

    @Transactional
    public SubjectResponse createSubject(SubjectDto dto) {
        if (subjectRepository.findByCode(dto.getCode()).isPresent()) {
            throw new IllegalArgumentException("Subject code already exists: " + dto.getCode());
        }

        Teacher teacher = null;
        if (dto.getTeacherId() != null) {
            teacher = teacherRepository.findById(dto.getTeacherId())
                    .orElseThrow(() -> new IllegalArgumentException("Teacher not found with id: " + dto.getTeacherId()));
        }

        Subject subject = Subject.builder()
                .name(dto.getName())
                .code(dto.getCode())
                .description(dto.getDescription())
                .teacher(teacher)
                .credits(dto.getCredits() != null ? dto.getCredits() : 2)
                .build();

        return mapToResponse(subjectRepository.save(subject));
    }

    @Transactional
    public SubjectResponse updateSubject(Long id, SubjectDto dto) {
        Subject subject = subjectRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Subject not found with id: " + id));

        if (!subject.getCode().equals(dto.getCode()) && subjectRepository.findByCode(dto.getCode()).isPresent()) {
            throw new IllegalArgumentException("Subject code already in use: " + dto.getCode());
        }

        subject.setName(dto.getName());
        subject.setCode(dto.getCode());
        subject.setDescription(dto.getDescription());
        if (dto.getCredits() != null) {
            subject.setCredits(dto.getCredits());
        }

        if (dto.getTeacherId() != null) {
            Teacher teacher = teacherRepository.findById(dto.getTeacherId())
                    .orElseThrow(() -> new IllegalArgumentException("Teacher not found with id: " + dto.getTeacherId()));
            subject.setTeacher(teacher);
        } else {
            subject.setTeacher(null);
        }

        return mapToResponse(subjectRepository.save(subject));
    }

    @Transactional
    public void deleteSubject(Long id) {
        if (!subjectRepository.existsById(id)) {
            throw new IllegalArgumentException("Subject not found with id: " + id);
        }
        subjectRepository.deleteById(id);
    }

    private SubjectResponse mapToResponse(Subject s) {
        return SubjectResponse.builder()
                .id(s.getId())
                .name(s.getName())
                .code(s.getCode())
                .description(s.getDescription())
                .teacherId(s.getTeacher() != null ? s.getTeacher().getId() : null)
                .teacherName(s.getTeacher() != null && s.getTeacher().getUser() != null ? s.getTeacher().getUser().getName() : null)
                .credits(s.getCredits())
                .createdAt(s.getCreatedAt())
                .build();
    }
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/academic/controller/SubjectController.java
package com.elearning.modules.academic.controller;

import com.elearning.common.response.ApiResponse;
import com.elearning.modules.academic.dto.SubjectDto;
import com.elearning.modules.academic.dto.SubjectResponse;
import com.elearning.modules.academic.service.SubjectService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/v1/subjects")
@RequiredArgsConstructor
@Tag(name = "Subject Management", description = "Endpoints for academic subjects")
public class SubjectController {

    private final SubjectService subjectService;

    @GetMapping
    @Operation(summary = "Get all subjects")
    public ResponseEntity<ApiResponse<List<SubjectResponse>>> getAllSubjects() {
        return ResponseEntity.ok(ApiResponse.success(subjectService.getAllSubjects(), "Subjects fetched successfully"));
    }

    @GetMapping("/{id}")
    @Operation(summary = "Get subject by ID")
    public ResponseEntity<ApiResponse<SubjectResponse>> getSubjectById(@PathVariable Long id) {
        return ResponseEntity.ok(ApiResponse.success(subjectService.getSubjectById(id), "Subject details"));
    }

    @PostMapping
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Create subject (Admin only)")
    public ResponseEntity<ApiResponse<SubjectResponse>> createSubject(@Valid @RequestBody SubjectDto dto) {
        SubjectResponse response = subjectService.createSubject(dto);
        return ResponseEntity.status(HttpStatus.CREATED).body(ApiResponse.success(response, "Subject created successfully"));
    }

    @PutMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Update subject (Admin only)")
    public ResponseEntity<ApiResponse<SubjectResponse>> updateSubject(@PathVariable Long id, @Valid @RequestBody SubjectDto dto) {
        return ResponseEntity.ok(ApiResponse.success(subjectService.updateSubject(id, dto), "Subject updated successfully"));
    }

    @DeleteMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Delete subject (Admin only)")
    public ResponseEntity<ApiResponse<Void>> deleteSubject(@PathVariable Long id) {
        subjectService.deleteSubject(id);
        return ResponseEntity.ok(ApiResponse.success(null, "Subject deleted successfully"));
    }
}
EOF`
Ran command: `mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/dto
mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/service
mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/controller
ls -la ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/entity/User.java`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/dto/TeacherDto.java
package com.elearning.modules.user.dto;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class TeacherDto {
    @NotBlank(message = "Name is required")
    private String name;

    @Email(message = "Invalid email format")
    @NotBlank(message = "Email is required")
    private String email;

    private String password;

    private String phone;

    @NotBlank(message = "NIP is required")
    private String nip;

    private String address;

    private String photo;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/dto/TeacherResponse.java
package com.elearning.modules.user.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class TeacherResponse {
    private Long id;
    private Long userId;
    private String name;
    private String email;
    private String phone;
    private String nip;
    private String address;
    private String photo;
    private boolean active;
    private LocalDateTime createdAt;
}
EOF`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/repository/RoleRepository.java`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/service/TeacherService.java
package com.elearning.modules.user.service;

import com.elearning.common.enums.RoleType;
import com.elearning.modules.user.dto.TeacherDto;
import com.elearning.modules.user.dto.TeacherResponse;
import com.elearning.modules.user.entity.Role;
import com.elearning.modules.user.entity.Teacher;
import com.elearning.modules.user.entity.User;
import com.elearning.modules.user.repository.RoleRepository;
import com.elearning.modules.user.repository.TeacherRepository;
import com.elearning.modules.user.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.Collections;
import java.util.HashSet;
import java.util.List;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
public class TeacherService {

    private final TeacherRepository teacherRepository;
    private final UserRepository userRepository;
    private final RoleRepository roleRepository;
    private final PasswordEncoder passwordEncoder;

    @Transactional(readOnly = true)
    public List<TeacherResponse> getAllTeachers() {
        return teacherRepository.findAll().stream()
                .map(this::mapToResponse)
                .collect(Collectors.toList());
    }

    @Transactional(readOnly = true)
    public TeacherResponse getTeacherById(Long id) {
        Teacher teacher = teacherRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Teacher not found with id: " + id));
        return mapToResponse(teacher);
    }

    @Transactional
    public TeacherResponse createTeacher(TeacherDto dto) {
        if (userRepository.existsByEmail(dto.getEmail())) {
            throw new IllegalArgumentException("Email is already registered: " + dto.getEmail());
        }
        if (teacherRepository.existsByNip(dto.getNip())) {
            throw new IllegalArgumentException("NIP is already registered: " + dto.getNip());
        }

        Role teacherRole = roleRepository.findByName(RoleType.ROLE_TEACHER)
                .orElseThrow(() -> new IllegalStateException("ROLE_TEACHER not found"));

        String rawPassword = (dto.getPassword() != null && !dto.getPassword().isBlank()) ? dto.getPassword() : "Teacher@123";

        User user = User.builder()
                .name(dto.getName())
                .email(dto.getEmail())
                .password(passwordEncoder.encode(rawPassword))
                .phone(dto.getPhone())
                .active(true)
                .roles(new HashSet<>(Collections.singletonList(teacherRole)))
                .build();

        user = userRepository.save(user);

        Teacher teacher = Teacher.builder()
                .user(user)
                .nip(dto.getNip())
                .address(dto.getAddress())
                .photo(dto.getPhoto())
                .build();

        return mapToResponse(teacherRepository.save(teacher));
    }

    @Transactional
    public TeacherResponse updateTeacher(Long id, TeacherDto dto) {
        Teacher teacher = teacherRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Teacher not found with id: " + id));

        User user = teacher.getUser();
        if (!user.getEmail().equals(dto.getEmail()) && userRepository.existsByEmail(dto.getEmail())) {
            throw new IllegalArgumentException("Email is already registered: " + dto.getEmail());
        }
        if (!teacher.getNip().equals(dto.getNip()) && teacherRepository.existsByNip(dto.getNip())) {
            throw new IllegalArgumentException("NIP is already registered: " + dto.getNip());
        }

        user.setName(dto.getName());
        user.setEmail(dto.getEmail());
        user.setPhone(dto.getPhone());
        if (dto.getPassword() != null && !dto.getPassword().isBlank()) {
            user.setPassword(passwordEncoder.encode(dto.getPassword()));
        }
        userRepository.save(user);

        teacher.setNip(dto.getNip());
        teacher.setAddress(dto.getAddress());
        if (dto.getPhoto() != null) {
            teacher.setPhoto(dto.getPhoto());
        }

        return mapToResponse(teacherRepository.save(teacher));
    }

    @Transactional
    public void deleteTeacher(Long id) {
        Teacher teacher = teacherRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Teacher not found with id: " + id));
        teacherRepository.delete(teacher);
        userRepository.delete(teacher.getUser());
    }

    private TeacherResponse mapToResponse(Teacher t) {
        return TeacherResponse.builder()
                .id(t.getId())
                .userId(t.getUser().getId())
                .name(t.getUser().getName())
                .email(t.getUser().getEmail())
                .phone(t.getUser().getPhone())
                .nip(t.getNip())
                .address(t.getAddress())
                .photo(t.getPhoto())
                .active(t.getUser().isActive())
                .createdAt(t.getCreatedAt())
                .build();
    }
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/controller/TeacherController.java
package com.elearning.modules.user.controller;

import com.elearning.common.response.ApiResponse;
import com.elearning.modules.user.dto.TeacherDto;
import com.elearning.modules.user.dto.TeacherResponse;
import com.elearning.modules.user.service.TeacherService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/v1/teachers")
@RequiredArgsConstructor
@Tag(name = "Teacher Management", description = "Endpoints for managing teachers")
public class TeacherController {

    private final TeacherService teacherService;

    @GetMapping
    @Operation(summary = "Get all teachers")
    public ResponseEntity<ApiResponse<List<TeacherResponse>>> getAllTeachers() {
        return ResponseEntity.ok(ApiResponse.success(teacherService.getAllTeachers(), "Teachers fetched successfully"));
    }

    @GetMapping("/{id}")
    @Operation(summary = "Get teacher by ID")
    public ResponseEntity<ApiResponse<TeacherResponse>> getTeacherById(@PathVariable Long id) {
        return ResponseEntity.ok(ApiResponse.success(teacherService.getTeacherById(id), "Teacher details"));
    }

    @PostMapping
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Create teacher (Admin only)")
    public ResponseEntity<ApiResponse<TeacherResponse>> createTeacher(@Valid @RequestBody TeacherDto dto) {
        TeacherResponse response = teacherService.createTeacher(dto);
        return ResponseEntity.status(HttpStatus.CREATED).body(ApiResponse.success(response, "Teacher created successfully"));
    }

    @PutMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Update teacher (Admin only)")
    public ResponseEntity<ApiResponse<TeacherResponse>> updateTeacher(@PathVariable Long id, @Valid @RequestBody TeacherDto dto) {
        return ResponseEntity.ok(ApiResponse.success(teacherService.updateTeacher(id, dto), "Teacher updated successfully"));
    }

    @DeleteMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Delete teacher (Admin only)")
    public ResponseEntity<ApiResponse<Void>> deleteTeacher(@PathVariable Long id) {
        teacherService.deleteTeacher(id);
        return ResponseEntity.ok(ApiResponse.success(null, "Teacher deleted successfully"));
    }
}
EOF`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/repository/StudentRepository.java`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/dto/StudentDto.java
package com.elearning.modules.user.dto;

import com.elearning.common.enums.Gender;
import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDate;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class StudentDto {
    @NotBlank(message = "Name is required")
    private String name;

    @Email(message = "Invalid email format")
    @NotBlank(message = "Email is required")
    private String email;

    private String password;

    private String phone;

    @NotBlank(message = "NIS is required")
    private String nis;

    private String nisn;

    private LocalDate birthDate;

    @NotNull(message = "Gender is required (M or F)")
    private Gender gender;

    private String address;

    private String photo;

    private Long classroomId;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/dto/StudentResponse.java
package com.elearning.modules.user.dto;

import com.elearning.common.enums.Gender;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDate;
import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class StudentResponse {
    private Long id;
    private Long userId;
    private String name;
    private String email;
    private String phone;
    private String nis;
    private String nisn;
    private LocalDate birthDate;
    private Gender gender;
    private String address;
    private String photo;
    private Long classroomId;
    private String classroomName;
    private boolean active;
    private LocalDateTime createdAt;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/dto/StudentExamCardResponse.java
package com.elearning.modules.user.dto;

import com.elearning.common.enums.Gender;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDate;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class StudentExamCardResponse {
    private Long studentId;
    private String name;
    private String nis;
    private String nisn;
    private Gender gender;
    private LocalDate birthDate;
    private String classroomName;
    private String academicYear;
    private String barcodeData;
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/service/StudentService.java
package com.elearning.modules.user.service;

import com.elearning.common.enums.Gender;
import com.elearning.common.enums.RoleType;
import com.elearning.modules.academic.entity.Classroom;
import com.elearning.modules.academic.repository.ClassroomRepository;
import com.elearning.modules.user.dto.StudentDto;
import com.elearning.modules.user.dto.StudentExamCardResponse;
import com.elearning.modules.user.dto.StudentResponse;
import com.elearning.modules.user.entity.Role;
import com.elearning.modules.user.entity.Student;
import com.elearning.modules.user.entity.User;
import com.elearning.modules.user.repository.RoleRepository;
import com.elearning.modules.user.repository.StudentRepository;
import com.elearning.modules.user.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.apache.poi.ss.usermodel.*;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.multipart.MultipartFile;

import java.io.InputStream;
import java.util.*;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
@Slf4j
public class StudentService {

    private final StudentRepository studentRepository;
    private final UserRepository userRepository;
    private final RoleRepository roleRepository;
    private final ClassroomRepository classroomRepository;
    private final PasswordEncoder passwordEncoder;

    @Transactional(readOnly = true)
    public List<StudentResponse> getStudents(Long classroomId) {
        List<Student> students;
        if (classroomId != null) {
            students = studentRepository.findByClassroomId(classroomId);
        } else {
            students = studentRepository.findAll();
        }
        return students.stream().map(this::mapToResponse).collect(Collectors.toList());
    }

    @Transactional(readOnly = true)
    public StudentResponse getStudentById(Long id) {
        Student student = studentRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Student not found with id: " + id));
        return mapToResponse(student);
    }

    @Transactional
    public StudentResponse createStudent(StudentDto dto) {
        if (userRepository.existsByEmail(dto.getEmail())) {
            throw new IllegalArgumentException("Email is already registered: " + dto.getEmail());
        }
        if (studentRepository.existsByNis(dto.getNis())) {
            throw new IllegalArgumentException("NIS is already registered: " + dto.getNis());
        }

        Role studentRole = roleRepository.findByName(RoleType.ROLE_STUDENT)
                .orElseThrow(() -> new IllegalStateException("ROLE_STUDENT not found"));

        String rawPassword = (dto.getPassword() != null && !dto.getPassword().isBlank()) ? dto.getPassword() : "Student@123";

        User user = User.builder()
                .name(dto.getName())
                .email(dto.getEmail())
                .password(passwordEncoder.encode(rawPassword))
                .phone(dto.getPhone())
                .active(true)
                .roles(new HashSet<>(Collections.singletonList(studentRole)))
                .build();

        user = userRepository.save(user);

        Classroom classroom = null;
        if (dto.getClassroomId() != null) {
            classroom = classroomRepository.findById(dto.getClassroomId())
                    .orElseThrow(() -> new IllegalArgumentException("Classroom not found with id: " + dto.getClassroomId()));
        }

        Student student = Student.builder()
                .user(user)
                .classroom(classroom)
                .nis(dto.getNis())
                .nisn(dto.getNisn())
                .birthDate(dto.getBirthDate())
                .gender(dto.getGender())
                .address(dto.getAddress())
                .photo(dto.getPhoto())
                .build();

        return mapToResponse(studentRepository.save(student));
    }

    @Transactional
    public StudentResponse updateStudent(Long id, StudentDto dto) {
        Student student = studentRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Student not found with id: " + id));

        User user = student.getUser();
        if (!user.getEmail().equals(dto.getEmail()) && userRepository.existsByEmail(dto.getEmail())) {
            throw new IllegalArgumentException("Email is already registered: " + dto.getEmail());
        }
        if (!student.getNis().equals(dto.getNis()) && studentRepository.existsByNis(dto.getNis())) {
            throw new IllegalArgumentException("NIS is already registered: " + dto.getNis());
        }

        user.setName(dto.getName());
        user.setEmail(dto.getEmail());
        user.setPhone(dto.getPhone());
        if (dto.getPassword() != null && !dto.getPassword().isBlank()) {
            user.setPassword(passwordEncoder.encode(dto.getPassword()));
        }
        userRepository.save(user);

        if (dto.getClassroomId() != null) {
            Classroom classroom = classroomRepository.findById(dto.getClassroomId())
                    .orElseThrow(() -> new IllegalArgumentException("Classroom not found with id: " + dto.getClassroomId()));
            student.setClassroom(classroom);
        } else {
            student.setClassroom(null);
        }

        student.setNis(dto.getNis());
        student.setNisn(dto.getNisn());
        student.setBirthDate(dto.getBirthDate());
        student.setGender(dto.getGender());
        student.setAddress(dto.getAddress());
        if (dto.getPhoto() != null) {
            student.setPhoto(dto.getPhoto());
        }

        return mapToResponse(studentRepository.save(student));
    }

    @Transactional
    public void deleteStudent(Long id) {
        Student student = studentRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Student not found with id: " + id));
        studentRepository.delete(student);
        userRepository.delete(student.getUser());
    }

    @Transactional
    public Map<String, Object> importStudentsFromExcel(MultipartFile file, Long classroomId) {
        Classroom classroom = null;
        if (classroomId != null) {
            classroom = classroomRepository.findById(classroomId)
                    .orElseThrow(() -> new IllegalArgumentException("Classroom not found with id: " + classroomId));
        }

        Role studentRole = roleRepository.findByName(RoleType.ROLE_STUDENT)
                .orElseThrow(() -> new IllegalStateException("ROLE_STUDENT not found"));

        int successCount = 0;
        int errorCount = 0;
        List<String> errors = new ArrayList<>();

        try (InputStream inputStream = file.getInputStream();
             Workbook workbook = WorkbookFactory.create(inputStream)) {

            Sheet sheet = workbook.getSheetAt(0);
            DataFormatter formatter = new DataFormatter();

            // Row 0 is header: [Name, Email, NIS, NISN, Gender (M/F), Phone, Address]
            for (int r = 1; r <= sheet.getLastRowNum(); r++) {
                Row row = sheet.getRow(r);
                if (row == null) continue;

                try {
                    String name = formatter.formatCellValue(row.getCell(0)).trim();
                    String email = formatter.formatCellValue(row.getCell(1)).trim();
                    String nis = formatter.formatCellValue(row.getCell(2)).trim();
                    String nisn = formatter.formatCellValue(row.getCell(3)).trim();
                    String genderStr = formatter.formatCellValue(row.getCell(4)).trim().toUpperCase();
                    String phone = formatter.formatCellValue(row.getCell(5)).trim();
                    String address = formatter.formatCellValue(row.getCell(6)).trim();

                    if (name.isEmpty() || email.isEmpty() || nis.isEmpty()) {
                        continue;
                    }

                    if (userRepository.existsByEmail(email)) {
                        errors.add("Row " + (r + 1) + ": Email " + email + " already exists");
                        errorCount++;
                        continue;
                    }
                    if (studentRepository.existsByNis(nis)) {
                        errors.add("Row " + (r + 1) + ": NIS " + nis + " already exists");
                        errorCount++;
                        continue;
                    }

                    Gender gender = Gender.M;
                    if ("F".equals(genderStr) || "P".equals(genderStr) || "FEMALE".equals(genderStr) || "PEREMPUAN".equals(genderStr)) {
                        gender = Gender.F;
                    }

                    User user = User.builder()
                            .name(name)
                            .email(email)
                            .password(passwordEncoder.encode("Student@123"))
                            .phone(phone.isEmpty() ? null : phone)
                            .active(true)
                            .roles(new HashSet<>(Collections.singletonList(studentRole)))
                            .build();

                    user = userRepository.save(user);

                    Student student = Student.builder()
                            .user(user)
                            .classroom(classroom)
                            .nis(nis)
                            .nisn(nisn.isEmpty() ? null : nisn)
                            .gender(gender)
                            .address(address.isEmpty() ? null : address)
                            .build();

                    studentRepository.save(student);
                    successCount++;
                } catch (Exception ex) {
                    errors.add("Row " + (r + 1) + " error: " + ex.getMessage());
                    errorCount++;
                }
            }
        } catch (Exception e) {
            log.error("Failed to parse excel file", e);
            throw new RuntimeException("Failed to read Excel file: " + e.getMessage());
        }

        Map<String, Object> result = new HashMap<>();
        result.put("successCount", successCount);
        result.put("errorCount", errorCount);
        result.put("errors", errors);
        return result;
    }

    @Transactional(readOnly = true)
    public List<StudentExamCardResponse> getExamCards(Long classroomId) {
        List<Student> students;
        if (classroomId != null) {
            students = studentRepository.findByClassroomId(classroomId);
        } else {
            students = studentRepository.findAll();
        }

        return students.stream().map(s -> {
            String classroomName = s.getClassroom() != null ? s.getClassroom().getName() : "-";
            String academicYear = s.getClassroom() != null ? s.getClassroom().getAcademicYear() : "-";
            String barcodeData = "EXAM-" + s.getNis() + "-" + (s.getNisn() != null ? s.getNisn() : "0");

            return StudentExamCardResponse.builder()
                    .studentId(s.getId())
                    .name(s.getUser().getName())
                    .nis(s.getNis())
                    .nisn(s.getNisn())
                    .gender(s.getGender())
                    .birthDate(s.getBirthDate())
                    .classroomName(classroomName)
                    .academicYear(academicYear)
                    .barcodeData(barcodeData)
                    .build();
        }).collect(Collectors.toList());
    }

    private StudentResponse mapToResponse(Student s) {
        return StudentResponse.builder()
                .id(s.getId())
                .userId(s.getUser().getId())
                .name(s.getUser().getName())
                .email(s.getUser().getEmail())
                .phone(s.getUser().getPhone())
                .nis(s.getNis())
                .nisn(s.getNisn())
                .birthDate(s.getBirthDate())
                .gender(s.getGender())
                .address(s.getAddress())
                .photo(s.getPhoto())
                .classroomId(s.getClassroom() != null ? s.getClassroom().getId() : null)
                .classroomName(s.getClassroom() != null ? s.getClassroom().getName() : null)
                .active(s.getUser().isActive())
                .createdAt(s.getCreatedAt())
                .build();
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/user/controller/StudentController.java
package com.elearning.modules.user.controller;

import com.elearning.common.response.ApiResponse;
import com.elearning.modules.user.dto.StudentDto;
import com.elearning.modules.user.dto.StudentExamCardResponse;
import com.elearning.modules.user.dto.StudentResponse;
import com.elearning.modules.user.service.StudentService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/api/v1/students")
@RequiredArgsConstructor
@Tag(name = "Student Management", description = "Endpoints for managing students, bulk Excel import, and exam cards")
public class StudentController {

    private final StudentService studentService;

    @GetMapping
    @Operation(summary = "Get all students with optional classroom filter")
    public ResponseEntity<ApiResponse<List<StudentResponse>>> getStudents(
            @RequestParam(required = false) Long classroomId) {
        return ResponseEntity.ok(ApiResponse.success(studentService.getStudents(classroomId), "Students fetched successfully"));
    }

    @GetMapping("/{id}")
    @Operation(summary = "Get student by ID")
    public ResponseEntity<ApiResponse<StudentResponse>> getStudentById(@PathVariable Long id) {
        return ResponseEntity.ok(ApiResponse.success(studentService.getStudentById(id), "Student details"));
    }

    @PostMapping
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Create student (Admin only)")
    public ResponseEntity<ApiResponse<StudentResponse>> createStudent(@Valid @RequestBody StudentDto dto) {
        StudentResponse response = studentService.createStudent(dto);
        return ResponseEntity.status(HttpStatus.CREATED).body(ApiResponse.success(response, "Student created successfully"));
    }

    @PutMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Update student (Admin only)")
    public ResponseEntity<ApiResponse<StudentResponse>> updateStudent(@PathVariable Long id, @Valid @RequestBody StudentDto dto) {
        return ResponseEntity.ok(ApiResponse.success(studentService.updateStudent(id, dto), "Student updated successfully"));
    }

    @DeleteMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Delete student (Admin only)")
    public ResponseEntity<ApiResponse<Void>> deleteStudent(@PathVariable Long id) {
        studentService.deleteStudent(id);
        return ResponseEntity.ok(ApiResponse.success(null, "Student deleted successfully"));
    }

    @PostMapping(value = "/import-excel", consumes = MediaType.MULTIPART_FORM_DATA_VALUE)
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Import students from Excel template")
    public ResponseEntity<ApiResponse<Map<String, Object>>> importStudents(
            @RequestParam("file") MultipartFile file,
            @RequestParam(value = "classroomId", required = false) Long classroomId) {
        Map<String, Object> result = studentService.importStudentsFromExcel(file, classroomId);
        return ResponseEntity.ok(ApiResponse.success(result, "Excel import completed"));
    }

    @GetMapping("/exam-cards")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Get exam card print data for students")
    public ResponseEntity<ApiResponse<List<StudentExamCardResponse>>> getExamCards(
            @RequestParam(required = false) Long classroomId) {
        return ResponseEntity.ok(ApiResponse.success(studentService.getExamCards(classroomId), "Exam cards fetched successfully"));
    }
}
EOF`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/entity/LearningMaterial.java
echo "---"
cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/entity/MaterialView.java`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/repository/LearningMaterialRepository.java`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/repository/MaterialViewRepository.java
package com.elearning.modules.material.repository;

import com.elearning.modules.material.entity.MaterialView;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface MaterialViewRepository extends JpaRepository<MaterialView, Long> {
    Optional<MaterialView> findByLearningMaterialIdAndStudentId(Long materialId, Long studentId);
    boolean existsByLearningMaterialIdAndStudentId(Long materialId, Long studentId);
    long countByLearningMaterialId(Long materialId);
    List<MaterialView> findByLearningMaterialId(Long materialId);
}
EOF`
Ran command: `mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/dto
mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/service
mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/controller

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/dto/LearningMaterialDto.java
package com.elearning.modules.material.dto;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class LearningMaterialDto {
    @NotNull(message = "Subject ID is required")
    private Long subjectId;

    private Long classroomId;

    @NotBlank(message = "Title is required")
    private String title;

    private String description;

    @NotBlank(message = "Type is required (e.g. PDF, VIDEO, ARTICLE)")
    private String type;

    private String content;

    private String fileUrl;

    @Builder.Default
    private boolean published = false;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/dto/LearningMaterialResponse.java
package com.elearning.modules.material.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class LearningMaterialResponse {
    private Long id;
    private Long teacherId;
    private String teacherName;
    private Long subjectId;
    private String subjectName;
    private Long classroomId;
    private String classroomName;
    private String title;
    private String description;
    private String type;
    private String content;
    private String fileUrl;
    private String filePath;
    private boolean published;
    private long totalViews;
    private boolean hasViewed;
    private LocalDateTime createdAt;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/dto/MaterialViewerResponse.java
package com.elearning.modules.material.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class MaterialViewerResponse {
    private Long studentId;
    private String studentName;
    private String nis;
    private LocalDateTime viewedAt;
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/service/LearningMaterialService.java
package com.elearning.modules.material.service;

import com.elearning.common.enums.RoleType;
import com.elearning.common.service.FileStorageService;
import com.elearning.modules.academic.entity.Classroom;
import com.elearning.modules.academic.entity.Subject;
import com.elearning.modules.academic.repository.ClassroomRepository;
import com.elearning.modules.academic.repository.SubjectRepository;
import com.elearning.modules.material.dto.LearningMaterialDto;
import com.elearning.modules.material.dto.LearningMaterialResponse;
import com.elearning.modules.material.dto.MaterialViewerResponse;
import com.elearning.modules.material.entity.LearningMaterial;
import com.elearning.modules.material.entity.MaterialView;
import com.elearning.modules.material.repository.LearningMaterialRepository;
import com.elearning.modules.material.repository.MaterialViewRepository;
import com.elearning.modules.user.entity.Student;
import com.elearning.modules.user.entity.Teacher;
import com.elearning.modules.user.entity.User;
import com.elearning.modules.user.repository.StudentRepository;
import com.elearning.modules.user.repository.TeacherRepository;
import com.elearning.security.UserPrincipal;
import lombok.RequiredArgsConstructor;
import org.springframework.security.access.AccessDeniedException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.multipart.MultipartFile;

import java.time.LocalDateTime;
import java.util.List;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
public class LearningMaterialService {

    private final LearningMaterialRepository materialRepository;
    private final MaterialViewRepository materialViewRepository;
    private final TeacherRepository teacherRepository;
    private final StudentRepository studentRepository;
    private final SubjectRepository subjectRepository;
    private final ClassroomRepository classroomRepository;
    private final FileStorageService fileStorageService;

    @Transactional(readOnly = true)
    public List<LearningMaterialResponse> getMaterialsForUser(UserPrincipal principal) {
        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        boolean isTeacher = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_TEACHER"));

        if (isAdmin) {
            return materialRepository.findAll().stream()
                    .map(m -> mapToResponse(m, null))
                    .collect(Collectors.toList());
        }

        if (isTeacher) {
            Teacher teacher = teacherRepository.findByUserId(principal.getId())
                    .orElseThrow(() -> new IllegalArgumentException("Teacher profile not found"));
            return materialRepository.findByTeacherId(teacher.getId()).stream()
                    .map(m -> mapToResponse(m, null))
                    .collect(Collectors.toList());
        }

        // Student
        Student student = studentRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));
        if (student.getClassroom() == null) {
            return List.of();
        }

        return materialRepository.findByPublishedTrueAndClassroomId(student.getClassroom().getId()).stream()
                .map(m -> mapToResponse(m, student.getId()))
                .collect(Collectors.toList());
    }

    @Transactional(readOnly = true)
    public LearningMaterialResponse getMaterialById(Long id, UserPrincipal principal) {
        LearningMaterial material = materialRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Material not found with id: " + id));

        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        boolean isTeacher = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_TEACHER"));

        Long studentId = null;
        if (!isAdmin && !isTeacher) {
            Student student = studentRepository.findByUserId(principal.getId())
                    .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));
            studentId = student.getId();

            if (!material.isPublished()) {
                throw new AccessDeniedException("Material is not published");
            }
            if (material.getClassroom() != null && (student.getClassroom() == null || !material.getClassroom().getId().equals(student.getClassroom().getId()))) {
                throw new AccessDeniedException("You are not authorized to view this material");
            }
        }

        return mapToResponse(material, studentId);
    }

    @Transactional
    public LearningMaterialResponse createMaterial(LearningMaterialDto dto, MultipartFile file, UserPrincipal principal) {
        Teacher teacher = teacherRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Teacher profile not found for user: " + principal.getUsername()));

        Subject subject = subjectRepository.findById(dto.getSubjectId())
                .orElseThrow(() -> new IllegalArgumentException("Subject not found: " + dto.getSubjectId()));

        Classroom classroom = null;
        if (dto.getClassroomId() != null) {
            classroom = classroomRepository.findById(dto.getClassroomId())
                    .orElseThrow(() -> new IllegalArgumentException("Classroom not found: " + dto.getClassroomId()));
        }

        String storedFilePath = null;
        String fileUrl = dto.getFileUrl();
        if (file != null && !file.isEmpty()) {
            storedFilePath = fileStorageService.storeFile(file, "materials");
            fileUrl = "/api/v1/files/" + storedFilePath;
        }

        LearningMaterial material = LearningMaterial.builder()
                .teacher(teacher)
                .subject(subject)
                .classroom(classroom)
                .title(dto.getTitle())
                .description(dto.getDescription())
                .type(dto.getType())
                .content(dto.getContent())
                .fileUrl(fileUrl)
                .filePath(storedFilePath)
                .published(dto.isPublished())
                .build();

        return mapToResponse(materialRepository.save(material), null);
    }

    @Transactional
    public LearningMaterialResponse updateMaterial(Long id, LearningMaterialDto dto, MultipartFile file, UserPrincipal principal) {
        LearningMaterial material = materialRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Material not found: " + id));

        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        if (!isAdmin && !material.getTeacher().getUser().getId().equals(principal.getId())) {
            throw new AccessDeniedException("You are not allowed to update this material");
        }

        Subject subject = subjectRepository.findById(dto.getSubjectId())
                .orElseThrow(() -> new IllegalArgumentException("Subject not found: " + dto.getSubjectId()));

        Classroom classroom = null;
        if (dto.getClassroomId() != null) {
            classroom = classroomRepository.findById(dto.getClassroomId())
                    .orElseThrow(() -> new IllegalArgumentException("Classroom not found: " + dto.getClassroomId()));
        }

        if (file != null && !file.isEmpty()) {
            if (material.getFilePath() != null) {
                fileStorageService.deleteFile(material.getFilePath());
            }
            String storedPath = fileStorageService.storeFile(file, "materials");
            material.setFilePath(storedPath);
            material.setFileUrl("/api/v1/files/" + storedPath);
        } else if (dto.getFileUrl() != null) {
            material.setFileUrl(dto.getFileUrl());
        }

        material.setSubject(subject);
        material.setClassroom(classroom);
        material.setTitle(dto.getTitle());
        material.setDescription(dto.getDescription());
        material.setType(dto.getType());
        material.setContent(dto.getContent());
        material.setPublished(dto.isPublished());

        return mapToResponse(materialRepository.save(material), null);
    }

    @Transactional
    public void deleteMaterial(Long id, UserPrincipal principal) {
        LearningMaterial material = materialRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Material not found: " + id));

        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        if (!isAdmin && !material.getTeacher().getUser().getId().equals(principal.getId())) {
            throw new AccessDeniedException("You are not allowed to delete this material");
        }

        if (material.getFilePath() != null) {
            fileStorageService.deleteFile(material.getFilePath());
        }

        materialRepository.delete(material);
    }

    @Transactional
    public void recordView(Long materialId, UserPrincipal principal) {
        Student student = studentRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));

        LearningMaterial material = materialRepository.findById(materialId)
                .orElseThrow(() -> new IllegalArgumentException("Material not found"));

        if (!materialViewRepository.existsByLearningMaterialIdAndStudentId(material.getId(), student.getId())) {
            MaterialView view = MaterialView.builder()
                    .learningMaterial(material)
                    .student(student)
                    .viewedAt(LocalDateTime.now())
                    .build();
            materialViewRepository.save(view);
        }
    }

    @Transactional(readOnly = true)
    public List<MaterialViewerResponse> getMaterialViewers(Long materialId, UserPrincipal principal) {
        LearningMaterial material = materialRepository.findById(materialId)
                .orElseThrow(() -> new IllegalArgumentException("Material not found"));

        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        if (!isAdmin && !material.getTeacher().getUser().getId().equals(principal.getId())) {
            throw new AccessDeniedException("You are not allowed to view analytics for this material");
        }

        return materialViewRepository.findByLearningMaterialId(materialId).stream()
                .map(v -> MaterialViewerResponse.builder()
                        .studentId(v.getStudent().getId())
                        .studentName(v.getStudent().getUser().getName())
                        .nis(v.getStudent().getNis())
                        .viewedAt(v.getViewedAt())
                        .build())
                .collect(Collectors.toList());
    }

    private LearningMaterialResponse mapToResponse(LearningMaterial m, Long studentId) {
        long totalViews = materialViewRepository.countByLearningMaterialId(m.getId());
        boolean hasViewed = studentId != null && materialViewRepository.existsByLearningMaterialIdAndStudentId(m.getId(), studentId);

        return LearningMaterialResponse.builder()
                .id(m.getId())
                .teacherId(m.getTeacher().getId())
                .teacherName(m.getTeacher().getUser().getName())
                .subjectId(m.getSubject().getId())
                .subjectName(m.getSubject().getName())
                .classroomId(m.getClassroom() != null ? m.getClassroom().getId() : null)
                .classroomName(m.getClassroom() != null ? m.getClassroom().getName() : "All Classes")
                .title(m.getTitle())
                .description(m.getDescription())
                .type(m.getType())
                .content(m.getContent())
                .fileUrl(m.getFileUrl())
                .filePath(m.getFilePath())
                .published(m.isPublished())
                .totalViews(totalViews)
                .hasViewed(hasViewed)
                .createdAt(m.getCreatedAt())
                .build();
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/material/controller/LearningMaterialController.java
package com.elearning.modules.material.controller;

import com.elearning.common.response.ApiResponse;
import com.elearning.modules.material.dto.LearningMaterialDto;
import com.elearning.modules.material.dto.LearningMaterialResponse;
import com.elearning.modules.material.dto.MaterialViewerResponse;
import com.elearning.modules.material.service.LearningMaterialService;
import com.elearning.security.UserPrincipal;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import java.util.List;

@RestController
@RequestMapping("/api/v1/materials")
@RequiredArgsConstructor
@Tag(name = "Learning Materials", description = "Endpoints for learning materials, file uploads, and view tracking")
public class LearningMaterialController {

    private final LearningMaterialService materialService;

    @GetMapping
    @Operation(summary = "Get learning materials accessible to current user")
    public ResponseEntity<ApiResponse<List<LearningMaterialResponse>>> getMaterials(
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(materialService.getMaterialsForUser(principal), "Materials fetched successfully"));
    }

    @GetMapping("/{id}")
    @Operation(summary = "Get learning material by ID")
    public ResponseEntity<ApiResponse<LearningMaterialResponse>> getMaterialById(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(materialService.getMaterialById(id, principal), "Material details"));
    }

    @PostMapping(consumes = MediaType.MULTIPART_FORM_DATA_VALUE)
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Create learning material with optional attachment")
    public ResponseEntity<ApiResponse<LearningMaterialResponse>> createMaterial(
            @RequestPart("data") @Valid LearningMaterialDto dto,
            @RequestPart(value = "file", required = false) MultipartFile file,
            @AuthenticationPrincipal UserPrincipal principal) {
        LearningMaterialResponse response = materialService.createMaterial(dto, file, principal);
        return ResponseEntity.status(HttpStatus.CREATED).body(ApiResponse.success(response, "Material created successfully"));
    }

    @PutMapping(value = "/{id}", consumes = MediaType.MULTIPART_FORM_DATA_VALUE)
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Update learning material with optional attachment")
    public ResponseEntity<ApiResponse<LearningMaterialResponse>> updateMaterial(
            @PathVariable Long id,
            @RequestPart("data") @Valid LearningMaterialDto dto,
            @RequestPart(value = "file", required = false) MultipartFile file,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(materialService.updateMaterial(id, dto, file, principal), "Material updated successfully"));
    }

    @DeleteMapping("/{id}")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Delete learning material")
    public ResponseEntity<ApiResponse<Void>> deleteMaterial(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        materialService.deleteMaterial(id, principal);
        return ResponseEntity.ok(ApiResponse.success(null, "Material deleted successfully"));
    }

    @PostMapping("/{id}/views")
    @PreAuthorize("hasRole('STUDENT')")
    @Operation(summary = "Record material view for current student")
    public ResponseEntity<ApiResponse<Void>> recordView(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        materialService.recordView(id, principal);
        return ResponseEntity.ok(ApiResponse.success(null, "View recorded"));
    }

    @GetMapping("/{id}/viewers")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Get list of students who viewed this material")
    public ResponseEntity<ApiResponse<List<MaterialViewerResponse>>> getMaterialViewers(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(materialService.getMaterialViewers(id, principal), "Viewers fetched successfully"));
    }
}
EOF`
Ran command: `cd ../java-spring-react-elearning && git add . && git commit -m "feat(backend): implement academic, teacher, student with excel import, learning materials with secure file storage"`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/entity/Examination.java
echo "---"
cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/entity/Question.java`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/entity/ExamAttempt.java
echo "---"
cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/entity/ExamAnswer.java`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/repository/ExaminationRepository.java
echo "---"
cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/repository/QuestionRepository.java
echo "---"
cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/repository/ExamAttemptRepository.java
echo "---"
cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/repository/ExamAnswerRepository.java`
Ran command: `mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/dto
mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/service
mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/controller
ls -la ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/dto/ExamDto.java
package com.elearning.modules.exam.dto;

import com.elearning.common.enums.ExamType;
import com.elearning.common.enums.Status;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDate;
import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ExamDto {
    @NotNull(message = "Subject ID is required")
    private Long subjectId;

    @NotNull(message = "Classroom ID is required")
    private Long classroomId;

    @NotBlank(message = "Title is required")
    private String title;

    private String description;

    @Builder.Default
    private ExamType type = ExamType.quiz;

    @Builder.Default
    private Integer durationMinutes = 60;

    @Builder.Default
    private Integer passingScore = 75;

    @NotNull(message = "Start time is required")
    private LocalDateTime startAt;

    @NotNull(message = "End time is required")
    private LocalDateTime endAt;

    private LocalDate examDate;

    @Builder.Default
    private boolean shuffleQuestions = false;

    @Builder.Default
    private boolean shuffleOptions = false;

    @Builder.Default
    private boolean showResult = false;

    @Builder.Default
    private boolean allowRetry = false;

    @Builder.Default
    private Status status = Status.draft;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/dto/ExamResponse.java
package com.elearning.modules.exam.dto;

import com.elearning.common.enums.ExamType;
import com.elearning.common.enums.Status;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDate;
import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ExamResponse {
    private Long id;
    private Long teacherId;
    private String teacherName;
    private Long subjectId;
    private String subjectName;
    private Long classroomId;
    private String classroomName;
    private String title;
    private String description;
    private ExamType type;
    private Integer durationMinutes;
    private Integer passingScore;
    private LocalDateTime startAt;
    private LocalDateTime endAt;
    private LocalDate examDate;
    private Integer totalQuestions;
    private boolean shuffleQuestions;
    private boolean shuffleOptions;
    private boolean showResult;
    private boolean allowRetry;
    private Status status;
    private LocalDateTime createdAt;
    // Student attempt status if fetched in student context
    private String studentAttemptStatus;
    private Integer studentScore;
    private Boolean studentPassed;
    private Long currentAttemptId;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/dto/QuestionDto.java
package com.elearning.modules.exam.dto;

import com.elearning.common.enums.Difficulty;
import com.elearning.common.enums.QuestionType;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.List;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class QuestionDto {
    @NotNull(message = "Examination ID is required")
    private Long examinationId;

    @NotBlank(message = "Question text is required")
    private String questionText;

    @NotNull(message = "Question type is required")
    private QuestionType questionType;

    private List<String> options;

    @NotBlank(message = "Correct answer is required")
    private String correctAnswer;

    private String audioPath;
    private String imagePath;
    private String explanation;

    @Builder.Default
    private Integer points = 1;

    @Builder.Default
    private Difficulty difficulty = Difficulty.medium;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/dto/QuestionResponse.java
package com.elearning.modules.exam.dto;

import com.elearning.common.enums.Difficulty;
import com.elearning.common.enums.QuestionType;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.List;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class QuestionResponse {
    private Long id;
    private Long examinationId;
    private String questionText;
    private QuestionType questionType;
    private List<String> options;
    private String audioPath;
    private String imagePath;
    private Integer points;
    private Difficulty difficulty;
    // Only populated for teachers or when results are officially revealed
    private String correctAnswer;
    private String explanation;
    // Current student's saved answer (if exam is running)
    private String studentAnswer;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/dto/ExamStartResponse.java
package com.elearning.modules.exam.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;
import java.util.List;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ExamStartResponse {
    private Long attemptId;
    private Long examId;
    private String examTitle;
    private Integer durationMinutes;
    private Long remainingSeconds;
    private LocalDateTime startedAt;
    private Integer totalQuestions;
    private List<QuestionResponse> questions;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/dto/SubmitAnswerRequest.java
package com.elearning.modules.exam.dto;

import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class SubmitAnswerRequest {
    @NotNull(message = "Question ID is required")
    private Long questionId;
    private String answerText;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/dto/ViolationReportRequest.java
package com.elearning.modules.exam.dto;

import jakarta.validation.constraints.NotBlank;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ViolationReportRequest {
    @NotBlank(message = "Violation reason is required")
    private String reason;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/dto/EssayGradingDto.java
package com.elearning.modules.exam.dto;

import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class EssayGradingDto {
    @NotNull(message = "Points earned is required")
    private Integer pointsEarned;
    private String feedback;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/dto/ExamMonitorResponse.java
package com.elearning.modules.exam.dto;

import com.elearning.common.enums.AttemptStatus;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ExamMonitorResponse {
    private Long attemptId;
    private Long studentId;
    private String studentName;
    private String nis;
    private AttemptStatus status;
    private Integer score;
    private Boolean passed;
    private Integer violations;
    private Integer answeredCount;
    private Integer totalQuestions;
    private LocalDateTime startedAt;
    private LocalDateTime finishedAt;
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/service/ExamService.java
package com.elearning.modules.exam.service;

import com.elearning.common.enums.Difficulty;
import com.elearning.common.enums.QuestionType;
import com.elearning.common.enums.Status;
import com.elearning.modules.academic.entity.Classroom;
import com.elearning.modules.academic.entity.Subject;
import com.elearning.modules.academic.repository.ClassroomRepository;
import com.elearning.modules.academic.repository.SubjectRepository;
import com.elearning.modules.exam.dto.ExamDto;
import com.elearning.modules.exam.dto.ExamResponse;
import com.elearning.modules.exam.dto.QuestionDto;
import com.elearning.modules.exam.dto.QuestionResponse;
import com.elearning.modules.exam.entity.ExamAttempt;
import com.elearning.modules.exam.entity.Examination;
import com.elearning.modules.exam.entity.Question;
import com.elearning.modules.exam.repository.ExamAttemptRepository;
import com.elearning.modules.exam.repository.ExaminationRepository;
import com.elearning.modules.exam.repository.QuestionRepository;
import com.elearning.modules.user.entity.Student;
import com.elearning.modules.user.entity.Teacher;
import com.elearning.modules.user.repository.StudentRepository;
import com.elearning.modules.user.repository.TeacherRepository;
import com.elearning.security.UserPrincipal;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.apache.poi.ss.usermodel.*;
import org.springframework.security.access.AccessDeniedException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.multipart.MultipartFile;

import java.io.InputStream;
import java.util.*;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
@Slf4j
public class ExamService {

    private final ExaminationRepository examRepository;
    private final QuestionRepository questionRepository;
    private final ExamAttemptRepository attemptRepository;
    private final TeacherRepository teacherRepository;
    private final StudentRepository studentRepository;
    private final SubjectRepository subjectRepository;
    private final ClassroomRepository classroomRepository;

    @Transactional(readOnly = true)
    public List<ExamResponse> getExamsForUser(UserPrincipal principal) {
        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        boolean isTeacher = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_TEACHER"));

        if (isAdmin) {
            return examRepository.findAll().stream()
                    .map(e -> mapToResponse(e, null))
                    .collect(Collectors.toList());
        }

        if (isTeacher) {
            Teacher teacher = teacherRepository.findByUserId(principal.getId())
                    .orElseThrow(() -> new IllegalArgumentException("Teacher profile not found"));
            return examRepository.findByTeacherId(teacher.getId()).stream()
                    .map(e -> mapToResponse(e, null))
                    .collect(Collectors.toList());
        }

        // Student
        Student student = studentRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));
        if (student.getClassroom() == null) {
            return List.of();
        }

        return examRepository.findByClassroomIdAndStatus(student.getClassroom().getId(), Status.published).stream()
                .map(e -> mapToResponse(e, student.getId()))
                .collect(Collectors.toList());
    }

    @Transactional(readOnly = true)
    public ExamResponse getExamById(Long id, UserPrincipal principal) {
        Examination exam = examRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Exam not found with id: " + id));

        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        boolean isTeacher = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_TEACHER"));

        Long studentId = null;
        if (!isAdmin && !isTeacher) {
            Student student = studentRepository.findByUserId(principal.getId())
                    .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));
            studentId = student.getId();

            if (exam.getStatus() != Status.published) {
                throw new AccessDeniedException("Exam is not published");
            }
            if (!exam.getClassroom().getId().equals(student.getClassroom().getId())) {
                throw new AccessDeniedException("Exam is not scheduled for your class");
            }
        }

        return mapToResponse(exam, studentId);
    }

    @Transactional
    public ExamResponse createExam(ExamDto dto, UserPrincipal principal) {
        Teacher teacher = teacherRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Teacher profile not found"));

        Subject subject = subjectRepository.findById(dto.getSubjectId())
                .orElseThrow(() -> new IllegalArgumentException("Subject not found: " + dto.getSubjectId()));

        Classroom classroom = classroomRepository.findById(dto.getClassroomId())
                .orElseThrow(() -> new IllegalArgumentException("Classroom not found: " + dto.getClassroomId()));

        Examination exam = Examination.builder()
                .teacher(teacher)
                .subject(subject)
                .classroom(classroom)
                .title(dto.getTitle())
                .description(dto.getDescription())
                .type(dto.getType())
                .durationMinutes(dto.getDurationMinutes())
                .passingScore(dto.getPassingScore())
                .startAt(dto.getStartAt())
                .endAt(dto.getEndAt())
                .examDate(dto.getExamDate() != null ? dto.getExamDate() : dto.getStartAt().toLocalDate())
                .shuffleQuestions(dto.isShuffleQuestions())
                .shuffleOptions(dto.isShuffleOptions())
                .showResult(dto.isShowResult())
                .allowRetry(dto.isAllowRetry())
                .status(dto.getStatus())
                .totalQuestions(0)
                .build();

        return mapToResponse(examRepository.save(exam), null);
    }

    @Transactional
    public ExamResponse updateExam(Long id, ExamDto dto, UserPrincipal principal) {
        Examination exam = examRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Exam not found: " + id));

        validateTeacherOwnership(exam, principal);

        Subject subject = subjectRepository.findById(dto.getSubjectId())
                .orElseThrow(() -> new IllegalArgumentException("Subject not found: " + dto.getSubjectId()));

        Classroom classroom = classroomRepository.findById(dto.getClassroomId())
                .orElseThrow(() -> new IllegalArgumentException("Classroom not found: " + dto.getClassroomId()));

        exam.setSubject(subject);
        exam.setClassroom(classroom);
        exam.setTitle(dto.getTitle());
        exam.setDescription(dto.getDescription());
        exam.setType(dto.getType());
        exam.setDurationMinutes(dto.getDurationMinutes());
        exam.setPassingScore(dto.getPassingScore());
        exam.setStartAt(dto.getStartAt());
        exam.setEndAt(dto.getEndAt());
        exam.setExamDate(dto.getExamDate() != null ? dto.getExamDate() : dto.getStartAt().toLocalDate());
        exam.setShuffleQuestions(dto.isShuffleQuestions());
        exam.setShuffleOptions(dto.isShuffleOptions());
        exam.setShowResult(dto.isShowResult());
        exam.setAllowRetry(dto.isAllowRetry());
        exam.setStatus(dto.getStatus());

        return mapToResponse(examRepository.save(exam), null);
    }

    @Transactional
    public void deleteExam(Long id, UserPrincipal principal) {
        Examination exam = examRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Exam not found: " + id));

        validateTeacherOwnership(exam, principal);
        examRepository.delete(exam);
    }

    @Transactional
    public ExamResponse updateExamStatus(Long id, Status status, UserPrincipal principal) {
        Examination exam = examRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Exam not found: " + id));

        validateTeacherOwnership(exam, principal);
        exam.setStatus(status);
        return mapToResponse(examRepository.save(exam), null);
    }

    // Questions Management
    @Transactional(readOnly = true)
    public List<QuestionResponse> getQuestions(Long examId, UserPrincipal principal) {
        Examination exam = examRepository.findById(examId)
                .orElseThrow(() -> new IllegalArgumentException("Exam not found: " + examId));

        validateTeacherOwnership(exam, principal);

        return questionRepository.findByExaminationIdOrderByIdAsc(examId).stream()
                .map(q -> QuestionResponse.builder()
                        .id(q.getId())
                        .examinationId(exam.getId())
                        .questionText(q.getQuestionText())
                        .questionType(q.getQuestionType())
                        .options(q.getOptions())
                        .correctAnswer(q.getCorrectAnswer())
                        .explanation(q.getExplanation())
                        .audioPath(q.getAudioPath())
                        .imagePath(q.getImagePath())
                        .points(q.getPoints())
                        .difficulty(q.getDifficulty())
                        .build())
                .collect(Collectors.toList());
    }

    @Transactional
    public QuestionResponse addQuestion(QuestionDto dto, UserPrincipal principal) {
        Examination exam = examRepository.findById(dto.getExaminationId())
                .orElseThrow(() -> new IllegalArgumentException("Exam not found: " + dto.getExaminationId()));

        validateTeacherOwnership(exam, principal);

        Question q = Question.builder()
                .examination(exam)
                .questionText(dto.getQuestionText())
                .questionType(dto.getQuestionType())
                .options(dto.getOptions())
                .correctAnswer(dto.getCorrectAnswer())
                .explanation(dto.getExplanation())
                .audioPath(dto.getAudioPath())
                .imagePath(dto.getImagePath())
                .points(dto.getPoints() != null ? dto.getPoints() : 1)
                .difficulty(dto.getDifficulty() != null ? dto.getDifficulty() : Difficulty.medium)
                .build();

        q = questionRepository.save(q);

        exam.setTotalQuestions(exam.getTotalQuestions() + 1);
        examRepository.save(exam);

        return QuestionResponse.builder()
                .id(q.getId())
                .examinationId(exam.getId())
                .questionText(q.getQuestionText())
                .questionType(q.getQuestionType())
                .options(q.getOptions())
                .correctAnswer(q.getCorrectAnswer())
                .explanation(q.getExplanation())
                .audioPath(q.getAudioPath())
                .imagePath(q.getImagePath())
                .points(q.getPoints())
                .difficulty(q.getDifficulty())
                .build();
    }

    @Transactional
    public QuestionResponse updateQuestion(Long id, QuestionDto dto, UserPrincipal principal) {
        Question q = questionRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Question not found: " + id));

        validateTeacherOwnership(q.getExamination(), principal);

        q.setQuestionText(dto.getQuestionText());
        q.setQuestionType(dto.getQuestionType());
        q.setOptions(dto.getOptions());
        q.setCorrectAnswer(dto.getCorrectAnswer());
        q.setExplanation(dto.getExplanation());
        q.setAudioPath(dto.getAudioPath());
        q.setImagePath(dto.getImagePath());
        if (dto.getPoints() != null) q.setPoints(dto.getPoints());
        if (dto.getDifficulty() != null) q.setDifficulty(dto.getDifficulty());

        q = questionRepository.save(q);

        return QuestionResponse.builder()
                .id(q.getId())
                .examinationId(q.getExamination().getId())
                .questionText(q.getQuestionText())
                .questionType(q.getQuestionType())
                .options(q.getOptions())
                .correctAnswer(q.getCorrectAnswer())
                .explanation(q.getExplanation())
                .audioPath(q.getAudioPath())
                .imagePath(q.getImagePath())
                .points(q.getPoints())
                .difficulty(q.getDifficulty())
                .build();
    }

    @Transactional
    public void deleteQuestion(Long id, UserPrincipal principal) {
        Question q = questionRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Question not found: " + id));

        Examination exam = q.getExamination();
        validateTeacherOwnership(exam, principal);

        questionRepository.delete(q);
        exam.setTotalQuestions(Math.max(0, exam.getTotalQuestions() - 1));
        examRepository.save(exam);
    }

    @Transactional
    public Map<String, Object> importQuestionsFromExcel(Long examId, MultipartFile file, UserPrincipal principal) {
        Examination exam = examRepository.findById(examId)
                .orElseThrow(() -> new IllegalArgumentException("Exam not found: " + examId));

        validateTeacherOwnership(exam, principal);

        int count = 0;
        try (InputStream is = file.getInputStream();
             Workbook workbook = WorkbookFactory.create(is)) {

            Sheet sheet = workbook.getSheetAt(0);
            DataFormatter formatter = new DataFormatter();

            // Columns: [Question Text, Question Type, Opt A, Opt B, Opt C, Opt D, Opt E, Correct Answer, Points, Explanation]
            for (int r = 1; r <= sheet.getLastRowNum(); r++) {
                Row row = sheet.getRow(r);
                if (row == null) continue;

                String text = formatter.formatCellValue(row.getCell(0)).trim();
                String typeStr = formatter.formatCellValue(row.getCell(1)).trim().toUpperCase();
                if (text.isEmpty()) continue;

                QuestionType type = QuestionType.multiple_choice;
                if ("ESSAY".equals(typeStr)) type = QuestionType.essay;
                else if ("TRUE_FALSE".equals(typeStr)) type = QuestionType.true_false;

                List<String> options = new ArrayList<>();
                if (type == QuestionType.multiple_choice) {
                    for (int c = 2; c <= 6; c++) {
                        String opt = formatter.formatCellValue(row.getCell(c)).trim();
                        if (!opt.isEmpty()) options.add(opt);
                    }
                } else if (type == QuestionType.true_false) {
                    options = List.of("Benar", "Salah");
                }

                String correctAnswer = formatter.formatCellValue(row.getCell(7)).trim();
                String pointsStr = formatter.formatCellValue(row.getCell(8)).trim();
                int points = 1;
                try {
                    if (!pointsStr.isEmpty()) points = Integer.parseInt(pointsStr);
                } catch (NumberFormatException ignored) {}

                String explanation = formatter.formatCellValue(row.getCell(9)).trim();

                Question question = Question.builder()
                        .examination(exam)
                        .questionText(text)
                        .questionType(type)
                        .options(options)
                        .correctAnswer(correctAnswer)
                        .explanation(explanation)
                        .points(points)
                        .difficulty(Difficulty.medium)
                        .build();

                questionRepository.save(question);
                count++;
            }

            exam.setTotalQuestions(exam.getTotalQuestions() + count);
            examRepository.save(exam);

        } catch (Exception e) {
            log.error("Failed to parse questions Excel", e);
            throw new RuntimeException("Failed to read Excel questions: " + e.getMessage());
        }

        Map<String, Object> result = new HashMap<>();
        result.put("importedCount", count);
        return result;
    }

    private void validateTeacherOwnership(Examination exam, UserPrincipal principal) {
        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        if (!isAdmin && !exam.getTeacher().getUser().getId().equals(principal.getId())) {
            throw new AccessDeniedException("You are not authorized to modify this exam");
        }
    }

    private ExamResponse mapToResponse(Examination e, Long studentId) {
        String attemptStatus = null;
        Integer studentScore = null;
        Boolean studentPassed = null;
        Long currentAttemptId = null;

        if (studentId != null) {
            Optional<ExamAttempt> attempt = attemptRepository.findByExaminationIdAndStudentId(e.getId(), studentId);
            if (attempt.isPresent()) {
                ExamAttempt att = attempt.get();
                attemptStatus = att.getStatus().name();
                studentScore = att.getScore();
                studentPassed = att.getPassed();
                currentAttemptId = att.getId();
            }
        }

        return ExamResponse.builder()
                .id(e.getId())
                .teacherId(e.getTeacher().getId())
                .teacherName(e.getTeacher().getUser().getName())
                .subjectId(e.getSubject().getId())
                .subjectName(e.getSubject().getName())
                .classroomId(e.getClassroom().getId())
                .classroomName(e.getClassroom().getName())
                .title(e.getTitle())
                .description(e.getDescription())
                .type(e.getType())
                .durationMinutes(e.getDurationMinutes())
                .passingScore(e.getPassingScore())
                .startAt(e.getStartAt())
                .endAt(e.getEndAt())
                .examDate(e.getExamDate())
                .totalQuestions(e.getTotalQuestions())
                .shuffleQuestions(e.isShuffleQuestions())
                .shuffleOptions(e.isShuffleOptions())
                .showResult(e.isShowResult())
                .allowRetry(e.isAllowRetry())
                .status(e.getStatus())
                .createdAt(e.getCreatedAt())
                .studentAttemptStatus(attemptStatus)
                .studentScore(studentScore)
                .studentPassed(studentPassed)
                .currentAttemptId(currentAttemptId)
                .build();
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/service/ExamRunnerService.java
package com.elearning.modules.exam.service;

import com.elearning.common.enums.AttemptStatus;
import com.elearning.common.enums.QuestionType;
import com.elearning.common.enums.Status;
import com.elearning.modules.exam.dto.*;
import com.elearning.modules.exam.entity.ExamAnswer;
import com.elearning.modules.exam.entity.ExamAttempt;
import com.elearning.modules.exam.entity.Examination;
import com.elearning.modules.exam.entity.Question;
import com.elearning.modules.exam.repository.ExamAnswerRepository;
import com.elearning.modules.exam.repository.ExamAttemptRepository;
import com.elearning.modules.exam.repository.ExaminationRepository;
import com.elearning.modules.exam.repository.QuestionRepository;
import com.elearning.modules.user.entity.Student;
import com.elearning.modules.user.repository.StudentRepository;
import com.elearning.security.UserPrincipal;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.security.access.AccessDeniedException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.Duration;
import java.time.LocalDateTime;
import java.util.*;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
@Slf4j
public class ExamRunnerService {

    private final ExaminationRepository examRepository;
    private final QuestionRepository questionRepository;
    private final ExamAttemptRepository attemptRepository;
    private final ExamAnswerRepository answerRepository;
    private final StudentRepository studentRepository;

    @Transactional
    public ExamStartResponse startOrResumeAttempt(Long examId, UserPrincipal principal) {
        Student student = studentRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));

        Examination exam = examRepository.findById(examId)
                .orElseThrow(() -> new IllegalArgumentException("Exam not found: " + examId));

        if (exam.getStatus() != Status.published) {
            throw new IllegalStateException("Exam is not active or published");
        }

        LocalDateTime now = LocalDateTime.now();
        if (now.isBefore(exam.getStartAt())) {
            throw new IllegalStateException("Exam has not started yet. Starts at: " + exam.getStartAt());
        }
        if (now.isAfter(exam.getEndAt())) {
            throw new IllegalStateException("Exam schedule has ended at: " + exam.getEndAt());
        }

        if (student.getClassroom() == null || !student.getClassroom().getId().equals(exam.getClassroom().getId())) {
            throw new AccessDeniedException("This exam is not assigned to your classroom");
        }

        Optional<ExamAttempt> existingAttemptOpt = attemptRepository.findByExaminationIdAndStudentId(examId, student.getId());
        ExamAttempt attempt;

        if (existingAttemptOpt.isPresent()) {
            attempt = existingAttemptOpt.get();
            if (attempt.getStatus() == AttemptStatus.completed || attempt.getStatus() == AttemptStatus.submitted) {
                if (!exam.isAllowRetry()) {
                    throw new IllegalStateException("Exam already completed. Retries are not permitted.");
                } else {
                    attempt = createNewAttempt(exam, student, attempt.getAttemptNumber() + 1);
                }
            }
        } else {
            attempt = createNewAttempt(exam, student, 1);
        }

        // Calculate remaining seconds
        long remainingSeconds = calculateRemainingSeconds(exam, attempt, now);
        if (remainingSeconds <= 0 && attempt.getStatus() == AttemptStatus.in_progress) {
            autoFinalizeAttempt(attempt, exam);
            throw new IllegalStateException("Exam duration has expired");
        }

        // Fetch questions and existing answers
        List<Question> questions = questionRepository.findByExaminationIdOrderByIdAsc(examId);
        if (exam.isShuffleQuestions()) {
            // Seeded shuffle so the order is consistent for this attempt
            Collections.shuffle(questions, new Random(attempt.getId() * 31));
        }

        Map<Long, String> studentAnswersMap = answerRepository.findByExamAttemptId(attempt.getId())
                .stream()
                .filter(a -> a.getAnswerText() != null)
                .collect(Collectors.toMap(a -> a.getQuestion().getId(), ExamAnswer::getAnswerText, (a1, a2) -> a1));

        List<QuestionResponse> questionResponses = questions.stream().map(q -> {
            List<String> options = q.getOptions();
            if (options != null && exam.isShuffleOptions()) {
                options = new ArrayList<>(options);
                Collections.shuffle(options, new Random(attempt.getId() * 17 + q.getId()));
            }

            return QuestionResponse.builder()
                    .id(q.getId())
                    .examinationId(exam.getId())
                    .questionText(q.getQuestionText())
                    .questionType(q.getQuestionType())
                    .options(options)
                    .audioPath(q.getAudioPath())
                    .imagePath(q.getImagePath())
                    .points(q.getPoints())
                    .difficulty(q.getDifficulty())
                    .correctAnswer(null) // STRICT SECURITY: hide answer key
                    .explanation(null)   // STRICT SECURITY: hide explanation
                    .studentAnswer(studentAnswersMap.get(q.getId()))
                    .build();
        }).collect(Collectors.toList());

        return ExamStartResponse.builder()
                .attemptId(attempt.getId())
                .examId(exam.getId())
                .examTitle(exam.getTitle())
                .durationMinutes(exam.getDurationMinutes())
                .remainingSeconds(remainingSeconds)
                .startedAt(attempt.getStartedAt())
                .totalQuestions(questionResponses.size())
                .questions(questionResponses)
                .build();
    }

    @Transactional
    public void saveAnswer(Long attemptId, SubmitAnswerRequest req, UserPrincipal principal) {
        ExamAttempt attempt = getValidatedStudentAttempt(attemptId, principal);

        if (attempt.getStatus() != AttemptStatus.in_progress) {
            throw new IllegalStateException("Cannot save answer: attempt is " + attempt.getStatus());
        }

        long remaining = calculateRemainingSeconds(attempt.getExamination(), attempt, LocalDateTime.now());
        if (remaining <= 0) {
            autoFinalizeAttempt(attempt, attempt.getExamination());
            throw new IllegalStateException("Exam duration has expired. Attempt automatically submitted.");
        }

        Question question = questionRepository.findById(req.getQuestionId())
                .orElseThrow(() -> new IllegalArgumentException("Question not found"));

        if (!question.getExamination().getId().equals(attempt.getExamination().getId())) {
            throw new IllegalArgumentException("Question does not belong to this examination");
        }

        ExamAnswer answer = answerRepository.findByExamAttemptIdAndQuestionId(attempt.getId(), question.getId())
                .orElseGet(() -> ExamAnswer.builder()
                        .examAttempt(attempt)
                        .question(question)
                        .pointsEarned(0)
                        .build());

        answer.setAnswerText(req.getAnswerText());
        answer.setAnsweredAt(LocalDateTime.now());
        answerRepository.save(answer);
    }

    @Transactional
    public int reportViolation(Long attemptId, ViolationReportRequest req, UserPrincipal principal) {
        ExamAttempt attempt = getValidatedStudentAttempt(attemptId, principal);

        int currentViolations = attempt.getViolations() + 1;
        attempt.setViolations(currentViolations);

        log.warn("Exam violation reported for student {} in attempt {}: {} (Total violations: {})",
                principal.getUsername(), attemptId, req.getReason(), currentViolations);

        // Auto submit if student exceeds maximum violations threshold (5)
        if (currentViolations >= 5 && attempt.getStatus() == AttemptStatus.in_progress) {
            log.warn("Exceeded max violations. Auto-submitting attempt {}", attemptId);
            autoFinalizeAttempt(attempt, attempt.getExamination());
        } else {
            attemptRepository.save(attempt);
        }

        return currentViolations;
    }

    @Transactional
    public ExamAttempt submitAttempt(Long attemptId, UserPrincipal principal) {
        ExamAttempt attempt = getValidatedStudentAttempt(attemptId, principal);
        if (attempt.getStatus() != AttemptStatus.in_progress) {
            return attempt;
        }

        return autoFinalizeAttempt(attempt, attempt.getExamination());
    }

    @Transactional
    public ExamAttempt autoFinalizeAttempt(ExamAttempt attempt, Examination exam) {
        List<Question> questions = questionRepository.findByExaminationIdOrderByIdAsc(exam.getId());
        List<ExamAnswer> answers = answerRepository.findByExamAttemptId(attempt.getId());

        Map<Long, ExamAnswer> answersByQuestionId = answers.stream()
                .collect(Collectors.toMap(a -> a.getQuestion().getId(), a -> a, (a1, a2) -> a1));

        int totalPossiblePoints = 0;
        int totalEarnedPoints = 0;
        boolean hasEssay = false;

        for (Question q : questions) {
            int qPoints = q.getPoints() != null ? q.getPoints() : 1;
            totalPossiblePoints += qPoints;

            ExamAnswer ans = answersByQuestionId.get(q.getId());
            if (ans == null) {
                // Unanswered question
                ans = ExamAnswer.builder()
                        .examAttempt(attempt)
                        .question(q)
                        .answerText(null)
                        .pointsEarned(0)
                        .correct(false)
                        .build();
                answerRepository.save(ans);
                continue;
            }

            if (q.getQuestionType() == QuestionType.multiple_choice || q.getQuestionType() == QuestionType.true_false) {
                boolean isCorrect = ans.getAnswerText() != null &&
                        q.getCorrectAnswer() != null &&
                        ans.getAnswerText().trim().equalsIgnoreCase(q.getCorrectAnswer().trim());

                ans.setCorrect(isCorrect);
                ans.setPointsEarned(isCorrect ? qPoints : 0);
                if (isCorrect) {
                    totalEarnedPoints += qPoints;
                }
                answerRepository.save(ans);
            } else if (q.getQuestionType() == QuestionType.essay) {
                hasEssay = true;
                ans.setCorrect(null);
                ans.setPointsEarned(0);
                answerRepository.save(ans);
            }
        }

        int score = totalPossiblePoints > 0 ? (totalEarnedPoints * 100) / totalPossiblePoints : 0;
        attempt.setScore(score);
        attempt.setPassed(score >= exam.getPassingScore());
        attempt.setStatus(hasEssay ? AttemptStatus.needs_grading : AttemptStatus.completed);
        attempt.setFinishedAt(LocalDateTime.now());

        return attemptRepository.save(attempt);
    }

    @Transactional
    public void gradeEssay(Long attemptId, Long answerId, EssayGradingDto dto, UserPrincipal principal) {
        ExamAttempt attempt = attemptRepository.findById(attemptId)
                .orElseThrow(() -> new IllegalArgumentException("Attempt not found"));

        validateTeacherOrAdmin(attempt.getExamination(), principal);

        ExamAnswer answer = answerRepository.findById(answerId)
                .orElseThrow(() -> new IllegalArgumentException("Answer not found"));

        if (!answer.getExamAttempt().getId().equals(attempt.getId())) {
            throw new IllegalArgumentException("Answer does not belong to this attempt");
        }

        answer.setPointsEarned(dto.getPointsEarned());
        answer.setFeedback(dto.getFeedback());
        answer.setCorrect(dto.getPointsEarned() > 0);
        answerRepository.save(answer);

        // Recalculate total score
        List<Question> questions = questionRepository.findByExaminationIdOrderByIdAsc(attempt.getExamination().getId());
        List<ExamAnswer> answers = answerRepository.findByExamAttemptId(attempt.getId());

        int totalPossible = questions.stream().mapToInt(q -> q.getPoints() != null ? q.getPoints() : 1).sum();
        int totalEarned = answers.stream().mapToInt(ExamAnswer::getPointsEarned).sum();

        int score = totalPossible > 0 ? (totalEarned * 100) / totalPossible : 0;
        attempt.setScore(score);
        attempt.setPassed(score >= attempt.getExamination().getPassingScore());
        attempt.setStatus(AttemptStatus.completed);
        attemptRepository.save(attempt);
    }

    @Transactional(readOnly = true)
    public List<ExamMonitorResponse> getExamMonitor(Long examId, UserPrincipal principal) {
        Examination exam = examRepository.findById(examId)
                .orElseThrow(() -> new IllegalArgumentException("Exam not found: " + examId));

        validateTeacherOrAdmin(exam, principal);

        List<ExamAttempt> attempts = attemptRepository.findByExaminationId(examId);

        return attempts.stream().map(att -> {
            int answeredCount = answerRepository.findByExamAttemptId(att.getId()).size();
            return ExamMonitorResponse.builder()
                    .attemptId(att.getId())
                    .studentId(att.getStudent().getId())
                    .studentName(att.getStudent().getUser().getName())
                    .nis(att.getStudent().getNis())
                    .status(att.getStatus())
                    .score(att.getScore())
                    .passed(att.getPassed())
                    .violations(att.getViolations())
                    .answeredCount(answeredCount)
                    .totalQuestions(exam.getTotalQuestions())
                    .startedAt(att.getStartedAt())
                    .finishedAt(att.getFinishedAt())
                    .build();
        }).collect(Collectors.toList());
    }

    private ExamAttempt createNewAttempt(Examination exam, Student student, int attemptNumber) {
        ExamAttempt attempt = ExamAttempt.builder()
                .examination(exam)
                .student(student)
                .attemptNumber(attemptNumber)
                .status(AttemptStatus.in_progress)
                .violations(0)
                .startedAt(LocalDateTime.now())
                .build();
        return attemptRepository.save(attempt);
    }

    private long calculateRemainingSeconds(Examination exam, ExamAttempt attempt, LocalDateTime now) {
        LocalDateTime examDeadline = attempt.getStartedAt().plusMinutes(exam.getDurationMinutes());
        if (exam.getEndAt() != null && exam.getEndAt().isBefore(examDeadline)) {
            examDeadline = exam.getEndAt();
        }
        return Math.max(0, Duration.between(now, examDeadline).getSeconds());
    }

    private ExamAttempt getValidatedStudentAttempt(Long attemptId, UserPrincipal principal) {
        ExamAttempt attempt = attemptRepository.findById(attemptId)
                .orElseThrow(() -> new IllegalArgumentException("Attempt not found: " + attemptId));

        Student student = studentRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));

        if (!attempt.getStudent().getId().equals(student.getId())) {
            throw new AccessDeniedException("This attempt does not belong to you");
        }

        return attempt;
    }

    private void validateTeacherOrAdmin(Examination exam, UserPrincipal principal) {
        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        if (!isAdmin && !exam.getTeacher().getUser().getId().equals(principal.getId())) {
            throw new AccessDeniedException("You are not authorized for this exam");
        }
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/controller/ExamController.java
package com.elearning.modules.exam.controller;

import com.elearning.common.enums.Status;
import com.elearning.common.response.ApiResponse;
import com.elearning.modules.exam.dto.ExamDto;
import com.elearning.modules.exam.dto.ExamResponse;
import com.elearning.modules.exam.dto.QuestionDto;
import com.elearning.modules.exam.dto.QuestionResponse;
import com.elearning.modules.exam.service.ExamService;
import com.elearning.security.UserPrincipal;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/api/v1/exams")
@RequiredArgsConstructor
@Tag(name = "Examination Management", description = "Endpoints for managing exams and question bank")
public class ExamController {

    private final ExamService examService;

    @GetMapping
    @Operation(summary = "Get exams accessible to current user")
    public ResponseEntity<ApiResponse<List<ExamResponse>>> getExams(
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(examService.getExamsForUser(principal), "Exams fetched successfully"));
    }

    @GetMapping("/{id}")
    @Operation(summary = "Get exam details by ID")
    public ResponseEntity<ApiResponse<ExamResponse>> getExamById(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(examService.getExamById(id, principal), "Exam details"));
    }

    @PostMapping
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Create an examination")
    public ResponseEntity<ApiResponse<ExamResponse>> createExam(
            @Valid @RequestBody ExamDto dto,
            @AuthenticationPrincipal UserPrincipal principal) {
        ExamResponse response = examService.createExam(dto, principal);
        return ResponseEntity.status(HttpStatus.CREATED).body(ApiResponse.success(response, "Exam created successfully"));
    }

    @PutMapping("/{id}")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Update an examination")
    public ResponseEntity<ApiResponse<ExamResponse>> updateExam(
            @PathVariable Long id,
            @Valid @RequestBody ExamDto dto,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(examService.updateExam(id, dto, principal), "Exam updated successfully"));
    }

    @DeleteMapping("/{id}")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Delete an examination")
    public ResponseEntity<ApiResponse<Void>> deleteExam(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        examService.deleteExam(id, principal);
        return ResponseEntity.ok(ApiResponse.success(null, "Exam deleted successfully"));
    }

    @PatchMapping("/{id}/status")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Update exam publish status (draft/published/archived)")
    public ResponseEntity<ApiResponse<ExamResponse>> updateStatus(
            @PathVariable Long id,
            @RequestParam Status status,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(examService.updateExamStatus(id, status, principal), "Status updated"));
    }

    // Question Bank Endpoints
    @GetMapping("/{id}/questions")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Get all questions for an examination (Teacher/Admin)")
    public ResponseEntity<ApiResponse<List<QuestionResponse>>> getQuestions(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(examService.getQuestions(id, principal), "Questions fetched successfully"));
    }

    @PostMapping("/{id}/questions")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Add a question to an examination")
    public ResponseEntity<ApiResponse<QuestionResponse>> addQuestion(
            @PathVariable Long id,
            @Valid @RequestBody QuestionDto dto,
            @AuthenticationPrincipal UserPrincipal principal) {
        dto.setExaminationId(id);
        return ResponseEntity.status(HttpStatus.CREATED).body(ApiResponse.success(examService.addQuestion(dto, principal), "Question added"));
    }

    @PutMapping("/{id}/questions/{questionId}")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Update a question")
    public ResponseEntity<ApiResponse<QuestionResponse>> updateQuestion(
            @PathVariable Long id,
            @PathVariable Long questionId,
            @Valid @RequestBody QuestionDto dto,
            @AuthenticationPrincipal UserPrincipal principal) {
        dto.setExaminationId(id);
        return ResponseEntity.ok(ApiResponse.success(examService.updateQuestion(questionId, dto, principal), "Question updated"));
    }

    @DeleteMapping("/{id}/questions/{questionId}")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Delete a question")
    public ResponseEntity<ApiResponse<Void>> deleteQuestion(
            @PathVariable Long id,
            @PathVariable Long questionId,
            @AuthenticationPrincipal UserPrincipal principal) {
        examService.deleteQuestion(questionId, principal);
        return ResponseEntity.ok(ApiResponse.success(null, "Question deleted"));
    }

    @PostMapping(value = "/{id}/questions/import-excel", consumes = MediaType.MULTIPART_FORM_DATA_VALUE)
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Bulk import questions from Excel file")
    public ResponseEntity<ApiResponse<Map<String, Object>>> importQuestions(
            @PathVariable Long id,
            @RequestParam("file") MultipartFile file,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(examService.importQuestionsFromExcel(id, file, principal), "Questions imported successfully"));
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/controller/ExamRunnerController.java
package com.elearning.modules.exam.controller;

import com.elearning.common.response.ApiResponse;
import com.elearning.modules.exam.dto.*;
import com.elearning.modules.exam.entity.ExamAttempt;
import com.elearning.modules.exam.service.ExamRunnerService;
import com.elearning.security.UserPrincipal;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/api/v1/exams")
@RequiredArgsConstructor
@Tag(name = "CBT Exam Runner & Live Monitor", description = "Endpoints for students taking exams, autosaving, anti-cheat detection, and live monitoring")
public class ExamRunnerController {

    private final ExamRunnerService examRunnerService;

    @PostMapping("/{id}/start")
    @PreAuthorize("hasRole('STUDENT')")
    @Operation(summary = "Start or resume examination session for student")
    public ResponseEntity<ApiResponse<ExamStartResponse>> startExam(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        ExamStartResponse response = examRunnerService.startOrResumeAttempt(id, principal);
        return ResponseEntity.ok(ApiResponse.success(response, "Exam session started"));
    }

    @PostMapping("/attempts/{attemptId}/answer")
    @PreAuthorize("hasRole('STUDENT')")
    @Operation(summary = "Autosave student's answer for a question")
    public ResponseEntity<ApiResponse<Void>> saveAnswer(
            @PathVariable Long attemptId,
            @Valid @RequestBody SubmitAnswerRequest req,
            @AuthenticationPrincipal UserPrincipal principal) {
        examRunnerService.saveAnswer(attemptId, req, principal);
        return ResponseEntity.ok(ApiResponse.success(null, "Answer saved"));
    }

    @PostMapping("/attempts/{attemptId}/violation")
    @PreAuthorize("hasRole('STUDENT')")
    @Operation(summary = "Report anti-cheat violation (tab-switch, fullscreen exit)")
    public ResponseEntity<ApiResponse<Map<String, Object>>> reportViolation(
            @PathVariable Long attemptId,
            @Valid @RequestBody ViolationReportRequest req,
            @AuthenticationPrincipal UserPrincipal principal) {
        int violations = examRunnerService.reportViolation(attemptId, req, principal);
        return ResponseEntity.ok(ApiResponse.success(Map.of("violations", violations), "Violation recorded"));
    }

    @PostMapping("/attempts/{attemptId}/submit")
    @PreAuthorize("hasRole('STUDENT')")
    @Operation(summary = "Finalize and submit examination attempt")
    public ResponseEntity<ApiResponse<Map<String, Object>>> submitAttempt(
            @PathVariable Long attemptId,
            @AuthenticationPrincipal UserPrincipal principal) {
        ExamAttempt attempt = examRunnerService.submitAttempt(attemptId, principal);
        return ResponseEntity.ok(ApiResponse.success(Map.of(
                "attemptId", attempt.getId(),
                "status", attempt.getStatus(),
                "score", attempt.getScore() != null ? attempt.getScore() : 0,
                "passed", attempt.getPassed() != null ? attempt.getPassed() : false
        ), "Exam submitted successfully"));
    }

    @PostMapping("/attempts/{attemptId}/answers/{answerId}/grade-essay")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Grade an essay answer manually")
    public ResponseEntity<ApiResponse<Void>> gradeEssay(
            @PathVariable Long attemptId,
            @PathVariable Long answerId,
            @Valid @RequestBody EssayGradingDto dto,
            @AuthenticationPrincipal UserPrincipal principal) {
        examRunnerService.gradeEssay(attemptId, answerId, dto, principal);
        return ResponseEntity.ok(ApiResponse.success(null, "Essay answer graded successfully"));
    }

    @GetMapping("/{id}/monitor")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Get live examination monitoring room data")
    public ResponseEntity<ApiResponse<List<ExamMonitorResponse>>> getExamMonitor(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        List<ExamMonitorResponse> monitorData = examRunnerService.getExamMonitor(id, principal);
        return ResponseEntity.ok(ApiResponse.success(monitorData, "Exam monitor data fetched"));
    }
}
EOF`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/entity/Assignment.java
echo "---"
cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/entity/AssignmentSubmission.java
echo "---"
cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/entity/AssignmentDiscussion.java`
Ran command: `cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/repository/AssignmentRepository.java
echo "---"
cat ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/repository/AssignmentSubmissionRepository.java`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/repository/AssignmentDiscussionRepository.java
package com.elearning.modules.assignment.repository;

import com.elearning.modules.assignment.entity.AssignmentDiscussion;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface AssignmentDiscussionRepository extends JpaRepository<AssignmentDiscussion, Long> {
    List<AssignmentDiscussion> findByAssignmentIdAndParentIsNullOrderByCreatedAtAsc(Long assignmentId);
    List<AssignmentDiscussion> findByParentIdOrderByCreatedAtAsc(Long parentId);
}
EOF`
Ran command: `mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/dto
mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/service
mkdir -p ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/controller
ls -la ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/dto/AssignmentDto.java
package com.elearning.modules.assignment.dto;

import com.elearning.common.enums.Status;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class AssignmentDto {
    @NotNull(message = "Subject ID is required")
    private Long subjectId;

    @NotNull(message = "Classroom ID is required")
    private Long classroomId;

    @NotBlank(message = "Title is required")
    private String title;

    private String description;
    private String instructions;

    @Builder.Default
    private Integer maxScore = 100;

    @NotNull(message = "Due date is required")
    private LocalDateTime dueDate;

    @Builder.Default
    private boolean allowLateSubmission = false;

    @Builder.Default
    private Status status = Status.draft;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/dto/AssignmentResponse.java
package com.elearning.modules.assignment.dto;

import com.elearning.common.enums.Status;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class AssignmentResponse {
    private Long id;
    private Long teacherId;
    private String teacherName;
    private Long subjectId;
    private String subjectName;
    private Long classroomId;
    private String classroomName;
    private String title;
    private String description;
    private String instructions;
    private Integer maxScore;
    private LocalDateTime dueDate;
    private boolean allowLateSubmission;
    private Status status;
    private LocalDateTime createdAt;
    // Student submission metadata if requested in student context
    private boolean hasSubmitted;
    private Integer studentScore;
    private String studentSubmissionStatus;
    private LocalDateTime studentSubmittedAt;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/dto/SubmissionResponse.java
package com.elearning.modules.assignment.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class SubmissionResponse {
    private Long id;
    private Long assignmentId;
    private Long studentId;
    private String studentName;
    private String studentNis;
    private String filePath;
    private String notes;
    private Integer score;
    private String feedback;
    private String status;
    private LocalDateTime submittedAt;
    private LocalDateTime gradedAt;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/dto/GradeSubmissionDto.java
package com.elearning.modules.assignment.dto;

import jakarta.validation.constraints.Max;
import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class GradeSubmissionDto {
    @NotNull(message = "Score is required")
    @Min(value = 0, message = "Score must be at least 0")
    @Max(value = 100, message = "Score cannot exceed 100")
    private Integer score;

    private String feedback;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/dto/DiscussionDto.java
package com.elearning.modules.assignment.dto;

import jakarta.validation.constraints.NotBlank;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class DiscussionDto {
    @NotBlank(message = "Message cannot be empty")
    private String message;
    private Long parentId;
}
EOF

cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/dto/DiscussionResponse.java
package com.elearning.modules.assignment.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;
import java.util.List;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class DiscussionResponse {
    private Long id;
    private Long assignmentId;
    private Long userId;
    private String userName;
    private String userRole;
    private String message;
    private LocalDateTime createdAt;
    private List<DiscussionResponse> replies;
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/service/AssignmentService.java
package com.elearning.modules.assignment.service;

import com.elearning.common.enums.Status;
import com.elearning.common.service.FileStorageService;
import com.elearning.modules.academic.entity.Classroom;
import com.elearning.modules.academic.entity.Subject;
import com.elearning.modules.academic.repository.ClassroomRepository;
import com.elearning.modules.academic.repository.SubjectRepository;
import com.elearning.modules.assignment.dto.*;
import com.elearning.modules.assignment.entity.Assignment;
import com.elearning.modules.assignment.entity.AssignmentDiscussion;
import com.elearning.modules.assignment.entity.AssignmentSubmission;
import com.elearning.modules.assignment.repository.AssignmentDiscussionRepository;
import com.elearning.modules.assignment.repository.AssignmentRepository;
import com.elearning.modules.assignment.repository.AssignmentSubmissionRepository;
import com.elearning.modules.user.entity.Student;
import com.elearning.modules.user.entity.Teacher;
import com.elearning.modules.user.entity.User;
import com.elearning.modules.user.repository.StudentRepository;
import com.elearning.modules.user.repository.TeacherRepository;
import com.elearning.modules.user.repository.UserRepository;
import com.elearning.security.UserPrincipal;
import lombok.RequiredArgsConstructor;
import org.springframework.security.access.AccessDeniedException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.multipart.MultipartFile;

import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
public class AssignmentService {

    private final AssignmentRepository assignmentRepository;
    private final AssignmentSubmissionRepository submissionRepository;
    private final AssignmentDiscussionRepository discussionRepository;
    private final TeacherRepository teacherRepository;
    private final StudentRepository studentRepository;
    private final UserRepository userRepository;
    private final SubjectRepository subjectRepository;
    private final ClassroomRepository classroomRepository;
    private final FileStorageService fileStorageService;

    @Transactional(readOnly = true)
    public List<AssignmentResponse> getAssignmentsForUser(UserPrincipal principal) {
        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        boolean isTeacher = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_TEACHER"));

        if (isAdmin) {
            return assignmentRepository.findAll().stream()
                    .map(a -> mapToResponse(a, null))
                    .collect(Collectors.toList());
        }

        if (isTeacher) {
            Teacher teacher = teacherRepository.findByUserId(principal.getId())
                    .orElseThrow(() -> new IllegalArgumentException("Teacher profile not found"));
            return assignmentRepository.findByTeacherId(teacher.getId()).stream()
                    .map(a -> mapToResponse(a, null))
                    .collect(Collectors.toList());
        }

        // Student
        Student student = studentRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));
        if (student.getClassroom() == null) {
            return List.of();
        }

        return assignmentRepository.findByClassroomIdAndStatus(student.getClassroom().getId(), Status.published).stream()
                .map(a -> mapToResponse(a, student.getId()))
                .collect(Collectors.toList());
    }

    @Transactional(readOnly = true)
    public AssignmentResponse getAssignmentById(Long id, UserPrincipal principal) {
        Assignment assignment = assignmentRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Assignment not found with id: " + id));

        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        boolean isTeacher = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_TEACHER"));

        Long studentId = null;
        if (!isAdmin && !isTeacher) {
            Student student = studentRepository.findByUserId(principal.getId())
                    .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));
            studentId = student.getId();

            if (assignment.getStatus() != Status.published) {
                throw new AccessDeniedException("Assignment is not published");
            }
            if (!assignment.getClassroom().getId().equals(student.getClassroom().getId())) {
                throw new AccessDeniedException("Assignment is not for your class");
            }
        }

        return mapToResponse(assignment, studentId);
    }

    @Transactional
    public AssignmentResponse createAssignment(AssignmentDto dto, UserPrincipal principal) {
        Teacher teacher = teacherRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Teacher profile not found"));

        Subject subject = subjectRepository.findById(dto.getSubjectId())
                .orElseThrow(() -> new IllegalArgumentException("Subject not found: " + dto.getSubjectId()));

        Classroom classroom = classroomRepository.findById(dto.getClassroomId())
                .orElseThrow(() -> new IllegalArgumentException("Classroom not found: " + dto.getClassroomId()));

        Assignment assignment = Assignment.builder()
                .teacher(teacher)
                .subject(subject)
                .classroom(classroom)
                .title(dto.getTitle())
                .description(dto.getDescription())
                .instructions(dto.getInstructions())
                .maxScore(dto.getMaxScore())
                .dueDate(dto.getDueDate())
                .allowLateSubmission(dto.isAllowLateSubmission())
                .status(dto.getStatus())
                .build();

        return mapToResponse(assignmentRepository.save(assignment), null);
    }

    @Transactional
    public AssignmentResponse updateAssignment(Long id, AssignmentDto dto, UserPrincipal principal) {
        Assignment assignment = assignmentRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Assignment not found: " + id));

        validateTeacherOwnership(assignment, principal);

        Subject subject = subjectRepository.findById(dto.getSubjectId())
                .orElseThrow(() -> new IllegalArgumentException("Subject not found: " + dto.getSubjectId()));

        Classroom classroom = classroomRepository.findById(dto.getClassroomId())
                .orElseThrow(() -> new IllegalArgumentException("Classroom not found: " + dto.getClassroomId()));

        assignment.setSubject(subject);
        assignment.setClassroom(classroom);
        assignment.setTitle(dto.getTitle());
        assignment.setDescription(dto.getDescription());
        assignment.setInstructions(dto.getInstructions());
        assignment.setMaxScore(dto.getMaxScore());
        assignment.setDueDate(dto.getDueDate());
        assignment.setAllowLateSubmission(dto.isAllowLateSubmission());
        assignment.setStatus(dto.getStatus());

        return mapToResponse(assignmentRepository.save(assignment), null);
    }

    @Transactional
    public void deleteAssignment(Long id, UserPrincipal principal) {
        Assignment assignment = assignmentRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Assignment not found: " + id));

        validateTeacherOwnership(assignment, principal);
        assignmentRepository.delete(assignment);
    }

    // Submissions
    @Transactional
    public SubmissionResponse submitAssignment(Long assignmentId, String notes, MultipartFile file, UserPrincipal principal) {
        Student student = studentRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));

        Assignment assignment = assignmentRepository.findById(assignmentId)
                .orElseThrow(() -> new IllegalArgumentException("Assignment not found: " + assignmentId));

        LocalDateTime now = LocalDateTime.now();
        if (now.isAfter(assignment.getDueDate()) && !assignment.isAllowLateSubmission()) {
            throw new IllegalStateException("Submission deadline has passed and late submissions are not allowed.");
        }

        if (student.getClassroom() == null || !student.getClassroom().getId().equals(assignment.getClassroom().getId())) {
            throw new AccessDeniedException("This assignment is not for your class");
        }

        String storedPath = null;
        if (file != null && !file.isEmpty()) {
            storedPath = fileStorageService.storeFile(file, "submissions");
        }

        AssignmentSubmission submission = submissionRepository.findByAssignmentIdAndStudentId(assignmentId, student.getId())
                .orElseGet(() -> AssignmentSubmission.builder()
                        .assignment(assignment)
                        .student(student)
                        .build());

        if (storedPath != null) {
            if (submission.getFilePath() != null) {
                fileStorageService.deleteFile(submission.getFilePath());
            }
            submission.setFilePath(storedPath);
        }

        submission.setNotes(notes);
        submission.setStatus("submitted");
        submission.setSubmittedAt(now);

        return mapToSubmissionResponse(submissionRepository.save(submission));
    }

    @Transactional(readOnly = true)
    public List<SubmissionResponse> getSubmissions(Long assignmentId, UserPrincipal principal) {
        Assignment assignment = assignmentRepository.findById(assignmentId)
                .orElseThrow(() -> new IllegalArgumentException("Assignment not found: " + assignmentId));

        validateTeacherOwnership(assignment, principal);

        return submissionRepository.findByAssignmentId(assignmentId).stream()
                .map(this::mapToSubmissionResponse)
                .collect(Collectors.toList());
    }

    @Transactional
    public SubmissionResponse gradeSubmission(Long submissionId, GradeSubmissionDto dto, UserPrincipal principal) {
        AssignmentSubmission submission = submissionRepository.findById(submissionId)
                .orElseThrow(() -> new IllegalArgumentException("Submission not found: " + submissionId));

        validateTeacherOwnership(submission.getAssignment(), principal);

        submission.setScore(dto.getScore());
        submission.setFeedback(dto.getFeedback());
        submission.setStatus("graded");
        submission.setGradedAt(LocalDateTime.now());

        return mapToSubmissionResponse(submissionRepository.save(submission));
    }

    // Discussions
    @Transactional(readOnly = true)
    public List<DiscussionResponse> getDiscussions(Long assignmentId) {
        List<AssignmentDiscussion> topLevel = discussionRepository.findByAssignmentIdAndParentIsNullOrderByCreatedAtAsc(assignmentId);
        return topLevel.stream().map(this::mapToDiscussionResponse).collect(Collectors.toList());
    }

    @Transactional
    public DiscussionResponse postDiscussion(Long assignmentId, DiscussionDto dto, UserPrincipal principal) {
        Assignment assignment = assignmentRepository.findById(assignmentId)
                .orElseThrow(() -> new IllegalArgumentException("Assignment not found"));

        User user = userRepository.findById(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("User not found"));

        AssignmentDiscussion parent = null;
        if (dto.getParentId() != null) {
            parent = discussionRepository.findById(dto.getParentId())
                    .orElseThrow(() -> new IllegalArgumentException("Parent discussion not found"));
        }

        AssignmentDiscussion discussion = AssignmentDiscussion.builder()
                .assignment(assignment)
                .user(user)
                .parent(parent)
                .message(dto.getMessage())
                .build();

        return mapToDiscussionResponse(discussionRepository.save(discussion));
    }

    private void validateTeacherOwnership(Assignment assignment, UserPrincipal principal) {
        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        if (!isAdmin && !assignment.getTeacher().getUser().getId().equals(principal.getId())) {
            throw new AccessDeniedException("You are not authorized for this assignment");
        }
    }

    private AssignmentResponse mapToResponse(Assignment a, Long studentId) {
        boolean hasSubmitted = false;
        Integer studentScore = null;
        String status = null;
        LocalDateTime submittedAt = null;

        if (studentId != null) {
            Optional<AssignmentSubmission> sub = submissionRepository.findByAssignmentIdAndStudentId(a.getId(), studentId);
            if (sub.isPresent()) {
                hasSubmitted = true;
                studentScore = sub.get().getScore();
                status = sub.get().getStatus();
                submittedAt = sub.get().getSubmittedAt();
            }
        }

        return AssignmentResponse.builder()
                .id(a.getId())
                .teacherId(a.getTeacher().getId())
                .teacherName(a.getTeacher().getUser().getName())
                .subjectId(a.getSubject().getId())
                .subjectName(a.getSubject().getName())
                .classroomId(a.getClassroom().getId())
                .classroomName(a.getClassroom().getName())
                .title(a.getTitle())
                .description(a.getDescription())
                .instructions(a.getInstructions())
                .maxScore(a.getMaxScore())
                .dueDate(a.getDueDate())
                .allowLateSubmission(a.isAllowLateSubmission())
                .status(a.getStatus())
                .createdAt(a.getCreatedAt())
                .hasSubmitted(hasSubmitted)
                .studentScore(studentScore)
                .studentSubmissionStatus(status)
                .studentSubmittedAt(submittedAt)
                .build();
    }

    private SubmissionResponse mapToSubmissionResponse(AssignmentSubmission s) {
        return SubmissionResponse.builder()
                .id(s.getId())
                .assignmentId(s.getAssignment().getId())
                .studentId(s.getStudent().getId())
                .studentName(s.getStudent().getUser().getName())
                .studentNis(s.getStudent().getNis())
                .filePath(s.getFilePath())
                .notes(s.getNotes())
                .score(s.getScore())
                .feedback(s.getFeedback())
                .status(s.getStatus())
                .submittedAt(s.getSubmittedAt())
                .gradedAt(s.getGradedAt())
                .build();
    }

    private DiscussionResponse mapToDiscussionResponse(AssignmentDiscussion d) {
        List<DiscussionResponse> replies = d.getReplies() != null ?
                d.getReplies().stream().map(this::mapToDiscussionResponse).collect(Collectors.toList()) : new ArrayList<>();

        String role = d.getUser().getRoles().stream().findFirst().map(r -> r.getName().name()).orElse("STUDENT");

        return DiscussionResponse.builder()
                .id(d.getId())
                .assignmentId(d.getAssignment().getId())
                .userId(d.getUser().getId())
                .userName(d.getUser().getName())
                .userRole(role)
                .message(d.getMessage())
                .createdAt(d.getCreatedAt())
                .replies(replies)
                .build();
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/assignment/controller/AssignmentController.java
package com.elearning.modules.assignment.controller;

import com.elearning.common.response.ApiResponse;
import com.elearning.modules.assignment.dto.*;
import com.elearning.modules.assignment.service.AssignmentService;
import com.elearning.security.UserPrincipal;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import java.util.List;

@RestController
@RequestMapping("/api/v1/assignments")
@RequiredArgsConstructor
@Tag(name = "Assignments & Discussions", description = "Endpoints for homework/assignments, submissions, grading, and discussion threads")
public class AssignmentController {

    private final AssignmentService assignmentService;

    @GetMapping
    @Operation(summary = "Get assignments for current user")
    public ResponseEntity<ApiResponse<List<AssignmentResponse>>> getAssignments(
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(assignmentService.getAssignmentsForUser(principal), "Assignments fetched successfully"));
    }

    @GetMapping("/{id}")
    @Operation(summary = "Get assignment details by ID")
    public ResponseEntity<ApiResponse<AssignmentResponse>> getAssignmentById(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(assignmentService.getAssignmentById(id, principal), "Assignment details"));
    }

    @PostMapping
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Create an assignment")
    public ResponseEntity<ApiResponse<AssignmentResponse>> createAssignment(
            @Valid @RequestBody AssignmentDto dto,
            @AuthenticationPrincipal UserPrincipal principal) {
        AssignmentResponse response = assignmentService.createAssignment(dto, principal);
        return ResponseEntity.status(HttpStatus.CREATED).body(ApiResponse.success(response, "Assignment created successfully"));
    }

    @PutMapping("/{id}")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Update an assignment")
    public ResponseEntity<ApiResponse<AssignmentResponse>> updateAssignment(
            @PathVariable Long id,
            @Valid @RequestBody AssignmentDto dto,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(assignmentService.updateAssignment(id, dto, principal), "Assignment updated successfully"));
    }

    @DeleteMapping("/{id}")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Delete an assignment")
    public ResponseEntity<ApiResponse<Void>> deleteAssignment(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        assignmentService.deleteAssignment(id, principal);
        return ResponseEntity.ok(ApiResponse.success(null, "Assignment deleted successfully"));
    }

    @PostMapping(value = "/{id}/submit", consumes = MediaType.MULTIPART_FORM_DATA_VALUE)
    @PreAuthorize("hasRole('STUDENT')")
    @Operation(summary = "Submit assignment with optional file attachment")
    public ResponseEntity<ApiResponse<SubmissionResponse>> submitAssignment(
            @PathVariable Long id,
            @RequestParam(value = "notes", required = false) String notes,
            @RequestPart(value = "file", required = false) MultipartFile file,
            @AuthenticationPrincipal UserPrincipal principal) {
        SubmissionResponse response = assignmentService.submitAssignment(id, notes, file, principal);
        return ResponseEntity.ok(ApiResponse.success(response, "Assignment submitted successfully"));
    }

    @GetMapping("/{id}/submissions")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Get all student submissions for an assignment")
    public ResponseEntity<ApiResponse<List<SubmissionResponse>>> getSubmissions(
            @PathVariable Long id,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(assignmentService.getSubmissions(id, principal), "Submissions fetched successfully"));
    }

    @PostMapping("/submissions/{submissionId}/grade")
    @PreAuthorize("hasAnyRole('ADMIN', 'TEACHER')")
    @Operation(summary = "Grade student assignment submission")
    public ResponseEntity<ApiResponse<SubmissionResponse>> gradeSubmission(
            @PathVariable Long submissionId,
            @Valid @RequestBody GradeSubmissionDto dto,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(assignmentService.gradeSubmission(submissionId, dto, principal), "Submission graded successfully"));
    }

    @GetMapping("/{id}/discussions")
    @Operation(summary = "Get discussion board for an assignment")
    public ResponseEntity<ApiResponse<List<DiscussionResponse>>> getDiscussions(@PathVariable Long id) {
        return ResponseEntity.ok(ApiResponse.success(assignmentService.getDiscussions(id), "Discussions fetched"));
    }

    @PostMapping("/{id}/discussions")
    @Operation(summary = "Post a discussion comment or reply")
    public ResponseEntity<ApiResponse<DiscussionResponse>> postDiscussion(
            @PathVariable Long id,
            @Valid @RequestBody DiscussionDto dto,
            @AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success(assignmentService.postDiscussion(id, dto, principal), "Comment posted"));
    }
}
EOF`
Ran command: `grep "websocket" ../java-spring-react-elearning/backend/pom.xml`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/config/WebSocketConfig.java
package com.elearning.config;

import org.springframework.context.annotation.Configuration;
import org.springframework.messaging.simp.config.MessageBrokerRegistry;
import org.springframework.web.socket.config.annotation.EnableWebSocketMessageBroker;
import org.springframework.web.socket.config.annotation.StompEndpointRegistry;
import org.springframework.web.socket.config.annotation.WebSocketMessageBrokerConfigurer;

@Configuration
@EnableWebSocketMessageBroker
public class WebSocketConfig implements WebSocketMessageBrokerConfigurer {

    @Override
    public void configureMessageBroker(MessageBrokerRegistry config) {
        config.enableSimpleBroker("/topic", "/queue");
        config.setApplicationDestinationPrefixes("/app");
    }

    @Override
    public void registerStompEndpoints(StompEndpointRegistry registry) {
        registry.addEndpoint("/ws")
                .setAllowedOriginPatterns("*")
                .withSockJS();

        registry.addEndpoint("/ws")
                .setAllowedOriginPatterns("*");
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/service/ExamRealtimeService.java
package com.elearning.modules.exam.service;

import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.messaging.simp.SimpMessagingTemplate;
import org.springframework.stereotype.Service;

import java.util.Map;

@Service
@RequiredArgsConstructor
@Slf4j
public class ExamRealtimeService {

    private final SimpMessagingTemplate messagingTemplate;

    public void broadcastExamEvent(Long examId, String eventType, Object payload) {
        try {
            String destination = "/topic/exams/" + examId + "/monitor";
            Map<String, Object> message = Map.of(
                    "eventType", eventType,
                    "timestamp", System.currentTimeMillis(),
                    "data", payload
            );
            messagingTemplate.convertAndSend(destination, message);
        } catch (Exception e) {
            log.warn("Failed to broadcast WebSocket message for exam {}: {}", examId, e.getMessage());
        }
    }
}
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/backend/src/main/java/com/elearning/modules/exam/service/ExamRunnerService.java
package com.elearning.modules.exam.service;

import com.elearning.common.enums.AttemptStatus;
import com.elearning.common.enums.QuestionType;
import com.elearning.common.enums.Status;
import com.elearning.modules.exam.dto.*;
import com.elearning.modules.exam.entity.ExamAnswer;
import com.elearning.modules.exam.entity.ExamAttempt;
import com.elearning.modules.exam.entity.Examination;
import com.elearning.modules.exam.entity.Question;
import com.elearning.modules.exam.repository.ExamAnswerRepository;
import com.elearning.modules.exam.repository.ExamAttemptRepository;
import com.elearning.modules.exam.repository.ExaminationRepository;
import com.elearning.modules.exam.repository.QuestionRepository;
import com.elearning.modules.user.entity.Student;
import com.elearning.modules.user.repository.StudentRepository;
import com.elearning.security.UserPrincipal;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.security.access.AccessDeniedException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.Duration;
import java.time.LocalDateTime;
import java.util.*;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
@Slf4j
public class ExamRunnerService {

    private final ExaminationRepository examRepository;
    private final QuestionRepository questionRepository;
    private final ExamAttemptRepository attemptRepository;
    private final ExamAnswerRepository answerRepository;
    private final StudentRepository studentRepository;
    private final ExamRealtimeService realtimeService;

    @Transactional
    public ExamStartResponse startOrResumeAttempt(Long examId, UserPrincipal principal) {
        Student student = studentRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));

        Examination exam = examRepository.findById(examId)
                .orElseThrow(() -> new IllegalArgumentException("Exam not found: " + examId));

        if (exam.getStatus() != Status.published) {
            throw new IllegalStateException("Exam is not active or published");
        }

        LocalDateTime now = LocalDateTime.now();
        if (now.isBefore(exam.getStartAt())) {
            throw new IllegalStateException("Exam has not started yet. Starts at: " + exam.getStartAt());
        }
        if (now.isAfter(exam.getEndAt())) {
            throw new IllegalStateException("Exam schedule has ended at: " + exam.getEndAt());
        }

        if (student.getClassroom() == null || !student.getClassroom().getId().equals(exam.getClassroom().getId())) {
            throw new AccessDeniedException("This exam is not assigned to your classroom");
        }

        Optional<ExamAttempt> existingAttemptOpt = attemptRepository.findByExaminationIdAndStudentId(examId, student.getId());
        ExamAttempt attempt;

        if (existingAttemptOpt.isPresent()) {
            attempt = existingAttemptOpt.get();
            if (attempt.getStatus() == AttemptStatus.completed || attempt.getStatus() == AttemptStatus.submitted) {
                if (!exam.isAllowRetry()) {
                    throw new IllegalStateException("Exam already completed. Retries are not permitted.");
                } else {
                    attempt = createNewAttempt(exam, student, attempt.getAttemptNumber() + 1);
                }
            }
        } else {
            attempt = createNewAttempt(exam, student, 1);
        }

        // Calculate remaining seconds
        long remainingSeconds = calculateRemainingSeconds(exam, attempt, now);
        if (remainingSeconds <= 0 && attempt.getStatus() == AttemptStatus.in_progress) {
            autoFinalizeAttempt(attempt, exam);
            throw new IllegalStateException("Exam duration has expired");
        }

        // Fetch questions and existing answers
        List<Question> questions = questionRepository.findByExaminationIdOrderByIdAsc(examId);
        if (exam.isShuffleQuestions()) {
            Collections.shuffle(questions, new Random(attempt.getId() * 31));
        }

        Map<Long, String> studentAnswersMap = answerRepository.findByExamAttemptId(attempt.getId())
                .stream()
                .filter(a -> a.getAnswerText() != null)
                .collect(Collectors.toMap(a -> a.getQuestion().getId(), ExamAnswer::getAnswerText, (a1, a2) -> a1));

        List<QuestionResponse> questionResponses = questions.stream().map(q -> {
            List<String> options = q.getOptions();
            if (options != null && exam.isShuffleOptions()) {
                options = new ArrayList<>(options);
                Collections.shuffle(options, new Random(attempt.getId() * 17 + q.getId()));
            }

            return QuestionResponse.builder()
                    .id(q.getId())
                    .examinationId(exam.getId())
                    .questionText(q.getQuestionText())
                    .questionType(q.getQuestionType())
                    .options(options)
                    .audioPath(q.getAudioPath())
                    .imagePath(q.getImagePath())
                    .points(q.getPoints())
                    .difficulty(q.getDifficulty())
                    .correctAnswer(null) // STRICT SECURITY: hide answer key
                    .explanation(null)   // STRICT SECURITY: hide explanation
                    .studentAnswer(studentAnswersMap.get(q.getId()))
                    .build();
        }).collect(Collectors.toList());

        realtimeService.broadcastExamEvent(exam.getId(), "STUDENT_JOINED", Map.of(
                "attemptId", attempt.getId(),
                "studentId", student.getId(),
                "studentName", student.getUser().getName(),
                "status", attempt.getStatus()
        ));

        return ExamStartResponse.builder()
                .attemptId(attempt.getId())
                .examId(exam.getId())
                .examTitle(exam.getTitle())
                .durationMinutes(exam.getDurationMinutes())
                .remainingSeconds(remainingSeconds)
                .startedAt(attempt.getStartedAt())
                .totalQuestions(questionResponses.size())
                .questions(questionResponses)
                .build();
    }

    @Transactional
    public void saveAnswer(Long attemptId, SubmitAnswerRequest req, UserPrincipal principal) {
        ExamAttempt attempt = getValidatedStudentAttempt(attemptId, principal);

        if (attempt.getStatus() != AttemptStatus.in_progress) {
            throw new IllegalStateException("Cannot save answer: attempt is " + attempt.getStatus());
        }

        long remaining = calculateRemainingSeconds(attempt.getExamination(), attempt, LocalDateTime.now());
        if (remaining <= 0) {
            autoFinalizeAttempt(attempt, attempt.getExamination());
            throw new IllegalStateException("Exam duration has expired. Attempt automatically submitted.");
        }

        Question question = questionRepository.findById(req.getQuestionId())
                .orElseThrow(() -> new IllegalArgumentException("Question not found"));

        if (!question.getExamination().getId().equals(attempt.getExamination().getId())) {
            throw new IllegalArgumentException("Question does not belong to this examination");
        }

        ExamAnswer answer = answerRepository.findByExamAttemptIdAndQuestionId(attempt.getId(), question.getId())
                .orElseGet(() -> ExamAnswer.builder()
                        .examAttempt(attempt)
                        .question(question)
                        .pointsEarned(0)
                        .build());

        answer.setAnswerText(req.getAnswerText());
        answer.setAnsweredAt(LocalDateTime.now());
        answerRepository.save(answer);

        int answeredCount = answerRepository.findByExamAttemptId(attempt.getId()).size();
        realtimeService.broadcastExamEvent(attempt.getExamination().getId(), "ANSWER_SAVED", Map.of(
                "attemptId", attempt.getId(),
                "studentId", attempt.getStudent().getId(),
                "answeredCount", answeredCount
        ));
    }

    @Transactional
    public int reportViolation(Long attemptId, ViolationReportRequest req, UserPrincipal principal) {
        ExamAttempt attempt = getValidatedStudentAttempt(attemptId, principal);

        int currentViolations = attempt.getViolations() + 1;
        attempt.setViolations(currentViolations);

        log.warn("Exam violation reported for student {} in attempt {}: {} (Total violations: {})",
                principal.getUsername(), attemptId, req.getReason(), currentViolations);

        realtimeService.broadcastExamEvent(attempt.getExamination().getId(), "VIOLATION_REPORTED", Map.of(
                "attemptId", attempt.getId(),
                "studentId", attempt.getStudent().getId(),
                "studentName", attempt.getStudent().getUser().getName(),
                "reason", req.getReason(),
                "violations", currentViolations
        ));

        if (currentViolations >= 5 && attempt.getStatus() == AttemptStatus.in_progress) {
            log.warn("Exceeded max violations. Auto-submitting attempt {}", attemptId);
            autoFinalizeAttempt(attempt, attempt.getExamination());
        } else {
            attemptRepository.save(attempt);
        }

        return currentViolations;
    }

    @Transactional
    public ExamAttempt submitAttempt(Long attemptId, UserPrincipal principal) {
        ExamAttempt attempt = getValidatedStudentAttempt(attemptId, principal);
        if (attempt.getStatus() != AttemptStatus.in_progress) {
            return attempt;
        }

        return autoFinalizeAttempt(attempt, attempt.getExamination());
    }

    @Transactional
    public ExamAttempt autoFinalizeAttempt(ExamAttempt attempt, Examination exam) {
        List<Question> questions = questionRepository.findByExaminationIdOrderByIdAsc(exam.getId());
        List<ExamAnswer> answers = answerRepository.findByExamAttemptId(attempt.getId());

        Map<Long, ExamAnswer> answersByQuestionId = answers.stream()
                .collect(Collectors.toMap(a -> a.getQuestion().getId(), a -> a, (a1, a2) -> a1));

        int totalPossiblePoints = 0;
        int totalEarnedPoints = 0;
        boolean hasEssay = false;

        for (Question q : questions) {
            int qPoints = q.getPoints() != null ? q.getPoints() : 1;
            totalPossiblePoints += qPoints;

            ExamAnswer ans = answersByQuestionId.get(q.getId());
            if (ans == null) {
                ans = ExamAnswer.builder()
                        .examAttempt(attempt)
                        .question(q)
                        .answerText(null)
                        .pointsEarned(0)
                        .correct(false)
                        .build();
                answerRepository.save(ans);
                continue;
            }

            if (q.getQuestionType() == QuestionType.multiple_choice || q.getQuestionType() == QuestionType.true_false) {
                boolean isCorrect = ans.getAnswerText() != null &&
                        q.getCorrectAnswer() != null &&
                        ans.getAnswerText().trim().equalsIgnoreCase(q.getCorrectAnswer().trim());

                ans.setCorrect(isCorrect);
                ans.setPointsEarned(isCorrect ? qPoints : 0);
                if (isCorrect) {
                    totalEarnedPoints += qPoints;
                }
                answerRepository.save(ans);
            } else if (q.getQuestionType() == QuestionType.essay) {
                hasEssay = true;
                ans.setCorrect(null);
                ans.setPointsEarned(0);
                answerRepository.save(ans);
            }
        }

        int score = totalPossiblePoints > 0 ? (totalEarnedPoints * 100) / totalPossiblePoints : 0;
        attempt.setScore(score);
        attempt.setPassed(score >= exam.getPassingScore());
        attempt.setStatus(hasEssay ? AttemptStatus.needs_grading : AttemptStatus.completed);
        attempt.setFinishedAt(LocalDateTime.now());

        ExamAttempt savedAttempt = attemptRepository.save(attempt);

        realtimeService.broadcastExamEvent(exam.getId(), "EXAM_SUBMITTED", Map.of(
                "attemptId", savedAttempt.getId(),
                "studentId", savedAttempt.getStudent().getId(),
                "studentName", savedAttempt.getStudent().getUser().getName(),
                "score", savedAttempt.getScore() != null ? savedAttempt.getScore() : 0,
                "status", savedAttempt.getStatus()
        ));

        return savedAttempt;
    }

    @Transactional
    public void gradeEssay(Long attemptId, Long answerId, EssayGradingDto dto, UserPrincipal principal) {
        ExamAttempt attempt = attemptRepository.findById(attemptId)
                .orElseThrow(() -> new IllegalArgumentException("Attempt not found"));

        validateTeacherOrAdmin(attempt.getExamination(), principal);

        ExamAnswer answer = answerRepository.findById(answerId)
                .orElseThrow(() -> new IllegalArgumentException("Answer not found"));

        if (!answer.getExamAttempt().getId().equals(attempt.getId())) {
            throw new IllegalArgumentException("Answer does not belong to this attempt");
        }

        answer.setPointsEarned(dto.getPointsEarned());
        answer.setFeedback(dto.getFeedback());
        answer.setCorrect(dto.getPointsEarned() > 0);
        answerRepository.save(answer);

        List<Question> questions = questionRepository.findByExaminationIdOrderByIdAsc(attempt.getExamination().getId());
        List<ExamAnswer> answers = answerRepository.findByExamAttemptId(attempt.getId());

        int totalPossible = questions.stream().mapToInt(q -> q.getPoints() != null ? q.getPoints() : 1).sum();
        int totalEarned = answers.stream().mapToInt(ExamAnswer::getPointsEarned).sum();

        int score = totalPossible > 0 ? (totalEarned * 100) / totalPossible : 0;
        attempt.setScore(score);
        attempt.setPassed(score >= attempt.getExamination().getPassingScore());
        attempt.setStatus(AttemptStatus.completed);
        attemptRepository.save(attempt);
    }

    @Transactional(readOnly = true)
    public List<ExamMonitorResponse> getExamMonitor(Long examId, UserPrincipal principal) {
        Examination exam = examRepository.findById(examId)
                .orElseThrow(() -> new IllegalArgumentException("Exam not found: " + examId));

        validateTeacherOrAdmin(exam, principal);

        List<ExamAttempt> attempts = attemptRepository.findByExaminationId(examId);

        return attempts.stream().map(att -> {
            int answeredCount = answerRepository.findByExamAttemptId(att.getId()).size();
            return ExamMonitorResponse.builder()
                    .attemptId(att.getId())
                    .studentId(att.getStudent().getId())
                    .studentName(att.getStudent().getUser().getName())
                    .nis(att.getStudent().getNis())
                    .status(att.getStatus())
                    .score(att.getScore())
                    .passed(att.getPassed())
                    .violations(att.getViolations())
                    .answeredCount(answeredCount)
                    .totalQuestions(exam.getTotalQuestions())
                    .startedAt(att.getStartedAt())
                    .finishedAt(att.getFinishedAt())
                    .build();
        }).collect(Collectors.toList());
    }

    private ExamAttempt createNewAttempt(Examination exam, Student student, int attemptNumber) {
        ExamAttempt attempt = ExamAttempt.builder()
                .examination(exam)
                .student(student)
                .attemptNumber(attemptNumber)
                .status(AttemptStatus.in_progress)
                .violations(0)
                .startedAt(LocalDateTime.now())
                .build();
        return attemptRepository.save(attempt);
    }

    private long calculateRemainingSeconds(Examination exam, ExamAttempt attempt, LocalDateTime now) {
        LocalDateTime examDeadline = attempt.getStartedAt().plusMinutes(exam.getDurationMinutes());
        if (exam.getEndAt() != null && exam.getEndAt().isBefore(examDeadline)) {
            examDeadline = exam.getEndAt();
        }
        return Math.max(0, Duration.between(now, examDeadline).getSeconds());
    }

    private ExamAttempt getValidatedStudentAttempt(Long attemptId, UserPrincipal principal) {
        ExamAttempt attempt = attemptRepository.findById(attemptId)
                .orElseThrow(() -> new IllegalArgumentException("Attempt not found: " + attemptId));

        Student student = studentRepository.findByUserId(principal.getId())
                .orElseThrow(() -> new IllegalArgumentException("Student profile not found"));

        if (!attempt.getStudent().getId().equals(student.getId())) {
            throw new AccessDeniedException("This attempt does not belong to you");
        }

        return attempt;
    }

    private void validateTeacherOrAdmin(Examination exam, UserPrincipal principal) {
        boolean isAdmin = principal.getAuthorities().stream().anyMatch(a -> a.getAuthority().equals("ROLE_ADMIN"));
        if (!isAdmin && !exam.getTeacher().getUser().getId().equals(principal.getId())) {
            throw new AccessDeniedException("You are not authorized for this exam");
        }
    }
}
EOF`
Ran command: `cd ../java-spring-react-elearning && git add . && git commit -m "feat(backend): implement CBT exam engine, question bank, auto-grading, anti-cheat, assignments, and websocket stomp"`
Ran command: `ls -la ../java-spring-react-elearning/frontend`
Ran command: `ls -la ../java-spring-react-elearning/frontend/src`
Ran command: `cat ../java-spring-react-elearning/frontend/package.json`
Ran command: `cat ../java-spring-react-elearning/frontend/vite.config.ts`
Ran command: `cat ../java-spring-react-elearning/frontend/src/index.css`
Ran command: `cd ../java-spring-react-elearning/frontend && npm run build`
Ran command: `mkdir -p ../java-spring-react-elearning/frontend/src/types
cat << 'EOF' > ../java-spring-react-elearning/frontend/src/types/index.ts
export type Role = 'ROLE_ADMIN' | 'ROLE_TEACHER' | 'ROLE_STUDENT';

export interface User {
  id: number;
  name: string;
  email: string;
  roles: Role[];
  active: boolean;
  impersonatedBy?: number | null;
}

export interface AuthResponse {
  accessToken: string;
  refreshToken: string;
  tokenType: string;
  userId: number;
  name: string;
  email: string;
  roles: Role[];
  impersonatedBy?: number | null;
}

export interface Classroom {
  id: number;
  name: string;
  level: number;
  capacity: number;
  academicYear: string;
  homeroomTeacherId?: number | null;
  homeroomTeacherName?: string | null;
  createdAt: string;
}

export interface Subject {
  id: number;
  name: string;
  code: string;
  description?: string;
  teacherId?: number | null;
  teacherName?: string | null;
  credits: number;
  createdAt: string;
}

export interface Teacher {
  id: number;
  userId: number;
  name: string;
  email: string;
  phone?: string;
  nip: string;
  address?: string;
  photo?: string;
  active: boolean;
  createdAt: string;
}

export interface Student {
  id: number;
  userId: number;
  name: string;
  email: string;
  phone?: string;
  nis: string;
  nisn?: string;
  birthDate?: string;
  gender: 'M' | 'F';
  address?: string;
  photo?: string;
  classroomId?: number | null;
  classroomName?: string | null;
  active: boolean;
  createdAt: string;
}

export interface StudentExamCard {
  studentId: number;
  name: string;
  nis: string;
  nisn?: string;
  gender: 'M' | 'F';
  birthDate?: string;
  classroomName: string;
  academicYear: string;
  barcodeData: string;
}

export interface LearningMaterial {
  id: number;
  teacherId: number;
  teacherName: string;
  subjectId: number;
  subjectName: string;
  classroomId?: number | null;
  classroomName?: string;
  title: string;
  description?: string;
  type: string;
  content?: string;
  fileUrl?: string;
  filePath?: string;
  published: boolean;
  totalViews: number;
  hasViewed: boolean;
  createdAt: string;
}

export type ExamType = 'quiz' | 'midterm' | 'final' | 'tryout';
export type ExamStatus = 'draft' | 'published' | 'archived';
export type AttemptStatus = 'in_progress' | 'submitted' | 'needs_grading' | 'completed';

export interface Exam {
  id: number;
  teacherId: number;
  teacherName: string;
  subjectId: number;
  subjectName: string;
  classroomId: number;
  classroomName: string;
  title: string;
  description?: string;
  type: ExamType;
  durationMinutes: number;
  passingScore: number;
  startAt: string;
  endAt: string;
  examDate?: string;
  totalQuestions: number;
  shuffleQuestions: boolean;
  shuffleOptions: boolean;
  showResult: boolean;
  allowRetry: boolean;
  status: ExamStatus;
  createdAt: string;
  studentAttemptStatus?: AttemptStatus | null;
  studentScore?: number | null;
  studentPassed?: boolean | null;
  currentAttemptId?: number | null;
}

export interface Question {
  id: number;
  examinationId: number;
  questionText: string;
  questionType: 'multiple_choice' | 'essay' | 'true_false';
  options?: string[];
  points: number;
  difficulty: 'easy' | 'medium' | 'hard';
  correctAnswer?: string;
  explanation?: string;
  studentAnswer?: string;
  audioPath?: string;
  imagePath?: string;
}

export interface ExamStartResponse {
  attemptId: number;
  examId: number;
  examTitle: string;
  durationMinutes: number;
  remainingSeconds: number;
  startedAt: string;
  totalQuestions: number;
  questions: Question[];
}

export interface ExamMonitorItem {
  attemptId: number;
  studentId: number;
  studentName: string;
  nis: string;
  status: AttemptStatus;
  score?: number | null;
  passed?: boolean | null;
  violations: number;
  answeredCount: number;
  totalQuestions: number;
  startedAt: string;
  finishedAt?: string | null;
}

export interface Assignment {
  id: number;
  teacherId: number;
  teacherName: string;
  subjectId: number;
  subjectName: string;
  classroomId: number;
  classroomName: string;
  title: string;
  description?: string;
  instructions?: string;
  maxScore: number;
  dueDate: string;
  allowLateSubmission: boolean;
  status: ExamStatus;
  createdAt: string;
  hasSubmitted?: boolean;
  studentScore?: number | null;
  studentSubmissionStatus?: string | null;
  studentSubmittedAt?: string | null;
}

export interface Submission {
  id: number;
  assignmentId: number;
  studentId: number;
  studentName: string;
  studentNis: string;
  filePath?: string;
  notes?: string;
  score?: number | null;
  feedback?: string;
  status: string;
  submittedAt: string;
  gradedAt?: string | null;
}

export interface Discussion {
  id: number;
  assignmentId: number;
  userId: number;
  userName: string;
  userRole: string;
  message: string;
  createdAt: string;
  replies: Discussion[];
}

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
  errors?: Record<string, string>;
  timestamp: string;
}
EOF`
Ran command: `mkdir -p ../java-spring-react-elearning/frontend/src/api
cat << 'EOF' > ../java-spring-react-elearning/frontend/src/api/client.ts
import axios from 'axios';

const api = axios.create({
  baseURL: '/api/v1',
  headers: {
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      if (window.location.pathname !== '/login') {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

export default api;
EOF`
Ran command: `mkdir -p ../java-spring-react-elearning/frontend/src/store
cat << 'EOF' > ../java-spring-react-elearning/frontend/src/store/authStore.ts
import { create } from 'zustand';
import api from '../api/client';
import type { User, AuthResponse, ApiResponse } from '../types';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
  impersonate: (userId: number) => Promise<void>;
  stopImpersonate: () => Promise<void>;
  initAuth: () => void;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: true,

  initAuth: () => {
    const token = localStorage.getItem('token');
    const userStr = localStorage.getItem('user');

    if (token && userStr) {
      try {
        const user = JSON.parse(userStr);
        set({ user, token, isAuthenticated: true, isLoading: false });
      } catch {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        set({ user: null, token: null, isAuthenticated: false, isLoading: false });
      }
    } else {
      set({ user: null, token: null, isAuthenticated: false, isLoading: false });
    }
  },

  login: async (email: string, password: string) => {
    set({ isLoading: true });
    try {
      const response = await api.post<ApiResponse<AuthResponse>>('/auth/login', { email, password });
      const data = response.data.data;

      const user: User = {
        id: data.userId,
        name: data.name,
        email: data.email,
        roles: data.roles,
        active: true,
        impersonatedBy: data.impersonatedBy,
      };

      localStorage.setItem('token', data.accessToken);
      localStorage.setItem('user', JSON.stringify(user));

      set({ user, token: data.accessToken, isAuthenticated: true, isLoading: false });
    } catch (error) {
      set({ isLoading: false });
      throw error;
    }
  },

  logout: () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    set({ user: null, token: null, isAuthenticated: false });
    window.location.href = '/login';
  },

  impersonate: async (userId: number) => {
    const response = await api.post<ApiResponse<AuthResponse>>(`/admin/impersonate/${userId}`);
    const data = response.data.data;

    const user: User = {
      id: data.userId,
      name: data.name,
      email: data.email,
      roles: data.roles,
      active: true,
      impersonatedBy: data.impersonatedBy,
    };

    localStorage.setItem('token', data.accessToken);
    localStorage.setItem('user', JSON.stringify(user));

    set({ user, token: data.accessToken });
    window.location.href = '/dashboard';
  },

  stopImpersonate: async () => {
    const response = await api.post<ApiResponse<AuthResponse>>('/admin/stop-impersonate');
    const data = response.data.data;

    const user: User = {
      id: data.userId,
      name: data.name,
      email: data.email,
      roles: data.roles,
      active: true,
      impersonatedBy: null,
    };

    localStorage.setItem('token', data.accessToken);
    localStorage.setItem('user', JSON.stringify(user));

    set({ user, token: data.accessToken });
    window.location.href = '/dashboard';
  },
}));
EOF`
Ran command: `mkdir -p ../java-spring-react-elearning/frontend/src/components/common
cat << 'EOF' > ../java-spring-react-elearning/frontend/src/components/common/Navbar.tsx
import React from 'react';
import { useAuthStore } from '../../store/authStore';
import { LogOut, UserCheck, ShieldAlert, BookOpen } from 'lucide-react';

export const Navbar: React.FC = () => {
  const { user, logout, stopImpersonate } = useAuthStore();

  const getRoleBadge = () => {
    if (!user) return null;
    if (user.roles.includes('ROLE_ADMIN')) {
      return <span className="bg-rose-100 text-rose-700 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-rose-200">Admin</span>;
    }
    if (user.roles.includes('ROLE_TEACHER')) {
      return <span className="bg-amber-100 text-amber-700 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-amber-200">Teacher</span>;
    }
    return <span className="bg-emerald-100 text-emerald-700 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-emerald-200">Student</span>;
  };

  return (
    <header className="sticky top-0 z-40 bg-white border-b border-slate-200 shadow-xs">
      {user?.impersonatedBy && (
        <div className="bg-amber-500 text-white px-4 py-2 flex items-center justify-between text-sm font-medium animate-pulse">
          <div className="flex items-center space-x-2">
            <ShieldAlert className="w-5 h-5" />
            <span>
              Impersonation Active: Viewing system as <strong>{user.name}</strong> ({user.email})
            </span>
          </div>
          <button
            onClick={() => stopImpersonate()}
            className="bg-white text-amber-700 hover:bg-amber-50 px-3 py-1 rounded-md text-xs font-bold transition shadow-xs flex items-center space-x-1 cursor-pointer"
          >
            <UserCheck className="w-4 h-4 mr-1" />
            Exit Impersonation
          </button>
        </div>
      )}

      <div className="flex items-center justify-between h-16 px-6">
        <div className="flex items-center space-x-3">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-100">
            <BookOpen className="w-6 h-6" />
          </div>
          <div>
            <h1 className="text-lg font-bold text-slate-900 tracking-tight leading-none">EduPulse CBT & LMS</h1>
            <p className="text-xs text-slate-500 mt-0.5">Enterprise Learning & Computer Based Test</p>
          </div>
        </div>

        <div className="flex items-center space-x-4">
          <div className="flex items-center space-x-3 pr-4 border-r border-slate-200">
            <div className="text-right">
              <div className="text-sm font-semibold text-slate-800">{user?.name}</div>
              <div className="text-xs text-slate-500">{user?.email}</div>
            </div>
            {getRoleBadge()}
          </div>

          <button
            onClick={logout}
            title="Sign Out"
            className="flex items-center space-x-1.5 text-sm text-slate-600 hover:text-rose-600 transition px-3 py-2 rounded-lg hover:bg-rose-50 cursor-pointer"
          >
            <LogOut className="w-4 h-4" />
            <span className="font-medium">Sign Out</span>
          </button>
        </div>
      </div>
    </header>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/components/common/Sidebar.tsx
import React from 'react';
import { NavLink } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';
import {
  LayoutDashboard,
  GraduationCap,
  BookMarked,
  Users,
  UserCheck,
  FileCheck2,
  FileText,
  MessageSquareShare,
} from 'lucide-react';

export const Sidebar: React.FC = () => {
  const { user } = useAuthStore();
  const isAdmin = user?.roles.includes('ROLE_ADMIN');
  const isTeacher = user?.roles.includes('ROLE_TEACHER');

  const navClass = ({ isActive }: { isActive: boolean }) =>
    `flex items-center space-x-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-150 ${
      isActive
        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200'
        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
    }`;

  return (
    <aside className="w-64 bg-white border-r border-slate-200 min-h-[calc(100vh-4rem)] p-4 flex flex-col justify-between">
      <div className="space-y-6">
        <div>
          <div className="text-xs font-bold text-slate-400 uppercase tracking-wider px-4 mb-2">Main Menu</div>
          <nav className="space-y-1">
            <NavLink to="/dashboard" className={navClass}>
              <LayoutDashboard className="w-5 h-5" />
              <span>Dashboard</span>
            </NavLink>
          </nav>
        </div>

        {isAdmin && (
          <div>
            <div className="text-xs font-bold text-slate-400 uppercase tracking-wider px-4 mb-2">Academic & Users</div>
            <nav className="space-y-1">
              <NavLink to="/classrooms" className={navClass}>
                <GraduationCap className="w-5 h-5" />
                <span>Classrooms</span>
              </NavLink>
              <NavLink to="/subjects" className={navClass}>
                <BookMarked className="w-5 h-5" />
                <span>Subjects</span>
              </NavLink>
              <NavLink to="/teachers" className={navClass}>
                <UserCheck className="w-5 h-5" />
                <span>Teachers</span>
              </NavLink>
              <NavLink to="/students" className={navClass}>
                <Users className="w-5 h-5" />
                <span>Students</span>
              </NavLink>
            </nav>
          </div>
        )}

        <div>
          <div className="text-xs font-bold text-slate-400 uppercase tracking-wider px-4 mb-2">Learning & CBT</div>
          <nav className="space-y-1">
            <NavLink to="/exams" className={navClass}>
              <FileCheck2 className="w-5 h-5" />
              <span>{isTeacher || isAdmin ? 'CBT Exam & Questions' : 'My Exams'}</span>
            </NavLink>
            <NavLink to="/materials" className={navClass}>
              <FileText className="w-5 h-5" />
              <span>Learning Materials</span>
            </NavLink>
            <NavLink to="/assignments" className={navClass}>
              <MessageSquareShare className="w-5 h-5" />
              <span>Assignments</span>
            </NavLink>
          </nav>
        </div>
      </div>

      <div className="bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-xs text-slate-500">
        <div className="font-semibold text-slate-700">EduPulse v1.0.0</div>
        <p className="mt-0.5">Spring Boot 3.4 + React 19 Monorepo</p>
      </div>
    </aside>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/components/common/Layout.tsx
import React from 'react';
import { Outlet } from 'react-router-dom';
import { Navbar } from './Navbar';
import { Sidebar } from './Sidebar';

export const Layout: React.FC = () => {
  return (
    <div className="min-h-screen bg-slate-50 flex flex-col">
      <Navbar />
      <div className="flex flex-1">
        <Sidebar />
        <main className="flex-1 p-8 overflow-y-auto max-w-7xl mx-auto w-full">
          <Outlet />
        </main>
      </div>
    </div>
  );
};
EOF

cat << 'EOF' > ../java-spring-react-elearning/frontend/src/components/common/ProtectedRoute.tsx
import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';
import type { Role } from '../../types';

interface ProtectedRouteProps {
  allowedRoles?: Role[];
}

export const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ allowedRoles }) => {
  const { isAuthenticated, user, isLoading } = useAuthStore();

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-50">
        <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  if (!isAuthenticated || !user) {
    return <Navigate to="/login" replace />;
  }

  if (allowedRoles && !allowedRoles.some((role) => user.roles.includes(role))) {
    return <Navigate to="/dashboard" replace />;
  }

  return <Outlet />;
};
EOF`
Ran command: `mkdir -p ../java-spring-react-elearning/frontend/src/pages/auth
mkdir -p ../java-spring-react-elearning/frontend/src/pages/dashboard
mkdir -p ../java-spring-react-elearning/frontend/src/pages/admin
mkdir -p ../java-spring-react-elearning/frontend/src/pages/materials
mkdir -p ../java-spring-react-elearning/frontend/src/pages/exams
mkdir -p ../java-spring-react-elearning/frontend/src/pages/assignments
ls -la ../java-spring-react-elearning/frontend/src/pages`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/auth/Login.tsx
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';
import { BookOpen, Lock, Mail, ArrowRight, ShieldCheck, UserCheck, GraduationCap } from 'lucide-react';

export const Login: React.FC = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const { login } = useAuthStore();
  const navigate = useNavigate();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    try {
      await login(email, password);
      navigate('/dashboard');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Invalid email or password');
    } finally {
      setLoading(false);
    }
  };

  const handleQuickLogin = (demoEmail: string, demoPass: string) => {
    setEmail(demoEmail);
    setPassword(demoPass);
  };

  return (
    <div className="min-h-screen bg-slate-900 flex flex-col justify-center items-center p-6 relative overflow-hidden">
      {/* Background Glow */}
      <div className="absolute top-1/4 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-gradient-to-tr from-indigo-500/20 to-violet-500/10 rounded-full blur-3xl pointer-events-none" />

      <div className="max-w-md w-full relative z-10">
        <div className="text-center mb-8">
          <div className="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 items-center justify-center text-white shadow-xl shadow-indigo-500/25 mb-4">
            <BookOpen className="w-8 h-8" />
          </div>
          <h2 className="text-3xl font-extrabold text-white tracking-tight">EduPulse LMS & CBT</h2>
          <p className="text-slate-400 text-sm mt-2">Next-Gen Enterprise Learning & Exam Platform</p>
        </div>

        <div className="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 rounded-3xl p-8 shadow-2xl">
          {error && (
            <div className="mb-6 bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm px-4 py-3 rounded-xl">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                Email Address
              </label>
              <div className="relative">
                <Mail className="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="name@school.com"
                  className="w-full bg-slate-900/60 border border-slate-700 text-white rounded-xl pl-11 pr-4 py-3 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder:text-slate-500"
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                Password
              </label>
              <div className="relative">
                <Lock className="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input
                  type="password"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  className="w-full bg-slate-900/60 border border-slate-700 text-white rounded-xl pl-11 pr-4 py-3 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder:text-slate-500"
                />
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold py-3.5 px-4 rounded-xl shadow-lg shadow-indigo-600/30 transition duration-150 flex items-center justify-center space-x-2 cursor-pointer disabled:opacity-50"
            >
              <span>{loading ? 'Authenticating...' : 'Sign In to Portal'}</span>
              <ArrowRight className="w-4 h-4" />
            </button>
          </form>

          {/* Quick Demo Credentials */}
          <div className="mt-8 pt-6 border-t border-slate-700/60">
            <div className="text-xs font-semibold text-slate-400 text-center mb-3">
              Fast Demo Login:
            </div>
            <div className="grid grid-cols-3 gap-2">
              <button
                type="button"
                onClick={() => handleQuickLogin('admin@elearning.com', 'Admin@123')}
                className="bg-slate-700/50 hover:bg-slate-700 border border-slate-600 text-slate-200 text-xs py-2 px-2 rounded-lg flex flex-col items-center gap-1 transition cursor-pointer"
              >
                <ShieldCheck className="w-4 h-4 text-rose-400" />
                <span>Admin</span>
              </button>
              <button
                type="button"
                onClick={() => handleQuickLogin('teacher@elearning.com', 'Teacher@123')}
                className="bg-slate-700/50 hover:bg-slate-700 border border-slate-600 text-slate-200 text-xs py-2 px-2 rounded-lg flex flex-col items-center gap-1 transition cursor-pointer"
              >
                <UserCheck className="w-4 h-4 text-amber-400" />
                <span>Teacher</span>
              </button>
              <button
                type="button"
                onClick={() => handleQuickLogin('student@elearning.com', 'Student@123')}
                className="bg-slate-700/50 hover:bg-slate-700 border border-slate-600 text-slate-200 text-xs py-2 px-2 rounded-lg flex flex-col items-center gap-1 transition cursor-pointer"
              >
                <GraduationCap className="w-4 h-4 text-emerald-400" />
                <span>Student</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/dashboard/Dashboard.tsx
import React, { useEffect, useState } from 'react';
import { useAuthStore } from '../../store/authStore';
import api from '../../api/client';
import {
  GraduationCap,
  BookMarked,
  FileCheck2,
  MessageSquareShare,
  Users,
  Clock,
  ArrowRight,
  BookOpen,
} from 'lucide-react';
import { Link } from 'react-router-dom';

export const Dashboard: React.FC = () => {
  const { user } = useAuthStore();
  const isAdmin = user?.roles.includes('ROLE_ADMIN');
  const isTeacher = user?.roles.includes('ROLE_TEACHER');

  const [stats, setStats] = useState({
    classrooms: 0,
    subjects: 0,
    students: 0,
    exams: 0,
    materials: 0,
    assignments: 0,
  });

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const [examsRes, materialsRes, assignmentsRes] = await Promise.all([
          api.get('/exams').catch(() => ({ data: { data: [] } })),
          api.get('/materials').catch(() => ({ data: { data: [] } })),
          api.get('/assignments').catch(() => ({ data: { data: [] } })),
        ]);

        let classroomsCount = 0;
        let subjectsCount = 0;
        let studentsCount = 0;

        if (isAdmin) {
          const [crRes, subRes, stRes] = await Promise.all([
            api.get('/classrooms').catch(() => ({ data: { data: [] } })),
            api.get('/subjects').catch(() => ({ data: { data: [] } })),
            api.get('/students').catch(() => ({ data: { data: [] } })),
          ]);
          classroomsCount = crRes.data.data?.length || 0;
          subjectsCount = subRes.data.data?.length || 0;
          studentsCount = stRes.data.data?.length || 0;
        }

        setStats({
          classrooms: classroomsCount,
          subjects: subjectsCount,
          students: studentsCount,
          exams: examsRes.data.data?.length || 0,
          materials: materialsRes.data.data?.length || 0,
          assignments: assignmentsRes.data.data?.length || 0,
        });
      } catch (e) {
        console.error('Failed to fetch dashboard data', e);
      }
    };

    fetchDashboardData();
  }, [isAdmin]);

  return (
    <div className="space-y-8">
      {/* Welcome Banner */}
      <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-900 via-indigo-800 to-violet-900 p-8 text-white shadow-xl">
        <div className="relative z-10 max-w-2xl">
          <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 mb-4">
            Academic Session 2026/2027
          </span>
          <h2 className="text-3xl font-extrabold tracking-tight">
            Welcome back, {user?.name}!
          </h2>
          <p className="mt-2 text-indigo-100/90 text-sm leading-relaxed">
            {isAdmin
              ? 'Manage academic curriculums, supervise teachers & students, and oversee school-wide CBT operations.'
              : isTeacher
              ? 'Design interactive examinations, manage question banks, review student submissions, and host discussions.'
              : 'Access your classroom learning materials, take scheduled CBT examinations with secure anti-cheat, and submit assignments.'}
          </p>
        </div>
      </div>

      {/* Metric Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        {isAdmin && (
          <>
            <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
              <div className="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <GraduationCap className="w-6 h-6" />
              </div>
              <div>
                <p className="text-xs font-medium text-slate-500">Total Classrooms</p>
                <h3 className="text-2xl font-bold text-slate-800">{stats.classrooms}</h3>
              </div>
            </div>

            <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
              <div className="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <BookMarked className="w-6 h-6" />
              </div>
              <div>
                <p className="text-xs font-medium text-slate-500">Total Subjects</p>
                <h3 className="text-2xl font-bold text-slate-800">{stats.subjects}</h3>
              </div>
            </div>

            <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
              <div className="p-3 bg-violet-50 text-violet-600 rounded-xl">
                <Users className="w-6 h-6" />
              </div>
              <div>
                <p className="text-xs font-medium text-slate-500">Registered Students</p>
                <h3 className="text-2xl font-bold text-slate-800">{stats.students}</h3>
              </div>
            </div>
          </>
        )}

        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
          <div className="p-3 bg-amber-50 text-amber-600 rounded-xl">
            <FileCheck2 className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-medium text-slate-500">CBT Examinations</p>
            <h3 className="text-2xl font-bold text-slate-800">{stats.exams}</h3>
          </div>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
          <div className="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
            <BookOpen className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-medium text-slate-500">Learning Materials</p>
            <h3 className="text-2xl font-bold text-slate-800">{stats.materials}</h3>
          </div>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
          <div className="p-3 bg-rose-50 text-rose-600 rounded-xl">
            <MessageSquareShare className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-medium text-slate-500">Active Assignments</p>
            <h3 className="text-2xl font-bold text-slate-800">{stats.assignments}</h3>
          </div>
        </div>
      </div>

      {/* Quick Launch Cards */}
      <div>
        <h3 className="text-lg font-bold text-slate-900 mb-4">Quick Navigation</h3>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Link
            to="/exams"
            className="group bg-white p-6 rounded-2xl border border-slate-200 hover:border-indigo-500 shadow-xs hover:shadow-md transition duration-200"
          >
            <div className="flex items-center justify-between">
              <div className="p-3 bg-amber-50 text-amber-600 rounded-xl">
                <FileCheck2 className="w-6 h-6" />
              </div>
              <ArrowRight className="w-5 h-5 text-slate-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition" />
            </div>
            <h4 className="mt-4 text-base font-bold text-slate-800">
              {isTeacher || isAdmin ? 'CBT Exam Management' : 'Take CBT Examinations'}
            </h4>
            <p className="mt-1 text-xs text-slate-500">
              {isTeacher || isAdmin
                ? 'Create questions, manage schedules, and open live monitor rooms.'
                : 'Join live exam sessions with fullscreen proctoring and autosave.'}
            </p>
          </Link>

          <Link
            to="/materials"
            className="group bg-white p-6 rounded-2xl border border-slate-200 hover:border-indigo-500 shadow-xs hover:shadow-md transition duration-200"
          >
            <div className="flex items-center justify-between">
              <div className="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                <BookOpen className="w-6 h-6" />
              </div>
              <ArrowRight className="w-5 h-5 text-slate-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition" />
            </div>
            <h4 className="mt-4 text-base font-bold text-slate-800">Learning Materials</h4>
            <p className="mt-1 text-xs text-slate-500">
              Access digital syllabus, textbooks, and interactive lecture modules.
            </p>
          </Link>

          <Link
            to="/assignments"
            className="group bg-white p-6 rounded-2xl border border-slate-200 hover:border-indigo-500 shadow-xs hover:shadow-md transition duration-200"
          >
            <div className="flex items-center justify-between">
              <div className="p-3 bg-rose-50 text-rose-600 rounded-xl">
                <Clock className="w-6 h-6" />
              </div>
              <ArrowRight className="w-5 h-5 text-slate-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition" />
            </div>
            <h4 className="mt-4 text-base font-bold text-slate-800">Assignments & Discussions</h4>
            <p className="mt-1 text-xs text-slate-500">
              Submit homework files, receive grades, and participate in classroom forums.
            </p>
          </Link>
        </div>
      </div>
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/admin/Classrooms.tsx
import React, { useEffect, useState } from 'react';
import api from '../../api/client';
import type { Classroom, Teacher, ApiResponse } from '../../types';
import { Plus, Trash2, Edit2, GraduationCap, X } from 'lucide-react';

export const Classrooms: React.FC = () => {
  const [classrooms, setClassrooms] = useState<Classroom[]>([]);
  const [teachers, setTeachers] = useState<Teacher[]>([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingClassroom, setEditingClassroom] = useState<Classroom | null>(null);

  const [form, setForm] = useState({
    name: '',
    level: 10,
    capacity: 32,
    academicYear: '2026/2027',
    homeroomTeacherId: '',
  });

  const loadData = async () => {
    try {
      setLoading(true);
      const [crRes, tRes] = await Promise.all([
        api.get<ApiResponse<Classroom[]>>('/classrooms'),
        api.get<ApiResponse<Teacher[]>>('/teachers'),
      ]);
      setClassrooms(crRes.data.data);
      setTeachers(tRes.data.data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const openModal = (classroom?: Classroom) => {
    if (classroom) {
      setEditingClassroom(classroom);
      setForm({
        name: classroom.name,
        level: classroom.level,
        capacity: classroom.capacity,
        academicYear: classroom.academicYear,
        homeroomTeacherId: classroom.homeroomTeacherId ? String(classroom.homeroomTeacherId) : '',
      });
    } else {
      setEditingClassroom(null);
      setForm({
        name: '',
        level: 10,
        capacity: 32,
        academicYear: '2026/2027',
        homeroomTeacherId: '',
      });
    }
    setModalOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const payload = {
        name: form.name,
        level: Number(form.level),
        capacity: Number(form.capacity),
        academicYear: form.academicYear,
        homeroomTeacherId: form.homeroomTeacherId ? Number(form.homeroomTeacherId) : null,
      };

      if (editingClassroom) {
        await api.put(`/classrooms/${editingClassroom.id}`, payload);
      } else {
        await api.post('/classrooms', payload);
      }

      setModalOpen(false);
      loadData();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to save classroom');
    }
  };

  const handleDelete = async (id: number) => {
    if (confirm('Are you sure you want to delete this classroom?')) {
      await api.delete(`/classrooms/${id}`);
      loadData();
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 tracking-tight">Classroom Management</h2>
          <p className="text-xs text-slate-500 mt-1">Organize student classes and assign homeroom mentors</p>
        </div>
        <button
          onClick={() => openModal()}
          className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-4 py-2.5 rounded-xl shadow-xs transition flex items-center space-x-2 text-sm cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Add Classroom</span>
        </button>
      </div>

      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        {loading ? (
          <div className="p-8 text-center text-slate-500">Loading classrooms...</div>
        ) : classrooms.length === 0 ? (
          <div className="p-12 text-center text-slate-500">
            <GraduationCap className="w-12 h-12 mx-auto text-slate-300 mb-3" />
            <p className="text-base font-medium">No classrooms configured yet</p>
            <p className="text-xs mt-1 text-slate-400">Click "Add Classroom" to create your first class.</p>
          </div>
        ) : (
          <table className="w-full text-left border-collapse text-sm">
            <thead>
              <tr className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th className="px-6 py-4">Classroom Name</th>
                <th className="px-6 py-4">Level</th>
                <th className="px-6 py-4">Capacity</th>
                <th className="px-6 py-4">Academic Year</th>
                <th className="px-6 py-4">Homeroom Teacher</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {classrooms.map((c) => (
                <tr key={c.id} className="hover:bg-slate-50/80 transition">
                  <td className="px-6 py-4 font-semibold text-slate-900">{c.name}</td>
                  <td className="px-6 py-4 text-slate-600">Grade {c.level}</td>
                  <td className="px-6 py-4 text-slate-600">{c.capacity} students</td>
                  <td className="px-6 py-4 text-slate-600">{c.academicYear}</td>
                  <td className="px-6 py-4 text-slate-600">
                    {c.homeroomTeacherName ? (
                      <span className="font-medium text-slate-800">{c.homeroomTeacherName}</span>
                    ) : (
                      <span className="text-slate-400 italic">Unassigned</span>
                    )}
                  </td>
                  <td className="px-6 py-4 text-right space-x-2">
                    <button
                      onClick={() => openModal(c)}
                      className="text-slate-500 hover:text-indigo-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                    >
                      <Edit2 className="w-4 h-4" />
                    </button>
                    <button
                      onClick={() => handleDelete(c.id)}
                      className="text-slate-500 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {/* Add / Edit Modal */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative">
            <button
              onClick={() => setModalOpen(false)}
              className="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
            <h3 className="text-lg font-bold text-slate-900 mb-4">
              {editingClassroom ? 'Edit Classroom' : 'Create New Classroom'}
            </h3>

            <form onSubmit={handleSubmit} className="space-y-4 text-sm">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Classroom Name</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. X MIPA 1"
                  value={form.name}
                  onChange={(e) => setForm({ ...form, name: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Level / Grade</label>
                  <input
                    type="number"
                    required
                    min={1}
                    max={12}
                    value={form.level}
                    onChange={(e) => setForm({ ...form, level: Number(e.target.value) })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Capacity</label>
                  <input
                    type="number"
                    required
                    min={1}
                    max={60}
                    value={form.capacity}
                    onChange={(e) => setForm({ ...form, capacity: Number(e.target.value) })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Academic Year</label>
                <input
                  type="text"
                  required
                  value={form.academicYear}
                  onChange={(e) => setForm({ ...form, academicYear: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Homeroom Teacher</label>
                <select
                  value={form.homeroomTeacherId}
                  onChange={(e) => setForm({ ...form, homeroomTeacherId: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                >
                  <option value="">-- No Homeroom Teacher --</option>
                  {teachers.map((t) => (
                    <option key={t.id} value={t.id}>
                      {t.name} (NIP: {t.nip})
                    </option>
                  ))}
                </select>
              </div>

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer"
                >
                  Save Classroom
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/admin/Subjects.tsx
import React, { useEffect, useState } from 'react';
import api from '../../api/client';
import type { Subject, Teacher, ApiResponse } from '../../types';
import { Plus, Trash2, Edit2, BookMarked, X } from 'lucide-react';

export const Subjects: React.FC = () => {
  const [subjects, setSubjects] = useState<Subject[]>([]);
  const [teachers, setTeachers] = useState<Teacher[]>([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingSubject, setEditingSubject] = useState<Subject | null>(null);

  const [form, setForm] = useState({
    name: '',
    code: '',
    description: '',
    teacherId: '',
    credits: 2,
  });

  const loadData = async () => {
    try {
      setLoading(true);
      const [sRes, tRes] = await Promise.all([
        api.get<ApiResponse<Subject[]>>('/subjects'),
        api.get<ApiResponse<Teacher[]>>('/teachers'),
      ]);
      setSubjects(sRes.data.data);
      setTeachers(tRes.data.data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const openModal = (subject?: Subject) => {
    if (subject) {
      setEditingSubject(subject);
      setForm({
        name: subject.name,
        code: subject.code,
        description: subject.description || '',
        teacherId: subject.teacherId ? String(subject.teacherId) : '',
        credits: subject.credits,
      });
    } else {
      setEditingSubject(null);
      setForm({
        name: '',
        code: '',
        description: '',
        teacherId: '',
        credits: 2,
      });
    }
    setModalOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const payload = {
        name: form.name,
        code: form.code,
        description: form.description,
        teacherId: form.teacherId ? Number(form.teacherId) : null,
        credits: Number(form.credits),
      };

      if (editingSubject) {
        await api.put(`/subjects/${editingSubject.id}`, payload);
      } else {
        await api.post('/subjects', payload);
      }

      setModalOpen(false);
      loadData();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to save subject');
    }
  };

  const handleDelete = async (id: number) => {
    if (confirm('Are you sure you want to delete this subject?')) {
      await api.delete(`/subjects/${id}`);
      loadData();
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 tracking-tight">Subject Management</h2>
          <p className="text-xs text-slate-500 mt-1">Curriculum subjects, credit points, and teacher assignments</p>
        </div>
        <button
          onClick={() => openModal()}
          className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-4 py-2.5 rounded-xl shadow-xs transition flex items-center space-x-2 text-sm cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Add Subject</span>
        </button>
      </div>

      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        {loading ? (
          <div className="p-8 text-center text-slate-500">Loading subjects...</div>
        ) : subjects.length === 0 ? (
          <div className="p-12 text-center text-slate-500">
            <BookMarked className="w-12 h-12 mx-auto text-slate-300 mb-3" />
            <p className="text-base font-medium">No subjects found</p>
            <p className="text-xs mt-1 text-slate-400">Click "Add Subject" to create the first curriculum course.</p>
          </div>
        ) : (
          <table className="w-full text-left border-collapse text-sm">
            <thead>
              <tr className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th className="px-6 py-4">Code</th>
                <th className="px-6 py-4">Subject Name</th>
                <th className="px-6 py-4">Credits</th>
                <th className="px-6 py-4">Assigned Teacher</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {subjects.map((s) => (
                <tr key={s.id} className="hover:bg-slate-50/80 transition">
                  <td className="px-6 py-4 font-mono font-bold text-indigo-600">{s.code}</td>
                  <td className="px-6 py-4 font-semibold text-slate-900">{s.name}</td>
                  <td className="px-6 py-4 text-slate-600">{s.credits} SKS</td>
                  <td className="px-6 py-4 text-slate-600">
                    {s.teacherName ? (
                      <span className="font-medium text-slate-800">{s.teacherName}</span>
                    ) : (
                      <span className="text-slate-400 italic">Unassigned</span>
                    )}
                  </td>
                  <td className="px-6 py-4 text-right space-x-2">
                    <button
                      onClick={() => openModal(s)}
                      className="text-slate-500 hover:text-indigo-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                    >
                      <Edit2 className="w-4 h-4" />
                    </button>
                    <button
                      onClick={() => handleDelete(s.id)}
                      className="text-slate-500 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {modalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative">
            <button
              onClick={() => setModalOpen(false)}
              className="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
            <h3 className="text-lg font-bold text-slate-900 mb-4">
              {editingSubject ? 'Edit Subject' : 'Add Subject'}
            </h3>

            <form onSubmit={handleSubmit} className="space-y-4 text-sm">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Subject Code</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. MAT-10"
                    value={form.code}
                    onChange={(e) => setForm({ ...form, code: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Credits</label>
                  <input
                    type="number"
                    required
                    min={1}
                    max={6}
                    value={form.credits}
                    onChange={(e) => setForm({ ...form, credits: Number(e.target.value) })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Subject Name</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Matematika Wajib"
                  value={form.name}
                  onChange={(e) => setForm({ ...form, name: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Assigned Teacher</label>
                <select
                  value={form.teacherId}
                  onChange={(e) => setForm({ ...form, teacherId: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                >
                  <option value="">-- No Teacher Assigned --</option>
                  {teachers.map((t) => (
                    <option key={t.id} value={t.id}>
                      {t.name} (NIP: {t.nip})
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Description</label>
                <textarea
                  rows={3}
                  value={form.description}
                  onChange={(e) => setForm({ ...form, description: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  placeholder="Course summary and objectives..."
                />
              </div>

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer"
                >
                  Save Subject
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/admin/Teachers.tsx
import React, { useEffect, useState } from 'react';
import api from '../../api/client';
import { useAuthStore } from '../../store/authStore';
import type { Teacher, ApiResponse } from '../../types';
import { Plus, Trash2, UserCheck, ShieldAlert, X } from 'lucide-react';

export const Teachers: React.FC = () => {
  const [teachers, setTeachers] = useState<Teacher[]>([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const { impersonate } = useAuthStore();

  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    nip: '',
    phone: '',
    address: '',
  });

  const loadTeachers = async () => {
    try {
      setLoading(true);
      const res = await api.get<ApiResponse<Teacher[]>>('/teachers');
      setTeachers(res.data.data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadTeachers();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await api.post('/teachers', form);
      setModalOpen(false);
      setForm({ name: '', email: '', password: '', nip: '', phone: '', address: '' });
      loadTeachers();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to create teacher');
    }
  };

  const handleDelete = async (id: number) => {
    if (confirm('Delete teacher account?')) {
      await api.delete(`/teachers/${id}`);
      loadTeachers();
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 tracking-tight">Teacher Management</h2>
          <p className="text-xs text-slate-500 mt-1">Instructor accounts, NIP verification, and session impersonation</p>
        </div>
        <button
          onClick={() => setModalOpen(true)}
          className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-4 py-2.5 rounded-xl shadow-xs transition flex items-center space-x-2 text-sm cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Add Teacher</span>
        </button>
      </div>

      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        {loading ? (
          <div className="p-8 text-center text-slate-500">Loading teachers...</div>
        ) : teachers.length === 0 ? (
          <div className="p-12 text-center text-slate-500">
            <UserCheck className="w-12 h-12 mx-auto text-slate-300 mb-3" />
            <p className="text-base font-medium">No teachers found</p>
            <p className="text-xs mt-1 text-slate-400">Add instructors to begin assigning courses.</p>
          </div>
        ) : (
          <table className="w-full text-left border-collapse text-sm">
            <thead>
              <tr className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th className="px-6 py-4">Instructor Name</th>
                <th className="px-6 py-4">NIP</th>
                <th className="px-6 py-4">Email</th>
                <th className="px-6 py-4">Phone</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {teachers.map((t) => (
                <tr key={t.id} className="hover:bg-slate-50/80 transition">
                  <td className="px-6 py-4 font-semibold text-slate-900">{t.name}</td>
                  <td className="px-6 py-4 font-mono text-slate-600">{t.nip}</td>
                  <td className="px-6 py-4 text-slate-600">{t.email}</td>
                  <td className="px-6 py-4 text-slate-600">{t.phone || '-'}</td>
                  <td className="px-6 py-4 text-right space-x-2">
                    <button
                      onClick={() => impersonate(t.userId)}
                      title="Impersonate Teacher Session"
                      className="inline-flex items-center space-x-1 text-amber-700 bg-amber-50 hover:bg-amber-100 px-2.5 py-1 rounded-lg text-xs font-semibold border border-amber-200 transition cursor-pointer"
                    >
                      <ShieldAlert className="w-3.5 h-3.5 mr-0.5" />
                      <span>Login As</span>
                    </button>
                    <button
                      onClick={() => handleDelete(t.id)}
                      className="text-slate-500 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {modalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative">
            <button
              onClick={() => setModalOpen(false)}
              className="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
            <h3 className="text-lg font-bold text-slate-900 mb-4">Register Teacher Account</h3>

            <form onSubmit={handleSubmit} className="space-y-4 text-sm">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Full Name</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Budi Santoso, M.Pd."
                  value={form.name}
                  onChange={(e) => setForm({ ...form, name: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">NIP Number</label>
                  <input
                    type="text"
                    required
                    placeholder="198501012010011001"
                    value={form.nip}
                    onChange={(e) => setForm({ ...form, nip: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Phone Number</label>
                  <input
                    type="text"
                    placeholder="08123456789"
                    value={form.phone}
                    onChange={(e) => setForm({ ...form, phone: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Email Address</label>
                  <input
                    type="email"
                    required
                    placeholder="teacher@school.com"
                    value={form.email}
                    onChange={(e) => setForm({ ...form, email: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Initial Password</label>
                  <input
                    type="password"
                    placeholder="Defaults to Teacher@123"
                    value={form.password}
                    onChange={(e) => setForm({ ...form, password: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Residential Address</label>
                <textarea
                  rows={2}
                  value={form.address}
                  onChange={(e) => setForm({ ...form, address: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  placeholder="Street address..."
                />
              </div>

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer"
                >
                  Create Teacher
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/admin/Students.tsx
import React, { useEffect, useState } from 'react';
import api from '../../api/client';
import { useAuthStore } from '../../store/authStore';
import type { Student, Classroom, StudentExamCard, ApiResponse } from '../../types';
import {
  Plus,
  Trash2,
  Users,
  ShieldAlert,
  UploadCloud,
  Printer,
  X,
} from 'lucide-react';

export const Students: React.FC = () => {
  const [students, setStudents] = useState<Student[]>([]);
  const [classrooms, setClassrooms] = useState<Classroom[]>([]);
  const [selectedClassroom, setSelectedClassroom] = useState<string>('');
  const [loading, setLoading] = useState(true);

  // Modals
  const [modalOpen, setModalOpen] = useState(false);
  const [importModalOpen, setImportModalOpen] = useState(false);
  const [cardModalOpen, setCardModalOpen] = useState(false);
  const [examCards, setExamCards] = useState<StudentExamCard[]>([]);

  const [excelFile, setExcelFile] = useState<File | null>(null);
  const [importLoading, setImportLoading] = useState(false);
  const [importResult, setImportResult] = useState<any>(null);

  const { impersonate } = useAuthStore();

  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    nis: '',
    nisn: '',
    gender: 'M' as 'M' | 'F',
    phone: '',
    classroomId: '',
    address: '',
  });

  const loadData = async () => {
    try {
      setLoading(true);
      const url = selectedClassroom ? `/students?classroomId=${selectedClassroom}` : '/students';
      const [stRes, crRes] = await Promise.all([
        api.get<ApiResponse<Student[]>>(url),
        api.get<ApiResponse<Classroom[]>>('/classrooms'),
      ]);
      setStudents(stRes.data.data);
      setClassrooms(crRes.data.data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [selectedClassroom]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await api.post('/students', {
        ...form,
        classroomId: form.classroomId ? Number(form.classroomId) : null,
      });
      setModalOpen(false);
      setForm({
        name: '',
        email: '',
        password: '',
        nis: '',
        nisn: '',
        gender: 'M',
        phone: '',
        classroomId: '',
        address: '',
      });
      loadData();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to create student');
    }
  };

  const handleImportExcel = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!excelFile) return;

    const formData = new FormData();
    formData.append('file', excelFile);
    if (selectedClassroom) {
      formData.append('classroomId', selectedClassroom);
    }

    try {
      setImportLoading(true);
      const res = await api.post('/students/import-excel', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      setImportResult(res.data.data);
      loadData();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to import Excel');
    } finally {
      setImportLoading(false);
    }
  };

  const loadExamCards = async () => {
    try {
      const url = selectedClassroom ? `/students/exam-cards?classroomId=${selectedClassroom}` : '/students/exam-cards';
      const res = await api.get<ApiResponse<StudentExamCard[]>>(url);
      setExamCards(res.data.data);
      setCardModalOpen(true);
    } catch (err: any) {
      alert('Failed to load exam cards');
    }
  };

  const handleDelete = async (id: number) => {
    if (confirm('Delete student record?')) {
      await api.delete(`/students/${id}`);
      loadData();
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 tracking-tight">Student Management</h2>
          <p className="text-xs text-slate-500 mt-1">NIS registration, bulk Excel batch onboarding, and CBT exam cards</p>
        </div>

        <div className="flex items-center flex-wrap gap-2">
          <select
            value={selectedClassroom}
            onChange={(e) => setSelectedClassroom(e.target.value)}
            className="bg-white border border-slate-300 text-slate-700 text-xs font-medium rounded-xl px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-indigo-500 shadow-xs"
          >
            <option value="">All Classrooms</option>
            {classrooms.map((c) => (
              <option key={c.id} value={c.id}>
                {c.name}
              </option>
            ))}
          </select>

          <button
            onClick={() => loadExamCards()}
            className="bg-emerald-600 hover:bg-emerald-500 text-white font-medium px-3.5 py-2 rounded-xl text-xs flex items-center space-x-1.5 shadow-xs transition cursor-pointer"
          >
            <Printer className="w-4 h-4" />
            <span>Print Cards</span>
          </button>

          <button
            onClick={() => {
              setImportResult(null);
              setExcelFile(null);
              setImportModalOpen(true);
            }}
            className="bg-blue-600 hover:bg-blue-500 text-white font-medium px-3.5 py-2 rounded-xl text-xs flex items-center space-x-1.5 shadow-xs transition cursor-pointer"
          >
            <UploadCloud className="w-4 h-4" />
            <span>Import Excel</span>
          </button>

          <button
            onClick={() => setModalOpen(true)}
            className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-3.5 py-2 rounded-xl text-xs flex items-center space-x-1.5 shadow-xs transition cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Add Student</span>
          </button>
        </div>
      </div>

      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        {loading ? (
          <div className="p-8 text-center text-slate-500">Loading students...</div>
        ) : students.length === 0 ? (
          <div className="p-12 text-center text-slate-500">
            <Users className="w-12 h-12 mx-auto text-slate-300 mb-3" />
            <p className="text-base font-medium">No students registered yet</p>
            <p className="text-xs mt-1 text-slate-400">Use "Import Excel" to batch upload your student roster.</p>
          </div>
        ) : (
          <table className="w-full text-left border-collapse text-sm">
            <thead>
              <tr className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th className="px-6 py-4">Student Name</th>
                <th className="px-6 py-4">NIS / NISN</th>
                <th className="px-6 py-4">Gender</th>
                <th className="px-6 py-4">Classroom</th>
                <th className="px-6 py-4">Email</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {students.map((s) => (
                <tr key={s.id} className="hover:bg-slate-50/80 transition">
                  <td className="px-6 py-4 font-semibold text-slate-900">{s.name}</td>
                  <td className="px-6 py-4 font-mono text-slate-600">
                    <div>{s.nis}</div>
                    {s.nisn && <div className="text-xs text-slate-400">{s.nisn}</div>}
                  </td>
                  <td className="px-6 py-4">
                    <span
                      className={`text-xs font-semibold px-2 py-0.5 rounded-full ${
                        s.gender === 'M' ? 'bg-sky-100 text-sky-700' : 'bg-pink-100 text-pink-700'
                      }`}
                    >
                      {s.gender === 'M' ? 'Laki-laki' : 'Perempuan'}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-slate-600">
                    {s.classroomName || <span className="text-slate-400 italic">Unassigned</span>}
                  </td>
                  <td className="px-6 py-4 text-slate-600">{s.email}</td>
                  <td className="px-6 py-4 text-right space-x-2">
                    <button
                      onClick={() => impersonate(s.userId)}
                      title="Impersonate Student Session"
                      className="inline-flex items-center space-x-1 text-amber-700 bg-amber-50 hover:bg-amber-100 px-2.5 py-1 rounded-lg text-xs font-semibold border border-amber-200 transition cursor-pointer"
                    >
                      <ShieldAlert className="w-3.5 h-3.5 mr-0.5" />
                      <span>Login As</span>
                    </button>
                    <button
                      onClick={() => handleDelete(s.id)}
                      className="text-slate-500 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {/* Add Student Modal */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative">
            <button
              onClick={() => setModalOpen(false)}
              className="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
            <h3 className="text-lg font-bold text-slate-900 mb-4">Register Student Account</h3>

            <form onSubmit={handleSubmit} className="space-y-4 text-sm">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Full Name</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Siti Nurhaliza"
                  value={form.name}
                  onChange={(e) => setForm({ ...form, name: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">NIS</label>
                  <input
                    type="text"
                    required
                    placeholder="102938"
                    value={form.nis}
                    onChange={(e) => setForm({ ...form, nis: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">NISN</label>
                  <input
                    type="text"
                    placeholder="0056123491"
                    value={form.nisn}
                    onChange={(e) => setForm({ ...form, nisn: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Gender</label>
                  <select
                    value={form.gender}
                    onChange={(e) => setForm({ ...form, gender: e.target.value as 'M' | 'F' })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="M">Laki-laki (M)</option>
                    <option value="F">Perempuan (F)</option>
                  </select>
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Classroom</label>
                  <select
                    value={form.classroomId}
                    onChange={(e) => setForm({ ...form, classroomId: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="">-- Choose Classroom --</option>
                    {classrooms.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Email</label>
                  <input
                    type="email"
                    required
                    placeholder="student@school.com"
                    value={form.email}
                    onChange={(e) => setForm({ ...form, email: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Initial Password</label>
                  <input
                    type="password"
                    placeholder="Defaults to Student@123"
                    value={form.password}
                    onChange={(e) => setForm({ ...form, password: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer"
                >
                  Save Student
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Excel Bulk Import Modal */}
      {importModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative">
            <button
              onClick={() => setImportModalOpen(false)}
              className="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
            <h3 className="text-lg font-bold text-slate-900 mb-2">Import Students via Excel</h3>
            <p className="text-xs text-slate-500 mb-4">
              Upload an <code>.xlsx</code> spreadsheet with columns:{' '}
              <strong>Name, Email, NIS, NISN, Gender (M/F), Phone, Address</strong>.
            </p>

            <form onSubmit={handleImportExcel} className="space-y-4 text-sm">
              <div className="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-indigo-500 transition">
                <UploadCloud className="w-10 h-10 text-slate-400 mx-auto mb-2" />
                <input
                  type="file"
                  accept=".xlsx, .xls"
                  required
                  onChange={(e) => setExcelFile(e.target.files?.[0] || null)}
                  className="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                />
              </div>

              {importResult && (
                <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                  <div className="font-bold text-emerald-700">
                    Successfully imported: {importResult.successCount} students
                  </div>
                  {importResult.errorCount > 0 && (
                    <div className="font-bold text-rose-600 mt-1">
                      Failed rows: {importResult.errorCount}
                      <ul className="list-disc pl-4 font-normal mt-1 text-rose-500 space-y-0.5">
                        {importResult.errors?.map((err: string, i: number) => (
                          <li key={i}>{err}</li>
                        ))}
                      </ul>
                    </div>
                  )}
                </div>
              )}

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setImportModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Close
                </button>
                <button
                  type="submit"
                  disabled={importLoading || !excelFile}
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer disabled:opacity-50"
                >
                  {importLoading ? 'Processing Spreadsheet...' : 'Start Import'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Exam Cards Modal */}
      {cardModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] flex flex-col p-6 shadow-2xl relative">
            <div className="flex items-center justify-between pb-4 border-b border-slate-200">
              <div>
                <h3 className="text-lg font-bold text-slate-900">Student CBT Examination Cards</h3>
                <p className="text-xs text-slate-500">Official student exam hall tickets with NIS barcode verification</p>
              </div>
              <div className="flex items-center space-x-2">
                <button
                  onClick={() => window.print()}
                  className="bg-indigo-600 text-white px-3.5 py-1.5 rounded-xl text-xs font-semibold hover:bg-indigo-500 transition cursor-pointer"
                >
                  Print View
                </button>
                <button
                  onClick={() => setCardModalOpen(false)}
                  className="text-slate-400 hover:text-slate-600 p-1 cursor-pointer"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>
            </div>

            <div className="overflow-y-auto p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              {examCards.map((c) => (
                <div
                  key={c.studentId}
                  className="border-2 border-slate-800 rounded-2xl p-4 bg-white shadow-xs flex flex-col justify-between"
                >
                  <div>
                    <div className="flex items-center justify-between border-b border-slate-200 pb-2 mb-3">
                      <div>
                        <div className="text-xs font-bold uppercase tracking-wider text-indigo-700">
                          KARTU PESERTA UJIAN CBT
                        </div>
                        <div className="text-[10px] text-slate-500">Tahun Ajaran {c.academicYear}</div>
                      </div>
                      <div className="text-xs font-mono font-bold text-slate-700">{c.classroomName}</div>
                    </div>

                    <div className="space-y-1 text-xs text-slate-700">
                      <div>
                        <strong>Nama:</strong> {c.name}
                      </div>
                      <div>
                        <strong>NIS:</strong> {c.nis} / <strong>NISN:</strong> {c.nisn || '-'}
                      </div>
                      <div>
                        <strong>Gender:</strong> {c.gender === 'M' ? 'Laki-laki' : 'Perempuan'}
                      </div>
                    </div>
                  </div>

                  <div className="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between">
                    <div className="font-mono text-[10px] text-slate-500 tracking-wider">
                      ||| |||| | ||||| || ||||||
                      <div className="text-[9px] text-slate-400">{c.barcodeData}</div>
                    </div>
                    <div className="text-[10px] text-center border-t border-slate-400 w-24 pt-0.5">
                      Panitia Ujian
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/materials/MaterialsList.tsx
import React, { useEffect, useState } from 'react';
import api from '../../api/client';
import { useAuthStore } from '../../store/authStore';
import type { LearningMaterial, Subject, Classroom, ApiResponse } from '../../types';
import {
  BookOpen,
  Plus,
  Trash2,
  Eye,
  FileText,
  Video,
  Download,
  X,
  CheckCircle2,
} from 'lucide-react';
import { Link } from 'react-router-dom';

export const MaterialsList: React.FC = () => {
  const [materials, setMaterials] = useState<LearningMaterial[]>([]);
  const [subjects, setSubjects] = useState<Subject[]>([]);
  const [classrooms, setClassrooms] = useState<Classroom[]>([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);

  const { user } = useAuthStore();
  const isAdmin = user?.roles.includes('ROLE_ADMIN');
  const isTeacher = user?.roles.includes('ROLE_TEACHER');

  const [form, setForm] = useState({
    title: '',
    subjectId: '',
    classroomId: '',
    type: 'PDF',
    description: '',
    content: '',
    fileUrl: '',
    published: true,
  });
  const [selectedFile, setSelectedFile] = useState<File | null>(null);

  const loadData = async () => {
    try {
      setLoading(true);
      const [mRes, sRes, cRes] = await Promise.all([
        api.get<ApiResponse<LearningMaterial[]>>('/materials'),
        api.get<ApiResponse<Subject[]>>('/subjects'),
        api.get<ApiResponse<Classroom[]>>('/classrooms'),
      ]);
      setMaterials(mRes.data.data);
      setSubjects(sRes.data.data);
      setClassrooms(cRes.data.data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const formData = new FormData();
      const payload = {
        title: form.title,
        subjectId: Number(form.subjectId),
        classroomId: form.classroomId ? Number(form.classroomId) : null,
        type: form.type,
        description: form.description,
        content: form.content,
        fileUrl: form.fileUrl,
        published: form.published,
      };

      formData.append(
        'data',
        new Blob([JSON.stringify(payload)], { type: 'application/json' })
      );

      if (selectedFile) {
        formData.append('file', selectedFile);
      }

      await api.post('/materials', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      setModalOpen(false);
      setSelectedFile(null);
      setForm({
        title: '',
        subjectId: '',
        classroomId: '',
        type: 'PDF',
        description: '',
        content: '',
        fileUrl: '',
        published: true,
      });
      loadData();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to create material');
    }
  };

  const handleDelete = async (id: number) => {
    if (confirm('Delete this learning material?')) {
      await api.delete(`/materials/${id}`);
      loadData();
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 tracking-tight">Learning Materials</h2>
          <p className="text-xs text-slate-500 mt-1">Digital syllabus modules, reading guides, and lecture files</p>
        </div>

        {(isAdmin || isTeacher) && (
          <button
            onClick={() => setModalOpen(true)}
            className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-4 py-2.5 rounded-xl shadow-xs transition flex items-center space-x-2 text-sm cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Upload Material</span>
          </button>
        )}
      </div>

      {loading ? (
        <div className="p-8 text-center text-slate-500">Loading learning materials...</div>
      ) : materials.length === 0 ? (
        <div className="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500 shadow-xs">
          <BookOpen className="w-12 h-12 mx-auto text-slate-300 mb-3" />
          <p className="text-base font-medium">No learning materials published yet</p>
          <p className="text-xs mt-1 text-slate-400">Instructors can publish course readings and modules here.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {materials.map((m) => (
            <div
              key={m.id}
              className="bg-white rounded-2xl border border-slate-200 hover:border-indigo-400 p-6 shadow-xs hover:shadow-md transition flex flex-col justify-between"
            >
              <div>
                <div className="flex items-center justify-between mb-3">
                  <span className="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md">
                    {m.subjectName}
                  </span>
                  <div className="flex items-center space-x-2 text-xs text-slate-400">
                    <Eye className="w-3.5 h-3.5" />
                    <span>{m.totalViews} views</span>
                  </div>
                </div>

                <h3 className="text-base font-bold text-slate-900 line-clamp-1">{m.title}</h3>
                <p className="text-xs text-slate-500 mt-1 line-clamp-2">{m.description || 'No description provided.'}</p>

                <div className="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                  <span>Class: <strong>{m.classroomName || 'All'}</strong></span>
                  <span>By: <strong>{m.teacherName}</strong></span>
                </div>
              </div>

              <div className="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                <Link
                  to={`/materials/${m.id}`}
                  className="inline-flex items-center space-x-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg transition"
                >
                  <FileText className="w-3.5 h-3.5" />
                  <span>Open Reader</span>
                </Link>

                <div className="flex items-center space-x-2">
                  {m.hasViewed && (
                    <span className="inline-flex items-center text-xs text-emerald-600 font-medium">
                      <CheckCircle2 className="w-3.5 h-3.5 mr-1" />
                      Completed
                    </span>
                  )}
                  {(isAdmin || isTeacher) && (
                    <button
                      onClick={() => handleDelete(m.id)}
                      className="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Upload Material Modal */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl relative">
            <button
              onClick={() => setModalOpen(false)}
              className="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
            <h3 className="text-lg font-bold text-slate-900 mb-4">Publish Learning Material</h3>

            <form onSubmit={handleSubmit} className="space-y-4 text-sm">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Material Title</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Bab 1: Persamaan Linier dan Kuadrat"
                  value={form.title}
                  onChange={(e) => setForm({ ...form, title: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Subject</label>
                  <select
                    required
                    value={form.subjectId}
                    onChange={(e) => setForm({ ...form, subjectId: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="">-- Choose Subject --</option>
                    {subjects.map((s) => (
                      <option key={s.id} value={s.id}>
                        {s.name} ({s.code})
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Target Classroom</label>
                  <select
                    value={form.classroomId}
                    onChange={(e) => setForm({ ...form, classroomId: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="">All Classrooms</option>
                    {classrooms.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Content Type</label>
                  <select
                    value={form.type}
                    onChange={(e) => setForm({ ...form, type: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="PDF">PDF Document</option>
                    <option value="ARTICLE">Article / Text</option>
                    <option value="VIDEO">Video Link</option>
                  </select>
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Attachment File</label>
                  <input
                    type="file"
                    onChange={(e) => setSelectedFile(e.target.files?.[0] || null)}
                    className="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Summary Description</label>
                <textarea
                  rows={2}
                  value={form.description}
                  onChange={(e) => setForm({ ...form, description: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  placeholder="Short description of the topic..."
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Detailed Content</label>
                <textarea
                  rows={4}
                  value={form.content}
                  onChange={(e) => setForm({ ...form, content: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  placeholder="Markdown or lecture text..."
                />
              </div>

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer"
                >
                  Publish Material
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/materials/MaterialDetail.tsx
import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../../api/client';
import { useAuthStore } from '../../store/authStore';
import type { LearningMaterial, ApiResponse } from '../../types';
import {
  ArrowLeft,
  BookOpen,
  Eye,
  Download,
  Calendar,
  User,
  Users,
  CheckCircle2,
} from 'lucide-react';

export const MaterialDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const { user } = useAuthStore();
  const isAdmin = user?.roles.includes('ROLE_ADMIN');
  const isTeacher = user?.roles.includes('ROLE_TEACHER');

  const [material, setMaterial] = useState<LearningMaterial | null>(null);
  const [viewers, setViewers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadMaterial = async () => {
      try {
        setLoading(true);
        const res = await api.get<ApiResponse<LearningMaterial>>(`/materials/${id}`);
        setMaterial(res.data.data);

        // Record student view automatically
        if (!isAdmin && !isTeacher) {
          api.post(`/materials/${id}/views`).catch(() => {});
        } else {
          // If teacher or admin, load viewers list
          api.get<ApiResponse<any[]>>(`/materials/${id}/viewers`)
            .then((r) => setViewers(r.data.data))
            .catch(() => {});
        }
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    if (id) loadMaterial();
  }, [id, isAdmin, isTeacher]);

  if (loading) {
    return <div className="p-8 text-center text-slate-500">Loading module...</div>;
  }

  if (!material) {
    return (
      <div className="p-12 text-center text-slate-500">
        <p>Material not found.</p>
        <Link to="/materials" className="text-indigo-600 font-semibold mt-2 inline-block">
          Back to Materials
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <Link
        to="/materials"
        className="inline-flex items-center space-x-2 text-sm text-slate-500 hover:text-slate-800 transition"
      >
        <ArrowLeft className="w-4 h-4" />
        <span>Back to materials</span>
      </Link>

      <div className="bg-white rounded-3xl border border-slate-200 p-8 shadow-xs space-y-6">
        <div>
          <div className="flex items-center justify-between mb-3">
            <span className="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-3 py-1 rounded-md">
              {material.subjectName}
            </span>
            <div className="flex items-center space-x-2 text-xs text-slate-400">
              <Eye className="w-4 h-4" />
              <span>{material.totalViews} views</span>
            </div>
          </div>

          <h2 className="text-2xl font-bold text-slate-900">{material.title}</h2>
          <p className="text-sm text-slate-600 mt-2">{material.description}</p>

          <div className="flex items-center flex-wrap gap-4 mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
            <div className="flex items-center space-x-1">
              <User className="w-4 h-4 text-slate-400" />
              <span>Instructor: <strong>{material.teacherName}</strong></span>
            </div>
            <div className="flex items-center space-x-1">
              <Users className="w-4 h-4 text-slate-400" />
              <span>Classroom: <strong>{material.classroomName || 'All Classrooms'}</strong></span>
            </div>
            <div className="flex items-center space-x-1">
              <Calendar className="w-4 h-4 text-slate-400" />
              <span>Published: {new Date(material.createdAt).toLocaleDateString()}</span>
            </div>
          </div>
        </div>

        {/* Content Body */}
        {material.content && (
          <div className="prose prose-slate max-w-none pt-4 border-t border-slate-100 text-sm leading-relaxed text-slate-800 whitespace-pre-wrap">
            {material.content}
          </div>
        )}

        {/* Attachment download */}
        {material.fileUrl && (
          <div className="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100 flex items-center justify-between">
            <div className="flex items-center space-x-3">
              <div className="p-2.5 bg-indigo-600 text-white rounded-xl">
                <BookOpen className="w-5 h-5" />
              </div>
              <div>
                <p className="text-sm font-bold text-slate-800">Learning Attachment ({material.type})</p>
                <p className="text-xs text-slate-500">Download or open course syllabus file</p>
              </div>
            </div>
            <a
              href={material.fileUrl}
              target="_blank"
              rel="noreferrer"
              className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-4 py-2 rounded-xl text-xs flex items-center space-x-1.5 shadow-xs transition"
            >
              <Download className="w-4 h-4" />
              <span>Open Document</span>
            </a>
          </div>
        )}
      </div>

      {/* Teacher / Admin Analytics: Viewers Table */}
      {(isAdmin || isTeacher) && (
        <div className="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="text-base font-bold text-slate-900 flex items-center space-x-2">
              <CheckCircle2 className="w-5 h-5 text-emerald-600" />
              <span>Student Reading Activity ({viewers.length} Completed)</span>
            </h3>
          </div>

          {viewers.length === 0 ? (
            <p className="text-xs text-slate-400 italic">No students have accessed this material yet.</p>
          ) : (
            <div className="divide-y divide-slate-100 text-xs">
              {viewers.map((v) => (
                <div key={v.studentId} className="py-2.5 flex items-center justify-between">
                  <div>
                    <span className="font-semibold text-slate-800">{v.studentName}</span>
                    <span className="font-mono text-slate-400 ml-2">({v.nis})</span>
                  </div>
                  <div className="text-slate-400">
                    {new Date(v.viewedAt).toLocaleString()}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/exams/ExamList.tsx
import React, { useEffect, useState } from 'react';
import api from '../../api/client';
import { useAuthStore } from '../../store/authStore';
import type { Exam, Subject, Classroom, ApiResponse } from '../../types';
import {
  FileCheck2,
  Plus,
  Play,
  MonitorPlay,
  Settings,
  Clock,
  Calendar,
  CheckCircle,
  AlertTriangle,
  X,
  FileQuestion,
} from 'lucide-react';
import { Link, useNavigate } from 'react-router-dom';

export const ExamList: React.FC = () => {
  const [exams, setExams] = useState<Exam[]>([]);
  const [subjects, setSubjects] = useState<Subject[]>([]);
  const [classrooms, setClassrooms] = useState<Classroom[]>([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);

  const { user } = useAuthStore();
  const isAdmin = user?.roles.includes('ROLE_ADMIN');
  const isTeacher = user?.roles.includes('ROLE_TEACHER');
  const navigate = useNavigate();

  const [form, setForm] = useState({
    title: '',
    subjectId: '',
    classroomId: '',
    type: 'quiz',
    durationMinutes: 60,
    passingScore: 75,
    startAt: '',
    endAt: '',
    shuffleQuestions: true,
    shuffleOptions: true,
    showResult: true,
    allowRetry: false,
    description: '',
  });

  const loadData = async () => {
    try {
      setLoading(true);
      const [eRes, sRes, cRes] = await Promise.all([
        api.get<ApiResponse<Exam[]>>('/exams'),
        api.get<ApiResponse<Subject[]>>('/subjects'),
        api.get<ApiResponse<Classroom[]>>('/classrooms'),
      ]);
      setExams(eRes.data.data);
      setSubjects(sRes.data.data);
      setClassrooms(cRes.data.data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const payload = {
        title: form.title,
        subjectId: Number(form.subjectId),
        classroomId: Number(form.classroomId),
        type: form.type,
        durationMinutes: Number(form.durationMinutes),
        passingScore: Number(form.passingScore),
        startAt: form.startAt,
        endAt: form.endAt,
        shuffleQuestions: form.shuffleQuestions,
        shuffleOptions: form.shuffleOptions,
        showResult: form.showResult,
        allowRetry: form.allowRetry,
        description: form.description,
        status: 'published',
      };

      await api.post('/exams', payload);
      setModalOpen(false);
      loadData();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to create exam');
    }
  };

  const handleToggleStatus = async (id: number, currentStatus: string) => {
    const nextStatus = currentStatus === 'published' ? 'draft' : 'published';
    await api.patch(`/exams/${id}/status?status=${nextStatus}`);
    loadData();
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 tracking-tight">CBT Computer-Based Testing</h2>
          <p className="text-xs text-slate-500 mt-1">High-integrity online examinations with anti-cheat and live telemetry</p>
        </div>

        {(isAdmin || isTeacher) && (
          <button
            onClick={() => setModalOpen(true)}
            className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-4 py-2.5 rounded-xl shadow-xs transition flex items-center space-x-2 text-sm cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Create Examination</span>
          </button>
        )}
      </div>

      {loading ? (
        <div className="p-8 text-center text-slate-500">Loading examinations...</div>
      ) : exams.length === 0 ? (
        <div className="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500 shadow-xs">
          <FileCheck2 className="w-12 h-12 mx-auto text-slate-300 mb-3" />
          <p className="text-base font-medium">No examinations scheduled</p>
          <p className="text-xs mt-1 text-slate-400">Scheduled tests will appear here.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {exams.map((exam) => (
            <div
              key={exam.id}
              className="bg-white rounded-2xl border border-slate-200 hover:border-indigo-400 p-6 shadow-xs hover:shadow-md transition flex flex-col justify-between"
            >
              <div>
                <div className="flex items-center justify-between mb-3">
                  <span className="text-xs font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-md">
                    {exam.subjectName}
                  </span>
                  <span
                    className={`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase ${
                      exam.status === 'published'
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-amber-100 text-amber-700'
                    }`}
                  >
                    {exam.status}
                  </span>
                </div>

                <h3 className="text-base font-bold text-slate-900 line-clamp-1">{exam.title}</h3>
                <p className="text-xs text-slate-500 mt-1 line-clamp-2">
                  Class: <strong>{exam.classroomName}</strong> | Passing: <strong>{exam.passingScore}%</strong>
                </p>

                <div className="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs text-slate-600">
                  <div className="flex items-center space-x-1.5">
                    <Clock className="w-4 h-4 text-slate-400" />
                    <span>{exam.durationMinutes} Minutes</span>
                  </div>
                  <div className="flex items-center space-x-1.5">
                    <FileQuestion className="w-4 h-4 text-slate-400" />
                    <span>{exam.totalQuestions} Questions</span>
                  </div>
                  <div className="col-span-2 flex items-center space-x-1.5 text-[11px] text-slate-400">
                    <Calendar className="w-3.5 h-3.5" />
                    <span>
                      {new Date(exam.startAt).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' })}
                      {' - '}
                      {new Date(exam.endAt).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' })}
                    </span>
                  </div>
                </div>
              </div>

              {/* Action buttons */}
              <div className="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                {isAdmin || isTeacher ? (
                  <>
                    <Link
                      to={`/exams/${exam.id}/questions`}
                      className="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2 px-3 rounded-xl text-xs transition"
                    >
                      Questions ({exam.totalQuestions})
                    </Link>
                    <Link
                      to={`/exams/${exam.id}/monitor`}
                      className="flex-1 text-center bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 px-3 rounded-xl text-xs transition flex items-center justify-center space-x-1"
                    >
                      <MonitorPlay className="w-3.5 h-3.5 mr-1" />
                      <span>Live Monitor</span>
                    </Link>
                  </>
                ) : (
                  <>
                    {exam.studentAttemptStatus === 'completed' ? (
                      <div className="w-full flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                        <div className="flex items-center space-x-1.5 text-xs font-bold text-slate-700">
                          <CheckCircle className="w-4 h-4 text-emerald-600" />
                          <span>Score: {exam.studentScore ?? 0}/100</span>
                        </div>
                        <span
                          className={`text-xs font-bold px-2 py-0.5 rounded-md ${
                            exam.studentPassed ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'
                          }`}
                        >
                          {exam.studentPassed ? 'PASSED' : 'FAILED'}
                        </span>
                      </div>
                    ) : (
                      <Link
                        to={`/exams/${exam.id}/runner`}
                        className="w-full text-center bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center space-x-2"
                      >
                        <Play className="w-4 h-4 fill-white" />
                        <span>{exam.studentAttemptStatus === 'in_progress' ? 'Resume Exam' : 'Enter CBT Exam'}</span>
                      </Link>
                    )}
                  </>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Create Exam Modal */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl relative">
            <button
              onClick={() => setModalOpen(false)}
              className="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
            <h3 className="text-lg font-bold text-slate-900 mb-4">Create CBT Examination</h3>

            <form onSubmit={handleSubmit} className="space-y-4 text-sm">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Exam Title</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Penilaian Akhir Semester Ganjil 2026"
                  value={form.title}
                  onChange={(e) => setForm({ ...form, title: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Subject</label>
                  <select
                    required
                    value={form.subjectId}
                    onChange={(e) => setForm({ ...form, subjectId: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="">-- Choose Subject --</option>
                    {subjects.map((s) => (
                      <option key={s.id} value={s.id}>
                        {s.name} ({s.code})
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Target Class</label>
                  <select
                    required
                    value={form.classroomId}
                    onChange={(e) => setForm({ ...form, classroomId: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="">-- Choose Classroom --</option>
                    {classrooms.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Duration (Minutes)</label>
                  <input
                    type="number"
                    required
                    min={5}
                    max={240}
                    value={form.durationMinutes}
                    onChange={(e) => setForm({ ...form, durationMinutes: Number(e.target.value) })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Passing Score (%)</label>
                  <input
                    type="number"
                    required
                    min={0}
                    max={100}
                    value={form.passingScore}
                    onChange={(e) => setForm({ ...form, passingScore: Number(e.target.value) })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Start Time</label>
                  <input
                    type="datetime-local"
                    required
                    value={form.startAt}
                    onChange={(e) => setForm({ ...form, startAt: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">End Time</label>
                  <input
                    type="datetime-local"
                    required
                    value={form.endAt}
                    onChange={(e) => setForm({ ...form, endAt: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3 pt-2">
                <label className="flex items-center space-x-2 text-xs font-medium text-slate-700 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={form.shuffleQuestions}
                    onChange={(e) => setForm({ ...form, shuffleQuestions: e.target.checked })}
                    className="rounded text-indigo-600 focus:ring-indigo-500"
                  />
                  <span>Randomize Questions</span>
                </label>
                <label className="flex items-center space-x-2 text-xs font-medium text-slate-700 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={form.shuffleOptions}
                    onChange={(e) => setForm({ ...form, shuffleOptions: e.target.checked })}
                    className="rounded text-indigo-600 focus:ring-indigo-500"
                  />
                  <span>Randomize Options</span>
                </label>
              </div>

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer"
                >
                  Create & Publish Exam
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/exams/ExamEditor.tsx
import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../../api/client';
import type { Exam, Question, ApiResponse } from '../../types';
import {
  ArrowLeft,
  Plus,
  Trash2,
  UploadCloud,
  FileQuestion,
  CheckCircle,
  HelpCircle,
  X,
} from 'lucide-react';

export const ExamEditor: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const [exam, setExam] = useState<Exam | null>(null);
  const [questions, setQuestions] = useState<Question[]>([]);
  const [loading, setLoading] = useState(true);

  // Modals
  const [modalOpen, setModalOpen] = useState(false);
  const [excelModalOpen, setExcelModalOpen] = useState(false);
  const [excelFile, setExcelFile] = useState<File | null>(null);
  const [importLoading, setImportLoading] = useState(false);

  const [form, setForm] = useState({
    questionText: '',
    questionType: 'multiple_choice' as 'multiple_choice' | 'essay' | 'true_false',
    optA: '',
    optB: '',
    optC: '',
    optD: '',
    optE: '',
    correctAnswer: '',
    points: 1,
    difficulty: 'medium' as 'easy' | 'medium' | 'hard',
    explanation: '',
  });

  const loadExamAndQuestions = async () => {
    try {
      setLoading(true);
      const [eRes, qRes] = await Promise.all([
        api.get<ApiResponse<Exam>>(`/exams/${id}`),
        api.get<ApiResponse<Question[]>>(`/exams/${id}/questions`),
      ]);
      setExam(eRes.data.data);
      setQuestions(qRes.data.data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (id) loadExamAndQuestions();
  }, [id]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      let options: string[] = [];
      let correctAnswer = form.correctAnswer;

      if (form.questionType === 'multiple_choice') {
        options = [form.optA, form.optB, form.optC, form.optD, form.optE].filter(Boolean);
      } else if (form.questionType === 'true_false') {
        options = ['Benar', 'Salah'];
      }

      const payload = {
        examinationId: Number(id),
        questionText: form.questionText,
        questionType: form.questionType,
        options,
        correctAnswer,
        points: Number(form.points),
        difficulty: form.difficulty,
        explanation: form.explanation,
      };

      await api.post(`/exams/${id}/questions`, payload);
      setModalOpen(false);
      setForm({
        questionText: '',
        questionType: 'multiple_choice',
        optA: '',
        optB: '',
        optC: '',
        optD: '',
        optE: '',
        correctAnswer: '',
        points: 1,
        difficulty: 'medium',
        explanation: '',
      });
      loadExamAndQuestions();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to add question');
    }
  };

  const handleImportExcel = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!excelFile) return;

    const formData = new FormData();
    formData.append('file', excelFile);

    try {
      setImportLoading(true);
      const res = await api.post(`/exams/${id}/questions/import-excel`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      alert(`Imported ${res.data.data.importedCount} questions successfully!`);
      setExcelModalOpen(false);
      setExcelFile(null);
      loadExamAndQuestions();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to import questions');
    } finally {
      setImportLoading(false);
    }
  };

  const handleDeleteQuestion = async (questionId: number) => {
    if (confirm('Delete this question from examination?')) {
      await api.delete(`/exams/${id}/questions/${questionId}`);
      loadExamAndQuestions();
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <Link
          to="/exams"
          className="inline-flex items-center space-x-2 text-sm text-slate-500 hover:text-slate-800 transition"
        >
          <ArrowLeft className="w-4 h-4" />
          <span>Back to Examinations</span>
        </Link>

        <div className="flex items-center space-x-3">
          <button
            onClick={() => setExcelModalOpen(true)}
            className="bg-blue-600 hover:bg-blue-500 text-white font-medium px-3.5 py-2 rounded-xl text-xs flex items-center space-x-1.5 shadow-xs transition cursor-pointer"
          >
            <UploadCloud className="w-4 h-4" />
            <span>Excel Batch Import</span>
          </button>
          <button
            onClick={() => setModalOpen(true)}
            className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-3.5 py-2 rounded-xl text-xs flex items-center space-x-1.5 shadow-xs transition cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Add Question</span>
          </button>
        </div>
      </div>

      {exam && (
        <div className="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex items-center justify-between">
          <div>
            <span className="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md">
              {exam.subjectName}
            </span>
            <h2 className="text-xl font-bold text-slate-900 mt-2">{exam.title}</h2>
            <p className="text-xs text-slate-500 mt-1">
              Class: <strong>{exam.classroomName}</strong> | Total Questions: <strong>{questions.length}</strong> | Duration:{' '}
              <strong>{exam.durationMinutes} mins</strong>
            </p>
          </div>
        </div>
      )}

      {loading ? (
        <div className="p-8 text-center text-slate-500">Loading questions...</div>
      ) : questions.length === 0 ? (
        <div className="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500 shadow-xs">
          <FileQuestion className="w-12 h-12 mx-auto text-slate-300 mb-3" />
          <p className="text-base font-medium">No questions in this examination</p>
          <p className="text-xs mt-1 text-slate-400">Click "Add Question" or "Excel Batch Import" to populate questions.</p>
        </div>
      ) : (
        <div className="space-y-4">
          {questions.map((q, idx) => (
            <div
              key={q.id}
              className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4"
            >
              <div className="flex items-start justify-between">
                <div className="flex items-center space-x-3">
                  <span className="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs flex items-center justify-center">
                    {idx + 1}
                  </span>
                  <span className="text-xs font-semibold uppercase text-slate-500">
                    Type: <strong>{q.questionType}</strong> | Points: <strong>{q.points}</strong>
                  </span>
                </div>
                <button
                  onClick={() => handleDeleteQuestion(q.id)}
                  className="text-slate-400 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>

              <div className="text-sm font-semibold text-slate-900 whitespace-pre-wrap">{q.questionText}</div>

              {q.options && q.options.length > 0 && (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-2 pt-2">
                  {q.options.map((opt, optIdx) => {
                    const letter = String.fromCharCode(65 + optIdx);
                    const isCorrect = q.correctAnswer === opt || q.correctAnswer === letter;
                    return (
                      <div
                        key={optIdx}
                        className={`text-xs p-3 rounded-xl border flex items-center space-x-2 ${
                          isCorrect
                            ? 'bg-emerald-50 border-emerald-300 text-emerald-900 font-semibold'
                            : 'bg-slate-50 border-slate-200 text-slate-700'
                        }`}
                      >
                        <span className="w-5 h-5 rounded-md bg-white border border-slate-300 font-bold flex items-center justify-center text-[10px]">
                          {letter}
                        </span>
                        <span>{opt}</span>
                        {isCorrect && <CheckCircle className="w-4 h-4 text-emerald-600 ml-auto" />}
                      </div>
                    );
                  })}
                </div>
              )}

              {q.correctAnswer && (
                <div className="text-xs text-slate-500 pt-2 border-t border-slate-100">
                  Correct Answer: <strong className="text-emerald-700 font-mono">{q.correctAnswer}</strong>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {/* Add Question Modal */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <button
              onClick={() => setModalOpen(false)}
              className="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
            <h3 className="text-lg font-bold text-slate-900 mb-4">Add Examination Question</h3>

            <form onSubmit={handleSubmit} className="space-y-4 text-sm">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Question Type</label>
                  <select
                    value={form.questionType}
                    onChange={(e) => setForm({ ...form, questionType: e.target.value as any })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="multiple_choice">Multiple Choice (Pilihan Ganda)</option>
                    <option value="true_false">True / False (Benar / Salah)</option>
                    <option value="essay">Essay / Descriptive</option>
                  </select>
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Points / Score</label>
                  <input
                    type="number"
                    required
                    min={1}
                    max={50}
                    value={form.points}
                    onChange={(e) => setForm({ ...form, points: Number(e.target.value) })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Question Text</label>
                <textarea
                  rows={3}
                  required
                  value={form.questionText}
                  onChange={(e) => setForm({ ...form, questionText: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  placeholder="Enter the problem statement or question..."
                />
              </div>

              {form.questionType === 'multiple_choice' && (
                <div className="space-y-3 pt-2 border-t border-slate-100">
                  <div className="font-semibold text-slate-700">Answer Options & Correct Key</div>
                  {['A', 'B', 'C', 'D', 'E'].map((letter) => {
                    const keyName = `opt${letter}` as keyof typeof form;
                    return (
                      <div key={letter} className="flex items-center space-x-2">
                        <span className="w-7 h-7 rounded-lg bg-slate-100 font-bold text-xs flex items-center justify-center">
                          {letter}
                        </span>
                        <input
                          type="text"
                          required={letter === 'A' || letter === 'B'}
                          placeholder={`Option ${letter} text...`}
                          value={String(form[keyName])}
                          onChange={(e) => setForm({ ...form, [keyName]: e.target.value })}
                          className="flex-1 border border-slate-300 rounded-xl px-3 py-1.5 focus:ring-1 focus:ring-indigo-500 focus:outline-none text-xs"
                        />
                        <label className="flex items-center space-x-1 text-xs text-slate-600 cursor-pointer">
                          <input
                            type="radio"
                            name="correctAnswerKey"
                            checked={form.correctAnswer === String(form[keyName]) && form.correctAnswer !== ''}
                            onChange={() => setForm({ ...form, correctAnswer: String(form[keyName]) })}
                            className="text-emerald-600 focus:ring-emerald-500"
                          />
                          <span>Correct</span>
                        </label>
                      </div>
                    );
                  })}
                </div>
              )}

              {form.questionType === 'true_false' && (
                <div className="pt-2 border-t border-slate-100 space-y-2">
                  <label className="block font-semibold text-slate-700">Correct Answer</label>
                  <div className="flex items-center space-x-4">
                    <label className="flex items-center space-x-1.5 cursor-pointer">
                      <input
                        type="radio"
                        name="tf"
                        value="Benar"
                        checked={form.correctAnswer === 'Benar'}
                        onChange={(e) => setForm({ ...form, correctAnswer: e.target.value })}
                      />
                      <span>Benar (True)</span>
                    </label>
                    <label className="flex items-center space-x-1.5 cursor-pointer">
                      <input
                        type="radio"
                        name="tf"
                        value="Salah"
                        checked={form.correctAnswer === 'Salah'}
                        onChange={(e) => setForm({ ...form, correctAnswer: e.target.value })}
                      />
                      <span>Salah (False)</span>
                    </label>
                  </div>
                </div>
              )}

              {form.questionType === 'essay' && (
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Grading Rubric / Answer Key Notes</label>
                  <textarea
                    rows={2}
                    value={form.correctAnswer}
                    onChange={(e) => setForm({ ...form, correctAnswer: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                    placeholder="Key concepts required for maximum score..."
                  />
                </div>
              )}

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer"
                >
                  Save Question
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Excel Question Import Modal */}
      {excelModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative">
            <button
              onClick={() => setExcelModalOpen(false)}
              className="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
            <h3 className="text-lg font-bold text-slate-900 mb-2">Bulk Import Questions from Excel</h3>
            <p className="text-xs text-slate-500 mb-4">
              Upload an <code>.xlsx</code> spreadsheet with columns:{' '}
              <strong>Question Text, Type, Opt A, Opt B, Opt C, Opt D, Opt E, Correct Answer, Points, Explanation</strong>.
            </p>

            <form onSubmit={handleImportExcel} className="space-y-4 text-sm">
              <div className="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-indigo-500 transition">
                <UploadCloud className="w-10 h-10 text-slate-400 mx-auto mb-2" />
                <input
                  type="file"
                  accept=".xlsx, .xls"
                  required
                  onChange={(e) => setExcelFile(e.target.files?.[0] || null)}
                  className="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700"
                />
              </div>

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setExcelModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Close
                </button>
                <button
                  type="submit"
                  disabled={importLoading || !excelFile}
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer disabled:opacity-50"
                >
                  {importLoading ? 'Uploading & Parsing...' : 'Import Questions'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/exams/ExamMonitor.tsx
import React, { useEffect, useState, useRef } from 'react';
import { useParams, Link } from 'react-router-dom';
import { Client } from '@stomp/stompjs';
import SockJS from 'sockjs-client';
import api from '../../api/client';
import type { Exam, ExamMonitorItem, ApiResponse } from '../../types';
import {
  ArrowLeft,
  MonitorPlay,
  Users,
  AlertTriangle,
  CheckCircle2,
  Clock,
  Radio,
  RefreshCw,
  Award,
} from 'lucide-react';

export const ExamMonitor: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const [exam, setExam] = useState<Exam | null>(null);
  const [attempts, setAttempts] = useState<ExamMonitorItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [wsConnected, setWsConnected] = useState(false);
  const [recentAlert, setRecentAlert] = useState<string | null>(null);

  const stompClientRef = useRef<Client | null>(null);

  const loadData = async () => {
    try {
      setLoading(true);
      const [eRes, mRes] = await Promise.all([
        api.get<ApiResponse<Exam>>(`/exams/${id}`),
        api.get<ApiResponse<ExamMonitorItem[]>>(`/exams/${id}/monitor`),
      ]);
      setExam(eRes.data.data);
      setAttempts(mRes.data.data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (!id) return;
    loadData();

    // Setup STOMP WebSocket client
    const socketFactory = () => new SockJS('/ws');
    const client = new Client({
      webSocketFactory: socketFactory,
      reconnectDelay: 5000,
      heartbeatIncoming: 4000,
      heartbeatOutgoing: 4000,
      onConnect: () => {
        setWsConnected(true);
        client.subscribe(`/topic/exams/${id}/monitor`, (message) => {
          try {
            const event = JSON.parse(message.body);
            handleRealtimeEvent(event);
          } catch (e) {
            console.error('Error handling WS message', e);
          }
        });
      },
      onDisconnect: () => {
        setWsConnected(false);
      },
    });

    client.activate();
    stompClientRef.current = client;

    return () => {
      if (stompClientRef.current) {
        stompClientRef.current.deactivate();
      }
    };
  }, [id]);

  const handleRealtimeEvent = (event: any) => {
    const { eventType, data } = event;

    if (eventType === 'VIOLATION_REPORTED') {
      setRecentAlert(`🚨 VIOLATION: Student ${data.studentName} flagged for ${data.reason}!`);
      setTimeout(() => setRecentAlert(null), 6000);
    }

    // Refresh telemetry table
    api.get<ApiResponse<ExamMonitorItem[]>>(`/exams/${id}/monitor`)
      .then((r) => setAttempts(r.data.data))
      .catch(() => {});
  };

  const activeCount = attempts.filter((a) => a.status === 'in_progress').length;
  const completedCount = attempts.filter((a) => a.status === 'completed' || a.status === 'submitted').length;
  const totalViolations = attempts.reduce((acc, a) => acc + (a.violations || 0), 0);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <Link
          to="/exams"
          className="inline-flex items-center space-x-2 text-sm text-slate-500 hover:text-slate-800 transition"
        >
          <ArrowLeft className="w-4 h-4" />
          <span>Back to Examinations</span>
        </Link>

        <div className="flex items-center space-x-3">
          <div
            className={`flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border ${
              wsConnected
                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                : 'bg-amber-50 text-amber-700 border-amber-200 animate-pulse'
            }`}
          >
            <Radio className={`w-3.5 h-3.5 ${wsConnected ? 'text-emerald-500 animate-ping' : ''}`} />
            <span>{wsConnected ? 'Live Telemetry Active' : 'Connecting WebSocket...'}</span>
          </div>

          <button
            onClick={() => loadData()}
            className="p-2 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-slate-600 transition shadow-xs cursor-pointer"
          >
            <RefreshCw className="w-4 h-4" />
          </button>
        </div>
      </div>

      {recentAlert && (
        <div className="bg-rose-500 text-white px-5 py-3 rounded-2xl shadow-lg shadow-rose-500/25 flex items-center space-x-3 text-sm font-bold animate-bounce">
          <AlertTriangle className="w-5 h-5 shrink-0" />
          <span>{recentAlert}</span>
        </div>
      )}

      {exam && (
        <div className="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex items-center justify-between">
          <div>
            <div className="flex items-center space-x-2">
              <span className="text-xs font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-md">
                {exam.subjectName}
              </span>
              <span className="text-xs text-slate-400">Class: {exam.classroomName}</span>
            </div>
            <h2 className="text-2xl font-bold text-slate-900 mt-2">{exam.title}</h2>
          </div>
          <div className="text-right">
            <span className="text-xs font-medium text-slate-400">Exam Pass Mark</span>
            <div className="text-xl font-bold text-emerald-600">{exam.passingScore}%</div>
          </div>
        </div>
      )}

      {/* Metrics Row */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
          <div className="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
            <Users className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-medium text-slate-500">Actively Taking Exam</p>
            <h3 className="text-2xl font-bold text-slate-800">{activeCount} students</h3>
          </div>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
          <div className="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
            <CheckCircle2 className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-medium text-slate-500">Submitted & Completed</p>
            <h3 className="text-2xl font-bold text-slate-800">{completedCount} students</h3>
          </div>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
          <div className="p-3 bg-rose-50 text-rose-600 rounded-xl">
            <AlertTriangle className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-medium text-slate-500">Proctoring Violations</p>
            <h3 className="text-2xl font-bold text-rose-600">{totalViolations} flags</h3>
          </div>
        </div>
      </div>

      {/* Telemetry Table */}
      <div className="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h3 className="text-base font-bold text-slate-900 flex items-center space-x-2">
            <MonitorPlay className="w-5 h-5 text-indigo-600" />
            <span>Real-time Candidate Telemetry</span>
          </h3>
          <span className="text-xs text-slate-400">Total Enrolled: {attempts.length}</span>
        </div>

        {loading ? (
          <div className="p-8 text-center text-slate-500">Loading telemetry data...</div>
        ) : attempts.length === 0 ? (
          <div className="p-12 text-center text-slate-500">
            <Users className="w-12 h-12 mx-auto text-slate-300 mb-3" />
            <p className="text-base font-medium">No candidate sessions active</p>
            <p className="text-xs mt-1 text-slate-400">Students entering the CBT room will automatically appear here.</p>
          </div>
        ) : (
          <table className="w-full text-left border-collapse text-sm">
            <thead>
              <tr className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th className="px-6 py-4">Student</th>
                <th className="px-6 py-4">Session Status</th>
                <th className="px-6 py-4">Questions Progress</th>
                <th className="px-6 py-4">Violations</th>
                <th className="px-6 py-4">Score</th>
                <th className="px-6 py-4 text-right">Started At</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {attempts.map((att) => (
                <tr key={att.attemptId} className="hover:bg-slate-50/80 transition">
                  <td className="px-6 py-4">
                    <div className="font-semibold text-slate-900">{att.studentName}</div>
                    <div className="text-xs font-mono text-slate-400">NIS: {att.nis}</div>
                  </td>

                  <td className="px-6 py-4">
                    <span
                      className={`text-xs font-semibold px-2.5 py-1 rounded-full uppercase tracking-wider ${
                        att.status === 'in_progress'
                          ? 'bg-amber-100 text-amber-700 animate-pulse'
                          : att.status === 'completed'
                          ? 'bg-emerald-100 text-emerald-700'
                          : 'bg-indigo-100 text-indigo-700'
                      }`}
                    >
                      {att.status.replace('_', ' ')}
                    </span>
                  </td>

                  <td className="px-6 py-4">
                    <div className="flex items-center space-x-2">
                      <div className="w-24 bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div
                          className="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                          style={{
                            width: `${att.totalQuestions > 0 ? (att.answeredCount / att.totalQuestions) * 100 : 0}%`,
                          }}
                        />
                      </div>
                      <span className="text-xs font-medium text-slate-600">
                        {att.answeredCount}/{att.totalQuestions}
                      </span>
                    </div>
                  </td>

                  <td className="px-6 py-4">
                    {att.violations > 0 ? (
                      <span className="inline-flex items-center space-x-1 bg-rose-100 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-lg">
                        <AlertTriangle className="w-3.5 h-3.5" />
                        <span>{att.violations} Warning{att.violations > 1 ? 's' : ''}</span>
                      </span>
                    ) : (
                      <span className="text-xs text-slate-400">0 Flags</span>
                    )}
                  </td>

                  <td className="px-6 py-4">
                    {att.score !== null && att.score !== undefined ? (
                      <span className="font-bold text-slate-800 text-sm">
                        {att.score} / 100
                      </span>
                    ) : (
                      <span className="text-xs text-slate-400 italic">In progress</span>
                    )}
                  </td>

                  <td className="px-6 py-4 text-right text-xs text-slate-500">
                    {new Date(att.startedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/exams/ExamRunner.tsx
import React, { useEffect, useState, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../../api/client';
import type { ExamStartResponse, Question, ApiResponse } from '../../types';
import {
  Clock,
  AlertTriangle,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Send,
  Maximize2,
  Check,
  Award,
} from 'lucide-react';

export const ExamRunner: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const [session, setSession] = useState<ExamStartResponse | null>(null);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [answers, setAnswers] = useState<Record<number, string>>({});
  const [secondsLeft, setSecondsLeft] = useState<number>(0);
  const [violations, setViolations] = useState<number>(0);
  const [saving, setSaving] = useState(false);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Submit / Finish states
  const [submitConfirmOpen, setSubmitConfirmOpen] = useState(false);
  const [submittedResult, setSubmittedResult] = useState<any>(null);

  const attemptIdRef = useRef<number | null>(null);

  useEffect(() => {
    const startExam = async () => {
      try {
        setLoading(true);
        const res = await api.post<ApiResponse<ExamStartResponse>>(`/exams/${id}/start`);
        const data = res.data.data;
        setSession(data);
        attemptIdRef.current = data.attemptId;
        setSecondsLeft(data.remainingSeconds);

        // Prepopulate existing answers
        const initialAnswers: Record<number, string> = {};
        data.questions.forEach((q) => {
          if (q.studentAnswer) {
            initialAnswers[q.id] = q.studentAnswer;
          }
        });
        setAnswers(initialAnswers);
      } catch (err: any) {
        setError(err.response?.data?.message || 'Failed to start examination session');
      } finally {
        setLoading(false);
      }
    };

    startExam();
  }, [id]);

  // Anti-Cheat: Tab Switch & Visibility Detection
  useEffect(() => {
    const handleVisibilityChange = () => {
      if (document.hidden && attemptIdRef.current && !submittedResult) {
        api.post(`/exams/attempts/${attemptIdRef.current}/violation`, {
          reason: 'TAB_SWITCH_OR_MINIMIZED',
        }).then((res) => {
          const count = res.data.data.violations;
          setViolations(count);
          if (count >= 5) {
            alert('Maximum security violations reached (5). Your exam is being automatically submitted.');
            handleSubmitExam();
          }
        }).catch(() => {});
      }
    };

    const handleContextMenu = (e: MouseEvent) => {
      e.preventDefault();
    };

    document.addEventListener('visibilitychange', handleVisibilityChange);
    document.addEventListener('contextmenu', handleContextMenu);

    return () => {
      document.removeEventListener('visibilitychange', handleVisibilityChange);
      document.removeEventListener('contextmenu', handleContextMenu);
    };
  }, [submittedResult]);

  // Timer Countdown
  useEffect(() => {
    if (secondsLeft <= 0 || submittedResult) return;

    const timer = setInterval(() => {
      setSecondsLeft((prev) => {
        if (prev <= 1) {
          clearInterval(timer);
          handleSubmitExam();
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => clearInterval(timer);
  }, [secondsLeft, submittedResult]);

  const handleSelectOption = async (questionId: number, value: string) => {
    setAnswers((prev) => ({ ...prev, [questionId]: value }));

    if (session?.attemptId) {
      setSaving(true);
      try {
        await api.post(`/exams/attempts/${session.attemptId}/answer`, {
          questionId,
          answerText: value,
        });
      } catch (e) {
        console.error('Failed to autosave answer', e);
      } finally {
        setSaving(false);
      }
    }
  };

  const handleSubmitExam = async () => {
    if (!session?.attemptId) return;

    try {
      setLoading(true);
      const res = await api.post(`/exams/attempts/${session.attemptId}/submit`);
      setSubmittedResult(res.data.data);
      setSubmitConfirmOpen(false);
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to submit exam');
    } finally {
      setLoading(false);
    }
  };

  const formatTime = (secs: number) => {
    const h = Math.floor(secs / 3600);
    const m = Math.floor((secs % 3600) / 60);
    const s = secs % 60;
    return `${h > 0 ? `${h.toString().padStart(2, '0')}:` : ''}${m
      .toString()
      .padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
  };

  const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen().catch(() => {});
    } else {
      document.exitFullscreen().catch(() => {});
    }
  };

  if (loading && !session) {
    return (
      <div className="min-h-screen bg-slate-900 flex items-center justify-center text-white">
        <div className="text-center space-y-4">
          <div className="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
          <p className="text-sm font-semibold tracking-wide">Initializing secure CBT exam runner...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-slate-900 flex items-center justify-center p-6 text-white">
        <div className="max-w-md w-full bg-slate-800 p-8 rounded-3xl border border-slate-700 text-center space-y-4 shadow-2xl">
          <AlertTriangle className="w-12 h-12 text-rose-400 mx-auto" />
          <h2 className="text-xl font-bold">Access Restricted</h2>
          <p className="text-sm text-slate-300">{error}</p>
          <button
            onClick={() => navigate('/exams')}
            className="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-2.5 rounded-xl text-xs transition cursor-pointer"
          >
            Return to Exam List
          </button>
        </div>
      </div>
    );
  }

  if (submittedResult) {
    return (
      <div className="min-h-screen bg-slate-900 flex items-center justify-center p-6 text-white">
        <div className="max-w-md w-full bg-slate-800 p-8 rounded-3xl border border-slate-700 text-center space-y-6 shadow-2xl">
          <div className="w-16 h-16 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
            <Award className="w-8 h-8" />
          </div>
          <div>
            <h2 className="text-2xl font-bold">Examination Submitted!</h2>
            <p className="text-xs text-slate-400 mt-1">Your responses have been processed.</p>
          </div>

          <div className="bg-slate-900/60 p-6 rounded-2xl border border-slate-700/60 space-y-3">
            <div className="text-xs text-slate-400">Final Score</div>
            <div className="text-4xl font-extrabold text-indigo-400">{submittedResult.score} / 100</div>
            <div className="text-xs font-semibold">
              Status:{' '}
              <span className={submittedResult.passed ? 'text-emerald-400' : 'text-rose-400'}>
                {submittedResult.passed ? 'PASSED (LULUS)' : 'DID NOT PASS (TIDAK LULUS)'}
              </span>
            </div>
          </div>

          <button
            onClick={() => navigate('/exams')}
            className="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl text-sm transition cursor-pointer"
          >
            Return to Dashboard
          </button>
        </div>
      </div>
    );
  }

  if (!session || !session.questions || session.questions.length === 0) {
    return null;
  }

  const currentQ = session.questions[currentIndex];
  const answeredTotal = Object.keys(answers).length;

  return (
    <div className="min-h-screen bg-slate-900 text-slate-100 flex flex-col select-none">
      {/* Top Proctoring Header */}
      <header className="bg-slate-800/90 backdrop-blur-md border-b border-slate-700/80 px-6 py-3.5 flex items-center justify-between sticky top-0 z-40">
        <div className="flex items-center space-x-3">
          <div className="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-white text-sm shadow-md shadow-indigo-600/30">
            CBT
          </div>
          <div>
            <h1 className="text-sm font-bold text-white">{session.examTitle}</h1>
            <div className="flex items-center space-x-3 text-xs text-slate-400 mt-0.5">
              <span>Candidate Active</span>
              <span>•</span>
              <span className="text-emerald-400 font-medium">Autosave Connected</span>
            </div>
          </div>
        </div>

        {/* Timer & Fullscreen Controls */}
        <div className="flex items-center space-x-4">
          <div
            className={`flex items-center space-x-2 px-4 py-2 rounded-xl border font-mono text-sm font-bold shadow-xs ${
              secondsLeft < 300
                ? 'bg-rose-500/20 border-rose-500/50 text-rose-400 animate-pulse'
                : 'bg-slate-900 border-slate-700 text-indigo-400'
            }`}
          >
            <Clock className="w-4 h-4" />
            <span>{formatTime(secondsLeft)}</span>
          </div>

          <button
            onClick={toggleFullscreen}
            title="Toggle Fullscreen Lock"
            className="p-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-xl transition cursor-pointer"
          >
            <Maximize2 className="w-4 h-4" />
          </button>
        </div>
      </header>

      {/* Proctoring Warning Banner */}
      {violations > 0 && (
        <div className="bg-rose-600 text-white px-6 py-2.5 flex items-center justify-center space-x-2 text-xs font-bold animate-pulse">
          <AlertTriangle className="w-4 h-4" />
          <span>
            Security Alert: {violations}/5 Warnings recorded. Leaving the exam tab will automatically submit your test!
          </span>
        </div>
      )}

      {/* Main Examination Area */}
      <div className="flex-1 flex max-w-7xl mx-auto w-full p-6 gap-6">
        {/* Left Side: Question Display */}
        <div className="flex-1 flex flex-col justify-between bg-slate-800/60 border border-slate-700/60 rounded-3xl p-8 shadow-xl">
          <div>
            <div className="flex items-center justify-between pb-4 border-b border-slate-700/80 mb-6">
              <span className="text-xs font-bold text-indigo-400 uppercase tracking-wider">
                Question {currentIndex + 1} of {session.questions.length}
              </span>
              <div className="flex items-center space-x-2">
                {saving ? (
                  <span className="text-xs text-slate-400">Saving...</span>
                ) : (
                  <span className="text-xs text-emerald-400 flex items-center space-x-1">
                    <Check className="w-3.5 h-3.5" />
                    <span>Saved in cloud</span>
                  </span>
                )}
              </div>
            </div>

            <div className="text-base font-medium text-slate-100 leading-relaxed whitespace-pre-wrap mb-8">
              {currentQ.questionText}
            </div>

            {/* Answer Options */}
            {currentQ.questionType === 'multiple_choice' && currentQ.options && (
              <div className="space-y-3">
                {currentQ.options.map((opt, optIdx) => {
                  const letter = String.fromCharCode(65 + optIdx);
                  const isSelected = answers[currentQ.id] === opt || answers[currentQ.id] === letter;

                  return (
                    <button
                      key={optIdx}
                      type="button"
                      onClick={() => handleSelectOption(currentQ.id, opt)}
                      className={`w-full text-left p-4 rounded-2xl border transition duration-150 flex items-center space-x-4 cursor-pointer ${
                        isSelected
                          ? 'bg-indigo-600/20 border-indigo-500 text-white ring-1 ring-indigo-500 font-semibold'
                          : 'bg-slate-800/80 border-slate-700 text-slate-300 hover:bg-slate-700/60 hover:text-white'
                      }`}
                    >
                      <span
                        className={`w-8 h-8 rounded-xl font-bold text-xs flex items-center justify-center shrink-0 ${
                          isSelected
                            ? 'bg-indigo-600 text-white'
                            : 'bg-slate-700 border border-slate-600 text-slate-300'
                        }`}
                      >
                        {letter}
                      </span>
                      <span className="text-sm">{opt}</span>
                    </button>
                  );
                })}
              </div>
            )}

            {currentQ.questionType === 'true_false' && (
              <div className="grid grid-cols-2 gap-4">
                {['Benar', 'Salah'].map((val) => {
                  const isSelected = answers[currentQ.id] === val;
                  return (
                    <button
                      key={val}
                      type="button"
                      onClick={() => handleSelectOption(currentQ.id, val)}
                      className={`p-6 rounded-2xl border text-center font-bold text-base transition cursor-pointer ${
                        isSelected
                          ? 'bg-indigo-600/20 border-indigo-500 text-white ring-1 ring-indigo-500'
                          : 'bg-slate-800/80 border-slate-700 text-slate-300 hover:bg-slate-700/60'
                      }`}
                    >
                      {val}
                    </button>
                  );
                })}
              </div>
            )}

            {currentQ.questionType === 'essay' && (
              <div>
                <label className="block text-xs font-semibold text-slate-400 mb-2">Write your explanation / answer:</label>
                <textarea
                  rows={6}
                  value={answers[currentQ.id] || ''}
                  onChange={(e) => handleSelectOption(currentQ.id, e.target.value)}
                  placeholder="Type your response here..."
                  className="w-full bg-slate-900 border border-slate-700 text-white rounded-2xl p-4 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 placeholder:text-slate-600"
                />
              </div>
            )}
          </div>

          {/* Bottom Navigation Controls */}
          <div className="flex items-center justify-between pt-6 border-t border-slate-700/80 mt-8">
            <button
              onClick={() => setCurrentIndex((prev) => Math.max(0, prev - 1))}
              disabled={currentIndex === 0}
              className="bg-slate-700 hover:bg-slate-600 disabled:opacity-30 text-white font-medium px-4 py-2.5 rounded-xl text-xs flex items-center space-x-1.5 transition cursor-pointer disabled:cursor-not-allowed"
            >
              <ChevronLeft className="w-4 h-4" />
              <span>Previous</span>
            </button>

            {currentIndex < session.questions.length - 1 ? (
              <button
                onClick={() => setCurrentIndex((prev) => Math.min(session.questions.length - 1, prev + 1))}
                className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-5 py-2.5 rounded-xl text-xs flex items-center space-x-1.5 shadow-md shadow-indigo-600/25 transition cursor-pointer"
              >
                <span>Next Question</span>
                <ChevronRight className="w-4 h-4" />
              </button>
            ) : (
              <button
                onClick={() => setSubmitConfirmOpen(true)}
                className="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-2.5 rounded-xl text-xs flex items-center space-x-2 shadow-md shadow-emerald-600/25 transition cursor-pointer"
              >
                <Send className="w-4 h-4" />
                <span>Finish & Submit Exam</span>
              </button>
            )}
          </div>
        </div>

        {/* Right Side: Number Palette Grid */}
        <div className="w-72 bg-slate-800/60 border border-slate-700/60 rounded-3xl p-6 shadow-xl flex flex-col justify-between">
          <div>
            <h3 className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
              Question Navigator
            </h3>
            <p className="text-xs text-slate-500 mb-4">
              Answered: <strong>{answeredTotal}</strong> / {session.questions.length}
            </p>

            <div className="grid grid-cols-4 gap-2.5 max-h-[420px] overflow-y-auto pr-1">
              {session.questions.map((q, idx) => {
                const isAnswered = answers[q.id] !== undefined && answers[q.id] !== '';
                const isCurrent = idx === currentIndex;

                return (
                  <button
                    key={q.id}
                    onClick={() => setCurrentIndex(idx)}
                    className={`h-10 rounded-xl font-bold text-xs transition cursor-pointer flex items-center justify-center ${
                      isCurrent
                        ? 'ring-2 ring-indigo-400 bg-indigo-600 text-white shadow-md'
                        : isAnswered
                        ? 'bg-emerald-600/30 border border-emerald-500/50 text-emerald-300'
                        : 'bg-slate-700/50 border border-slate-600/60 text-slate-400 hover:bg-slate-700 hover:text-white'
                    }`}
                  >
                    {idx + 1}
                  </button>
                );
              })}
            </div>
          </div>

          <button
            onClick={() => setSubmitConfirmOpen(true)}
            className="w-full bg-slate-700 hover:bg-emerald-600 text-white font-bold py-3 rounded-2xl text-xs transition shadow-xs cursor-pointer mt-6"
          >
            Submit All Responses
          </button>
        </div>
      </div>

      {/* Submit Confirmation Modal */}
      {submitConfirmOpen && (
        <div className="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-slate-800 border border-slate-700 rounded-3xl max-w-md w-full p-6 shadow-2xl text-center space-y-4">
            <div className="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto">
              <CheckCircle2 className="w-6 h-6" />
            </div>
            <h3 className="text-lg font-bold text-white">Confirm Submission</h3>
            <p className="text-xs text-slate-400 leading-relaxed">
              You have answered <strong>{answeredTotal}</strong> out of{' '}
              <strong>{session.questions.length}</strong> questions. Once submitted, your answers cannot be changed.
            </p>

            <div className="flex space-x-3 pt-4 border-t border-slate-700">
              <button
                onClick={() => setSubmitConfirmOpen(false)}
                className="flex-1 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl text-xs font-semibold cursor-pointer"
              >
                Keep Reviewing
              </button>
              <button
                onClick={handleSubmitExam}
                className="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/30 cursor-pointer"
              >
                Yes, Submit Now
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/assignments/AssignmentList.tsx
import React, { useEffect, useState } from 'react';
import api from '../../api/client';
import { useAuthStore } from '../../store/authStore';
import type { Assignment, Subject, Classroom, ApiResponse } from '../../types';
import {
  MessageSquareShare,
  Plus,
  Trash2,
  Clock,
  CheckCircle,
  AlertCircle,
  X,
  FileText,
} from 'lucide-react';
import { Link } from 'react-router-dom';

export const AssignmentList: React.FC = () => {
  const [assignments, setAssignments] = useState<Assignment[]>([]);
  const [subjects, setSubjects] = useState<Subject[]>([]);
  const [classrooms, setClassrooms] = useState<Classroom[]>([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);

  const { user } = useAuthStore();
  const isAdmin = user?.roles.includes('ROLE_ADMIN');
  const isTeacher = user?.roles.includes('ROLE_TEACHER');

  const [form, setForm] = useState({
    title: '',
    subjectId: '',
    classroomId: '',
    description: '',
    instructions: '',
    maxScore: 100,
    dueDate: '',
    allowLateSubmission: false,
  });

  const loadData = async () => {
    try {
      setLoading(true);
      const [aRes, sRes, cRes] = await Promise.all([
        api.get<ApiResponse<Assignment[]>>('/assignments'),
        api.get<ApiResponse<Subject[]>>('/subjects'),
        api.get<ApiResponse<Classroom[]>>('/classrooms'),
      ]);
      setAssignments(aRes.data.data);
      setSubjects(sRes.data.data);
      setClassrooms(cRes.data.data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const payload = {
        title: form.title,
        subjectId: Number(form.subjectId),
        classroomId: Number(form.classroomId),
        description: form.description,
        instructions: form.instructions,
        maxScore: Number(form.maxScore),
        dueDate: form.dueDate,
        allowLateSubmission: form.allowLateSubmission,
        status: 'published',
      };

      await api.post('/assignments', payload);
      setModalOpen(false);
      loadData();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to create assignment');
    }
  };

  const handleDelete = async (id: number) => {
    if (confirm('Delete this assignment?')) {
      await api.delete(`/assignments/${id}`);
      loadData();
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 tracking-tight">Assignments & Homework</h2>
          <p className="text-xs text-slate-500 mt-1">Submit coursework, receive grading feedback, and discuss topics</p>
        </div>

        {(isAdmin || isTeacher) && (
          <button
            onClick={() => setModalOpen(true)}
            className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-4 py-2.5 rounded-xl shadow-xs transition flex items-center space-x-2 text-sm cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Create Assignment</span>
          </button>
        )}
      </div>

      {loading ? (
        <div className="p-8 text-center text-slate-500">Loading assignments...</div>
      ) : assignments.length === 0 ? (
        <div className="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500 shadow-xs">
          <MessageSquareShare className="w-12 h-12 mx-auto text-slate-300 mb-3" />
          <p className="text-base font-medium">No assignments currently active</p>
          <p className="text-xs mt-1 text-slate-400">Instructors can create assignments for enrolled classes here.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {assignments.map((a) => (
            <div
              key={a.id}
              className="bg-white rounded-2xl border border-slate-200 hover:border-indigo-400 p-6 shadow-xs hover:shadow-md transition flex flex-col justify-between"
            >
              <div>
                <div className="flex items-center justify-between mb-3">
                  <span className="text-xs font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-md">
                    {a.subjectName}
                  </span>
                  <span className="text-xs font-medium text-slate-500">Max: {a.maxScore} pts</span>
                </div>

                <h3 className="text-base font-bold text-slate-900 line-clamp-1">{a.title}</h3>
                <p className="text-xs text-slate-500 mt-1 line-clamp-2">{a.description || 'No description provided.'}</p>

                <div className="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                  <span>Class: <strong>{a.classroomName}</strong></span>
                  <div className="flex items-center space-x-1 text-amber-700 font-semibold">
                    <Clock className="w-3.5 h-3.5" />
                    <span>Due: {new Date(a.dueDate).toLocaleDateString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                  </div>
                </div>
              </div>

              <div className="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                <Link
                  to={`/assignments/${a.id}`}
                  className="inline-flex items-center space-x-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg transition"
                >
                  <FileText className="w-3.5 h-3.5" />
                  <span>View Details & Submissions</span>
                </Link>

                <div className="flex items-center space-x-2">
                  {a.hasSubmitted && (
                    <span className="inline-flex items-center text-xs text-emerald-600 font-semibold">
                      <CheckCircle className="w-3.5 h-3.5 mr-1" />
                      Submitted {a.studentScore !== null && a.studentScore !== undefined ? `(${a.studentScore}/${a.maxScore})` : ''}
                    </span>
                  )}

                  {(isAdmin || isTeacher) && (
                    <button
                      onClick={() => handleDelete(a.id)}
                      className="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Create Modal */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl relative">
            <button
              onClick={() => setModalOpen(false)}
              className="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
            <h3 className="text-lg font-bold text-slate-900 mb-4">Create Assignment</h3>

            <form onSubmit={handleSubmit} className="space-y-4 text-sm">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Assignment Title</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Tugas Mandiri 1: Pemrograman Berorientasi Objek"
                  value={form.title}
                  onChange={(e) => setForm({ ...form, title: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Subject</label>
                  <select
                    required
                    value={form.subjectId}
                    onChange={(e) => setForm({ ...form, subjectId: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="">-- Choose Subject --</option>
                    {subjects.map((s) => (
                      <option key={s.id} value={s.id}>
                        {s.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Classroom</label>
                  <select
                    required
                    value={form.classroomId}
                    onChange={(e) => setForm({ ...form, classroomId: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="">-- Choose Classroom --</option>
                    {classrooms.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Due Date & Time</label>
                  <input
                    type="datetime-local"
                    required
                    value={form.dueDate}
                    onChange={(e) => setForm({ ...form, dueDate: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Max Score</label>
                  <input
                    type="number"
                    required
                    min={10}
                    max={100}
                    value={form.maxScore}
                    onChange={(e) => setForm({ ...form, maxScore: Number(e.target.value) })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Instructions</label>
                <textarea
                  rows={3}
                  value={form.instructions}
                  onChange={(e) => setForm({ ...form, instructions: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  placeholder="Task instructions and guidelines..."
                />
              </div>

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer"
                >
                  Publish Assignment
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/assignments/AssignmentDetail.tsx
import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../../api/client';
import { useAuthStore } from '../../store/authStore';
import type {
  Assignment,
  Submission,
  Discussion,
  ApiResponse,
} from '../../types';
import {
  ArrowLeft,
  Clock,
  Send,
  UploadCloud,
  CheckCircle,
  MessageSquare,
  FileCheck,
  Download,
  Award,
} from 'lucide-react';

export const AssignmentDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const { user } = useAuthStore();
  const isAdmin = user?.roles.includes('ROLE_ADMIN');
  const isTeacher = user?.roles.includes('ROLE_TEACHER');

  const [assignment, setAssignment] = useState<Assignment | null>(null);
  const [submissions, setSubmissions] = useState<Submission[]>([]);
  const [discussions, setDiscussions] = useState<Discussion[]>([]);
  const [loading, setLoading] = useState(true);

  // Student submission form
  const [notes, setNotes] = useState('');
  const [file, setFile] = useState<File | null>(null);
  const [submitting, setSubmitting] = useState(false);

  // Teacher grading
  const [gradeModalOpen, setGradeModalOpen] = useState(false);
  const [selectedSub, setSelectedSub] = useState<Submission | null>(null);
  const [gradeScore, setGradeScore] = useState(100);
  const [gradeFeedback, setGradeFeedback] = useState('');

  // Discussion comment
  const [commentText, setCommentText] = useState('');

  const loadData = async () => {
    try {
      setLoading(true);
      const [aRes, dRes] = await Promise.all([
        api.get<ApiResponse<Assignment>>(`/assignments/${id}`),
        api.get<ApiResponse<Discussion[]>>(`/assignments/${id}/discussions`),
      ]);
      setAssignment(aRes.data.data);
      setDiscussions(dRes.data.data);

      if (isAdmin || isTeacher) {
        const sRes = await api.get<ApiResponse<Submission[]>>(`/assignments/${id}/submissions`);
        setSubmissions(sRes.data.data);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (id) loadData();
  }, [id]);

  const handleStudentSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!id) return;

    try {
      setSubmitting(true);
      const formData = new FormData();
      if (notes) formData.append('notes', notes);
      if (file) formData.append('file', file);

      await api.post(`/assignments/${id}/submit`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      alert('Assignment submitted successfully!');
      loadData();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to submit assignment');
    } finally {
      setSubmitting(false);
    }
  };

  const handleGradeSubmission = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedSub) return;

    try {
      await api.post(`/assignments/submissions/${selectedSub.id}/grade`, {
        score: Number(gradeScore),
        feedback: gradeFeedback,
      });

      setGradeModalOpen(false);
      loadData();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to grade submission');
    }
  };

  const handlePostDiscussion = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!commentText.trim()) return;

    try {
      await api.post(`/assignments/${id}/discussions`, {
        message: commentText,
      });
      setCommentText('');
      loadData();
    } catch (err: any) {
      alert('Failed to post comment');
    }
  };

  if (loading && !assignment) {
    return <div className="p-8 text-center text-slate-500">Loading assignment details...</div>;
  }

  if (!assignment) {
    return (
      <div className="p-12 text-center text-slate-500">
        <p>Assignment not found.</p>
        <Link to="/assignments" className="text-indigo-600 font-semibold mt-2 inline-block">
          Back to Assignments
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto space-y-6">
      <Link
        to="/assignments"
        className="inline-flex items-center space-x-2 text-sm text-slate-500 hover:text-slate-800 transition"
      >
        <ArrowLeft className="w-4 h-4" />
        <span>Back to Assignments</span>
      </Link>

      {/* Assignment Header Card */}
      <div className="bg-white rounded-3xl border border-slate-200 p-8 shadow-xs space-y-4">
        <div className="flex items-center justify-between">
          <span className="text-xs font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-md">
            {assignment.subjectName}
          </span>
          <div className="flex items-center space-x-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200">
            <Clock className="w-3.5 h-3.5" />
            <span>Due: {new Date(assignment.dueDate).toLocaleString()}</span>
          </div>
        </div>

        <h2 className="text-2xl font-bold text-slate-900">{assignment.title}</h2>
        <p className="text-sm text-slate-600">{assignment.description}</p>

        {assignment.instructions && (
          <div className="p-5 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-1">
            <strong className="block text-slate-900 font-bold mb-1">Task Instructions:</strong>
            <p className="whitespace-pre-wrap leading-relaxed">{assignment.instructions}</p>
          </div>
        )}
      </div>

      {/* Student: Submit Form */}
      {!isAdmin && !isTeacher && (
        <div className="bg-white rounded-3xl border border-slate-200 p-8 shadow-xs space-y-4">
          <h3 className="text-lg font-bold text-slate-900 flex items-center space-x-2">
            <FileCheck className="w-5 h-5 text-indigo-600" />
            <span>My Submission</span>
          </h3>

          {assignment.hasSubmitted ? (
            <div className="p-6 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-2">
              <div className="flex items-center space-x-2 text-emerald-800 font-bold text-sm">
                <CheckCircle className="w-5 h-5 text-emerald-600" />
                <span>Assignment Submitted Successfully</span>
              </div>
              <p className="text-xs text-emerald-700">
                Turned in on: {new Date(assignment.studentSubmittedAt || '').toLocaleString()}
              </p>
              {assignment.studentScore !== null && assignment.studentScore !== undefined && (
                <div className="mt-3 pt-3 border-t border-emerald-200 flex items-center space-x-2">
                  <Award className="w-5 h-5 text-emerald-700" />
                  <span className="text-sm font-bold text-emerald-900">
                    Grade: {assignment.studentScore} / {assignment.maxScore}
                  </span>
                </div>
              )}
            </div>
          ) : (
            <form onSubmit={handleStudentSubmit} className="space-y-4 text-sm">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Upload Work / Attachment</label>
                <div className="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-indigo-500 transition">
                  <UploadCloud className="w-8 h-8 text-slate-400 mx-auto mb-2" />
                  <input
                    type="file"
                    required
                    onChange={(e) => setFile(e.target.files?.[0] || null)}
                    className="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Submission Notes (Optional)</label>
                <textarea
                  rows={3}
                  value={notes}
                  onChange={(e) => setNotes(e.target.value)}
                  placeholder="Additional explanation or commentary for instructor..."
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <button
                type="submit"
                disabled={submitting || !file}
                className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 px-6 rounded-xl text-xs shadow-md shadow-indigo-600/25 transition cursor-pointer disabled:opacity-50"
              >
                {submitting ? 'Submitting File...' : 'Turn In Assignment'}
              </button>
            </form>
          )}
        </div>
      )}

      {/* Teacher / Admin: Submissions Grading Table */}
      {(isAdmin || isTeacher) && (
        <div className="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
          <div className="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
            <h3 className="text-base font-bold text-slate-900">
              Student Submissions ({submissions.length})
            </h3>
          </div>

          {submissions.length === 0 ? (
            <div className="p-8 text-center text-slate-400 text-sm">No submissions received yet.</div>
          ) : (
            <table className="w-full text-left border-collapse text-sm">
              <thead>
                <tr className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                  <th className="px-6 py-4">Student</th>
                  <th className="px-6 py-4">Submission Date</th>
                  <th className="px-6 py-4">File</th>
                  <th className="px-6 py-4">Score</th>
                  <th className="px-6 py-4 text-right">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {submissions.map((s) => (
                  <tr key={s.id} className="hover:bg-slate-50/80 transition">
                    <td className="px-6 py-4">
                      <div className="font-semibold text-slate-900">{s.studentName}</div>
                      <div className="text-xs font-mono text-slate-400">NIS: {s.studentNis}</div>
                    </td>
                    <td className="px-6 py-4 text-slate-600 text-xs">
                      {new Date(s.submittedAt).toLocaleString()}
                    </td>
                    <td className="px-6 py-4">
                      {s.filePath ? (
                        <a
                          href={`/api/v1/files/${s.filePath}`}
                          target="_blank"
                          rel="noreferrer"
                          className="inline-flex items-center space-x-1 text-xs text-indigo-600 font-semibold hover:underline"
                        >
                          <Download className="w-3.5 h-3.5" />
                          <span>Download</span>
                        </a>
                      ) : (
                        <span className="text-xs text-slate-400">-</span>
                      )}
                    </td>
                    <td className="px-6 py-4">
                      {s.score !== null && s.score !== undefined ? (
                        <span className="font-bold text-emerald-700 text-xs bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                          {s.score} / {assignment.maxScore}
                        </span>
                      ) : (
                        <span className="text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md font-semibold">
                          Needs Grading
                        </span>
                      )}
                    </td>
                    <td className="px-6 py-4 text-right">
                      <button
                        onClick={() => {
                          setSelectedSub(s);
                          setGradeScore(s.score ?? 100);
                          setGradeFeedback(s.feedback || '');
                          setGradeModalOpen(true);
                        }}
                        className="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition cursor-pointer"
                      >
                        Grade Work
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      )}

      {/* Classroom Discussion Board */}
      <div className="bg-white rounded-3xl border border-slate-200 p-8 shadow-xs space-y-6">
        <h3 className="text-lg font-bold text-slate-900 flex items-center space-x-2">
          <MessageSquare className="w-5 h-5 text-indigo-600" />
          <span>Classroom Discussion Board ({discussions.length})</span>
        </h3>

        <form onSubmit={handlePostDiscussion} className="flex gap-3">
          <input
            type="text"
            required
            value={commentText}
            onChange={(e) => setCommentText(e.target.value)}
            placeholder="Ask a question or start a topic discussion..."
            className="flex-1 border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-1 focus:ring-indigo-500 focus:outline-none"
          />
          <button
            type="submit"
            className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-5 py-2.5 rounded-xl text-xs flex items-center space-x-1.5 shadow-xs transition cursor-pointer"
          >
            <Send className="w-3.5 h-3.5" />
            <span>Post</span>
          </button>
        </form>

        <div className="divide-y divide-slate-100 space-y-4">
          {discussions.map((d) => (
            <div key={d.id} className="pt-4 space-y-2">
              <div className="flex items-center space-x-2">
                <span className="font-bold text-sm text-slate-800">{d.userName}</span>
                <span className="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase bg-slate-100 text-slate-600">
                  {d.userRole}
                </span>
                <span className="text-xs text-slate-400">
                  {new Date(d.createdAt).toLocaleString()}
                </span>
              </div>
              <p className="text-xs text-slate-700 leading-relaxed">{d.message}</p>
            </div>
          ))}
        </div>
      </div>

      {/* Grade Submission Modal */}
      {gradeModalOpen && selectedSub && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative space-y-4">
            <h3 className="text-lg font-bold text-slate-900">
              Grade Submission: {selectedSub.studentName}
            </h3>

            <form onSubmit={handleGradeSubmission} className="space-y-4 text-sm">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Score (0 - {assignment.maxScore})</label>
                <input
                  type="number"
                  required
                  min={0}
                  max={assignment.maxScore}
                  value={gradeScore}
                  onChange={(e) => setGradeScore(Number(e.target.value))}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Instructor Feedback</label>
                <textarea
                  rows={3}
                  value={gradeFeedback}
                  onChange={(e) => setGradeFeedback(e.target.value)}
                  placeholder="Suggestions, feedback, and rubric comments..."
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setGradeModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer"
                >
                  Save Grade
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/App.tsx
import React, { useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { useAuthStore } from './store/authStore';
import { Layout } from './components/common/Layout';
import { ProtectedRoute } from './components/common/ProtectedRoute';

import { Login } from './pages/auth/Login';
import { Dashboard } from './pages/dashboard/Dashboard';
import { Classrooms } from './pages/admin/Classrooms';
import { Subjects } from './pages/admin/Subjects';
import { Teachers } from './pages/admin/Teachers';
import { Students } from './pages/admin/Students';
import { MaterialsList } from './pages/materials/MaterialsList';
import { MaterialDetail } from './pages/materials/MaterialDetail';
import { ExamList } from './pages/exams/ExamList';
import { ExamEditor } from './pages/exams/ExamEditor';
import { ExamMonitor } from './pages/exams/ExamMonitor';
import { ExamRunner } from './pages/exams/ExamRunner';
import { AssignmentList } from './pages/assignments/AssignmentList';
import { AssignmentDetail } from './pages/assignments/AssignmentDetail';

export const App: React.FC = () => {
  const { initAuth } = useAuthStore();

  useEffect(() => {
    initAuth();
  }, [initAuth]);

  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />

        {/* Dedicated Standalone CBT Fullscreen Test Room */}
        <Route element={<ProtectedRoute allowedRoles={['ROLE_STUDENT', 'ROLE_ADMIN']} />}>
          <Route path="/exams/:id/runner" element={<ExamRunner />} />
        </Route>

        {/* Main Application Layout with Navigation Shell */}
        <Route element={<ProtectedRoute />}>
          <Route element={<Layout />}>
            <Route path="/dashboard" element={<Dashboard />} />

            {/* Admin Academic Routes */}
            <Route element={<ProtectedRoute allowedRoles={['ROLE_ADMIN']} />}>
              <Route path="/classrooms" element={<Classrooms />} />
              <Route path="/subjects" element={<Subjects />} />
              <Route path="/teachers" element={<Teachers />} />
              <Route path="/students" element={<Students />} />
            </Route>

            {/* Examination & Question Bank */}
            <Route path="/exams" element={<ExamList />} />
            <Route element={<ProtectedRoute allowedRoles={['ROLE_ADMIN', 'ROLE_TEACHER']} />}>
              <Route path="/exams/:id/questions" element={<ExamEditor />} />
              <Route path="/exams/:id/monitor" element={<ExamMonitor />} />
            </Route>

            {/* Learning Materials */}
            <Route path="/materials" element={<MaterialsList />} />
            <Route path="/materials/:id" element={<MaterialDetail />} />

            {/* Assignments & Forums */}
            <Route path="/assignments" element={<AssignmentList />} />
            <Route path="/assignments/:id" element={<AssignmentDetail />} />

            <Route path="/" element={<Navigate to="/dashboard" replace />} />
          </Route>
        </Route>

        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </BrowserRouter>
  );
};

export default App;
EOF`
Ran command: `cd ../java-spring-react-elearning/frontend && npm run build`
Ran command: `sed -i '' 's/create<AuthState>((set, get)/create<AuthState>((set)/g' ../java-spring-react-elearning/frontend/src/store/authStore.ts`
Ran command: `sed -i '' '/AlertCircle,/d' ../java-spring-react-elearning/frontend/src/pages/assignments/AssignmentList.tsx`
Ran command: `sed -i '' '/HelpCircle,/d' ../java-spring-react-elearning/frontend/src/pages/exams/ExamEditor.tsx`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/frontend/src/pages/exams/ExamList.tsx
import React, { useEffect, useState } from 'react';
import api from '../../api/client';
import { useAuthStore } from '../../store/authStore';
import type { Exam, Subject, Classroom, ApiResponse } from '../../types';
import {
  FileCheck2,
  Plus,
  Play,
  MonitorPlay,
  Clock,
  Calendar,
  CheckCircle,
  X,
  FileQuestion,
} from 'lucide-react';
import { Link } from 'react-router-dom';

export const ExamList: React.FC = () => {
  const [exams, setExams] = useState<Exam[]>([]);
  const [subjects, setSubjects] = useState<Subject[]>([]);
  const [classrooms, setClassrooms] = useState<Classroom[]>([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);

  const { user } = useAuthStore();
  const isAdmin = user?.roles.includes('ROLE_ADMIN');
  const isTeacher = user?.roles.includes('ROLE_TEACHER');

  const [form, setForm] = useState({
    title: '',
    subjectId: '',
    classroomId: '',
    type: 'quiz',
    durationMinutes: 60,
    passingScore: 75,
    startAt: '',
    endAt: '',
    shuffleQuestions: true,
    shuffleOptions: true,
    showResult: true,
    allowRetry: false,
    description: '',
  });

  const loadData = async () => {
    try {
      setLoading(true);
      const [eRes, sRes, cRes] = await Promise.all([
        api.get<ApiResponse<Exam[]>>('/exams'),
        api.get<ApiResponse<Subject[]>>('/subjects'),
        api.get<ApiResponse<Classroom[]>>('/classrooms'),
      ]);
      setExams(eRes.data.data);
      setSubjects(sRes.data.data);
      setClassrooms(cRes.data.data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const payload = {
        title: form.title,
        subjectId: Number(form.subjectId),
        classroomId: Number(form.classroomId),
        type: form.type,
        durationMinutes: Number(form.durationMinutes),
        passingScore: Number(form.passingScore),
        startAt: form.startAt,
        endAt: form.endAt,
        shuffleQuestions: form.shuffleQuestions,
        shuffleOptions: form.shuffleOptions,
        showResult: form.showResult,
        allowRetry: form.allowRetry,
        description: form.description,
        status: 'published',
      };

      await api.post('/exams', payload);
      setModalOpen(false);
      loadData();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to create exam');
    }
  };

  const handleToggleStatus = async (id: number, currentStatus: string) => {
    const nextStatus = currentStatus === 'published' ? 'draft' : 'published';
    await api.patch(`/exams/${id}/status?status=${nextStatus}`);
    loadData();
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 tracking-tight">CBT Computer-Based Testing</h2>
          <p className="text-xs text-slate-500 mt-1">High-integrity online examinations with anti-cheat and live telemetry</p>
        </div>

        {(isAdmin || isTeacher) && (
          <button
            onClick={() => setModalOpen(true)}
            className="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-4 py-2.5 rounded-xl shadow-xs transition flex items-center space-x-2 text-sm cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Create Examination</span>
          </button>
        )}
      </div>

      {loading ? (
        <div className="p-8 text-center text-slate-500">Loading examinations...</div>
      ) : exams.length === 0 ? (
        <div className="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500 shadow-xs">
          <FileCheck2 className="w-12 h-12 mx-auto text-slate-300 mb-3" />
          <p className="text-base font-medium">No examinations scheduled</p>
          <p className="text-xs mt-1 text-slate-400">Scheduled tests will appear here.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {exams.map((exam) => (
            <div
              key={exam.id}
              className="bg-white rounded-2xl border border-slate-200 hover:border-indigo-400 p-6 shadow-xs hover:shadow-md transition flex flex-col justify-between"
            >
              <div>
                <div className="flex items-center justify-between mb-3">
                  <span className="text-xs font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-md">
                    {exam.subjectName}
                  </span>
                  {(isAdmin || isTeacher) ? (
                    <button
                      onClick={() => handleToggleStatus(exam.id, exam.status)}
                      title="Click to toggle status"
                      className={`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase cursor-pointer transition ${
                        exam.status === 'published'
                          ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'
                          : 'bg-amber-100 text-amber-700 hover:bg-amber-200'
                      }`}
                    >
                      {exam.status}
                    </button>
                  ) : (
                    <span
                      className={`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase ${
                        exam.status === 'published'
                          ? 'bg-emerald-100 text-emerald-700'
                          : 'bg-amber-100 text-amber-700'
                      }`}
                    >
                      {exam.status}
                    </span>
                  )}
                </div>

                <h3 className="text-base font-bold text-slate-900 line-clamp-1">{exam.title}</h3>
                <p className="text-xs text-slate-500 mt-1 line-clamp-2">
                  Class: <strong>{exam.classroomName}</strong> | Passing: <strong>{exam.passingScore}%</strong>
                </p>

                <div className="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs text-slate-600">
                  <div className="flex items-center space-x-1.5">
                    <Clock className="w-4 h-4 text-slate-400" />
                    <span>{exam.durationMinutes} Minutes</span>
                  </div>
                  <div className="flex items-center space-x-1.5">
                    <FileQuestion className="w-4 h-4 text-slate-400" />
                    <span>{exam.totalQuestions} Questions</span>
                  </div>
                  <div className="col-span-2 flex items-center space-x-1.5 text-[11px] text-slate-400">
                    <Calendar className="w-3.5 h-3.5" />
                    <span>
                      {new Date(exam.startAt).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' })}
                      {' - '}
                      {new Date(exam.endAt).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' })}
                    </span>
                  </div>
                </div>
              </div>

              {/* Action buttons */}
              <div className="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                {isAdmin || isTeacher ? (
                  <>
                    <Link
                      to={`/exams/${exam.id}/questions`}
                      className="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2 px-3 rounded-xl text-xs transition"
                    >
                      Questions ({exam.totalQuestions})
                    </Link>
                    <Link
                      to={`/exams/${exam.id}/monitor`}
                      className="flex-1 text-center bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 px-3 rounded-xl text-xs transition flex items-center justify-center space-x-1"
                    >
                      <MonitorPlay className="w-3.5 h-3.5 mr-1" />
                      <span>Live Monitor</span>
                    </Link>
                  </>
                ) : (
                  <>
                    {exam.studentAttemptStatus === 'completed' ? (
                      <div className="w-full flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200">
                        <div className="flex items-center space-x-1.5 text-xs font-bold text-slate-700">
                          <CheckCircle className="w-4 h-4 text-emerald-600" />
                          <span>Score: {exam.studentScore ?? 0}/100</span>
                        </div>
                        <span
                          className={`text-xs font-bold px-2 py-0.5 rounded-md ${
                            exam.studentPassed ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'
                          }`}
                        >
                          {exam.studentPassed ? 'PASSED' : 'FAILED'}
                        </span>
                      </div>
                    ) : (
                      <Link
                        to={`/exams/${exam.id}/runner`}
                        className="w-full text-center bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center space-x-2"
                      >
                        <Play className="w-4 h-4 fill-white" />
                        <span>{exam.studentAttemptStatus === 'in_progress' ? 'Resume Exam' : 'Enter CBT Exam'}</span>
                      </Link>
                    )}
                  </>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Create Exam Modal */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl relative">
            <button
              onClick={() => setModalOpen(false)}
              className="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
            <h3 className="text-lg font-bold text-slate-900 mb-4">Create CBT Examination</h3>

            <form onSubmit={handleSubmit} className="space-y-4 text-sm">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Exam Title</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Penilaian Akhir Semester Ganjil 2026"
                  value={form.title}
                  onChange={(e) => setForm({ ...form, title: e.target.value })}
                  className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Subject</label>
                  <select
                    required
                    value={form.subjectId}
                    onChange={(e) => setForm({ ...form, subjectId: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="">-- Choose Subject --</option>
                    {subjects.map((s) => (
                      <option key={s.id} value={s.id}>
                        {s.name} ({s.code})
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Target Class</label>
                  <select
                    required
                    value={form.classroomId}
                    onChange={(e) => setForm({ ...form, classroomId: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="">-- Choose Classroom --</option>
                    {classrooms.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Duration (Minutes)</label>
                  <input
                    type="number"
                    required
                    min={5}
                    max={240}
                    value={form.durationMinutes}
                    onChange={(e) => setForm({ ...form, durationMinutes: Number(e.target.value) })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Passing Score (%)</label>
                  <input
                    type="number"
                    required
                    min={0}
                    max={100}
                    value={form.passingScore}
                    onChange={(e) => setForm({ ...form, passingScore: Number(e.target.value) })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">Start Time</label>
                  <input
                    type="datetime-local"
                    required
                    value={form.startAt}
                    onChange={(e) => setForm({ ...form, startAt: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-slate-700 mb-1">End Time</label>
                  <input
                    type="datetime-local"
                    required
                    value={form.endAt}
                    onChange={(e) => setForm({ ...form, endAt: e.target.value })}
                    className="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3 pt-2">
                <label className="flex items-center space-x-2 text-xs font-medium text-slate-700 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={form.shuffleQuestions}
                    onChange={(e) => setForm({ ...form, shuffleQuestions: e.target.checked })}
                    className="rounded text-indigo-600 focus:ring-indigo-500"
                  />
                  <span>Randomize Questions</span>
                </label>
                <label className="flex items-center space-x-2 text-xs font-medium text-slate-700 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={form.shuffleOptions}
                    onChange={(e) => setForm({ ...form, shuffleOptions: e.target.checked })}
                    className="rounded text-indigo-600 focus:ring-indigo-500"
                  />
                  <span>Randomize Options</span>
                </label>
              </div>

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-xs cursor-pointer"
                >
                  Create & Publish Exam
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
EOF`
Ran command: `sed -i '' '/Clock,/d' ../java-spring-react-elearning/frontend/src/pages/exams/ExamMonitor.tsx
sed -i '' '/Award,/d' ../java-spring-react-elearning/frontend/src/pages/exams/ExamMonitor.tsx`
Ran command: `sed -i '' 's/ExamStartResponse, Question, ApiResponse/ExamStartResponse, ApiResponse/g' ../java-spring-react-elearning/frontend/src/pages/exams/ExamRunner.tsx`
Ran command: `sed -i '' '/Video,/d' ../java-spring-react-elearning/frontend/src/pages/materials/MaterialsList.tsx
sed -i '' '/Download,/d' ../java-spring-react-elearning/frontend/src/pages/materials/MaterialsList.tsx`
Ran command: `cd ../java-spring-react-elearning/frontend && npm run build`
Ran command: `cd ../java-spring-react-elearning && git add frontend && git commit -m "feat(frontend): implement React 19 UI with Tailwind CSS v4, role dashboards, admin CRUD, CBT test runner with anti-cheat, and realtime monitor"`
Ran command: `cd ../java-spring-react-elearning && git status`
Ran command: `cd ../java-spring-react-elearning && git log --oneline -n 6`
Ran command: `cat ../java-spring-react-elearning/README.md`
Ran command: `cat << 'EOF' > ../java-spring-react-elearning/README.md
# EduPulse: Enterprise CBT & LMS Platform
### Built with Java 21, Spring Boot 3.4+, React 19, and Tailwind CSS v4

EduPulse is an enterprise-grade Learning Management System (LMS) and Computer-Based Testing (CBT) engine designed from the ground up to replace legacy PHP/Laravel monolithic applications with a cloud-native, high-concurrency micro-architecture.

---

## 🏗️ Technology Stack

| Layer | Technology |
|---|---|
| **Backend Core** | Java 21 LTS, Spring Boot 3.4.3 |
| **Security & Auth** | Spring Security 6, JJWT 0.12.6, Method Security (`@PreAuthorize`), Impersonation Engine |
| **Persistence** | PostgreSQL 16, Spring Data JPA, Hibernate 6, HikariCP, Flyway Migrations |
| **Cache & Realtime** | Redis 7, Spring WebSocket + STOMP Message Broker, SockJS |
| **Batch Processing** | Apache POI (Excel `.xlsx` batch parsing for Students & Question Banks) |
| **API Documentation** | Springdoc OpenAPI 3 / Swagger UI |
| **Frontend Core** | React 19, TypeScript 5+, Vite 6 |
| **Styling** | Tailwind CSS v4 (Native CSS engine with `@tailwindcss/vite`) |
| **State Management** | Zustand 5, Axios with JWT interceptors |
| **Icons & UI** | Lucide React |
| **Deployment** | Docker Compose, Multi-stage Dockerfiles (OpenJDK 21 Alpine & Node 20 Alpine) |

---

## 🛡️ Enterprise Security & Anti-IDOR Architecture

1. **Answer Key Protection (`@JsonIgnore`)**: Question answer keys and explanations are stripped at the JPA entity serialization boundary. Students cannot inspect HTTP responses to harvest answers.
2. **Deterministic Anti-Cheat Proctoring**:
   - Tab-switch and window blur tracking via browser `visibilitychange` events.
   - Right-click context menu prevention.
   - Server-side violation counter with auto-termination when threshold (5) is exceeded.
3. **Server-Enforced Timer Integrity**:
   - Remaining time is strictly computed on the server (`min((startedAt + duration), endAt) - now()`).
   - Late answer saves are automatically rejected with immediate session finalization.
4. **Method-Level Object Ownership Checks**:
   - Teachers are strictly constrained to their assigned classrooms, subjects, examinations, and materials.
   - Cross-tenant and cross-user parameter tampering (IDOR) is blocked with standard 403 Forbidden exceptions.
5. **Secure Admin Impersonation**:
   - `POST /api/v1/admin/impersonate/{userId}` generates a scoped impersonation JWT carrying the admin's original identity claim.
   - Exiting impersonation restores the administrator's original session instantly.

---

## 🚀 Quick Start with Docker

To run the entire system (PostgreSQL, Redis, MinIO, Spring Boot Backend, and React Frontend) in containers:

```bash
docker compose up --build -d
```

- **Frontend App**: [http://localhost](http://localhost) (or [http://localhost:3000](http://localhost:3000) in dev mode)
- **Backend API**: [http://localhost:8080/api/v1](http://localhost:8080/api/v1)
- **Swagger Documentation**: [http://localhost:8080/swagger-ui.html](http://localhost:8080/swagger-ui.html)
- **MinIO Object Console**: [http://localhost:9001](http://localhost:9001)

---

## 💻 Local Development Setup

### 1. Start Database & Redis
```bash
docker compose up -d postgres redis minio
```

### 2. Start Backend
```bash
cd backend
./mvnw spring-boot:run
```

### 3. Start Frontend
```bash
cd frontend
npm install
npm run dev
```

---

## 🔑 Default Credentials

The database migration auto-seeds the initial administrator, and the demo interface includes fast 1-click credential presets:

| Role | Email | Password | Permissions |
|---|---|---|---|
| **Admin** | `admin@elearning.com` | `Admin@123` | Full system control, Classrooms, Subjects, Teachers, Students, Excel Import, Impersonation |
| **Teacher** | `teacher@elearning.com` | `Teacher@123` | Exam Creator, Question Bank, Real-time CBT Monitor, Assignment Grading, Materials |
| **Student** | `student@elearning.com` | `Student@123` | CBT Fullscreen Exam Runner, Course Materials, Assignment Submissions, Discussion Board |

---

## 📁 Repository Layout

```
java-spring-react-elearning/
├── docker-compose.yml              # Complete container orchestration
├── README.md                       # Comprehensive architectural guide
├── backend/                        # Spring Boot 3.4+ Application
│   ├── Dockerfile                  # Multi-stage Java 21 container build
│   ├── pom.xml                     # Maven dependencies
│   └── src/main/
│       ├── java/com/elearning/
│       │   ├── common/             # BaseEntity, Enums, ApiResponse, FileStorage, Exceptions
│       │   ├── config/             # SecurityConfig, WebSocketConfig
│       │   ├── security/           # JwtProvider, JwtFilter, CustomUserDetailsService
│       │   └── modules/
│       │       ├── academic/       # Classrooms & Subjects
│       │       ├── assignment/     # Assignments, Submissions, Discussions
│       │       ├── auth/           # Login, Impersonate, Profile
│       │       ├── exam/           # CBT Engine, Questions, ExamRunner, Live Monitor, STOMP
│       │       ├── material/       # Learning Materials, Views Tracking
│       │       └── user/           # Teachers, Students (Excel POI import, Exam Cards)
│       └── resources/
│           ├── application.yml     # Config properties
│           └── db/migration/       # Flyway schema V1__initial_schema.sql
└── frontend/                       # React 19 + TypeScript + Vite 6 Application
    ├── Dockerfile                  # Production Nginx container build
    ├── nginx.conf                  # Reverse proxy for /api/ and /ws/
    └── src/
        ├── api/client.ts           # Axios client with JWT interceptors
        ├── store/authStore.ts      # Zustand auth & impersonation state
        ├── types/index.ts          # TypeScript domain models
        ├── components/common/      # Navbar, Sidebar, Layout, ProtectedRoute
        └── pages/
            ├── auth/Login.tsx
            ├── dashboard/Dashboard.tsx
            ├── admin/              # Classrooms, Subjects, Teachers, Students (with Excel import & Exam Cards)
            ├── materials/          # MaterialsList, MaterialDetail reader
            ├── exams/              # ExamList, ExamEditor, ExamMonitor, ExamRunner
            └── assignments/        # AssignmentList, AssignmentDetail
```
EOF`
Ran command: `cd ../java-spring-react-elearning && git add README.md && git commit -m "docs: add enterprise architecture guide, setup instructions, and security specs"`
Ran command: `cd ../java-spring-react-elearning && git log --oneline`
Ran command: `git status`
Ran command: `rm -f starter.zip`

Proyek monorepo **`java-spring-react-elearning`** telah selesai dibuat dan dikonfigurasi secara lengkap dengan arsitektur enterprise-grade berbasis **Java 21 + Spring Boot 3.4+** di backend dan **React 19 + TypeScript + Vite + Tailwind CSS v4** di frontend.

Lokasi repositori baru:
📁 `../java-spring-react-elearning` (relatif terhadap repositori ini) atau `/Users/user/Documents/Works/Repos/java-spring-react-elearning`.

---

### 📦 Ringkasan Commit Git di `java-spring-react-elearning`

1. **`52e7a4f`** — `chore: initial scaffold of java-spring-react-elearning monorepo with Spring Boot 3.4+ and React 19`
2. **`fd6d0f8`** — `feat(backend): implement spring security 6, jwt, flyway schema, and core domain entities`
3. **`d52dec3`** — `feat(backend): implement academic, teacher, student with excel import, learning materials with secure file storage`
4. **`efef4dc`** — `feat(backend): implement CBT exam engine, question bank, auto-grading, anti-cheat, assignments, and websocket stomp`
5. **`f92da70`** — `feat(frontend): implement React 19 UI with Tailwind CSS v4, role dashboards, admin CRUD, CBT test runner with anti-cheat, and realtime monitor`
6. **`5e43aa7`** — `docs: add enterprise architecture guide, setup instructions, and security specs`

---

### 🏛️ Komponen & Fitur yang Diimplementasikan

#### 1. Backend (Spring Boot 3.4 + Java 21)
- **Spring Security 6 & JJWT 0.12.6**:
  - Stateless JWT token flow dengan refresh token & impersonasi admin.
  - Method security (`@PreAuthorize`) di setiap endpoint untuk pencegahan IDOR (object ownership validation).
- **Flyway Database Migration (`V1__initial_schema.sql`)**:
  - 22 tabel relasional PostgreSQL (Users, Roles, Classrooms, Subjects, Teachers, Students, Exams, Questions, Exam Attempts, Exam Answers, Materials, Material Views, Assignments, Submissions, Discussions).
  - Mengadopsi indeks performa tinggi pada foreign keys dan kolom pencarian.
- **Apache POI Batch Excel Engine**:
  - Batch import siswa dari spreadsheet Excel (`.xlsx`/`.xls`) langsung ke kelas terkait.
  - Batch import bank soal ujian (pilihan ganda, benar/salah, esai) dari Excel.
- **CBT Anti-Cheat & Auto-Grading Engine**:
  - Validasi timer server-side ketat (`now() <= deadline`).
  - Proteksi kunci jawaban (`@JsonIgnore` pada entity `Question` agar kunci jawaban tidak pernah bocor ke inspeksi network browser siswa).
  - Pencatatan insiden kecurangan (perpindahan tab/window minimasi) dengan penalti auto-submit jika mencapai batas 5 pelanggaran.
  - Algoritma auto-grading instan untuk pilihan ganda & true/false, serta antarmuka penilaian esai manual bagi guru.
- **WebSocket STOMP Broker**:
  - Endpoint `/ws` dan topic `/topic/exams/{id}/monitor` untuk broadcast telemetri live pengerjaan ujian siswa secara realtime.

#### 2. Frontend (React 19 + TypeScript + Vite + Tailwind CSS v4)
- **Role-Based Routing & Impersonasi**:
  - Navigasi dinamis untuk Admin, Guru, dan Siswa.
  - Banner peringatan aktif saat sesi impersonasi berlangsung dengan tombol kembali ke akun Admin.
  - Fast Demo Login di halaman login untuk 1-click test credential Admin, Guru, dan Siswa.
- **Admin Management Portal**:
  - Manajemen Kelas & Homeroom Teacher.
  - Manajemen Mata Pelajaran (SKS, kode mapel, guru pengampu).
  - Manajemen Guru (NIP, akun login).
  - Manajemen Siswa (filter kelas, modal import Excel, dan modal preview cetak Kartu Peserta Ujian ber-barcode).
- **Modul Materi Belajar**:
  - Upload materi & lampiran (PDF/dokumen).
  - Reader materi dengan auto-tracking view progress siswa.
  - Telemetri aktivitas baca siswa untuk guru.
- **CBT Exam Taking Room (Siswa)**:
  - Antarmuka ujian mandiri layar penuh (*fullscreen mode*).
  - Deteksi perpindahan tab (*visibilitychange event listener*).
  - Live countdown timer sinkron dengan server.
  - Palet navigasi nomor soal interaktif (sudah dijawab, belum dijawab, aktif).
  - Autosave otomatis ke cloud setiap kali memilih opsi.
- **CBT Real-Time Monitoring Room (Guru/Admin)**:
  - Telemetri realtime via STOMP WebSocket: status siswa aktif, progress soal dijawab, skor, dan jumlah pelanggaran kecurangan.
- **Modul Tugas & Forum Diskusi**:
  - Penyerahan tugas file & catatan oleh siswa.
  - Penilaian & feedback guru.
  - Thread forum diskusi kelas per tugas.

---

### 🚀 Cara Menjalankan Aplikasi

#### Opsi 1: Menggunakan Docker Compose (Paling Mudah)
Jalankan seluruh stack (PostgreSQL, Redis, MinIO, Spring Boot Backend, dan Nginx Frontend) dengan satu perintah:

```bash
cd ../java-spring-react-elearning
docker compose up --build -d
```

- **Frontend Web UI**: [http://localhost](http://localhost) (atau port `80`)
- **Backend API**: [http://localhost:8080/api/v1](http://localhost:8080/api/v1)
- **OpenAPI Swagger UI**: [http://localhost:8080/swagger-ui.html](http://localhost:8080/swagger-ui.html)
- **MinIO S3 Console**: [http://localhost:9001](http://localhost:9001)

#### Opsi 2: Local Development
1. **Jalankan Database & Cache**:
   ```bash
   cd ../java-spring-react-elearning
   docker compose up -d postgres redis minio
   ```
2. **Jalankan Backend**:
   ```bash
   cd backend
   ./mvnw spring-boot:run
   ```
3. **Jalankan Frontend**:
   ```bash
   cd frontend
   npm run dev
   ```
   Akses frontend di [http://localhost:3000](http://localhost:3000).

---

### 🔑 Akun Default untuk Pengujian

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@elearning.com` | `Admin@123` |
| **Guru (Teacher)** | `teacher@elearning.com` | `Teacher@123` |
| **Siswa (Student)** | `student@elearning.com` | `Student@123` |