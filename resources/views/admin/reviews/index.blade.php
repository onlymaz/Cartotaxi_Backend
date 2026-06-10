@extends('layouts.modern')

@section('title')
    <title>{{ $title }} | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">{{ $title }}</h1>
        <p class="ct-page-subtitle">
            @if(str_contains($title, 'User'))
                Reviews submitted by customers after completed deliveries
            @else
                Customer ratings and comments about riders
            @endif
        </p>
    </div>
    <div class="ct-page-actions">
        <a href="{{ route('reviews.index') }}" class="ct-btn {{ Request::is('user-reviews') ? 'ct-btn-primary' : 'ct-btn-outline' }}">
            <i class="fas fa-user"></i> User Reviews
        </a>
        <a href="{{ route('reviews.riders') }}" class="ct-btn {{ Request::is('rider-reviews') ? 'ct-btn-primary' : 'ct-btn-outline' }}">
            <i class="fas fa-motorcycle"></i> Rider Reviews
        </a>
    </div>
</div>

@if($reviews->count() > 0)
    <div class="ct-card">
        @foreach($reviews as $review)
            @php
                $rr      = $review->reviewRatings->first();
                $comment = $rr ? $rr->comments : null;
                $rating  = $rr ? $rr->rating : null;
                $order   = $rr ? $rr->order : null;
                $otherUser = str_contains($title, 'User') ? optional($order)->rider : $review->user;
            @endphp
            <div class="review-card">
                <div class="review-head">
                    <div class="review-user">
                        <div class="review-avatar">
                            {{ $review->user ? strtoupper(substr($review->user->first_name,0,1).substr($review->user->last_name,0,1)) : '?' }}
                        </div>
                        <div>
                            <div class="review-name">{{ $review->user ? trim($review->user->first_name.' '.$review->user->last_name) : '—' }}</div>
                            <div class="review-email">{{ optional($review->user)->email ?? '' }}</div>
                        </div>
                    </div>
                    <div class="review-meta">
                        @if($order)
                            <span class="review-order">Order #{{ $order->id }}</span>
                        @endif
                        <span class="review-date">{{ $review->created_at ? \Carbon\Carbon::parse($review->created_at)->format('M d, Y') : '' }}</span>
                    </div>
                </div>

                @if($otherUser)
                    <div class="review-subject">
                        <span class="review-subject-label">{{ str_contains($title,'User') ? 'Rider' : 'Reviewer' }}:</span>
                        <span class="review-subject-name">{{ trim(($otherUser->first_name ?? '').' '.($otherUser->last_name ?? '')) }}</span>
                    </div>
                @endif

                @if($comment)
                    <p class="review-comment">"{{ $comment }}"</p>
                @endif

                <div class="review-footer">
                    <span class="review-type-badge">{{ ucfirst($review->types ?? '') }}</span>
                    @if($review->approved)
                        <span class="review-badge approved"><i class="fas fa-check-circle"></i> Approved</span>
                    @else
                        <span class="review-badge pending"><i class="fas fa-clock"></i> Pending</span>
                    @endif
                </div>
            </div>
        @endforeach
        <div class="review-pag">{{ $reviews->links() }}</div>
    </div>
@else
    <div class="ct-card">
        <div class="review-empty">
            <div class="review-empty-icon"><i class="fas fa-star"></i></div>
            <h3>No reviews yet</h3>
            <p>{{ $title }} will appear here once customers submit them after completed deliveries.</p>
        </div>
    </div>
@endif

<style>
    .review-card { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--ct-gray-100); }
    .review-card:last-of-type { border-bottom: none; }
    .review-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 0.75rem; }
    .review-user { display: flex; align-items: center; gap: 0.625rem; }
    .review-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--ct-accent), #65a30d); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.8125rem; font-weight: 700; flex-shrink: 0; }
    .review-name { font-weight: 600; color: var(--ct-gray-900); }
    .review-email { font-size: 0.75rem; color: var(--ct-gray-500); }
    .review-meta { text-align: right; display: flex; flex-direction: column; gap: 0.25rem; align-items: flex-end; }
    .review-order { display: inline-block; padding: 2px 8px; background: #eef2ff; color: #4338ca; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
    .review-date { font-size: 0.75rem; color: var(--ct-gray-500); }
    .review-subject { font-size: 0.8125rem; margin-bottom: 0.5rem; }
    .review-subject-label { font-weight: 600; color: var(--ct-gray-400); font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.04em; margin-right: 0.375rem; }
    .review-subject-name { font-weight: 600; color: var(--ct-gray-800); }
    .review-comment { font-size: 0.875rem; color: var(--ct-gray-700); font-style: italic; margin: 0 0 0.75rem; padding-left: 1rem; border-left: 3px solid var(--ct-accent); }
    .review-footer { display: flex; align-items: center; gap: 0.75rem; }
    .review-type-badge { display: inline-block; padding: 2px 10px; background: var(--ct-gray-100); color: var(--ct-gray-600); border-radius: 999px; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
    .review-badge { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; font-weight: 600; }
    .review-badge.approved { color: #15803d; }
    .review-badge.pending { color: #a16207; }
    .review-pag { padding: 1rem 1.5rem; border-top: 1px solid var(--ct-gray-200); display: flex; justify-content: flex-end; }
    .review-pag svg { width: 14px; height: 14px; }
    .review-pag nav[role="navigation"] { display: inline-flex; align-items: center; gap: 4px; }
    .review-pag span[aria-disabled],.review-pag a[rel],.review-pag span[aria-current] { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 10px; border-radius: 8px; border: 1px solid var(--ct-gray-200); background: var(--ct-white); color: var(--ct-gray-700); font-size: 0.8125rem; text-decoration: none; }
    .review-pag a[rel]:hover { background: var(--ct-gray-50); }
    .review-pag span[aria-current] { background: var(--ct-primary); color: #fff; border-color: var(--ct-primary); }
    .review-pag span[aria-disabled] { color: var(--ct-gray-400); background: var(--ct-gray-50); }
    .review-empty { padding: 4rem 2rem; text-align: center; }
    .review-empty-icon { width: 72px; height: 72px; margin: 0 auto 1rem; border-radius: 50%; background: #fef9c3; color: #ca8a04; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; }
    .review-empty h3 { font-size: 1.125rem; font-weight: 700; color: var(--ct-primary); margin: 0 0 0.375rem; }
    .review-empty p { color: var(--ct-gray-500); font-size: 0.875rem; max-width: 380px; margin: 0 auto; }
</style>
@endsection

@section('scripts')
{!! $html->scripts() !!}
@endsection
