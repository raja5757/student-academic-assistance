<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\AcademicAnalysisService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users()
    {
        return view('admin.users', [
            'users' => User::with('role')->latest()->paginate(20),
        ]);
    }

    public function toggleUserStatus(User $user)
    {
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', "{$user->name} is now {$user->status}.");
    }

    public function departments()
    {
        return view('admin.departments', [
            'departments' => Department::withCount('subjects')->get(),
        ]);
    }

    public function storeDepartment(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'code' => ['required', 'string', 'max:10', 'unique:departments,code'],
            'description' => ['nullable', 'string'],
        ]);

        Department::create($data);

        return back()->with('status', 'Department created.');
    }

    public function subjects()
    {
        return view('admin.subjects', [
            'subjects' => Subject::with('department')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function storeSubject(Request $request)
    {
        $data = $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:255'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:6'],
            'description' => ['nullable', 'string'],
        ]);

        Subject::create($data);

        return back()->with('status', 'Subject created.');
    }

    public function students(AcademicAnalysisService $analysis)
    {
        $students = Student::with('user', 'department', 'semester')->get()->map(fn ($student) => [
            'model' => $student,
            'attendance' => $analysis->attendancePercentage($student),
            'average' => $analysis->averageMarks($student),
            'risk' => $analysis->calculateRisk($student, false),
        ]);

        return view('admin.students', ['students' => $students]);
    }

    public function studentDetail(Student $student, AcademicAnalysisService $analysis)
    {
        $student->load(['user', 'department', 'semester', 'marks.subject', 'attendanceRecords.subject', 'studyPlans.tasks', 'recommendations.subject']);

        return view('admin.student-detail', [
            'student' => $student,
            'data' => $analysis->studentDashboard($student),
        ]);
    }

    public function activityLogs()
    {
        return view('admin.activity-logs', [
            'logs' => ActivityLog::with('user')->latest()->paginate(30),
        ]);
    }
}
