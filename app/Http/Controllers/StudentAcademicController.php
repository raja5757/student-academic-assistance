<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AttendanceRecord;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Subject;
use App\Models\UploadedDocument;
use App\Services\DocumentTextExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentAcademicController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student->load(['courses.subject', 'marks.subject', 'attendanceRecords.subject']);

        return view('student.academics', [
            'student' => $student,
            'subjects' => Subject::orderBy('name')->get(),
            'assignments' => Assignment::with('subject')->where(function ($query) use ($student) {
                $query->where('student_id', $student->id)
                    ->orWhereIn('course_id', $student->courses->pluck('id'));
            })->latest('due_date')->get(),
            'exams' => Exam::with('subject')->whereIn('course_id', $student->courses->pluck('id'))->orderBy('exam_date')->get(),
            'documents' => UploadedDocument::where('student_id', $student->id)->latest()->get(),
        ]);
    }

    public function storeAssignment(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:pending,in_progress,completed,overdue'],
        ]);

        Assignment::create($data + ['student_id' => $request->user()->student->id]);

        return back()->with('status', 'Assignment added.');
    }

    public function updateAssignment(Request $request, Assignment $assignment)
    {
        abort_unless($assignment->student_id === $request->user()->student->id, 403);
        $assignment->update($request->validate([
            'status' => ['required', 'in:pending,in_progress,completed,overdue'],
        ]));

        return back()->with('status', 'Assignment status updated.');
    }

    public function storeExam(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'exam_date' => ['required', 'date'],
            'exam_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'total_marks' => ['required', 'numeric', 'min:1'],
        ]);

        Exam::create($data + ['created_by' => $request->user()->id]);

        return back()->with('status', 'Exam added.');
    }

    public function storeAttendance(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,late,excused'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        AttendanceRecord::updateOrCreate([
            'student_id' => $request->user()->student->id,
            'subject_id' => $data['subject_id'],
            'class_date' => $data['class_date'],
        ], $data);

        return back()->with('status', 'Attendance recorded.');
    }

    public function storeMark(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'type' => ['required', 'in:assignment,quiz,midterm,final,project'],
            'title' => ['required', 'string', 'max:255'],
            'obtained_marks' => ['required', 'numeric', 'min:0'],
            'total_marks' => ['required', 'numeric', 'min:1'],
            'recorded_on' => ['nullable', 'date'],
        ]);

        Mark::create($data + ['student_id' => $request->user()->student->id, 'recorded_by' => $request->user()->id]);

        return back()->with('status', 'Marks recorded.');
    }

    public function uploadDocument(Request $request, DocumentTextExtractor $extractor)
    {
        $data = $request->validate([
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'document' => ['required', 'file', 'max:5120', 'mimes:txt,pdf,docx'],
        ]);

        $file = $data['document'];
        $path = $file->store('documents');
        $document = UploadedDocument::create([
            'student_id' => $request->user()->student->id,
            'subject_id' => $data['subject_id'] ?? null,
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getMimeType(),
            'status' => 'processing',
        ]);

        try {
            $text = $extractor->extract(Storage::path($path), $file->getMimeType());
            $document->update(['extracted_text' => $text, 'status' => 'processed']);
        } catch (\Throwable $exception) {
            $document->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
            return back()->withErrors(['document' => $exception->getMessage()]);
        }

        return back()->with('status', 'Document uploaded and processed.');
    }

    public function profile(Request $request)
    {
        return view('student.profile', [
            'student' => $request->user()->student->load('department')
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date'],
        ]);

        $request->user()->student->update($data);

        return back()->with('status', 'Profile updated.');
    }
}
