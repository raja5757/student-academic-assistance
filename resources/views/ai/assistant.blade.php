@extends('layouts.app')
@section('title', 'AI Academic Assistant')
@section('page-title', 'AI Academic Assistant')

@section('content')
<div class="row animate-in">
    <div class="col-lg-8 mx-auto">
        <div class="content-card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-robot text-primary"></i>
                <span>Chat with AI Assistant</span>
                <span class="badge bg-success-subtle text-success ms-auto" style="font-size:.7rem;">Powered by Gemini</span>
            </div>
            <div class="card-body">
                {{-- Chat Messages --}}
                <div class="chat-container mb-3" id="chatContainer">
                    {{-- Welcome message --}}
                    <div class="d-flex mb-3">
                        <div class="chat-bubble chat-ai">
                            <strong>AI Assistant</strong><br>
                            Hello! I'm your AI academic assistant. Ask me anything about your courses — I'll explain concepts,
                            provide examples, and help you revise. 📚
                        </div>
                    </div>

                    @foreach($conversation->messages as $msg)
                        <div class="d-flex mb-3 {{ $msg->sender === 'user' ? 'justify-content-end' : '' }}">
                            <div class="chat-bubble {{ $msg->sender === 'user' ? 'chat-user' : 'chat-ai' }}">
                                @if($msg->sender === 'assistant')
                                    {!! nl2br(e($msg->message)) !!}
                                @else
                                    {{ $msg->message }}
                                @endif
                                <div class="mt-1" style="font-size:.68rem; opacity:.6;">{{ $msg->created_at->format('h:i A') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Input Form --}}
                <form id="chatForm" method="POST" action="{{ route('ai.assistant.ask') }}">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="message" id="messageInput" class="form-control" placeholder="Ask an academic question..." required autocomplete="off" maxlength="3000">
                        <button type="submit" class="btn btn-primary px-4" id="sendBtn">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">Example: "Explain normalization in DBMS with examples"</small>
                </form>
            </div>
        </div>

        {{-- Tips --}}
        <div class="content-card mt-3">
            <div class="card-header"><i class="bi bi-lightbulb me-1"></i> Suggested Questions</div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach(['Explain normalization in DBMS','What is backpropagation in neural networks?','Explain MVC architecture in web engineering','What are functional vs non-functional requirements?','How does binary search work?','Explain SQL joins with examples'] as $q)
                    <div class="col-md-6">
                        <button class="btn btn-outline-secondary btn-sm w-100 text-start suggest-btn" data-question="{{ $q }}">
                            <i class="bi bi-chat-dots me-1"></i> {{ $q }}
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('chatContainer');
    container.scrollTop = container.scrollHeight;

    // AJAX chat
    const form = document.getElementById('chatForm');
    const input = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        // Add user bubble
        container.innerHTML += `<div class="d-flex mb-3 justify-content-end"><div class="chat-bubble chat-user">${escapeHtml(message)}<div class="mt-1" style="font-size:.68rem; opacity:.6;">${new Date().toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit',hour12:true})}</div></div></div>`;

        // Add loading
        const loadingId = 'loading-' + Date.now();
        container.innerHTML += `<div class="d-flex mb-3" id="${loadingId}"><div class="chat-bubble chat-ai"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Thinking...</div></div>`;
        container.scrollTop = container.scrollHeight;

        input.value = '';
        sendBtn.disabled = true;

        fetch('{{ route("ai.assistant.ask") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ message })
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById(loadingId).remove();
            container.innerHTML += `<div class="d-flex mb-3"><div class="chat-bubble chat-ai">${data.answer}<div class="mt-1" style="font-size:.68rem; opacity:.6;">${data.created_at}</div></div></div>`;
            container.scrollTop = container.scrollHeight;
        })
        .catch(() => {
            document.getElementById(loadingId).remove();
            container.innerHTML += `<div class="d-flex mb-3"><div class="chat-bubble chat-ai text-danger">Something went wrong. Please try again.</div></div>`;
        })
        .finally(() => { sendBtn.disabled = false; input.focus(); });
    });

    // Suggestion buttons
    document.querySelectorAll('.suggest-btn').forEach(btn => {
        btn.addEventListener('click', () => { input.value = btn.dataset.question; input.focus(); });
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endpush
