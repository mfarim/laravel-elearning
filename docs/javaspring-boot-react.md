# Implementation Plan: `java-spring-react-elearning`

Enterprise-grade Implementation Plan untuk membangun kembali Learning Management System & CBT (*Computer Based Testing*) dari arsitektur Laravel Livewire menjadi arsitektur modern terpisah: **Java 21 + Spring Boot 3.4+ (Backend)** dan **React 19 + TypeScript + Vite + Tailwind CSS (Frontend)**.

---

## 1. System Architecture Overview

```mermaid
graph TD
    subgraph Client ["Client Layer (React 19 SPA)"]
        UI_Admin["Admin Portal (Lucide + TanStack Table)"]
        UI_Teacher["Teacher Portal (Exam Monitor + Question Bank)"]
        UI_Student["Student Portal (CBT Engine + Fullscreen Lock)"]
    end

    subgraph Gateway_Security ["Security & API Gateway"]
        SecurityFilter["Spring Security 6 + JWT Filter"]
        RateLimiter["Rate Limiting Filter (Bucket4j)"]
        IpBlockFilter["IP & Account Blocking Filter"]
    end

    subgraph Backend ["Spring Boot 3.4+ Backend"]
        REST_API["REST Controllers (OpenAPI / Swagger)"]
        WS_Broker["WebSocket STOMP Broker (SockJS)"]
        Service_Layer["Domain Services & Business Logic"]
        Security_Expressions["Method Security (@PreAuthorize + Custom Evaluators)"]
        Async_Tasks["Spring Task Executor (Auto-grading & Reports)"]
    end

    subgraph Data_Storage ["Persistence & Storage"]
        PostgreSQL[("PostgreSQL 16 (Primary DB)")]
        Redis[("Redis 7 (Session, Cache, Exam Timers, WS Pub/Sub)")]
        FileStorage["MinIO / Local Private Storage (Encrypted Streaming)"]
    end

    Client <-->|HTTPS / REST API| Gateway_Security
    Client <-->|WSS / STOMP| WS_Broker
    Gateway_Security --> REST_API
    REST_API --> Service_Layer
    WS_Broker --> Service_Layer
    Service_Layer --> Security_Expressions
    Service_Layer --> Async_Tasks
    Service_Layer --> PostgreSQL
    Service_Layer --> Redis
    Service_Layer --> FileStorage
```

---

## 2. Tech Stack Selection & Justification

| Layer | Technology | Justification |
| :--- | :--- | :--- |
| **Language & Runtime** | Java 21 LTS | Virtual Threads (Project Loom) untuk konkurensi tinggi pengerjaan CBT bersamaan |
| **Backend Framework** | Spring Boot 3.4+ | Framework enterprise standar dengan ekosistem terlengkap dan performa tinggi |
| **Security** | Spring Security 6 + JJWT | Stateless JWT + Refresh Token Rotation, Method Security (`@PreAuthorize`) |
| **Database** | PostgreSQL 16 | Relational integrity ketat, JSONB support untuk format opsi soal CBT |
| **Caching & Realtime** | Redis 7 + Redisson | Distributed locks, CBT countdown timers, dan WebSocket message relay |
| **Realtime Messaging**| Spring WebSocket + STOMP | Realtime CBT monitor, violation tracking, dan live forum diskusi |
| **Object Storage** | MinIO / S3 API | Isolasi berkas submission siswa (non-public disk dengan presigned/streamed access) |
| **Frontend Framework**| React 19 + TypeScript | Type-safe, reaktif, modular component system |
| **Build Tool** | Vite 6 | Fast HMR, optimized production build splitting |
| **UI Design System** | Tailwind CSS v4 + Shadcn UI | Design modern, responsif, dark mode, dan glassmorphism premium |
| **State & Data Fetch**| TanStack Query v5 + Zustand | Server state caching cerdas + lightweight client state management |
| **Forms & Validation**| React Hook Form + Zod | Schema-driven client-side validation sinkron dengan backend DTO |

---

## 3. Database Schema & ERD Architecture

```mermaid
erDiagram
    USERS ||--o{ TEACHERS : has
    USERS ||--o{ STUDENTS : has
    USERS ||--o{ USER_ROLES : has
    CLASSROOMS ||--o{ STUDENTS : contains
    CLASSROOMS ||--o{ EXAMINATIONS : assigned
    CLASSROOMS ||--o{ ASSIGNMENTS : assigned
    CLASSROOMS ||--o{ LEARNING_MATERIALS : assigned
    SUBJECTS ||--o{ EXAMINATIONS : categorized
    SUBJECTS ||--o{ ASSIGNMENTS : categorized
    SUBJECTS ||--o{ LEARNING_MATERIALS : categorized
    TEACHERS ||--o{ EXAMINATIONS : creates
    TEACHERS ||--o{ ASSIGNMENTS : creates
    TEACHERS ||--o{ LEARNING_MATERIALS : uploads

    EXAMINATIONS ||--o{ QUESTIONS : contains
    EXAMINATIONS ||--o{ EXAM_ATTEMPTS : tracks
    STUDENTS ||--o{ EXAM_ATTEMPTS : undertakes
    EXAM_ATTEMPTS ||--o{ EXAM_ANSWERS : records
    QUESTIONS ||--o{ EXAM_ANSWERS : answers

    ASSIGNMENTS ||--o{ ASSIGNMENT_SUBMISSIONS : receives
    STUDENTS ||--o{ ASSIGNMENT_SUBMISSIONS : submits
    ASSIGNMENTS ||--o{ ASSIGNMENT_DISCUSSIONS : hosts
    USERS ||--o{ ASSIGNMENT_DISCUSSIONS : participates
```

### Key Schema Entities:
1. **`users` & `roles`**: RBAC (`ROLE_ADMIN`, `ROLE_TEACHER`, `ROLE_STUDENT`). Mendukung status aktif dan audit log.
2. **`teachers` & `students`**: Profil terhubung ke `user_id`, NIP/NIS unik, relasi kelas (`classroom_id`).
3. **`examinations`**: Model ujian (CBT) dengan parameter waktu (`start_at`, `end_at`, `duration_minutes`), passing score, flag acak (`shuffle_questions`, `shuffle_options`), retry setting.
4. **`questions`**: Tipe soal (`MULTIPLE_CHOICE`, `ESSAY`, `SHORT_ANSWER`), opsi dalam JSONB, bobot poin, kunci jawaban (`correct_answer` hanya bisa diakses guru).
5. **`exam_attempts` & `exam_answers`**: Status attempt (`IN_PROGRESS`, `NEEDS_GRADING`, `COMPLETED`, `FORCE_FINISHED`), pelanggaran (*violations counter*), skor total, feedback essay.
6. **`assignments` & `submissions`**: Pengumpulan berkas privat, batas waktu (*late submission tolerance*), grading, feedback.
7. **`learning_materials` & `material_views`**: Berkas modul (PDF/Video/Link), status published, tracking waktu baca siswa.
8. **`blocked_ips` & `blocked_users`**: Tabel keamanan untuk mitigasi serangan brute-force dan penangguhan akses.

---

## 4. API & Security Architecture (Defense-in-Depth)

### A. Stateless Authentication & Impersonation
- **JWT Architecture**: Access Token (15 menit) + Refresh Token (7 hari di HttpOnly Cookie dengan rotation).
- **Secure Impersonation**: Endpoint `POST /api/v1/admin/impersonate/{userId}` menerbitkan token sementara (*impersonation scope*) dengan klaim `impersonator_admin_id`. Endpoint `POST /api/v1/admin/stop-impersonate` mengembalikan konteks admin. Mencegah eskalasi hak akses antar-admin.

### B. Otorisasi Objek (Pencegahan IDOR)
Setiap mutasi dan pengambilan objek dilindungi pada layer Service menggunakan Spring Security SpEL Custom Expression:
```java
// Contoh Otorisasi Akses Ujian
@PreAuthorize("hasRole('ADMIN') or @examSecurity.isExamOwner(#examId, authentication)")
public ExamDetailResponse getExamDetail(Long examId) { ... }

// Contoh Otorisasi Pengerjaan Ujian Siswa
@PreAuthorize("hasRole('STUDENT') and @examSecurity.canStudentTakeExam(#examId, authentication)")
public ExamStartResponse startExam(Long examId) { ... }
```

### C. Proteksi Integritas CBT Engine
1. **Server-Side Deadline Enforcement**: Timer di browser siswa hanya berupa representasi visual. Backend menghitung deadline aktual `min(started_at + duration, examination.end_at)` dan menolak jawaban yang dikirim lewat waktu toleransi (+5 detik network latency buffer).
2. **Question Ownership Isolation**: Endpoint `POST /api/v1/student/exams/{examId}/answers` memverifikasi secara langsung bahwa `questionId` memang terikat pada `examId` dan `attempt` berstatus `IN_PROGRESS`.
3. **Hidden Answer Keys**: DTO `StudentQuestionResponse` mengecualikan properti `correctAnswer` dan `explanation`. Guru menggunakan `TeacherQuestionResponse`.
4. **Violation Counter**: Event fullscreen exit dan blur/tab switch dikirim melalui throttling controller dan WebSocket untuk monitoring guru secara realtime.

---

## 5. Project Repository Structure

```text
java-spring-react-elearning/
├── backend/                             # Spring Boot 3.4+ Application
│   ├── src/main/java/com/elearning/
│   │   ├── common/                      # Exceptions, Base Entity, Utils, Constants
│   │   ├── config/                      # SecurityConfig, WebSocketConfig, RedisConfig
│   │   ├── security/                    # JwtProvider, CustomUserDetailsService, Evaluators
│   │   └── modules/
│   │       ├── auth/                    # AuthController, DTOs, AuthService
│   │       ├── academic/                # Classroom & Subject Management
│   │       ├── material/                # Learning Materials & View Tracking
│   │       ├── assignment/              # Assignment, Submissions, Discussions
│   │       ├── exam/                    # CBT Engine, Question Bank, Grading
│   │       ├── monitoring/              # Realtime WebSocket STOMP Handlers
│   │       └── admin/                   # User Management, Impersonate, Excel Import
│   ├── src/main/resources/
│   │   ├── db/migration/                # Flyway SQL Migration Scripts (V1__init.sql, etc.)
│   │   └── application.yml              # Profiles: dev, prod, test
│   └── pom.xml                          # Maven Dependencies
│
├── frontend/                            # React 19 + Vite Application
│   ├── src/
│   │   ├── assets/                      # Icons, Static Images
│   │   ├── components/
│   │   │   ├── ui/                      # Shadcn UI Base Components (Button, Dialog, etc.)
│   │   │   └── common/                  # Navbar, Sidebar, ProtectedRoute, PageHeader
│   │   ├── hooks/                       # useAuth, useCbtTimer, useWebSocket, useViolation
│   │   ├── layouts/                     # AdminLayout, TeacherLayout, StudentLayout, ExamLayout
│   │   ├── pages/
│   │   │   ├── auth/                    # Login, Register, ForgotPassword
│   │   │   ├── admin/                   # Teachers, Students, Classrooms, Subjects
│   │   │   ├── teacher/                 # Dashboard, Materials, Assignments, CBT Monitor, Grading
│   │   │   └── student/                 # Dashboard, Materials, Assignments, CBT Exam Runner
│   │   ├── services/                    # Axios API Client & Endpoints
│   │   ├── stores/                      # Zustand Stores (AuthStore, ExamStore)
│   │   └── types/                       # TypeScript Interfaces & API Types
│   ├── index.html
│   ├── package.json
│   ├── vite.config.ts
│   └── tailwind.config.ts
│
├── docker-compose.yml                   # PostgreSQL, Redis, MinIO, Backend, Frontend
└── README.md
```

---

## 6. Phased Implementation Roadmap

```mermaid
gantt
    title Roadmap Implementasi java-spring-react-elearning
    dateFormat  YYYY-MM-DD
    section Phase 1: Foundation & Auth
    Project Setup & Docker Compose           :p1_1, 2026-10-05, 3d
    DB Flyway Migrations & Entities          :p1_2, after p1_1, 3d
    Spring Security, JWT & Impersonate       :p1_3, after p1_2, 4d
    React Auth & Layout System               :p1_4, after p1_3, 4d

    section Phase 2: Academic & Material
    Classroom & Subject CRUD                 :p2_1, after p1_4, 3d
    Student/Teacher Management & Excel Import:p2_2, after p2_1, 4d
    Learning Material & Secure File Upload   :p2_3, after p2_2, 4d

    section Phase 3: Assignment & Forum
    Assignment Management & Due Dates        :p3_1, after p2_3, 3d
    Student Submission & Private Storage     :p3_2, after p3_1, 3d
    Realtime Discussion Forum (WebSocket)    :p3_3, after p3_2, 4d

    section Phase 4: CBT Engine (Core)
    Question Bank & Exam Configuration       :p4_1, after p3_3, 4d
    React Exam Runner UI (Fullscreen & Timer):p4_2, after p4_1, 5d
    Auto-save, Anti-tamper & Auto-grading    :p4_3, after p4_2, 4d

    section Phase 5: Grading & Monitoring
    Teacher Essay Grading Interface          :p5_1, after p4_3, 3d
    Live CBT Monitor (STOMP WebSocket)       :p5_2, after p5_1, 4d
    PDF Generation (Exam Cards & Attendance) :p5_3, after p5_2, 3d

    section Phase 6: Security Hardening
    Audit IDOR & Method Security             :p6_1, after p5_3, 3d
    Rate Limiting, IP/User Block Filter      :p6_2, after p6_1, 2d
    Integration & E2E Testing                :p6_3, after p6_2, 4d

    section Phase 7: Deployment
    CI/CD Pipelines & Production Build       :p7_1, after p6_3, 3d
```

---

## 7. Migration Mapping: Laravel to Spring Boot & React

| Feature | Implementasi di Laravel Elearning | Implementasi Baru di `java-spring-react-elearning` |
| :--- | :--- | :--- |
| **Routing & Auth** | Laravel Session + Breeze + Spatie Roles | Spring Security 6 Stateless JWT + Refresh Token Rotation + React Protected Routes |
| **Component Logic** | Livewire 3 Components (`wire:click`, Livewire state) | Spring Boot REST API Controller + React 19 Client UI with TanStack Query |
| **Realtime Updates** | Laravel Reverb / Pusher + Laravel Echo | Spring WebSocket STOMP + SockJS + `@stomp/stompjs` client |
| **Authorization** | Laravel Policies (`$this->authorize(...)`) | Spring `@PreAuthorize("@policyEvaluator.canAccess(...)")` |
| **Exam Engine UI** | Livewire Blade + Alpine.js `examEngine` | Dedicated React Exam Runner with Zustand, Web Audio, and Fullscreen API |
| **File Submissions** | Local/Public disk symlink | MinIO / S3 Private Bucket with Authorized Streaming / Pre-signed URLs |
| **Bulk Import** | Maatwebsite Excel | Apache POI (Streaming reader `StreamingReader` untuk memory-efficient parsing) |
| **PDF Reporting** | Blade View + Print CSS | OpenHTMLtoPDF / JasperReports REST Endpoint |

---

## 8. Verification & Acceptance Criteria

1. **Security**: Zero IDOR vulnerabilities; automated integration tests verify cross-teacher, cross-student, and cross-classroom access denials (403 Forbidden).
2. **CBT Engine Accuracy**: Otomatisasi penilaian pilihan ganda akurat 100%; batas waktu ujian ditegakkan di sisi server; jawaban siswa tersimpan persisten secara berkala (*heartbeat auto-save*).
3. **Realtime Stability**: WebSocket monitor mampu menangani hingga 1.000 siswa aktif secara bersamaan tanpa degradasi performa pada broker STOMP.
4. **User Experience**: Tampilan antarmuka berstandar premium (*glassmorphism*, dark/light mode, micro-interactions, responsive mobile layout).
