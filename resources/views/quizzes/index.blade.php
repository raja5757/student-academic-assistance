@extends('layouts.app')
@section('title', 'AI Quizzes')
@section('page-title', 'AI Quiz Generator')

@section('content')
<div class="animate-in">
    {{-- Generate Quiz --}}
    <div class="content-card mb-4">
        <div class="card-header"><i class="bi bi-stars me-1"></i> Generate AI Quiz from Study Material</div>
        <div class="card-body">
            <form method="POST" action="{{ route('quizzes.generate') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Subject (optional)</label>
                        <select name="subject_id" class="form-select"><option value="">— Auto detect —</option>@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Topic Focus (optional)</label>
                        <input type="text" name="topic" class="form-control" placeholder="e.g. Normalization">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Questions</label>
                        <input type="number" name="question_count" class="form-control" value="5" min="3" max="25" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Difficulty</label>
                        <select name="difficulty" class="form-select"><option value="easy">Easy</option><option value="medium" selected>Medium</option><option value="hard">Hard</option></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload Material (PDF, TXT, DOCX — max 5MB)</label>
                        <input type="file" name="file" class="form-control" accept=".txt,.pdf,.docx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Or Select Processed Document</label>
                        <select name="document_id" class="form-select"><option value="">— None —</option>@foreach($documents as $doc)<option value="{{ $doc->id }}">{{ $doc->original_name }}</option>@endforeach</select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Or Paste Text Material</label>
                        <textarea name="material" class="form-control" rows="3" placeholder="Paste your study notes here..."></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-magic me-1"></i> Generate Quiz</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Previous Quizzes --}}
    <div class="content-card">
        <div class="card-header"><i class="bi bi-collection me-1"></i> Your Quizzes</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Title</th><th>Topic</th><th>Difficulty</th><th>Questions</th><th>Created</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($quizzes as $quiz)
                        <tr>
                            <td class="fw-semibold">{{ $quiz->title }}</td>
                            <td>{{ $quiz->topic ?? '—' }}</td>
                            <td><span class="badge {{ $quiz->difficulty === 'hard' ? 'bg-danger' : ($quiz->difficulty === 'medium' ? 'bg-warning' : 'bg-success') }}">{{ ucfirst($quiz->difficulty) }}</span></td>
                            <td>{{ $quiz->questions_count }}</td>
                            <td><small class="text-muted">{{ $quiz->created_at->format('M d') }}</small></td>
                            <td><a href="{{ route('quizzes.attempt', $quiz) }}" class="btn btn-sm btn-primary"><i class="bi bi-play"></i> Take</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-muted text-center py-4">No quizzes yet. Generate one above!</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
