<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Student;
use App\Services\AcademicAnalysisService;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function courses(Request $request)
    {
        $teacher = $request->user()->teacher;

        return view('teacher.courses', [
            'courses' => Course::with('subject', 'students.user')
                ->where('teacher_id', $teacher->id)
                ->get(),
        ]);
    }

    public function courseDetail(Request $request, Course $course, AcademicAnalysisService $analysis)
    {
        abort_unless($course->teacher_id === $request->user()->teacher->id, 403);

        $students = $course->students()->with('user')->get()->map(fn ($student) => [
            'model' => $student,
            'attendance' => $analysis->attendancePercentage($student),
            'average' => $analysis->averageMarks($student),
            'risk' => $analysis->calculateRisk($student, false),
        ]);

        return view('teacher.course-detail', [
            'course' => $course->load('subject'),
            'students' => $students,
            'subjects' => [$course->subject],
        ]);
    }

    public function studentDetail(Request $request, Student $student, AcademicAnalysisService $analysis)
    {
        $student->load(['user', 'marks.subject', 'attendanceRecords.subject', 'studyPlans.tasks', 'recommendations.subject']);

        return view('teacher.student-detail', [
            'student' => $student,
            'data' => $analysis->studentDashboard($student),
        ]);
    }

    public function storeAttendance(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'class_date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,late,excused'],
        ]);

        AttendanceRecord::updateOrCreate(
            ['student_id' => $data['student_id'], 'subject_id' => $data['subject_id'], 'class_date' => $data['class_date']],
            $data + ['teacher_id' => $request->user()->teacher->id]
        );

        return back()->with('status', 'Attendance recorded.');
    }

    public function storeMarks(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'type' => ['required', 'in:assignment,quiz,midterm,final,project'],
            'title' => ['required', 'string', 'max:255'],
            'obtained_marks' => ['required', 'numeric', 'min:0'],
            'total_marks' => ['required', 'numeric', 'min:1'],
        ]);

        Mark::create($data + ['recorded_by' => $request->user()->id, 'recorded_on' => now()]);

        return back()->with('status', 'Marks recorded.');
    }

    public function storeAssignment(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date'],
            'priority' => ['required', 'in:low,medium,high'],
            'total_marks' => ['required', 'numeric', 'min:1'],
        ]);

        Assignment::create($data + ['teacher_id' => $request->user()->teacher->id, 'status' => 'pending']);

        return back()->with('status', 'Assignment created.');
    }

    public function storeExam(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'exam_date' => ['required', 'date'],
            'exam_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'total_marks' => ['required', 'numeric', 'min:1'],
        ]);

        Exam::create($data + ['created_by' => $request->user()->id]);

        return back()->with('status', 'Exam created.');
    }

    public function profile(Request $request)
    {
        return view('teacher.profile', ['teacher' => $request->user()->teacher->load('department')]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'designation' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'office_hours' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->update(['name' => $data['name'], 'phone' => $data['phone'] ?? null]);
        $request->user()->teacher->update(collect($data)->only(['designation', 'specialization', 'office_hours', 'bio'])->all());

        return back()->with('status', 'Profile updated.');
    }
}
