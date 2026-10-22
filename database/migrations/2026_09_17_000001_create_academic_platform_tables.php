<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('year');
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->timestamps();
            $table->unique(['name', 'year']);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('credit_hours')->default(3);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->string('roll_no')->unique();
            $table->string('program')->default('BS Computer Science');
            $table->string('batch')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->string('guardian_contact')->nullable();
            $table->text('academic_goal')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('designation')->nullable();
            $table->string('specialization')->nullable();
            $table->string('office_hours')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->unique(['student_id', 'course_id']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->date('class_date')->index();
            $table->string('status')->default('present')->index();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'subject_id', 'class_date']);
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->index();
            $table->string('priority')->default('medium')->index();
            $table->string('status')->default('pending')->index();
            $table->unsignedSmallInteger('total_marks')->default(100);
            $table->timestamps();
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status')->default('pending')->index();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
            $table->unique(['assignment_id', 'student_id']);
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('exam');
            $table->date('exam_date')->index();
            $table->time('exam_time')->nullable();
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('total_marks')->default(100);
            $table->timestamps();
        });

        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('quiz')->index();
            $table->string('title');
            $table->decimal('obtained_marks', 6, 2);
            $table->decimal('total_marks', 6, 2)->default(100);
            $table->date('recorded_on')->nullable();
            $table->timestamps();
        });

        Schema::create('study_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('title');
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->decimal('available_hours_per_day', 4, 1)->nullable();
            $table->json('preferred_times')->nullable();
            $table->json('source_data')->nullable();
            $table->text('ai_summary')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('study_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->date('task_date')->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('topic');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium')->index();
            $table->string('status')->default('pending')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('uploaded_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type')->nullable();
            $table->longText('extracted_text')->nullable();
            $table->string('status')->default('processed')->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_document_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('topic')->nullable();
            $table->string('difficulty')->default('medium');
            $table->unsignedTinyInteger('question_count')->default(10);
            $table->longText('source_excerpt')->nullable();
            $table->boolean('generated_by_ai')->default(true);
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c');
            $table->string('option_d');
            $table->string('correct_option', 1);
            $table->text('explanation')->nullable();
            $table->string('topic')->nullable();
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedTinyInteger('total_questions')->default(0);
            $table->unsignedTinyInteger('correct_count')->default(0);
            $table->json('weak_topics')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->string('selected_option', 1)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('title')->default('Academic Assistant Chat');
            $table->timestamps();
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('sender')->index();
            $table->longText('message');
            $table->timestamps();
        });

        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('study')->index();
            $table->string('title');
            $table->text('body');
            $table->string('priority')->default('medium')->index();
            $table->json('source_data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('academic_risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('level')->default('low')->index();
            $table->json('reasons')->nullable();
            $table->json('recommendations')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('type')->default('info')->index();
            $table->string('action_url')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action')->index();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'activity_logs',
            'notifications',
            'academic_risk_scores',
            'ai_recommendations',
            'ai_messages',
            'ai_conversations',
            'quiz_answers',
            'quiz_attempts',
            'quiz_questions',
            'quizzes',
            'uploaded_documents',
            'study_tasks',
            'study_plans',
            'marks',
            'exams',
            'assignment_submissions',
            'assignments',
            'attendance_records',
            'enrollments',
            'courses',
            'teachers',
            'students',
            'subjects',
            'semesters',
            'departments',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
