<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunctionalAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the database
        $this->artisan('db:seed');
    }

    public function test_public_pages_load()
    {
        // Homepage
        $response = $this->get('/');
        $response->assertStatus(302); // Redirects to login

        // Login
        $response = $this->get('/login');
        $response->assertStatus(200);

        // Register
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_student_dashboard_and_features()
    {
        $studentUser = User::whereHas('role', function ($q) {
            $q->where('slug', 'student');
        })->first();

        $this->actingAs($studentUser);

        // Dashboard redirect
        $this->get('/dashboard')->assertRedirect('/student/dashboard');

        // Dashboard loads
        $this->get('/student/dashboard')->assertStatus(200);

        // Academics
        $this->get('/student/academics')->assertStatus(200);

        // AI Assistant
        $this->get('/student/ai-assistant')->assertStatus(200);

        // Study Plans
        $this->get('/student/study-plans')->assertStatus(200);

        // Quizzes
        $this->get('/student/quizzes')->assertStatus(200);

        // Profile
        $this->get('/student/profile')->assertStatus(200);
    }

    public function test_teacher_dashboard_and_features()
    {
        $teacherUser = User::whereHas('role', function ($q) {
            $q->where('slug', 'teacher');
        })->first();

        $this->actingAs($teacherUser);

        $this->get('/dashboard')->assertRedirect('/teacher/dashboard');
        $this->get('/teacher/dashboard')->assertStatus(200);
        $this->get('/teacher/courses')->assertStatus(200);
        $this->get('/teacher/profile')->assertStatus(200);
    }

    public function test_admin_dashboard_and_features()
    {
        $adminUser = User::whereHas('role', function ($q) {
            $q->where('slug', 'admin');
        })->first();

        $this->actingAs($adminUser);

        $this->get('/dashboard')->assertRedirect('/admin/dashboard');
        $this->get('/admin/dashboard')->assertStatus(200);
        $this->get('/admin/users')->assertStatus(200);
        $this->get('/admin/departments')->assertStatus(200);
        $this->get('/admin/subjects')->assertStatus(200);
        $this->get('/admin/students')->assertStatus(200);
        $this->get('/admin/activity-logs')->assertStatus(200);
    }
}
