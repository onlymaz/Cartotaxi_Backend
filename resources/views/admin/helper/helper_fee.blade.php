@extends('layouts.modern')

@section('title')
    <title>Helper Fee | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Helper Fee</h1>
        <p class="ct-page-subtitle">Set the flat fee charged per helper request</p>
    </div>
</div>

<div style="max-width: 480px;">
    <div class="ct-card">
        <div style="padding: 1.5rem;">
            @if(session('success'))
                <div class="fee-alert success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <div class="fee-current">
                <div class="fee-current-label">Current fee</div>
                <div class="fee-current-value">&euro;{{ number_format((float)$fee->fee, 0) }}</div>
                <div class="fee-current-sub">per helper request</div>
            </div>

            <form method="POST" action="{{ url('helper/fee/store') }}" id="feeForm">
                @csrf
                <div style="margin-bottom: 1.25rem;">
                    <label class="fee-label">New fee amount (€)</label>
                    <div class="fee-input-wrap">
                        <span class="fee-prefix">&euro;</span>
                        <input
                            type="number"
                            name="fee"
                            id="fee"
                            value="{{ $fee->fee }}"
                            min="1"
                            step="1"
                            oninput="this.value = Math.round(this.value)"
                            class="fee-input"
                            placeholder="e.g. 20"
                            required>
                    </div>
                </div>
                <button type="submit" class="ct-btn ct-btn-primary" style="width:100%;">
                    <i class="fas fa-save"></i> Update Fee
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .fee-alert { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 500; }
    .fee-alert.success { background: #dcfce7; color: #15803d; }
    .fee-current { text-align: center; padding: 1.5rem 0 1.75rem; }
    .fee-current-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ct-gray-500); font-weight: 600; margin-bottom: 0.375rem; }
    .fee-current-value { font-size: 3rem; font-weight: 800; color: var(--ct-primary); line-height: 1; }
    .fee-current-sub { font-size: 0.8125rem; color: var(--ct-gray-500); margin-top: 0.25rem; }
    .fee-label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--ct-gray-700); margin-bottom: 0.5rem; }
    .fee-input-wrap { position: relative; }
    .fee-prefix { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--ct-gray-500); font-size: 1rem; }
    .fee-input {
        width: 100%; padding: 0.7rem 0.75rem 0.7rem 2rem; border: 1px solid var(--ct-gray-200);
        border-radius: 10px; font-size: 1.125rem; font-weight: 700; color: var(--ct-gray-900);
        box-sizing: border-box; transition: border-color .15s, box-shadow .15s;
    }
    .fee-input:focus { outline: none; border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(132,204,22,.15); }
</style>
@endsection
