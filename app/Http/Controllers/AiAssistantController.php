<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AcademicAnalysisService;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;
        $conversation = AiConversation::firstOrCreate(
            ['student_id' => $student->id],
            ['title' => 'Academic Assistant Chat']
        );

        return view('ai.assistant', [
            'conversation' => $conversation->load('messages'),
        ]);
    }

    public function ask(Request $request, GeminiService $gemini, AcademicAnalysisService $analysis)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $student = $request->user()->student;
        $conversation = AiConversation::firstOrCreate(
            ['student_id' => $student->id],
            ['title' => 'Academic Assistant Chat']
        );

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'sender' => 'user',
            'message' => $data['message'],
        ]);

        $context = [
            'average_marks' => $analysis->averageMarks($student),
            'attendance' => $analysis->attendancePercentage($student),
            'weak_subjects' => $analysis->subjectPerformance($student)->where('status', 'weak')->values()->all(),
        ];

        $answer = $gemini->answerAcademicQuestion($data['message'], $context);

        $message = AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'sender' => 'assistant',
            'message' => $answer,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'answer' => nl2br(e($message->message)),
                'created_at' => $message->created_at->format('h:i A'),
            ]);
        }

        return back();
    }
}
