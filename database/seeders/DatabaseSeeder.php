<?php

namespace Database\Seeders;

use App\Models\AcademicRiskScore;
use App\Models\AiRecommendation;
use App\Models\Assignment;
use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Notification;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudyPlan;
use App\Models\StudyTask;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect([
            ['name' => 'Administrator', 'slug' => 'admin'],
            ['name' => 'Teacher', 'slug' => 'teacher'],
            ['name' => 'Student', 'slug' => 'student'],
        ])->mapWithKeys(fn ($role) => [$role['slug'] => Role::create($role)]);

        $department = Department::create([
            'name' => 'Computer Science',
            'code' => 'CS',
            'description' => 'Software engineering, data, networks, databases and AI.',
        ]);

        $semester = Semester::create([
            'name' => 'Fall',
            'year' => 2026,
            'starts_on' => now()->startOfMonth(),
            'ends_on' => now()->addMonths(4),
        ]);

        $subjects = collect([
            ['code' => 'CS-301', 'name' => 'Database Management Systems'],
            ['code' => 'CS-355', 'name' => 'Artificial Intelligence'],
            ['code' => 'CS-330', 'name' => 'Web Engineering'],
            ['code' => 'CS-320', 'name' => 'Software Engineering'],
        ])->mapWithKeys(fn ($subject) => [
            $subject['code'] => Subject::create($subject + [
                'department_id' => $department->id,
                'credit_hours' => 3,
            ]),
        ]);

        $admin = User::create([
            'role_id' => $roles['admin']->id,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '+92 300 0000001',
            'password' => Hash::make('password'),
        ]);

        $teacherUsers = collect([
            ['name' => 'Dr. Ayesha Khan', 'email' => 'teacher@example.com', 'specialization' => 'Databases and Web Engineering'],
            ['name' => 'Prof. Bilal Ahmed', 'email' => 'bilal.teacher@example.com', 'specialization' => 'Artificial Intelligence'],
        ])->map(function ($data) use ($roles, $department) {
            $user = User::create([
                'role_id' => $roles['teacher']->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => '+92 300 '.fake()->numerify('######'),
                'password' => Hash::make('password'),
            ]);

            return Teacher::create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'designation' => 'Lecturer',
                'specialization' => $data['specialization'],
                'office_hours' => 'Mon-Wed 2:00 PM - 4:00 PM',
            ]);
        });

        $courses = collect([
            ['subject' => 'CS-301', 'teacher' => 0, 'code' => 'DBMS-F26-A', 'title' => 'DBMS - Section A'],
            ['subject' => 'CS-355', 'teacher' => 1, 'code' => 'AI-F26-A', 'title' => 'Artificial Intelligence - Section A'],
            ['subject' => 'CS-330', 'teacher' => 0, 'code' => 'WEB-F26-A', 'title' => 'Web Engineering - Section A'],
            ['subject' => 'CS-320', 'teacher' => 1, 'code' => 'SE-F26-A', 'title' => 'Software Engineering - Section A'],
        ])->map(function ($course) use ($subjects, $teacherUsers, $department, $semester) {
            return Course::create([
                'subject_id' => $subjects[$course['subject']]->id,
                'teacher_id' => $teacherUsers[$course['teacher']]->id,
                'department_id' => $department->id,
                'semester_id' => $semester->id,
                'code' => $course['code'],
                'title' => $course['title'],
                'description' => 'Demo course for the FYP academic assistance workflow.',
            ]);
        });

        collect([
            ['name' => 'Rida Fatima', 'email' => 'student@example.com', 'roll' => 'BSCS-21-001', 'gpa' => 3.42, 'risk' => 28],
            ['name' => 'Hamza Ali', 'email' => 'hamza.student@example.com', 'roll' => 'BSCS-21-018', 'gpa' => 2.64, 'risk' => 57],
            ['name' => 'Sara Malik', 'email' => 'sara.student@example.com', 'roll' => 'BSCS-21-031', 'gpa' => 2.11, 'risk' => 78],
        ])->each(function ($data) use ($roles, $department, $semester, $courses, $subjects) {
            $user = User::create([
                'role_id' => $roles['student']->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => '+92 301 '.fake()->numerify('######'),
                'password' => Hash::make('password'),
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'semester_id' => $semester->id,
                'roll_no' => $data['roll'],
                'program' => 'BS Computer Science',
                'batch' => '2021-2025',
                'gpa' => $data['gpa'],
                'academic_goal' => 'Improve semester performance and prepare for final exams.',
            ]);

            foreach ($courses as $course) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'status' => 'active',
                ]);
            }

            $this->seedStudentAcademics($student, $courses, $subjects, $data['risk']);
        });

        Notification::create([
            'user_id' => $admin->id,
            'title' => 'Demo data ready',
            'body' => 'The FYP demo accounts, courses, marks and AI records have been seeded.',
            'type' => 'system',
        ]);
    }

    private function seedStudentAcademics(Student $student, $courses, $subjects, int $risk): void
    {
        foreach ($courses as $course) {
            $subject = $course->subject;
            for ($i = 1; $i <= 12; $i++) {
                $absentThreshold = $risk > 70 ? 4 : ($risk > 45 ? 3 : 2);
                AttendanceRecord::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'course_id' => $course->id,
                    'teacher_id' => $course->teacher_id,
                    'class_date' => now()->subDays(35 - ($i * 2)),
                    'status' => $i % $absentThreshold === 0 ? 'absent' : 'present',
                ]);
            }

            $base = max(42, 92 - $risk + random_int(-6, 8));
            foreach (['assignment', 'quiz', 'midterm'] as $index => $type) {
                Mark::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'course_id' => $course->id,
                    'recorded_by' => $course->teacher?->user_id,
                    'type' => $type,
                    'title' => ucfirst($type).' '.($index + 1),
                    'obtained_marks' => min(95, max(35, $base + random_int(-8, 9))),
                    'total_marks' => 100,
                    'recorded_on' => now()->subDays(20 - ($index * 5)),
                ]);
            }
        }

        foreach ($subjects as $subject) {
            Assignment::create([
                'subject_id' => $subject->id,
                'student_id' => $student->id,
                'title' => $subject->name.' Revision Task',
                'description' => 'Prepare summary notes and solve end-of-chapter questions.',
                'due_date' => now()->addDays(random_int(3, 14)),
                'priority' => $risk > 55 ? 'high' : 'medium',
                'status' => random_int(1, 10) > 7 ? 'in_progress' : 'pending',
                'total_marks' => 20,
            ]);
        }

        Exam::create([
            'subject_id' => $subjects['CS-301']->id,
            'course_id' => $courses[0]->id,
            'title' => 'DBMS Midterm',
            'type' => 'midterm',
            'exam_date' => now()->addDays(12),
            'exam_time' => '10:00',
            'location' => 'CS Block Room 204',
            'total_marks' => 50,
        ]);

        $plan = StudyPlan::create([
            'student_id' => $student->id,
            'title' => 'AI Generated Weekly Recovery Plan',
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addDays(6)->toDateString(),
            'available_hours_per_day' => 2.5,
            'preferred_times' => ['5:00 PM', '8:00 PM'],
            'ai_summary' => 'Prioritize weaker subjects, keep sessions short, and revise before the DBMS midterm.',
        ]);

        foreach ([
            ['CS-301', 'Normalization and functional dependencies', 'high'],
            ['CS-355', 'Classification metrics revision', $risk > 55 ? 'high' : 'medium'],
            ['CS-330', 'Laravel routing and controllers', 'medium'],
            ['CS-320', 'UML and requirements practice', 'medium'],
        ] as $index => [$code, $topic, $priority]) {
            StudyTask::create([
                'study_plan_id' => $plan->id,
                'student_id' => $student->id,
                'subject_id' => $subjects[$code]->id,
                'task_date' => now()->addDays($index)->toDateString(),
                'start_time' => '17:00',
                'end_time' => '18:00',
                'topic' => $topic,
                'description' => 'Revise concept notes, solve two examples, and write quick recall points.',
                'priority' => $priority,
                'status' => $index === 0 && $risk < 45 ? 'completed' : 'pending',
                'completed_at' => $index === 0 && $risk < 45 ? now() : null,
            ]);
        }

        $level = $risk >= 70 ? 'high' : ($risk >= 40 ? 'medium' : 'low');
        AcademicRiskScore::create([
            'student_id' => $student->id,
            'score' => $risk,
            'level' => $level,
            'reasons' => $level === 'low'
                ? ['Attendance and marks are currently stable.']
                : ['Attendance gaps detected.', 'Some assessment scores are below the class target.', 'Pending study tasks need attention.'],
            'recommendations' => ['Revise weak topics daily.', 'Complete pending assignments.', 'Discuss difficult concepts with the course teacher.'],
            'calculated_at' => now(),
        ]);

        AiRecommendation::create([
            'student_id' => $student->id,
            'subject_id' => $subjects['CS-301']->id,
            'type' => 'early_intervention',
            'title' => $level === 'high' ? 'Immediate DBMS revision recommended' : 'Maintain DBMS revision rhythm',
            'body' => $level === 'high'
                ? 'Your recent DBMS attendance and marks indicate that normalization and joins need focused revision before the upcoming exam.'
                : 'Keep practicing DBMS examples twice a week to maintain current performance.',
            'priority' => $level === 'high' ? 'high' : 'medium',
            'source_data' => ['risk_score' => $risk],
        ]);

        $quiz = Quiz::create([
            'student_id' => $student->id,
            'subject_id' => $subjects['CS-301']->id,
            'title' => 'DBMS Normalization Practice',
            'topic' => 'Normalization',
            'difficulty' => 'medium',
            'question_count' => 3,
            'source_excerpt' => 'Normalization reduces redundancy using functional dependencies and normal forms.',
        ]);

        foreach ([
            ['Which normal form removes partial dependency?', '1NF', '2NF', '3NF', 'BCNF', 'B', '2NF removes partial dependency from a composite key.'],
            ['What does DBMS normalization mainly reduce?', 'Redundancy', 'Security', 'Hardware cost', 'Compilation time', 'A', 'Normalization organizes data to reduce redundancy and anomalies.'],
            ['A transitive dependency is handled by which normal form?', '1NF', '2NF', '3NF', '4NF', 'C', '3NF targets transitive dependencies.'],
        ] as $item) {
            QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => $item[0],
                'option_a' => $item[1],
                'option_b' => $item[2],
                'option_c' => $item[3],
                'option_d' => $item[4],
                'correct_option' => $item[5],
                'explanation' => $item[6],
                'topic' => 'Normalization',
            ]);
        }

        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'score' => $risk > 60 ? 45 : 80,
            'total_questions' => 3,
            'correct_count' => $risk > 60 ? 1 : 2,
            'weak_topics' => $risk > 60 ? ['Normalization', 'Functional dependencies'] : ['BCNF edge cases'],
            'started_at' => now()->subDays(2),
            'completed_at' => now()->subDays(2)->addMinutes(9),
        ]);

        Notification::create([
            'user_id' => $student->user_id,
            'title' => $level === 'high' ? 'High academic risk indicator' : 'Academic progress update',
            'body' => $level === 'high'
                ? 'Your early-warning score is high. Review the AI recommendations and complete pending study tasks.'
                : 'Your dashboard has updated recommendations based on recent academic activity.',
            'type' => $level === 'high' ? 'risk' : 'recommendation',
        ]);
    }
}
