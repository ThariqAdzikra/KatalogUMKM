@props(['context' => 'general'])

<div id="ai-chat-widget" class="position-fixed bottom-0 end-0 m-4" style="z-index: 1050;" data-context="{{ $context }}">
    {{-- Chat Toggle Button - Cyan to match Analisa Sekarang --}}
    <button id="ai-chat-toggle" class="btn btn-outline-info rounded-circle shadow-lg p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
        <i class="bi bi-robot fs-3"></i>
    </button>

    {{-- Backdrop Overlay for Maximized View --}}
    <div id="ai-chat-backdrop" class="ai-chat-backdrop"></div>

    {{-- Chat Box - WhatsApp Style --}}
    <div id="ai-chat-box" class="d-none">
        {{-- Header --}}
        <div class="ai-chat-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-robot fs-5"></i>
                <h6><i class="bi bi-stars me-2"></i>AI Assistant ({{ ucfirst($context) }})</h6>
            </div>
            <div class="ai-chat-header-actions">
                <button id="ai-chat-maximize" title="Maximize/Minimize">
                    <i class="bi bi-arrows-fullscreen" id="maximize-icon"></i>
                </button>
                <button id="ai-chat-close" title="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        {{-- Messages Area --}}
        <div id="ai-chat-messages">
            {{-- Welcome Message --}}
            <div class="chat-bubble chat-bubble-ai">
                <div class="chat-bubble-meta">
                    <i class="bi bi-robot"></i>
                    <span>AI Assistant</span>
                </div>
                <div class="chat-bubble-text">
                    👋 Halo! Saya asisten cerdas untuk modul <strong>{{ ucfirst($context) }}</strong>. Ada yang bisa saya bantu?
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="ai-chat-input-area">
            <form id="ai-chat-form">
                <input type="text" id="ai-chat-input" placeholder="Ketik pesan..." required autocomplete="off">
                <button type="submit" title="Send">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="/css/ai-chat.css">
@endpush

@push('scripts')
    <script src="/js/admin/ai-chat.js"></script>
@endpush
