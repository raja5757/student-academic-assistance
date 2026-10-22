<?php

namespace App\Http\Controllers;

use App\Services\AcademicAnalysisService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function redirect(Request $request)
    {
        return match ($request->user()->role?->slug) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    }

    public function student(Request $request, AcademicAnalysisService $analysis)
    {
        return view('dashboards.student', [
            'student' => $request->user()->student,
            'data' => $analysis->studentDashboard($request->user()->student),
        ]);
    }

    public function teacher(Request $request, AcademicAnalysisService $analysis)
    {
        return view('dashboards.teacher', [
            'teacher' => $request->user()->teacher,
            'data' => $analysis->teacherDashboard($request->user()->teacher),
        ]);
    }

    public function admin(AcademicAnalysisService $analysis)
    {
        return view('dashboards.admin', [
            'data' => $analysis->adminDashboard(),
        ]);
    }
}
