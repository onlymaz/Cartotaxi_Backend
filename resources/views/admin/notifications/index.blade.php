@extends('layouts.modern')
@section('title')<title>Notifications | {{ config('app.name', 'Laravel') }}</title>@stop
@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Notifications</h1>
        <p class="ct-page-subtitle">Your activity feed — @if($unreadCount > 0)<span style="color:var(--ct-accent);font-weight:700;">{{ $unreadCount }} unread</span>@else all caught up @endif</p>
    </div>
    @if($unreadCount > 0)
    <div class="ct-page-actions">
        <button type="button" class="ct-btn ct-btn-outline" id="markAllRead"><i class="fas fa-check-double"></i> Mark all as read</button>
    </div>
    @endif
</div>
<div class="ct-card">
    @if($notifications->count() > 0)
    <div class="notif-list">
        @foreach($notifications as $n)
        <div class="notif-item {{ $n->read ? '' : 'unread' }}" data-id="{{ $n->id }}">
            <div class="notif-icon {{ $n->read ? 'read' : 'unread-icon' }}"><i class="fas fa-bell"></i></div>
            <div class="notif-body">
                @if($n->link)<a href="{{ $n->link }}" class="notif-text">{{ $n->notifications_text }}</a>
                @else<p class="notif-text">{{ $n->notifications_text }}</p>@endif
                <div class="notif-time"><i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</div>
            </div>
            @if(!$n->read)<div class="notif-dot"></div>@endif
        </div>
        @endforeach
    </div>
    <div class="notif-pag">{{ $notifications->links() }}</div>
    @else
    <div class="notif-empty">
        <div class="notif-empty-icon"><i class="fas fa-bell-slash"></i></div>
        <h3>No notifications</h3>
        <p>You're all caught up. New activity will appear here.</p>
    </div>
    @endif
</div>
<style>
.notif-item{display:flex;align-items:flex-start;gap:.875rem;padding:1rem 1.5rem;border-bottom:1px solid var(--ct-gray-100);transition:background .12s}
.notif-item:last-child{border-bottom:none}
.notif-item.unread{background:#f0fdf4}
.notif-item:hover{background:var(--ct-gray-50)}
.notif-item.unread:hover{background:#dcfce7}
.notif-icon{width:40px;height:40px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.875rem}
.notif-icon.unread-icon{background:linear-gradient(135deg,var(--ct-accent),#65a30d);color:#fff}
.notif-icon.read{background:var(--ct-gray-100);color:var(--ct-gray-400)}
.notif-body{flex:1;min-width:0}
.notif-text{font-size:.875rem;color:var(--ct-gray-800);font-weight:500;line-height:1.4;text-decoration:none;display:block;margin:0 0 .25rem}
a.notif-text:hover{color:var(--ct-accent)}
.notif-time{font-size:.75rem;color:var(--ct-gray-500);display:flex;align-items:center;gap:.25rem}
.notif-dot{width:8px;height:8px;border-radius:50%;background:var(--ct-accent);flex-shrink:0;margin-top:4px}
.notif-pag{padding:1rem 1.5rem;border-top:1px solid var(--ct-gray-200);display:flex;justify-content:flex-end}
.notif-pag svg{width:14px;height:14px}
.notif-pag nav[role="navigation"]{display:inline-flex;align-items:center;gap:4px}
.notif-pag span[aria-disabled],.notif-pag a[rel],.notif-pag span[aria-current]{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;border-radius:8px;border:1px solid var(--ct-gray-200);background:var(--ct-white);color:var(--ct-gray-700);font-size:.8125rem;text-decoration:none}
.notif-pag a[rel]:hover{background:var(--ct-gray-50)}
.notif-pag span[aria-current]{background:var(--ct-primary);color:#fff;border-color:var(--ct-primary)}
.notif-pag span[aria-disabled]{color:var(--ct-gray-400);background:var(--ct-gray-50)}
.notif-empty{padding:4rem 2rem;text-align:center}
.notif-empty-icon{width:72px;height:72px;margin:0 auto 1rem;border-radius:50%;background:var(--ct-gray-100);color:var(--ct-gray-400);display:flex;align-items:center;justify-content:center;font-size:1.75rem}
.notif-empty h3{font-size:1.125rem;font-weight:700;color:var(--ct-primary);margin:0 0 .375rem}
.notif-empty p{color:var(--ct-gray-500);font-size:.875rem}
</style>
@endsection
@section('scripts')
<script>
var mrBtn = document.getElementById('markAllRead');
if (mrBtn) mrBtn.addEventListener('click', function(){ window.location.reload(); });
</script>
@endsection
