@extends('layouts.app')
@section('title', 'Quiz Result')
@section('page-title', 'Quiz Result')

@section('content')
<div class="row animate-in">
    <div class="col-lg-8 mx-auto">
        {{-- Score Card --}}
        <div class="content-card mb-4">
            <div class="card-body text-center py-4">
                <h2 class="fw-bold mb-1" style="font-size:3rem; color: {{ $attempt->score >= 70 ? 'var(--success)' : ($attempt->score >= 50 ? 'var(--warning)' : 'var(--danger)') }}">
                    {{ $attempt->score }}%
                </h2>
                <p class="text-muted mb-3">{{ $attempt->correct_count }} correct out of {{ $attempt->total_questions }} questions</p>
                <div class="progress mx-auto" style="max-width:400px; height:12px;">
                    <div class="progress-bar {{ $attempt->score >= 70 ? 'bg-success' : ($attempt->score >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $attempt->score }}%"></div>
                </div>
                @if($attempt->weak_topics && count($attempt->weak_topics))
                <div class="mt-3">
                    <small class="text-muted fw-semibold">Weak Topics:</small>
                    @foreach($attempt->weak_topics as $topic)
                        <span class="badge bg-danger-subtle text-danger ms-1">{{ $topic }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Detailed Answers --}}
        <div class="content-card">
            <div class="card-header"><i class="bi bi-list-check me-1"></i> Detailed Review</div>
            <div class="card-body">
                @foreach($attempt->quiz->questions as $index => $q)
                    @php
                        $answer = $attempt->answers->firstWhere('quiz_question_id', $q->id);
                        $isCorrect = $answer?->is_correct;
                        $selected = $answer?->selected_option;
                    @endphp
                    <div class="mb-4 pb-3 border-bottom">
                        <div class="d-flex gap-2 align-items-start">
                            <span class="badge {{ $isCorrect ? 'bg-success' : 'bg-danger' }} mt-1">Q{{ $index + 1 }}</span>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-2">{{ $q->question }}</h6>
                                @foreach(['A' => $q->option_a, 'B' => $q->option_b, 'C' => $q->option_c, 'D' => $q->option_d] as $key => $option)
                                    <div class="py-1 px-2 rounded mb-1 {{ $key === $q->correct_option ? 'bg-success-subtle' : ($key === $selected && !$isCorrect ? 'bg-danger-subtle' : '') }}" style="font-size:.88rem;">
                                        <strong>{{ $key }}.</strong> {{ $option }}
                                        @if($key === $q->correct_option) <i class="bi bi-check-circle-fill text-success ms-1"></i> @endif
                                        @if($key === $selected && !$isCorrect) <i class="bi bi-x-circle-fill text-danger ms-1"></i> @endif
                                    </div>
                                @endforeach
                                @if($q->explanation)
                                    <div class="mt-2 p-2 bg-light rounded" style="font-size:.82rem;">
                                        <i class="bi bi-lightbulb text-warning me-1"></i> <strong>Explanation:</strong> {{ $q->explanation }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('quizzes.index') }}" class="btn btn-primary"><i class="bi bi-arrow-left me-1"></i> Back to Quizzes</a>
            <a href="{{ route('quizzes.attempt', $attempt->quiz) }}" class="btn btn-outline-primary"><i class="bi bi-arrow-repeat me-1"></i> Retake Quiz</a>
        </div>
    </div>
</div>
@endsection
