<?php

namespace App\Services;

use App\Models\AcademicRiskScore;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Collection;

class AcademicAnalysisService
{
    public function studentDashboard(Student $student): array
    {
        $student->load(['courses.subject', 'marks.subject', 'attendanceRecords.subject', 'studyPlans.tasks', 'recommendations.subject', 'latestRiskScore']);

        $subjectPerformance = $this->subjectPerformance($student);
        $risk = $this->calculateRisk($student);
        $attendance = $this->attendancePercentage($student);
        $averageMarks = $this->averageMarks($student);
        $studyProgress = $this->studyProgress($student);

        return [
            'totalCourses' => $student->courses->count(),
            'upcomingAssignments' => Assignment::where(function ($query) use ($student) {
                $query->where('student_id', $student->id)
                    ->orWhereIn('course_id', $student->courses->pluck('id'));
            })->whereDate('due_date', '>=', now())->orderBy('due_date')->take(5)->get(),
            'upcomingExams' => Exam::whereIn('course_id', $student->courses->pluck('id'))->whereDate('exam_date', '>=', now())->orderBy('exam_date')->take(5)->get(),
            'attendancePercentage' => $attendance,
            'averageMarks' => $averageMarks,
            'risk' => $risk,
            'performanceScore' => round(($attendance + $averageMarks + $studyProgress) / 3),
            'studyProgress' => $studyProgress,
            'weakSubjects' => $subjectPerformance->where('average', '<', 60)->values(),
            'subjectPerformance' => $subjectPerformance,
            'attendanceChart' => $this->attendanceChart($student),
            'marksTrend' => $student->marks->sortBy('recorded_on')->map(fn ($mark) => [
                'label' => optional($mark->recorded_on)->format('M d') ?: $mark->created_at->format('M d'),
                'value' => round(($mark->obtained_marks / max(1, $mark->total_marks)) * 100),
            ])->values(),
            'recommendations' => $student->recommendations()->latest()->take(5)->get(),
            'notifications' => $student->user->appNotifications()->latest()->take(6)->get(),
        ];
    }

    public function teacherDashboard(Teacher $teacher): array
    {
        $teacher->load('courses.students.user', 'courses.subject');
        $students = $teacher->courses->flatMap->students->unique('id')->values();
        $risks = $students->map(fn ($student) => $this->calculateRisk($student));

        return [
            'totalStudents' => $students->count(),
            'lowRisk' => $risks->where('level', 'low')->count(),
            'mediumRisk' => $risks->where('level', 'medium')->count(),
            'highRisk' => $risks->where('level', 'high')->count(),
            'averageAttendance' => round($students->avg(fn ($student) => $this->attendancePercentage($student)) ?: 0),
            'averageMarks' => round($students->avg(fn ($student) => $this->averageMarks($student)) ?: 0),
            'weakestSubjects' => $students->flatMap(fn ($student) => $this->subjectPerformance($student)->where('average', '<', 60))->take(6),
            'students' => $students->map(fn ($student) => [
                'model' => $student,
                'attendance' => $this->attendancePercentage($student),
                'average' => $this->averageMarks($student),
                'risk' => $this->calculateRisk($student),
            ]),
            'courses' => $teacher->courses,
        ];
    }

    public function adminDashboard(): array
    {
        $students = Student::with('user')->get();
        $risks = $students->map(fn ($student) => $this->calculateRisk($student));

        return [
            'students' => $students,
            'users' => User::with('role')->latest()->take(10)->get(),
            'totalStudents' => $students->count(),
            'totalTeachers' => Teacher::count(),
            'totalCourses' => Course::count(),
            'highRisk' => $risks->where('level', 'high')->count(),
            'riskDistribution' => [
                'low' => $risks->where('level', 'low')->count(),
                'medium' => $risks->where('level', 'medium')->count(),
                'high' => $risks->where('level', 'high')->count(),
            ],
            'recentRisks' => AcademicRiskScore::with('student.user')->latest('calculated_at')->take(8)->get(),
        ];
    }

    public function calculateRisk(Student $student, bool $persist = true): AcademicRiskScore
    {
        $student->loadMissing(['marks', 'attendanceRecords', 'studyPlans.tasks']);

        $attendance = $this->attendancePercentage($student);
        $marks = $this->averageMarks($student);
        $studyProgress = $this->studyProgress($student);
        $overdue = Assignment::where('student_id', $student->id)->whereDate('due_date', '<', now())->whereNotIn('status', ['completed'])->count();

        $score = min(100, round(
            max(0, 75 - $attendance) * 0.9 +
            max(0, 70 - $marks) * 0.8 +
            max(0, 70 - $studyProgress) * 0.3 +
            min(15, $overdue * 5)
        ));

        $level = $score >= 70 ? 'high' : ($score >= 40 ? 'medium' : 'low');
        $reasons = [];

        if ($attendance < 75) {
            $reasons[] = 'Attendance is below the recommended 75% threshold.';
        }
        if ($marks < 60) {
            $reasons[] = 'Average marks indicate weak current performance.';
        }
        if ($studyProgress < 60) {
            $reasons[] = 'Study-plan completion is behind schedule.';
        }
        if ($overdue > 0) {
            $reasons[] = "{$overdue} assignment(s) are overdue or incomplete.";
        }
        if ($reasons === []) {
            $reasons[] = 'Attendance, marks and study progress are currently stable.';
        }

        $data = [
            'student_id' => $student->id,
            'score' => $score,
            'level' => $level,
            'reasons' => $reasons,
            'recommendations' => [
                'Complete pending assignments before new tasks.',
                'Allocate extra revision time to weak subjects.',
                'Consult the course teacher for difficult topics.',
            ],
            'calculated_at' => now(),
        ];

        return $persist ? AcademicRiskScore::create($data) : new AcademicRiskScore($data);
    }

    public function subjectPerformance(Student $student): Collection
    {
        $student->loadMissing('marks.subject');

        return $student->marks
            ->groupBy('subject_id')
            ->map(function ($marks) {
                $subject = $marks->first()->subject;
                $average = $marks->avg(fn ($mark) => ($mark->obtained_marks / max(1, $mark->total_marks)) * 100);

                return [
                    'id' => $subject?->id,
                    'name' => $subject?->name ?? 'Unknown subject',
                    'average' => round($average),
                    'status' => $average >= 75 ? 'strong' : ($average >= 60 ? 'average' : 'weak'),
                ];
            })
            ->sortBy('average')
            ->values();
    }

    public function attendancePercentage(Student $student): int
    {
        $student->loadMissing('attendanceRecords');
        $total = $student->attendanceRecords->count();

        if ($total === 0) {
            return 0;
        }

        return round(($student->attendanceRecords->whereIn('status', ['present', 'late'])->count() / $total) * 100);
    }

    public function averageMarks(Student $student): int
    {
        $student->loadMissing('marks');

        if ($student->marks->isEmpty()) {
            return 0;
        }

        return round($student->marks->avg(fn ($mark) => ($mark->obtained_marks / max(1, $mark->total_marks)) * 100));
    }

    public function studyProgress(Student $student): int
    {
        $student->loadMissing('studyPlans.tasks');
        $tasks = $student->studyPlans->flatMap->tasks;

        if ($tasks->isEmpty()) {
            return 0;
        }

        return round(($tasks->where('status', 'completed')->count() / $tasks->count()) * 100);
    }

    private function attendanceChart(Student $student): Collection
    {
        return $student->attendanceRecords
            ->groupBy(fn ($record) => $record->subject?->name ?? 'Subject')
            ->map(fn ($records, $subject) => [
                'label' => $subject,
                'value' => round(($records->whereIn('status', ['present', 'late'])->count() / max(1, $records->count())) * 100),
            ])
            ->values();
    }
}
