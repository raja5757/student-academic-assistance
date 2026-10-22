<?php

namespace App\Http\Controllers;

use App\Models\AiRecommendation;
use App\Models\StudyPlan;
use App\Models\StudyTask;
use App\Models\Subject;
use App\Services\AcademicAnalysisService;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudyPlanController extends Controller
{
    public function index(Request $request, AcademicAnalysisService $analysis)
    {
        $student = $request->user()->student;

        return view('study-plans.index', [
            'student' => $student,
            'plans' => $student->studyPlans()->with('tasks.subject')->latest()->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'analysis' => $analysis->studentDashboard($student),
        ]);
    }

    public function generate(Request $request, GeminiService $gemini, AcademicAnalysisService $analysis)
    {
        $data = $request->validate([
            'available_hours_per_day' => ['required', 'numeric', 'min:0.5', 'max:12'],
            'preferred_times' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', 'in:balanced,weak_subjects,exam_preparation'],
        ]);

        $student = $request->user()->student->load('courses.subject');
        $dashboard = $analysis->studentDashboard($student);
        $payload = [
            'subjects' => $student->courses->map(fn ($course) => $course->subject?->name)->filter()->values(),
            'weak_subjects' => $dashboard['weakSubjects']->all(),
            'exams' => $dashboard['upcomingExams']->map(fn ($exam) => ['title' => $exam->title, 'date' => $exam->exam_date?->toDateString(), 'subject' => $exam->subject?->name]),
            'assignments' => $dashboard['upcomingAssignments']->map(fn ($assignment) => ['title' => $assignment->title, 'due' => $assignment->due_date?->toDateString(), 'subject' => $assignment->subject?->name]),
            'available_hours_per_day' => $data['available_hours_per_day'],
            'preferred_times' => array_map('trim', explode(',', $data['preferred_times'] ?: '5:00 PM, 8:00 PM')),
            'priority' => $data['priority'],
            'attendance' => $dashboard['attendancePercentage'],
            'average_marks' => $dashboard['averageMarks'],
        ];

        $generated = $gemini->generateStudyPlan($payload);
        $plan = StudyPlan::create([
            'student_id' => $student->id,
            'title' => 'AI Personalized Study Plan - '.now()->format('M d'),
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addDays(6)->toDateString(),
            'available_hours_per_day' => $data['available_hours_per_day'],
            'preferred_times' => $payload['preferred_times'],
            'source_data' => $payload,
            'ai_summary' => $generated['summary'],
        ]);

        foreach ($generated['tasks'] as $task) {
            $subject = Subject::where('name', 'like', '%'.($task['subject'] ?? '').'%')->first();
            StudyTask::create([
                'study_plan_id' => $plan->id,
                'student_id' => $student->id,
                'subject_id' => $subject?->id,
                'task_date' => $this->safeDate($task['date'] ?? null),
                'start_time' => $this->safeTime($task['start_time'] ?? null),
                'end_time' => $this->safeTime($task['end_time'] ?? null),
                'topic' => $task['topic'] ?? 'Focused revision',
                'description' => $task['description'] ?? 'Review notes and solve practice questions.',
                'priority' => in_array($task['priority'] ?? '', ['low', 'medium', 'high'], true) ? $task['priority'] : 'medium',
            ]);
        }

        return redirect()->route('study-plans.index')->with('status', 'AI study plan generated and saved.');
    }

    public function toggleTask(Request $request, StudyTask $task)
    {
        abort_unless($task->student_id === $request->user()->student->id, 403);
        $completed = $task->status !== 'completed';
        $task->update([
            'status' => $completed ? 'completed' : 'pending',
            'completed_at' => $completed ? now() : null,
        ]);

        return back()->with('status', 'Study task updated.');
    }

    public function updateTask(Request $request, StudyTask $task)
    {
        abort_unless($task->student_id === $request->user()->student->id, 403);
        $task->update($request->validate([
            'task_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'topic' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
        ]));

        return back()->with('status', 'Study task edited.');
    }

    public function destroyTask(Request $request, StudyTask $task)
    {
        abort_unless($task->student_id === $request->user()->student->id, 403);
        $task->delete();

        return back()->with('status', 'Study task deleted.');
    }

    public function adapt(Request $request, GeminiService $gemini, AcademicAnalysisService $analysis)
    {
        $student = $request->user()->student;
        $dashboard = $analysis->studentDashboard($student);
        $advice = $gemini->adaptiveStudyAdvice([
            'missed_tasks' => $student->studyPlans()->with('tasks')->get()->flatMap->tasks->where('status', 'pending')->count(),
            'weak_subjects' => $dashboard['weakSubjects']->all(),
            'risk_score' => $dashboard['risk']->score,
            'attendance' => $dashboard['attendancePercentage'],
            'average_marks' => $dashboard['averageMarks'],
        ]);

        AiRecommendation::create([
            'student_id' => $student->id,
            'type' => 'adaptive_plan',
            'title' => 'AI Recommendation',
            'body' => $advice,
            'priority' => $dashboard['risk']->level === 'high' ? 'high' : 'medium',
            'source_data' => ['risk_score' => $dashboard['risk']->score],
        ]);

        return back()->with('status', $advice);
    }

    private function safeDate(?string $date): string
    {
        try {
            return Carbon::parse($date ?: now())->toDateString();
        } catch (\Throwable) {
            return now()->toDateString();
        }
    }

    private function safeTime(?string $time): ?string
    {
        try {
            return $time ? Carbon::parse($time)->format('H:i') : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
