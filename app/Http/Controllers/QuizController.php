<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Subject;
use App\Models\UploadedDocument;
use App\Services\DocumentTextExtractor;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        return view('quizzes.index', [
            'subjects' => Subject::orderBy('name')->get(),
            'documents' => UploadedDocument::where('student_id', $student->id)->latest()->get(),
            'quizzes' => Quiz::withCount('questions')->where('student_id', $student->id)->latest()->get(),
        ]);
    }

    public function generate(Request $request, GeminiService $gemini, DocumentTextExtractor $extractor)
    {
        $data = $request->validate([
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'document_id' => ['nullable', 'exists:uploaded_documents,id'],
            'material' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:5120', 'mimes:txt,pdf,docx'],
            'question_count' => ['required', 'integer', 'min:3', 'max:25'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'topic' => ['nullable', 'string', 'max:255'],
        ]);

        $student = $request->user()->student;
        $document = isset($data['document_id']) ? UploadedDocument::where('student_id', $student->id)->find($data['document_id']) : null;
        $text = $document?->extracted_text ?: ($data['material'] ?? '');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents');
            $text = $extractor->extract(Storage::path($path), $file->getMimeType());
            $document = UploadedDocument::create([
                'student_id' => $student->id,
                'subject_id' => $data['subject_id'] ?? null,
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'mime_type' => $file->getMimeType(),
                'extracted_text' => $text,
                'status' => 'processed',
            ]);
        }

        if (trim($text) === '') {
            return back()->withErrors(['material' => 'Please provide material, select a processed document, or upload a supported file.']);
        }

        $quiz = Quiz::create([
            'student_id' => $student->id,
            'subject_id' => $data['subject_id'] ?? $document?->subject_id,
            'uploaded_document_id' => $document?->id,
            'title' => ($data['topic'] ?: 'AI Practice').' Quiz',
            'topic' => $data['topic'] ?? null,
            'difficulty' => $data['difficulty'],
            'question_count' => $data['question_count'],
            'source_excerpt' => str($text)->limit(1500),
        ]);

        foreach ($gemini->generateQuiz($text, $data['question_count'], $data['difficulty'], $data['topic'] ?? null) as $item) {
            QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => $item['question'],
                'option_a' => $item['options']['A'],
                'option_b' => $item['options']['B'],
                'option_c' => $item['options']['C'],
                'option_d' => $item['options']['D'],
                'correct_option' => $item['correct_option'],
                'explanation' => $item['explanation'],
                'topic' => $item['topic'],
            ]);
        }

        return redirect()->route('quizzes.attempt', $quiz)->with('status', 'AI quiz generated.');
    }

    public function attempt(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->student_id === $request->user()->student->id, 403);

        return view('quizzes.attempt', ['quiz' => $quiz->load('questions')]);
    }

    public function submit(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->student_id === $request->user()->student->id, 403);
        $answers = $request->input('answers', []);
        $questions = $quiz->questions;
        $correct = 0;
        $weakTopics = [];

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $request->user()->student->id,
            'total_questions' => $questions->count(),
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        foreach ($questions as $question) {
            $selected = strtoupper($answers[$question->id] ?? '');
            $isCorrect = $selected === $question->correct_option;
            $correct += $isCorrect ? 1 : 0;
            if (! $isCorrect && $question->topic) {
                $weakTopics[] = $question->topic;
            }
            QuizAnswer::create([
                'quiz_attempt_id' => $attempt->id,
                'quiz_question_id' => $question->id,
                'selected_option' => $selected ?: null,
                'is_correct' => $isCorrect,
            ]);
        }

        $attempt->update([
            'correct_count' => $correct,
            'score' => $questions->count() ? round(($correct / $questions->count()) * 100, 2) : 0,
            'weak_topics' => array_values(array_unique($weakTopics)),
        ]);

        return redirect()->route('quizzes.result', $attempt);
    }

    public function result(Request $request, QuizAttempt $attempt)
    {
        abort_unless($attempt->student_id === $request->user()->student->id, 403);

        return view('quizzes.result', ['attempt' => $attempt->load('quiz.questions', 'answers')]);
    }
}
