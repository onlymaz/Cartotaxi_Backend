{{-- CargoTaxi Logo Component - Uses uploaded logo from settings --}}
@php
    $settings = \App\Models\SiteSetting::find(1);
    $logoUrl = $settings && $settings->site_logo ? url($settings->site_logo) : null;
    $siteName = $settings ? $settings->site_name : 'CargoTaxi';
@endphp

<div class="ct-logo-wrapper" style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;min-height:88px;padding:14px;box-sizing:border-box;">
    @if($logoUrl)
        {{-- Logo (transparent background) shown directly on the sidebar. --}}
        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="
            max-width:100%;
            max-height:74px;
            width:auto;
            height:auto;
            object-fit:contain;
            display:block;
        ">
    @else
        {{-- Fallback: Simple text logo --}}
        <div style="
            font-size: {{ isset($size) ? ($size * 0.32) : 22 }}px;
            font-weight: 700;
            color: {{ $textColor ?? '#ffffff' }};
            letter-spacing: 0.05em;
        ">
            {{ $siteName }}
        </div>
    @endif
</div>
