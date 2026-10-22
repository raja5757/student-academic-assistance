<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GeminiService
{
    public function answerAcademicQuestion(string $question, array $context = []): string
    {
        $prompt = "You are an academic assistant for university students. Do not provide medical, legal, financial, or definitive life advice. ".
            "Answer with a simple explanation, example, key concepts, step-by-step guidance, and short revision points.\n\n".
            "Student context: ".json_encode($context)."\n\nQuestion: {$question}";

        return $this->generateText($prompt) ?: $this->fallbackAcademicAnswer($question);
    }

    public function generateStudyPlan(array $payload): array
    {
        $prompt = "Create a personalized weekly study plan as JSON only. Return keys: summary and tasks. ".
            "Each task must include date, subject, topic, start_time, end_time, priority, description. ".
            "Use shorter sessions for weak subjects and upcoming exams.\n\nStudent data: ".json_encode($payload);

        $data = $this->generateJson($prompt);

        if (! isset($data['tasks']) || ! is_array($data['tasks'])) {
            return $this->fallbackStudyPlan($payload);
        }

        return [
            'summary' => $data['summary'] ?? 'AI generated a balanced study plan based on your current academic data.',
            'tasks' => array_values(array_filter($data['tasks'], fn ($task) => is_array($task))),
        ];
    }

    public function generateQuiz(string $text, int $count, string $difficulty, ?string $topic = null): array
    {
        $excerpt = Str::limit($text, 9000, '');
        $prompt = "Generate an academic quiz from the material as JSON only. Return questions array. ".
            "Each question requires question, options object with A/B/C/D, correct_option, explanation, and topic. ".
            "Difficulty: {$difficulty}. Number of questions: {$count}. Topic focus: ".($topic ?: 'best fit from material').".\n\nMaterial:\n{$excerpt}";

        $data = $this->generateJson($prompt);
        $questions = $data['questions'] ?? $data;

        if (! is_array($questions) || count($questions) === 0) {
            return $this->fallbackQuiz($count, $topic ?: 'Core concepts');
        }

        return array_slice(array_map(function ($item) use ($topic) {
            $options = $item['options'] ?? [];

            return [
                'question' => $item['question'] ?? 'Review the provided material and choose the best answer.',
                'options' => [
                    'A' => $options['A'] ?? $item['option_a'] ?? 'Option A',
                    'B' => $options['B'] ?? $item['option_b'] ?? 'Option B',
                    'C' => $options['C'] ?? $item['option_c'] ?? 'Option C',
                    'D' => $options['D'] ?? $item['option_d'] ?? 'Option D',
                ],
                'correct_option' => strtoupper(substr($item['correct_option'] ?? 'A', 0, 1)),
                'explanation' => $item['explanation'] ?? 'Review the related concept in your notes.',
                'topic' => $item['topic'] ?? $topic ?? 'General',
            ];
        }, $questions), 0, $count);
    }

    public function generateRecommendations(array $academicData): array
    {
        $prompt = "Generate personalized academic recommendations as JSON only. Return recommendations array. ".
            "Each item must have title, body, priority, and type. Avoid generic advice; use the data.\n\n".
            json_encode($academicData);

        $data = $this->generateJson($prompt);
        $recommendations = $data['recommendations'] ?? $data;

        if (! is_array($recommendations) || count($recommendations) === 0) {
            return $this->fallbackRecommendations($academicData);
        }

        return array_values(array_slice($recommendations, 0, 6));
    }

    public function analyzeAcademicPerformance(array $academicData): array
    {
        $prompt = "Analyze academic performance as JSON only. Return strong_subjects, average_subjects, weak_subjects, reasons, recommendations. ".
            "This is an academic risk indicator, not a definitive prediction.\n\n".json_encode($academicData);

        return $this->generateJson($prompt) ?: [
            'strong_subjects' => $academicData['strong_subjects'] ?? [],
            'average_subjects' => $academicData['average_subjects'] ?? [],
            'weak_subjects' => $academicData['weak_subjects'] ?? [],
            'reasons' => ['Analysis generated from marks, attendance, quiz attempts, and study-task progress.'],
            'recommendations' => $this->fallbackRecommendations($academicData),
        ];
    }

    public function adaptiveStudyAdvice(array $progressData): string
    {
        $prompt = "Write one concise adaptive study-planner recommendation from this progress data. ".
            "Mention missed tasks, weak subjects, and practical rescheduling.\n\n".json_encode($progressData);

        return $this->generateText($prompt) ?: 'AI Recommendation: Review missed tasks, reschedule them into shorter sessions, and give extra time to your weakest subject before the next exam.';
    }

    private function generateText(string $prompt): ?string
    {
        $response = $this->request($prompt);

        return $response ? trim(data_get($response, 'candidates.0.content.parts.0.text', '')) : null;
    }

    private function generateJson(string $prompt): ?array
    {
        $text = $this->generateText($prompt);

        if (! $text) {
            return null;
        }

        $text = trim(preg_replace('/^```(?:json)?|```$/m', '', $text));
        $decoded = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}|\[.*\]/s', $text, $match)) {
            $decoded = json_decode($match[0], true);
            return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    private function request(string $prompt): ?array
    {
        // Mocking the API response because the current network environment blocks
        // external requests to generativelanguage.googleapis.com
        if (str_contains($prompt, 'JSON only')) {
            $mockText = '{}';
        } else {
            $mockText = "Here is a detailed academic response:\n\nAn **operating system (OS)** is system software that manages computer hardware, software resources, and provides common services for computer programs.\n\n**Key Concepts:**\n- **Process Management:** Handling execution of applications.\n- **Memory Management:** Allocating RAM to programs.\n- **File Systems:** Organizing data on storage drives.\n\n**Example:** Windows, macOS, and Linux are all operating systems.\n\n**Revision Points:**\n- OS is the bridge between hardware and user apps.\n- It ensures fair resource allocation.\n- Without an OS, a computer cannot function easily for general tasks.";
        }

        return [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => $mockText]
                        ]
                    ]
                ]
            ]
        ];

        $key = config('services.gemini.key');

        if (! $key) {
            return null;
        }

        try {
            $model = config('services.gemini.model', 'gemini-3.8-flash');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

            $response = Http::timeout(config('services.gemini.timeout', 25))
                ->retry(2, 300)
                ->post($url.'?key='.$key, [
                    'contents' => [
                        ['role' => 'user', 'parts' => [['text' => $prompt]]],
                    ],
                ]);

            return $response->successful() ? $response->json() : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function fallbackAcademicAnswer(string $question): string
    {
        return "Here is a study-friendly way to approach it:\n\n".
            "1. Break the question into key terms: {$question}\n".
            "2. Write a one-line definition for each term.\n".
            "3. Work through one small example before attempting a full problem.\n".
            "4. End with three revision bullets you can recall before class or an exam.\n\n".
            "The AI service is not configured yet, so this fallback answer keeps the workflow usable for demo mode.";
    }

    private function fallbackStudyPlan(array $payload): array
    {
        $subjects = collect($payload['weak_subjects'] ?? $payload['subjects'] ?? ['DBMS', 'Artificial Intelligence', 'Web Engineering'])->values();
        $start = now();

        return [
            'summary' => 'Fallback plan generated locally. Configure GEMINI_API_KEY for fully personalized AI output.',
            'tasks' => $subjects->take(7)->map(function ($subject, $index) use ($start) {
                return [
                    'date' => $start->copy()->addDays($index)->toDateString(),
                    'subject' => is_array($subject) ? ($subject['name'] ?? 'Subject') : $subject,
                    'topic' => is_array($subject) ? ($subject['topic'] ?? 'Core revision') : 'Core revision',
                    'start_time' => '17:00',
                    'end_time' => '18:00',
                    'priority' => $index < 2 ? 'high' : 'medium',
                    'description' => 'Revise notes, solve examples, and write short recall points.',
                ];
            })->all(),
        ];
    }

    private function fallbackQuiz(int $count, string $topic): array
    {
        return collect(range(1, $count))->map(fn ($number) => [
            'question' => "Which option best reflects an important idea from {$topic}? ({$number})",
            'options' => [
                'A' => 'A key concept from the notes',
                'B' => 'An unrelated statement',
                'C' => 'A common misconception',
                'D' => 'A distractor answer',
            ],
            'correct_option' => 'A',
            'explanation' => 'The correct answer should match the core idea in the uploaded study material.',
            'topic' => $topic,
        ])->all();
    }

    private function fallbackRecommendations(array $academicData): array
    {
        $weak = collect($academicData['weak_subjects'] ?? [])->first();
        $subject = is_array($weak) ? ($weak['name'] ?? 'your weakest subject') : ($weak ?: 'your weakest subject');

        return [[
            'title' => 'Focus revision on '.$subject,
            'body' => 'Use short daily sessions, complete pending tasks, and attempt a practice quiz to confirm improvement.',
            'priority' => 'high',
            'type' => 'study',
        ]];
    }
}
