@extends('layouts.admin')

@section('title', 'Chat - Hội thoại')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="mb-1">Hội thoại</h4>
            <div class="text-muted">
                @if($userId)
                    User #{{ $userId }}
                @else
                    Guest: {{ $guestId }}
                @endif
            </div>
        </div>
        <div>
            <a href="{{ route('admin.chat-support.index') }}" class="btn btn-outline-secondary btn-sm">Quay lại</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body" id="chatThreadBody" style="max-height: 60vh; overflow: auto;">
            @forelse($messages as $m)
                @php
                    $isUser = $m->type === 'user';
                    $isStaff = $m->type === 'staff';
                    $isSystem = $m->type === 'system';
                @endphp
                <div class="d-flex mb-2 {{ $isUser ? 'justify-content-end' : 'justify-content-start' }}">
                    <div style="max-width: 78%;">
                        <div class="small text-muted" style="margin-bottom: 2px;">
                            @if($isUser) Khách @elseif($isStaff) Nhân viên @else Hệ thống @endif
                            - {{ optional($m->created_at)->format('d/m/Y H:i') }}
                        </div>
                        <div class="p-2 rounded {{ $isUser ? 'bg-primary text-white' : ($isSystem ? 'bg-light' : 'bg-white border') }}" style="white-space: pre-line;">
                            {{ $m->text }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">Chưa có tin nhắn</div>
            @endforelse
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-bold">Gửi tin nhắn</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.chat-support.send') }}" id="chatSendForm">
                @csrf
                <input type="hidden" name="user_id" value="{{ $userId }}">
                <input type="hidden" name="guest_id" value="{{ $guestId }}">

                <div class="mb-2">
                    <textarea name="text" class="form-control" rows="3" maxlength="5000" placeholder="Nhập tin nhắn..." id="chatSendText"></textarea>
                    @error('text')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary">Gửi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function(){
    var body = document.getElementById('chatThreadBody');
    if(!body) return;

    var sendForm = document.getElementById('chatSendForm');
    var sendText = document.getElementById('chatSendText');

    var userId = @json($userId);
    var guestId = @json($guestId);
    var lastId = @json((int) optional($messages->last())->id);

    var pollTimer = null;
    var pollDelayMs = 2500;
    var pollFailCount = 0;

    function escapeHtml(s){
        return String(s || '')
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&#39;');
    }

    function formatTime(iso){
        if(!iso) return '';
        try {
            var d = new Date(iso);
            if(isNaN(d.getTime())) return '';
            var dd = String(d.getDate()).padStart(2,'0');
            var mm = String(d.getMonth()+1).padStart(2,'0');
            var yy = d.getFullYear();
            var hh = String(d.getHours()).padStart(2,'0');
            var mi = String(d.getMinutes()).padStart(2,'0');
            return dd + '/' + mm + '/' + yy + ' ' + hh + ':' + mi;
        } catch(e){
            return '';
        }
    }

    function appendMsg(m){
        var shouldScroll = isNearBottom();
        var type = String(m.type || 'system');
        var isUser = type === 'user';
        var isStaff = type === 'staff';
        var isSystem = type === 'system';
        var label = isUser ? 'Khách' : (isStaff ? 'Nhân viên' : 'Hệ thống');

        var wrap = document.createElement('div');
        wrap.className = 'd-flex mb-2 ' + (isUser ? 'justify-content-end' : 'justify-content-start');

        var box = document.createElement('div');
        box.style.maxWidth = '78%';

        var meta = document.createElement('div');
        meta.className = 'small text-muted';
        meta.style.marginBottom = '2px';
        meta.textContent = label + ' - ' + formatTime(m.created_at);

        var bubble = document.createElement('div');
        bubble.className = 'p-2 rounded ' + (isUser ? 'bg-primary text-white' : (isSystem ? 'bg-light' : 'bg-white border'));
        bubble.style.whiteSpace = 'pre-line';
        bubble.textContent = String(m.text || '');

        box.appendChild(meta);
        box.appendChild(bubble);
        wrap.appendChild(box);
        body.appendChild(wrap);
        if(shouldScroll) scrollToBottom();
    }

    function isNearBottom(){
        try {
            var threshold = 120;
            return (body.scrollHeight - body.scrollTop - body.clientHeight) <= threshold;
        } catch(e){
            return true;
        }
    }

    function scrollToBottom(){
        try {
            body.scrollTop = body.scrollHeight;
        } catch(e) {}
    }

    function getCsrfToken(){
        var el = document.querySelector('meta[name="csrf-token"]');
        return el ? (el.getAttribute('content') || '') : '';
    }

    function isPageVisible(){
        try {
            return document.visibilityState === 'visible';
        } catch(e){
            return true;
        }
    }

    function stopPolling(){
        if(pollTimer){
            clearTimeout(pollTimer);
            pollTimer = null;
        }
    }

    function scheduleNextPoll(){
        stopPolling();
        if(!isPageVisible()) return;
        pollTimer = setTimeout(poll, pollDelayMs);
    }

    function poll(){
        if(!isPageVisible()) return;
        var url = @json(route('admin.chat-support.thread')) + '?ajax=1&after_id=' + encodeURIComponent(String(lastId || 0));
        if(userId){
            url += '&user_id=' + encodeURIComponent(String(userId));
        } else {
            url += '&guest_id=' + encodeURIComponent(String(guestId || ''));
        }

        fetch(url, {headers:{'Accept':'application/json'}})
            .then(function(r){ if(!r.ok) return null; return r.json(); })
            .then(function(json){
                if(!json || !json.ok || !Array.isArray(json.items) || !json.items.length) return;
                json.items.forEach(function(m){
                    appendMsg(m);
                    if(m && m.id) lastId = Math.max(Number(lastId||0), Number(m.id));
                });

                pollFailCount = 0;
                pollDelayMs = 2500;
            })
            .catch(function(){
                pollFailCount = Math.min(pollFailCount + 1, 5);
                pollDelayMs = Math.min(2500 * Math.pow(2, pollFailCount), 20000);
            })
            .finally(function(){
                scheduleNextPoll();
            });
    }

    if(sendForm && sendText){
        sendForm.addEventListener('submit', function(e){
            e.preventDefault();
            var txt = String(sendText.value || '').trim();
            if(!txt) return;

            var fd = new FormData(sendForm);
            fd.set('text', txt);

            fetch(sendForm.getAttribute('action') + '?ajax=1', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: fd,
                credentials: 'same-origin'
            })
                .then(function(r){
                    if(!r.ok) return r.json().catch(function(){ return null; }).then(function(j){ throw j || new Error('HTTP ' + r.status); });
                    return r.json();
                })
                .then(function(json){
                    if(!json || json.ok !== true || !json.item) return;
                    appendMsg(json.item);
                    if(json.item && json.item.id) lastId = Math.max(Number(lastId||0), Number(json.item.id));
                    sendText.value = '';
                    scrollToBottom();
                })
                .catch(function(){
                });
        });

        sendText.addEventListener('keydown', function(e){
            if(e.key !== 'Enter') return;
            if(e.shiftKey) return;
            e.preventDefault();
            try { sendForm.requestSubmit(); } catch(err){ sendForm.dispatchEvent(new Event('submit', {cancelable:true, bubbles:true})); }
        });
    }

    document.addEventListener('visibilitychange', function(){
        if(isPageVisible()){
            pollFailCount = 0;
            pollDelayMs = 2500;
            scheduleNextPoll();
        } else {
            stopPolling();
        }
    });

    scrollToBottom();
    scheduleNextPoll();
})();
</script>
@endsection
