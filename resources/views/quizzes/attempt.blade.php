@extends('layouts.app')
@section('title', 'Take Quiz')
@section('page-title', $quiz->title)

@section('content')
<div class="row animate-in">
    <div class="col-lg-8 mx-auto">
        <div class="content-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-question-diamond me-1"></i> {{ $quiz->title }}</span>
                <span class="badge bg-primary-subtle text-primary">{{ $quiz->questions->count() }} Questions • {{ ucfirst($quiz->difficulty) }}</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('quizzes.submit', $quiz) }}">
                    @csrf
                    @foreach($quiz->questions as $index => $q)
                    <div class="mb-4 pb-3 border-bottom">
                        <h6 class="fw-bold"><span class="badge bg-primary me-2">Q{{ $index + 1 }}</span> {{ $q->question }}</h6>
                        @if($q->topic)<small class="text-muted mb-2 d-block">Topic: {{ $q->topic }}</small>@endif
                        <div class="mt-2">
                            @foreach(['A' => $q->option_a, 'B' => $q->option_b, 'C' => $q->option_c, 'D' => $q->option_d] as $key => $option)
                            <div class="form-check mb-2">
                                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $key }}" class="form-check-input" id="q{{ $q->id }}_{{ $key }}">
                                <label class="form-check-label" for="q{{ $q->id }}_{{ $key }}">
                                    <strong>{{ $key }}.</strong> {{ $option }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-check-circle me-1"></i> Submit Quiz
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
