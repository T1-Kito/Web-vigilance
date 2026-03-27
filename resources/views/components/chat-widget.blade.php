@php
    $chatWidgetConfig = [
        'brandName' => 'Vigilance',
        'supportName' => 'Nhân viên tư vấn',
        'hotline' => '0982 751 039',
        'zaloPhone' => '0982751039',
        'iconUrl' => asset('images/chatai.png'),
        'zaloIconUrl' => asset('images/zalo.webp'),
        'companyName' => 'CÔNG TY CỔ PHẦN VIGILANCE Việt Nam',
        'companyAddress' => '96 Đường số 14, KDC Him Lam, Phường Tân Hưng, TP.HCM',
        'companyMapsUrl' => 'https://www.google.com/maps/search/?api=1&query=96%20%C4%90%C6%B0%E1%BB%9Dng%20s%E1%BB%91%2014%2C%20KDC%20Him%20Lam%2C%20Ph%C6%B0%E1%BB%9Dng%20T%C3%A2n%20H%C6%B0ng%2C%20TP.HCM',
    ];
@endphp

<div id="vw-chat-widget" class="vw-chat-widget" data-user-id="{{ auth()->check() ? auth()->id() : '' }}" data-brand-name="{{ $chatWidgetConfig['brandName'] }}" data-support-name="{{ $chatWidgetConfig['supportName'] }}" data-hotline="{{ $chatWidgetConfig['hotline'] }}" data-zalo-phone="{{ $chatWidgetConfig['zaloPhone'] }}" data-icon-url="{{ $chatWidgetConfig['iconUrl'] }}" data-company-name="{{ $chatWidgetConfig['companyName'] }}" data-company-address="{{ $chatWidgetConfig['companyAddress'] }}" data-company-maps-url="{{ $chatWidgetConfig['companyMapsUrl'] }}">
    <div class="vw-chat-intro" role="status" aria-live="polite">
        <div class="vw-chat-intro__bubble">
            <div class="vw-chat-intro__title">Chat với nhân viên tư vấn</div>
            <div class="vw-chat-intro__text">Bấm để bắt đầu trò chuyện.</div>
        </div>
    </div>

    <div class="vw-chat-launcher">
        <div class="vw-chat-contact-pop" id="vw-chat-contact-pop" aria-hidden="true">
            <div class="vw-chat-contact-menu" role="menu" aria-label="Chọn kênh liên hệ">
                <button type="button" class="vw-chat-menu-item" data-action="open-chat" role="menuitem">
                    <span class="vw-chat-menu-item__icon vw-chat-menu-item__icon--staff" aria-hidden="true">
                        <img src="{{ $chatWidgetConfig['iconUrl'] }}" alt="">
                    </span>
                    <span class="vw-chat-menu-item__text">Chat với nhân viên</span>
                </button>
                <button type="button" class="vw-chat-menu-item" data-action="open-zalo" role="menuitem">
                    <span class="vw-chat-menu-item__icon vw-chat-menu-item__icon--zalo" aria-hidden="true">
                        <img src="{{ $chatWidgetConfig['zaloIconUrl'] }}" alt="" loading="lazy" decoding="async">
                    </span>
                    <span class="vw-chat-menu-item__text">Liên hệ Zalo</span>
                </button>
            </div>
        </div>
        <button type="button" class="vw-chat-fab" aria-label="Mở menu liên hệ" title="Liên hệ / Chat tư vấn">
            <span class="vw-chat-fab__ring" aria-hidden="true"></span>
            <img class="vw-chat-fab__img" src="{{ $chatWidgetConfig['iconUrl'] }}" alt="Chat" loading="lazy" decoding="async">
        </button>
    </div>

    <div class="vw-chat-panel" role="dialog" aria-label="Chat với nhân viên tư vấn" aria-modal="false">
        <div class="vw-chat-header">
            <div class="vw-chat-header__avatar" aria-hidden="true">
                <img class="vw-chat-header__avatar-img" src="{{ $chatWidgetConfig['iconUrl'] }}" alt="" loading="lazy" decoding="async">
            </div>
            <div class="vw-chat-header__title">
                <div class="vw-chat-header__name">{{ $chatWidgetConfig['supportName'] }}</div>
                <div class="vw-chat-header__sub">Em ở đây để hỗ trợ cho mình ạ</div>
            </div>
            <button type="button" class="vw-chat-close" aria-label="Đóng chat">&times;</button>
        </div>

        <div class="vw-chat-body" aria-live="polite"></div>

        <div class="vw-chat-guest-info" aria-hidden="true">
            <div class="vw-chat-guest-info__title">Thông tin cơ bản</div>
            <div class="vw-chat-guest-info__grid">
                <input type="text" class="vw-chat-guest-name" placeholder="Nhập tên của bạn *" maxlength="80" aria-label="Nhập tên của bạn">
                <input type="tel" class="vw-chat-guest-phone" placeholder="Nhập số điện thoại của bạn *" maxlength="20" aria-label="Nhập số điện thoại của bạn">
                <input type="email" class="vw-chat-guest-email" placeholder="Nhập Gmail của bạn *" maxlength="120" aria-label="Nhập Gmail của bạn" autocomplete="email">
            </div>
            <div class="vw-chat-guest-info__hint">Vui lòng nhập tên, số điện thoại và Gmail để bắt đầu.</div>
            <div class="vw-chat-guest-info__actions">
                <button type="button" class="vw-chat-guest-start" aria-label="Bắt đầu trò chuyện">
                    <span>BẮT ĐẦU TRÒ CHUYỆN</span>
                </button>
            </div>
        </div>

        <form class="vw-chat-input" autocomplete="off">
            <textarea class="vw-chat-text" placeholder="Tin nhắn" maxlength="2000" aria-label="Nhập tin nhắn" rows="2"></textarea>
            <button type="submit" class="vw-chat-send" aria-label="Gửi">
                <span>Gửi</span>
            </button>
        </form>

        <div class="vw-chat-footer">
            <a class="vw-chat-link" href="tel:{{ $chatWidgetConfig['hotline'] }}">Hotline: {{ $chatWidgetConfig['hotline'] }}</a>
            <a class="vw-chat-link" href="https://zalo.me/{{ $chatWidgetConfig['zaloPhone'] }}" target="_blank" rel="noopener">Zalo</a>
        </div>
    </div>
</div>

<style>
    .vw-chat-widget{position:fixed;right:18px;bottom:18px;z-index:9999;font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif}
    @media (max-width: 768px){.vw-chat-widget{right:14px;bottom:calc(14px + var(--app-bottom-nav-height, 74px))}}

    .vw-chat-launcher{position:relative;display:flex;flex-direction:column;align-items:flex-end}
    .vw-chat-contact-pop{position:absolute;right:0;bottom:100%;display:none;flex-direction:column;align-items:flex-end;gap:10px;margin-bottom:10px;z-index:2}
    .vw-chat-widget.is-menu .vw-chat-contact-pop{display:flex}
    .vw-chat-contact-menu{background:#fff;border-radius:16px;box-shadow:0 14px 44px rgba(15,23,42,.22);border:1px solid rgba(15,23,42,.10);overflow:hidden;min-width:240px;padding:6px 0}
    .vw-chat-menu-item{display:flex;align-items:center;gap:12px;width:100%;border:0;background:transparent;padding:12px 14px;cursor:pointer;text-align:left;font:inherit;font-size:14px;color:#0f172a;transition:background .12s ease}
    .vw-chat-menu-item:hover,.vw-chat-menu-item:focus{background:rgba(43,47,142,.07);outline:none}
    .vw-chat-menu-item__icon{flex:0 0 40px;width:40px;height:40px;border-radius:999px;display:flex;align-items:center;justify-content:center;overflow:hidden}
    .vw-chat-menu-item__icon--staff{background:transparent;border-radius:0}
    .vw-chat-menu-item__icon--staff img{width:40px;height:40px;object-fit:contain;display:block}
    /* Zalo.webp thường có viền trắng rộng → scale để logo nhìn cân với icon chat */
    .vw-chat-menu-item__icon--zalo{
        background:#fff;
        padding:0;
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 0 0 1px rgba(15,23,42,.08);
    }
    .vw-chat-menu-item__icon--zalo img{
        width:100%;
        height:100%;
        object-fit:contain;
        display:block;
        transform:scale(1.72);
        transform-origin:center center;
    }
    .vw-chat-menu-item__text{line-height:1.25}

    .vw-chat-intro{position:absolute;right:0;bottom:78px;max-width:320px;display:none}
    .vw-chat-widget.is-intro .vw-chat-intro{display:block;animation:vwIntroIn .18s ease-out}
    @keyframes vwIntroIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
    .vw-chat-intro__bubble{background:#fff;border:1px solid rgba(15,23,42,.10);border-radius:16px;padding:11px 13px;box-shadow:0 14px 44px rgba(15,23,42,.20);position:relative;margin-bottom: 33px;}
    .vw-chat-intro__bubble:after{content:"";position:absolute;right:26px;bottom:-7px;width:14px;height:14px;background:#fff;border-right:1px solid rgba(15,23,42,.10);border-bottom:1px solid rgba(15,23,42,.10);transform:rotate(45deg)}
    .vw-chat-intro__title{font-weight:900;font-size:13px;color:#0f172a;line-height:1.15;letter-spacing:-.01em}
    .vw-chat-intro__text{margin-top:4px;font-size:12.5px;color:rgba(15,23,42,.72);line-height:1.28;white-space:pre-line}

    .vw-chat-fab{width:72px;height:72px;border-radius:999px;border:0;background:transparent;box-shadow:none;display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;padding:0}
    .vw-chat-fab:focus{outline:2px solid rgba(43,47,142,.25);outline-offset:3px}
    .vw-chat-fab__img{object-fit:contain;filter:drop-shadow(0 10px 18px rgba(15,23,42,.22));transform:translateZ(0);animation:vwFabPulse 1.6s ease-in-out infinite;margin-bottom: 40px;}

    @keyframes vwFabPulse{0%,100%{transform:scale(1);filter:drop-shadow(0 10px 18px rgba(15,23,42,.22))}50%{transform:scale(1.06);filter:drop-shadow(0 16px 26px rgba(15,23,42,.28))}}
    .vw-chat-widget.is-open .vw-chat-fab__img{animation:none}
    @media (prefers-reduced-motion: reduce){.vw-chat-fab__img{animation:none}.vw-chat-fab__ring{animation:none}.vw-chat-fab__ring:before{animation:none}}

    .vw-chat-fab__ring{position:absolute;inset:-10px;border-radius:999px;border:2px solid rgba(43,47,142,.22);animation:vwRing 1.35s ease-out infinite;pointer-events:none}
    .vw-chat-fab__ring:before{content:"";position:absolute;inset:8px;border-radius:999px;border:2px solid rgba(227,0,25,.22);animation:vwRing 1.35s ease-out infinite;animation-delay:.35s}
    .vw-chat-widget.is-open .vw-chat-fab__ring{display:none}
    @keyframes vwRing{0%{transform:scale(.55);opacity:.75}70%{transform:scale(1.05);opacity:0}100%{opacity:0}}

    .vw-chat-panel{position:absolute;right:0;bottom:94px;width:390px;max-width:min(360px, calc(100vw - 28px));background:#fff;border-radius:18px;box-shadow:0 18px 50px rgba(15,23,42,.28);border:1px solid rgba(15,23,42,.10);overflow:hidden;display:none}
    .vw-chat-widget.is-open .vw-chat-panel{display:block;animation:vwChatIn .18s ease-out}
    @keyframes vwChatIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

    .vw-chat-header{background:linear-gradient(135deg, rgba(43,47,142,1), rgba(227,0,25,1));color:#fff;padding:12px 14px;display:flex;align-items:center;gap:12px}
    .vw-chat-header__avatar{width:44px;height:44px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex:0 0 auto;border-radius:10px;background:transparent;box-shadow:none}
    .vw-chat-header__avatar-img{object-fit:contain}
    .vw-chat-header__title{flex:1;min-width:0}
    .vw-chat-header__name{font-weight:800;font-size:15px;line-height:1.1}
    .vw-chat-header__sub{opacity:.92;font-size:12px;margin-top:4px;line-height:1.2}
    .vw-chat-close{border:0;background:rgba(255,255,255,.16);color:#fff;font-size:20px;line-height:1;cursor:pointer;padding:6px 10px;border-radius:12px}
    .vw-chat-close:hover{background:rgba(255,255,255,.22)}

    .vw-chat-body{padding:12px 12px 10px 12px;max-height:330px;overflow:auto;background:radial-gradient(1400px 420px at 50% -10%, rgba(43,47,142,.12), rgba(255,255,255,0)),linear-gradient(180deg, rgba(244,246,250,.82), rgba(255,255,255,1))}
    .vw-chat-body::-webkit-scrollbar{width:10px}
    .vw-chat-body::-webkit-scrollbar-thumb{background:rgba(15,23,42,.14);border-radius:999px;border:3px solid rgba(255,255,255,.65)}
    .vw-chat-body::-webkit-scrollbar-track{background:transparent}

    .vw-chat-msg{display:flex;gap:10px;margin:10px 0}
    .vw-chat-msg--bot{justify-content:flex-start}
    .vw-chat-msg--staff{justify-content:flex-start}
    .vw-chat-msg--user{justify-content:flex-end}
    .vw-chat-msg--system{justify-content:center}
    .vw-chat-bubble{max-width:82%;padding:10px 12px;border-radius:16px;font-size:13px;line-height:1.38;white-space:pre-line}
    .vw-chat-msg--bot .vw-chat-bubble{background:#fff;border:1px solid rgba(15,23,42,.10);box-shadow:0 10px 26px rgba(15,23,42,.08)}
    .vw-chat-msg--staff .vw-chat-bubble{background:#fff;border:1px solid rgba(15,23,42,.10);box-shadow:0 10px 26px rgba(15,23,42,.08)}
    .vw-chat-msg--user .vw-chat-bubble{background:linear-gradient(135deg, rgba(43,47,142,.14), rgba(227,0,25,.10));border:1px solid rgba(43,47,142,.18)}
    .vw-chat-msg--system .vw-chat-bubble{background:rgba(15,23,42,.06);border:1px solid rgba(15,23,42,.10);box-shadow:none}
    .vw-chat-time{font-size:11px;color:rgba(15,23,42,.55);margin-top:4px}

    .vw-chat-prodlist{display:flex;flex-direction:column;gap:10px}
    .vw-chat-prod{display:flex;gap:10px;align-items:center;padding:10px;border-radius:14px;border:1px solid rgba(15,23,42,.10);background:linear-gradient(180deg, rgba(255,255,255,1), rgba(248,250,252,1));text-decoration:none;color:inherit;transition:transform .10s ease,border-color .12s ease,box-shadow .12s ease}
    .vw-chat-prod:hover{border-color:rgba(43,47,142,.32);box-shadow:0 14px 28px rgba(15,23,42,.10);transform:translateY(-1px)}
    .vw-chat-prod__img{width:54px;height:54px;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;flex:0 0 auto;overflow:hidden;border:1px solid rgba(15,23,42,.08)}
    .vw-chat-prod__img img{width:100%;height:100%;object-fit:contain}
    .vw-chat-prod__meta{display:flex;flex-direction:column;gap:2px;min-width:0}
    .vw-chat-prod__name{font-weight:900;font-size:12.8px;line-height:1.25;color:#0f172a;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .vw-chat-prod__note{font-size:12px;color:rgba(15,23,42,.72);font-weight:700}
    .vw-chat-prod__price{font-weight:900;color:rgba(227,0,25,1);font-size:12.6px}
    .vw-chat-prod__cta{font-size:12px;font-weight:900;color:rgba(43,47,142,1)}
    .vw-chat-prodlist__hint{margin-top:2px;font-size:12px;color:rgba(15,23,42,.70);font-weight:800}

    .vw-chat-msg.is-new{animation:vwMsgIn .14s ease-out}
    @keyframes vwMsgIn{from{opacity:0}to{opacity:1}}

    .vw-chat-typing{display:flex;gap:6px;align-items:center;padding:10px 12px;border-radius:16px;background:#fff;border:1px solid rgba(15,23,42,.10);box-shadow:0 10px 26px rgba(15,23,42,.08);max-width:160px}
    .vw-chat-typing__dot{width:6px;height:6px;border-radius:999px;background:rgba(15,23,42,.45);animation:vwDot 1.05s ease-in-out infinite}
    .vw-chat-typing__dot:nth-child(2){animation-delay:.15s}
    .vw-chat-typing__dot:nth-child(3){animation-delay:.30s}
    @keyframes vwDot{0%,100%{transform:translateY(0);opacity:.55}50%{transform:translateY(-3px);opacity:1}}

    .vw-chat-input{display:flex;gap:8px;padding:10px 12px;background:#fff;border-top:1px solid rgba(15,23,42,.08)}
    .vw-chat-text{flex:1;border:1px solid rgba(15,23,42,.14);border-radius:14px;padding:10px 12px;font-size:13px;outline:none;background:rgba(248,250,252,1);resize:none;min-height:44px;max-height:110px;line-height:1.35}
    .vw-chat-text:focus{border-color:rgba(43,47,142,.45);box-shadow:0 0 0 4px rgba(43,47,142,.10)}
    .vw-chat-send{border:0;border-radius:14px;padding:10px 14px;font-size:13px;font-weight:900;background:linear-gradient(135deg, rgba(227,0,25,1), rgba(43,47,142,1));color:#fff;cursor:pointer;box-shadow:0 12px 24px rgba(15,23,42,.14)}
    .vw-chat-send:hover{filter:brightness(1.02)}
    .vw-chat-send:active{transform:translateY(1px)}

    .vw-chat-guest-info{display:none;padding:12px 12px 0 12px;background:#fff}
    .vw-chat-widget.is-need-guest-info .vw-chat-guest-info{display:block}
    .vw-chat-widget.is-need-guest-info .vw-chat-input{display:none}
    .vw-chat-guest-info__title{font-weight:900;color:#0f172a;margin:2px 0 10px 0;font-size:13px}
    .vw-chat-guest-info__grid{display:grid;grid-template-columns:1fr;gap:10px}
    .vw-chat-guest-info__grid input{width:100%;border:1px solid rgba(15,23,42,.14);border-radius:12px;padding:10px 12px;font-size:13px;outline:none;background:rgba(248,250,252,1)}
    .vw-chat-guest-info__grid input:focus{border-color:rgba(43,47,142,.45);box-shadow:0 0 0 4px rgba(43,47,142,.10)}
    .vw-chat-guest-info__hint{margin-top:8px;color:rgba(15,23,42,.68);font-size:12.5px;line-height:1.25}
    .vw-chat-guest-info__actions{margin-top:12px;display:flex;justify-content:center}
    .vw-chat-guest-start{border:0;border-radius:999px;padding:10px 18px;font-size:13px;font-weight:900;background:linear-gradient(135deg, rgba(227,0,25,1), rgba(43,47,142,1));color:#fff;cursor:pointer;box-shadow:0 12px 24px rgba(15,23,42,.14)}
    .vw-chat-guest-start:hover{filter:brightness(1.02)}
    .vw-chat-guest-start:active{transform:translateY(1px)}

    .vw-chat-system-row{display:flex;align-items:flex-start;gap:10px}
    .vw-chat-system-ic{flex:0 0 auto;width:22px;height:22px;border-radius:999px;background:rgba(43,47,142,.12);display:flex;align-items:center;justify-content:center;margin-top:2px}
    .vw-chat-system-ic svg{width:14px;height:14px;display:block;fill:rgba(43,47,142,1)}

    .vw-chat-footer{display:flex;justify-content:space-between;gap:10px;padding:10px 12px 12px 12px;background:#fff}
    .vw-chat-link{font-size:12px;font-weight:800;text-decoration:none;color:rgba(43,47,142,1)}
    .vw-chat-link:hover{color:rgba(227,0,25,1)}
</style>

<script>
(function(){
    var root = document.getElementById('vw-chat-widget');
    if(!root) return;

    var fab = root.querySelector('.vw-chat-fab');
    var contactPop = root.querySelector('#vw-chat-contact-pop');
    var panel = root.querySelector('.vw-chat-panel');
    var closeBtn = root.querySelector('.vw-chat-close');
    var body = root.querySelector('.vw-chat-body');
    var form = root.querySelector('.vw-chat-input');
    var input = root.querySelector('.vw-chat-text');
    var guestInfoBox = root.querySelector('.vw-chat-guest-info');
    var guestNameInput = root.querySelector('.vw-chat-guest-name');
    var guestPhoneInput = root.querySelector('.vw-chat-guest-phone');
    var guestEmailInput = root.querySelector('.vw-chat-guest-email');
    var guestStartBtn = root.querySelector('.vw-chat-guest-start');
    var guestHintEl = root.querySelector('.vw-chat-guest-info__hint');
    var introTitleEl = root.querySelector('.vw-chat-intro__title');
    var introTextEl = root.querySelector('.vw-chat-intro__text');

    var authedUserId = (root.getAttribute('data-user-id') || '').trim();
    var isAuthed = !!authedUserId;

    var pollTimer = null;
    var pollDelayMs = 2500;
    var pollFailCount = 0;
    var lastServerId = 0;
    var renderedIds = Object.create(null);
    var ackKey = 'vw_chat_widget_ack_v1:' + (isAuthed ? ('u:' + authedUserId) : ('g:' + getGuestId()));

    var LS_KEY = 'vw_chat_widget_v1';
    var LS_INTRO_KEY = 'vw_chat_widget_intro_v1';
    var SS_INTRO_COUNT_KEY = 'vw_chat_widget_intro_count_v1';
    var cfg = {
        brandName: root.getAttribute('data-brand-name') || 'Vigilance',
        supportName: root.getAttribute('data-support-name') || 'Tư vấn',
        hotline: root.getAttribute('data-hotline') || '',
        zaloPhone: root.getAttribute('data-zalo-phone') || '',
        companyName: root.getAttribute('data-company-name') || '',
        companyAddress: root.getAttribute('data-company-address') || '',
        companyMapsUrl: root.getAttribute('data-company-maps-url') || '',
    };

    var introLines = [
        {title: '👋 Bạn cần tư vấn không?', text: 'Bấm vào đây để mình hỗ trợ nhanh nha.'},
        {title: 'Xin chào!', text: 'Tôi có thể giúp gì cho bạn?'}
    ];
    var introIndex = 0;
    var introTimers = {show: null, hide: null, cycle: null};
    var lastInteractionAt = Date.now();
    var lastUserMessage = '';

    var GUEST_ID_KEY = 'vw_chat_guest_id_v1';
    var GUEST_INFO_KEY = 'vw_chat_guest_info_v1';
    function getGuestId(){
        try {
            var existing = localStorage.getItem(GUEST_ID_KEY);
            if(existing && String(existing).length >= 8) return String(existing);
            var id = 'g_' + Math.random().toString(36).slice(2) + Date.now().toString(36);
            localStorage.setItem(GUEST_ID_KEY, id);
            return id;
        } catch(e){
            return 'g_' + Math.random().toString(36).slice(2);
        }
    }

    function getGuestInfo(){
        try {
            var raw = localStorage.getItem(GUEST_INFO_KEY);
            if(!raw) return {name:'', phone:'', email:''};
            var obj = JSON.parse(raw);
            return {
                name: String((obj && obj.name) ? obj.name : '').trim(),
                phone: String((obj && obj.phone) ? obj.phone : '').trim(),
                email: String((obj && obj.email) ? obj.email : '').trim()
            };
        } catch(e){
            return {name:'', phone:'', email:''};
        }
    }

    function setGuestInfo(name, phone, email){
        try {
            localStorage.setItem(GUEST_INFO_KEY, JSON.stringify({
                name: String(name || '').trim(),
                phone: String(phone || '').trim(),
                email: String(email || '').trim()
            }));
        } catch(e) {}
    }

    function hasGuestInfo(){
        if(isAuthed) return true;
        var gi = getGuestInfo();
        var nameOk = gi.name.length >= 2;
        var phoneOk = gi.phone.replace(/\D/g, '').length >= 8;
        var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(gi.email || ''));
        return nameOk && phoneOk && emailOk;
    }

    function validateGuestInfo(n, p, e){
        var name = String(n || '').trim();
        var phoneDigits = String(p || '').replace(/\D/g, '');
        var email = String(e || '').trim();

        if(name.length < 2) return {ok:false, field:'name', message:'Vui lòng nhập tên (tối thiểu 2 ký tự).'};
        if(phoneDigits.length < 8) return {ok:false, field:'phone', message:'Vui lòng nhập số điện thoại hợp lệ.'};
        if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return {ok:false, field:'email', message:'Vui lòng nhập Gmail hợp lệ (ví dụ: ten@gmail.com).'};
        return {ok:true, field:'', message:''};
    }

    function guestStartedKey(){
        return 'vw_chat_guest_started_v1:' + getGuestId();
    }

    function isGuestStarted(){
        if(isAuthed) return true;
        try { return localStorage.getItem(guestStartedKey()) === '1'; } catch(e) { return false; }
    }

    function setGuestStarted(){
        if(isAuthed) return;
        try { localStorage.setItem(guestStartedKey(), '1'); } catch(e) {}
    }

    function showGuestInfoGate(show){
        if(isAuthed) return;
        if(show) root.classList.add('is-need-guest-info');
        else root.classList.remove('is-need-guest-info');

        if(guestInfoBox) guestInfoBox.setAttribute('aria-hidden', show ? 'false' : 'true');
    }

    function syncGuestInfoUi(){
        if(isAuthed) {
            showGuestInfoGate(false);
            return;
        }
        var gi = getGuestInfo();
        if(guestNameInput && !guestNameInput.value) guestNameInput.value = gi.name || '';
        if(guestPhoneInput && !guestPhoneInput.value) guestPhoneInput.value = gi.phone || '';
        if(guestEmailInput && !guestEmailInput.value) guestEmailInput.value = gi.email || '';

        var v = validateGuestInfo(gi.name, gi.phone, gi.email);
        if(guestHintEl){
            guestHintEl.textContent = v.ok ? 'Vui lòng nhập tên, số điện thoại và Gmail để bắt đầu.' : v.message;
            guestHintEl.style.color = v.ok ? 'rgba(15,23,42,.68)' : 'rgba(227,0,25,1)';
        }
        if(guestStartBtn){
            guestStartBtn.disabled = !v.ok;
            guestStartBtn.style.opacity = v.ok ? '1' : '.6';
            guestStartBtn.style.cursor = v.ok ? 'pointer' : 'not-allowed';
        }
        showGuestInfoGate(!isGuestStarted());
    }

    function maybeSendGuestInfoToAdminOnce(){
        if(isAuthed) return;
        if(!hasGuestInfo()) return;
        if(!isGuestStarted()) return;

        var gid = getGuestId();
        var sentKey = 'vw_chat_guest_info_sent_v1:' + gid;
        try {
            if(localStorage.getItem(sentKey) === '1') return;
        } catch(e) {}

        var gi = getGuestInfo();
        var text = 'Thông tin khách: ' + gi.name + ' | ' + gi.phone + ' | ' + gi.email;

        saveServerMessage('system', text, function(item){
            try { localStorage.setItem(sentKey, '1'); } catch(e) {}
            var sid = item && item.id ? Number(item.id) : 0;
            if(sid) lastServerId = Math.max(lastServerId, sid);
            if(sid) renderedIds[String(sid)] = true;
        });
    }

    function setIntroDismissed(){
        try { localStorage.setItem(LS_INTRO_KEY, '1'); } catch(e) {}
    }

    function isIntroDismissed(){
        try { return localStorage.getItem(LS_INTRO_KEY) === '1'; } catch(e) { return false; }
    }

    function showIntro(){
        if(root.classList.contains('is-open')) return;
        if(!introTitleEl || !introTextEl) return;

        introIndex = (introIndex + 1) % introLines.length;
        introTitleEl.textContent = introLines[introIndex].title;
        introTextEl.textContent = introLines[introIndex].text;

        root.classList.add('is-intro');
        if(introTimers.hide) clearTimeout(introTimers.hide);
        introTimers.hide = setTimeout(function(){
            root.classList.remove('is-intro');
        }, 5200);
    }

    function hideIntro(){
        root.classList.remove('is-intro');
    }

    function clearIntroSchedule(){
        if(introTimers.show) clearTimeout(introTimers.show);
        if(introTimers.hide) clearTimeout(introTimers.hide);
        if(introTimers.cycle) clearInterval(introTimers.cycle);
        introTimers.show = null;
        introTimers.hide = null;
        introTimers.cycle = null;
    }

    function getIntroCount(){
        try { return parseInt(sessionStorage.getItem(SS_INTRO_COUNT_KEY) || '0', 10) || 0; } catch(e) { return 0; }
    }

    function incIntroCount(){
        try { sessionStorage.setItem(SS_INTRO_COUNT_KEY, String(getIntroCount() + 1)); } catch(e) {}
    }

    function scheduleIntroOnInactivity(){
        clearIntroSchedule();

        function randInt(min, max){
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        var INACTIVITY_MIN_MS = 5000;
        var INACTIVITY_MAX_MS = 7000;

        function scheduleNext(){
            if(introTimers.show) clearTimeout(introTimers.show);
            var delay = randInt(INACTIVITY_MIN_MS, INACTIVITY_MAX_MS);
            introTimers.show = setTimeout(function(){
                if(root.classList.contains('is-open')){
                    scheduleNext();
                    return;
                }
                showIntro();
                scheduleNext();
            }, delay);
        }

        scheduleNext();
    }

    function nowTime(){
        try {
            return new Date().toLocaleTimeString('vi-VN', {hour:'2-digit', minute:'2-digit'});
        } catch(e){
            return '';
        }
    }

    function escapeHtml(str){
        return String(str)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/\"/g,'&quot;')
            .replace(/'/g,'&#039;');
    }

    function renderProductCards(items){
        var cards = items.map(function(it, idx){
            var name = String(it.name || '').trim();
            var url = String(it.url || '').trim();
            var img = String(it.image_url || '').trim();
            var p = (it.final_price != null ? it.final_price : it.price);
            var safeName = escapeHtml(name);
            var safeUrl = escapeHtml(url);
            var safeImg = escapeHtml(img);
            var priceText = escapeHtml(formatVnd(p));

            var capacityLine = '';
            if(it.user_capacity != null && String(it.user_capacity).trim() !== ''){
                capacityLine = '<span class="vw-chat-prod__note">Người dùng: ' + escapeHtml(String(it.user_capacity)) + '</span>';
            }
            if(it.fingerprint_capacity != null && String(it.fingerprint_capacity).trim() !== ''){
                capacityLine = '<span class="vw-chat-prod__note">Vân tay: ' + escapeHtml(String(it.fingerprint_capacity)) + '</span>';
            }

            return (
                '<a class="vw-chat-prod" href="' + safeUrl + '" target="_blank" rel="noopener">' +
                    '<span class="vw-chat-prod__img">' +
                        (safeImg ? ('<img src="' + safeImg + '" alt="' + safeName + '" loading="lazy" decoding="async">') : '') +
                    '</span>' +
                    '<span class="vw-chat-prod__meta">' +
                        '<span class="vw-chat-prod__name">' + safeName + '</span>' +
                        '<span class="vw-chat-prod__price">' + priceText + '</span>' +
                        capacityLine +
                        '<span class="vw-chat-prod__cta">Bấm để xem</span>' +
                    '</span>' +
                '</a>'
            );
        }).join('');

        return (
            '<div class="vw-chat-prodlist">' +
                cards +
            '</div>'
        );
    }

    function normalizeText(str){
        var s = String(str || '').toLowerCase();
        try {
            s = s.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        } catch(e) {}
        return s.replace(/đ/g, 'd');
    }

    function formatVnd(n){
        var num = Number(n || 0);
        try {
            return num.toLocaleString('vi-VN') + 'đ';
        } catch(e){
            return String(num) + 'đ';
        }
    }

    function lookupProducts(rawQuery, cb){
        var q = String(rawQuery || '').trim();
        if(q.length < 2) return cb(null);
        fetch('/api/chat/product-lookup?q=' + encodeURIComponent(q), {headers:{'Accept':'application/json'}})
            .then(function(r){
                if(!r.ok) return null;
                return r.json();
            })
            .then(function(json){
                if(!json || !json.ok || !Array.isArray(json.items)) return cb(null);
                cb(json.items);
            })
            .catch(function(){
                cb(null);
            });
    }

    function lookupProductDetails(rawQuery, cb){
        var q = String(rawQuery || '').trim();
        if(q.length < 2) return cb(null);
        fetch('/api/chat/product-details?q=' + encodeURIComponent(q) + '&limit=2', {headers:{'Accept':'application/json'}})
            .then(function(r){
                if(!r.ok) return null;
                return r.json();
            })
            .then(function(json){
                if(!json || !json.ok || !Array.isArray(json.items)) return cb(null);
                cb(json.items);
            })
            .catch(function(){ cb(null); });
    }

    function specLookup(rawKeyword, cb){
        var q = String(rawKeyword || '').trim();
        if(q.length < 2) return cb(null);
        fetch('/api/chat/spec-lookup?q=' + encodeURIComponent(q) + '&limit=3', {headers:{'Accept':'application/json'}})
            .then(function(r){
                if(!r.ok) return null;
                return r.json();
            })
            .then(function(json){
                if(!json || !json.ok || !Array.isArray(json.items)) return cb(null);
                cb(json.items);
            })
            .catch(function(){ cb(null); });
    }

    function lookupFingerprint(min, cb){
        var m = Number(min || 0);
        if(!isFinite(m) || m <= 0) return cb(null);
        fetch('/api/chat/fingerprint-lookup?min=' + encodeURIComponent(String(m)), {headers:{'Accept':'application/json'}})
            .then(function(r){
                if(!r.ok) return null;
                return r.json();
            })
            .then(function(json){
                if(!json || !json.ok || !Array.isArray(json.items)) return cb(null);
                cb(json.items);
            })
            .catch(function(){ cb(null); });
    }

    function lookupUserCapacity(min, cb){
        var m = Number(min || 0);
        if(!isFinite(m) || m <= 0) return cb(null);
        fetch('/api/chat/user-capacity-lookup?min=' + encodeURIComponent(String(m)), {headers:{'Accept':'application/json'}})
            .then(function(r){
                if(!r.ok) return null;
                return r.json();
            })
            .then(function(json){
                if(!json || !json.ok || !Array.isArray(json.items)) return cb(null);
                cb(json.items);
            })
            .catch(function(){ cb(null); });
    }

    function addMsg(type, text, opts){
        opts = opts || {};

        if(!isAuthed && type === 'system' && /^\s*Thông tin khách\s*:/i.test(String(text || ''))){
            if(opts.id != null){
                renderedIds[String(opts.id)] = true;
            }
            return;
        }

        var shouldScroll = isNearBottom();

        var wrap = document.createElement('div');
        wrap.className = 'vw-chat-msg vw-chat-msg--' + type;
        if(opts.isNew) wrap.classList.add('is-new');

        if(opts.id != null){
            wrap.setAttribute('data-id', String(opts.id));
            renderedIds[String(opts.id)] = true;
        }

        var bubble = document.createElement('div');
        bubble.className = 'vw-chat-bubble';
        if(opts && opts.html != null){
            bubble.innerHTML = String(opts.html);
        } else {
            bubble.textContent = text;
        }

        var time = document.createElement('div');
        time.className = 'vw-chat-time';
        time.textContent = nowTime();

        var stack = document.createElement('div');
        stack.appendChild(bubble);
        stack.appendChild(time);

        wrap.appendChild(stack);
        body.appendChild(wrap);
        if(shouldScroll) scrollToBottom();

        if(!opts.skipPersist) persist();
    }

    function isNearBottom(){
        try {
            var threshold = 80;
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

    function escapeHtml(str){
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderSystemWithIcon(text){
        var safe = escapeHtml(text);
        return (
            '<div class="vw-chat-system-row">' +
                '<span class="vw-chat-system-ic" aria-hidden="true">' +
                    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.48 2 2 6.03 2 11c0 2.38 1.05 4.55 2.77 6.18L4 22l4.23-2.32c1.15.32 2.42.5 3.77.5 5.52 0 10-4.03 10-9S17.52 2 12 2zm1 12h-2v-2h2v2zm0-4h-2V6h2v4z"/></svg>' +
                '</span>' +
                '<span>' + safe + '</span>' +
            '</div>'
        );
    }

    function removeTyping(){
        var t = body.querySelector('.vw-chat-msg--typing');
        if(t) t.remove();
    }

    function showTyping(){
        removeTyping();
        var shouldScroll = isNearBottom();
        var wrap = document.createElement('div');
        wrap.className = 'vw-chat-msg vw-chat-msg--bot vw-chat-msg--typing';

        var bubble = document.createElement('div');
        bubble.className = 'vw-chat-typing';

        var d1 = document.createElement('span');
        d1.className = 'vw-chat-typing__dot';
        var d2 = document.createElement('span');
        d2.className = 'vw-chat-typing__dot';
        var d3 = document.createElement('span');
        d3.className = 'vw-chat-typing__dot';

        bubble.appendChild(d1);
        bubble.appendChild(d2);
        bubble.appendChild(d3);
        wrap.appendChild(bubble);

        body.appendChild(wrap);
        if(shouldScroll) scrollToBottom();
    }

    function getState(){
        try {
            var raw = localStorage.getItem(LS_KEY);
            if(!raw) return {open:false, msgs:[]};
            var parsed = JSON.parse(raw);
            if(!parsed || typeof parsed !== 'object') return {open:false, msgs:[]};
            if(!Array.isArray(parsed.msgs)) parsed.msgs = [];
            return parsed;
        } catch(e){
            return {open:false, msgs:[]};
        }
    }

    function persist(){
        if(isAuthed) return;
        var msgs = [];
        body.querySelectorAll('.vw-chat-msg').forEach(function(el){
            var isUser = el.classList.contains('vw-chat-msg--user');
            var t = (el.querySelector('.vw-chat-bubble') || {}).textContent || '';
            if(!t) return;
            if(el.classList.contains('vw-chat-msg--system')) msgs.push({type:'system', text:t});
            else msgs.push({type: isUser ? 'user' : 'staff', text: t});
        });
        var state = {open: root.classList.contains('is-open'), msgs: msgs.slice(-30)};
        try { localStorage.setItem(LS_KEY, JSON.stringify(state)); } catch(e) {}
    }

    function restore(){
        if(isAuthed){
            body.innerHTML = '';
            loadServerMessages(function(items){
                if(items && items.length){
                    renderedIds = Object.create(null);
                    items.forEach(function(m){
                        addMsg(m.type, m.text, {id:m.id, isNew:false, skipPersist:true, skipServer:true});
                    });
                } else {
                    addMsg('system', 'Chào bạn, bạn cần hỗ trợ gì ạ? Bạn có thể để lại nội dung, nhân viên sẽ phản hồi sớm nhất.', {isNew:false, skipServer:true});
                }
            });
            return;
        }

        var state = getState();
        if(state.msgs && state.msgs.length){
            body.innerHTML = '';
            renderedIds = Object.create(null);
            state.msgs.forEach(function(m){
                addMsg(m.type, m.text, {isNew:false, skipPersist:true});
            });
            persist();
        } else {
            body.innerHTML = '';
            addMsg('system', 'Chào bạn, bạn cần hỗ trợ gì ạ? Bạn có thể để lại nội dung, nhân viên sẽ phản hồi sớm nhất.', {isNew:false});
        }

        if(state.open){
            root.classList.add('is-open');
        }

        refreshFromServer();
    }

    function refreshFromServer(){
        loadServerMessages(function(items){
            if(!items || !items.length) return;
            body.innerHTML = '';
            renderedIds = Object.create(null);
            items.forEach(function(m){
                addMsg(m.type, m.text, {id:m.id, isNew:false, skipPersist:true, skipServer:true});
            });
            var last = items[items.length - 1];
            lastServerId = last && last.id ? Number(last.id) : lastServerId;
            persist();
        });
    }

    function isPageVisible(){
        try {
            return document.visibilityState === 'visible';
        } catch(e){
            return true;
        }
    }

    function scheduleNextPoll(){
        stopPolling();
        if(!root.classList.contains('is-open')) return;
        if(!isPageVisible()) return;

        pollTimer = setTimeout(function(){
            pollNewMessages();
        }, pollDelayMs);
    }

    function pollNewMessages(){
        if(!root.classList.contains('is-open')) return;
        if(!isPageVisible()) return;

        var url = '/api/chat/messages?limit=50';
        if(!isAuthed){
            url += '&guest_id=' + encodeURIComponent(getGuestId());
        }

        fetch(url, {headers:{'Accept':'application/json'}})
            .then(function(r){ if(!r.ok) return null; return r.json(); })
            .then(function(json){
                if(!json || !json.ok || !Array.isArray(json.items)) return;

                var items = json.items;
                if(items && items.length){
                    var last = items[items.length - 1];
                    var newLastId = last && last.id ? Number(last.id) : 0;
                    if(newLastId && newLastId > lastServerId){
                        items.forEach(function(m){
                            var mid = m && m.id ? Number(m.id) : 0;
                            if(mid && mid > lastServerId && !renderedIds[String(mid)]){
                                addMsg(m.type, m.text, {id:mid, isNew:true, skipPersist:true, skipServer:true});
                            }
                        });
                        lastServerId = newLastId;
                        persist();
                    }
                }

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

    function startPolling(){
        pollFailCount = 0;
        pollDelayMs = 2500;
        scheduleNextPoll();
    }

    function stopPolling(){
        if(pollTimer){
            clearTimeout(pollTimer);
            pollTimer = null;
        }
    }

    function getCsrfToken(){
        var el = document.querySelector('meta[name="csrf-token"]');
        return el ? (el.getAttribute('content') || '') : '';
    }

    function loadServerMessages(cb){
        var url = '/api/chat/messages?limit=50';
        if(!isAuthed){
            url += '&guest_id=' + encodeURIComponent(getGuestId());
        }
        fetch(url, {headers:{'Accept':'application/json'}})
            .then(function(r){ if(!r.ok) return null; return r.json(); })
            .then(function(json){
                if(!json || !json.ok || !Array.isArray(json.items)) return cb(null);
                cb(json.items);
            })
            .catch(function(){ cb(null); });
    }

    function saveServerMessage(type, text, cb){
        var token = getCsrfToken();
        fetch('/api/chat/messages', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({type: type, text: text, guest_id: isAuthed ? null : getGuestId()})
        })
            .then(function(r){ if(!r.ok) return null; return r.json(); })
            .then(function(json){
                if(!json || !json.ok || !json.item || !json.item.id) return;
                if(typeof cb === 'function') cb(json.item);
            })
            .catch(function(){});
    }

    function setContactPopHidden(hidden){
        if(!contactPop) return;
        contactPop.setAttribute('aria-hidden', hidden ? 'true' : 'false');
    }

    function closeMenu(){
        root.classList.remove('is-menu');
        setContactPopHidden(true);
    }

    function openMenu(){
        root.classList.add('is-menu');
        setContactPopHidden(false);
        hideIntro();
        clearIntroSchedule();
    }

    function open(){
        root.classList.remove('is-menu');
        setContactPopHidden(true);
        root.classList.add('is-open');
        hideIntro();
        clearIntroSchedule();
        syncGuestInfoUi();
        maybeSendGuestInfoToAdminOnce();
        refreshFromServer();
        startPolling();
        persist();
        setTimeout(function(){ try{ input.focus(); }catch(e){} }, 60);
    }

    function close(){
        root.classList.remove('is-open');
        stopPolling();
        persist();
        scheduleIntroOnInactivity();
    }

    document.addEventListener('visibilitychange', function(){
        if(!root.classList.contains('is-open')) return;
        if(isPageVisible()) startPolling();
        else stopPolling();
    });

    function toggle(){
        if(root.classList.contains('is-open')) close();
        else if(root.classList.contains('is-menu')) closeMenu();
        else openMenu();
    }

    function onSend(text){
        var t = String(text || '').trim();
        if(!t) return;

        if(!isAuthed && (!hasGuestInfo() || !isGuestStarted())){
            syncGuestInfoUi();
            try { if(guestNameInput) guestNameInput.focus(); } catch(e) {}
            return;
        }

        if(!isAuthed){
            var n = guestNameInput ? guestNameInput.value : '';
            var p = guestPhoneInput ? guestPhoneInput.value : '';
            var e = guestEmailInput ? guestEmailInput.value : '';
            setGuestInfo(n, p, e);
            showGuestInfoGate(false);
            maybeSendGuestInfoToAdminOnce();
        }

        lastUserMessage = t;
        addMsg('user', t, {isNew:true, skipServer:true});
        saveServerMessage('user', t, function(item){
            var sid = item && item.id ? Number(item.id) : 0;
            if(sid) lastServerId = Math.max(lastServerId, sid);
            if(sid) renderedIds[String(sid)] = true;
        });

        try {
            if(!sessionStorage.getItem(ackKey)){
                sessionStorage.setItem(ackKey, '1');
                systemMessage('Đã nhận tin nhắn. Nhân viên tư vấn sẽ phản hồi sớm nhất. Bạn có thể để lại SĐT/Zalo để tiện liên hệ.');
            }
        } catch(e) {}
    }

    function systemMessage(text){
        addMsg('system', text, {isNew:true});
    }

    fab.addEventListener('click', function(e){
        e.stopPropagation();
        toggle();
    });
    closeBtn.addEventListener('click', close);

    root.querySelectorAll('.vw-chat-menu-item[data-action]').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.stopPropagation();
            var act = btn.getAttribute('data-action');
            if(act === 'open-chat'){
                closeMenu();
                open();
                return;
            }
            if(act === 'open-zalo'){
                closeMenu();
                var zp = String(cfg.zaloPhone || '').replace(/\D/g, '');
                if(zp){
                    try {
                        window.open('https://zalo.me/' + zp, '_blank', 'noopener');
                    } catch(err){
                        location.href = 'https://zalo.me/' + zp;
                    }
                }
            }
        });
    });

    form.addEventListener('submit', function(e){
        e.preventDefault();
        var q = (input.value || '').trim();
        if(!q) return;
        input.value = '';
        onSend(q);
    });

    input.addEventListener('keydown', function(e){
        if(e.key !== 'Enter') return;
        if(e.shiftKey) return;
        e.preventDefault();
        try {
            form.requestSubmit();
        } catch(err){
            form.dispatchEvent(new Event('submit', {cancelable:true, bubbles:true}));
        }
    });

    function onGuestInfoChanged(){
        if(isAuthed) return;
        var n = guestNameInput ? guestNameInput.value : '';
        var p = guestPhoneInput ? guestPhoneInput.value : '';
        var e = guestEmailInput ? guestEmailInput.value : '';
        setGuestInfo(n, p, e);
        syncGuestInfoUi();
    }

    if(guestNameInput) guestNameInput.addEventListener('input', onGuestInfoChanged);
    if(guestPhoneInput) guestPhoneInput.addEventListener('input', onGuestInfoChanged);
    if(guestEmailInput) guestEmailInput.addEventListener('input', onGuestInfoChanged);

    if(guestStartBtn) guestStartBtn.addEventListener('click', function(){
        if(isAuthed) return;
        var n = guestNameInput ? guestNameInput.value : '';
        var p = guestPhoneInput ? guestPhoneInput.value : '';
        var e = guestEmailInput ? guestEmailInput.value : '';
        setGuestInfo(n, p, e);
        var v = validateGuestInfo(n, p, e);
        if(!v.ok){
            syncGuestInfoUi();
            try {
                if(v.field === 'name' && guestNameInput) guestNameInput.focus();
                else if(v.field === 'phone' && guestPhoneInput) guestPhoneInput.focus();
                else if(guestEmailInput) guestEmailInput.focus();
            } catch(err) {}
            return;
        }

        setGuestStarted();
        showGuestInfoGate(false);
        maybeSendGuestInfoToAdminOnce();
        var gi = getGuestInfo();
        var name = String(gi.name || '').trim();
        var greet = name ? ('Xin chào ' + name + ' 👋\nMình là nhân viên tư vấn của ' + String(cfg.brandName || '') + '. Bạn cần hỗ trợ gì ạ?')
            : ('Xin chào 👋\nMình là nhân viên tư vấn của ' + String(cfg.brandName || '') + '. Bạn cần hỗ trợ gì ạ?');
        addMsg('system', greet, {isNew:true, html: renderSystemWithIcon(greet)});
        try { if(input) input.focus(); } catch(e) {}
    });

    document.addEventListener('keydown', function(e){
        if(e.key !== 'Escape') return;
        if(root.classList.contains('is-open')) close();
        else if(root.classList.contains('is-menu')) closeMenu();
    });

    document.addEventListener('click', function(e){
        if(root.contains(e.target)) return;
        if(root.classList.contains('is-open')) close();
        if(root.classList.contains('is-menu')) closeMenu();
    });

    restore();
    syncGuestInfoUi();
    maybeSendGuestInfoToAdminOnce();

    scheduleIntroOnInactivity();
})();
</script>
