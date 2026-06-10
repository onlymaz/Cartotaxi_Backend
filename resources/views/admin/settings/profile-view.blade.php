@extends('layouts.modern')
@section('title')
    <title>My Profile | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">My Profile</h1>
        <p class="ct-page-subtitle">Manage your account settings and preferences</p>
    </div>
</div>

<div class="pf-grid">

    {{-- Left: Profile Card --}}
    <div class="pf-sidebar">
        <div class="ct-card pf-card">
            <div class="pf-cover"></div>
            <div class="pf-avatar-wrap">
                <div class="pf-avatar-ring">
                    @if($user->profile_image)
                        <img src="{{ url($user->profile_image) }}" class="pf-avatar-img" alt="Profile">
                    @else
                        <div class="pf-avatar-initials">
                            {{ strtoupper(substr($user->first_name,0,1).substr($user->last_name,0,1)) }}
                        </div>
                    @endif
                </div>
                <span class="pf-online-dot"></span>
            </div>
            <div class="pf-card-body">
                <div class="pf-name">{{ $user->first_name }} {{ $user->last_name }}</div>
                <div class="pf-email">{{ $user->email }}</div>

                <div class="pf-divider"></div>

                <div class="pf-info-list">
                    <div class="pf-info-row">
                        <span class="pf-info-icon"><i class="fas fa-phone-alt"></i></span>
                        <div>
                            <div class="pf-info-label">Phone</div>
                            <div class="pf-info-val">{{ $user->phone_number ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="pf-info-row">
                        <span class="pf-info-icon"><i class="fas fa-calendar-alt"></i></span>
                        <div>
                            <div class="pf-info-label">Member Since</div>
                            <div class="pf-info-val">{{ $user->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                    @if($user->last_login_at)
                    <div class="pf-info-row">
                        <span class="pf-info-icon"><i class="fas fa-clock"></i></span>
                        <div>
                            <div class="pf-info-label">Last Login</div>
                            <div class="pf-info-val">{{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endif
                    @if($user->company_name)
                    <div class="pf-info-row">
                        <span class="pf-info-icon"><i class="fas fa-building"></i></span>
                        <div>
                            <div class="pf-info-label">Company</div>
                            <div class="pf-info-val">{{ $user->company_name }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Tabbed Form --}}
    <div class="pf-main">
        <div class="ct-card">
            {{-- Tabs --}}
            <div class="pf-tabs">
                <button type="button" class="pf-tab active" data-tab="general">
                    <i class="fas fa-user"></i> General
                </button>
                <button type="button" class="pf-tab" data-tab="company">
                    <i class="fas fa-building"></i> Company
                </button>
                <button type="button" class="pf-tab" data-tab="social">
                    <i class="fas fa-share-alt"></i> Social
                </button>
                <button type="button" class="pf-tab" data-tab="security">
                    <i class="fas fa-shield-alt"></i> Security
                </button>
            </div>

            <form class="profile_form" data-url="{{ route('settings.profile_update', $user->id) }}" enctype="multipart/form-data">
                @csrf

                {{-- General --}}
                <div id="panel-general" class="pf-panel pf-panel-active">
                    <div class="pf-form-grid">
                        <div class="pf-field">
                            <label class="pf-label">First Name</label>
                            <input type="text" name="first_name" value="{{ $user->first_name }}" class="pf-input">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Last Name</label>
                            <input type="text" name="last_name" value="{{ $user->last_name }}" class="pf-input">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Email</label>
                            <input type="email" name="email" value="{{ $user->email }}" class="pf-input">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Phone</label>
                            <input type="tel" name="phone_number" value="{{ $user->phone_number }}" class="pf-input">
                        </div>
                        <div class="pf-field pf-field-full">
                            <label class="pf-label">Bio</label>
                            <textarea name="bio" rows="3" class="pf-input" placeholder="Tell us about yourself…">{{ $user->bio ?? '' }}</textarea>
                        </div>
                        <div class="pf-field pf-field-full">
                            <label class="pf-label">Profile Photo</label>
                            <label class="pf-upload" id="pf-upload-label">
                                <i class="fas fa-cloud-upload-alt pf-upload-icon"></i>
                                <span class="pf-upload-text"><strong>Click to upload</strong> or drag and drop</span>
                                <span class="pf-upload-hint">PNG, JPG or GIF (max 2MB)</span>
                                <input type="file" name="profile_image" class="pf-file-input" accept="image/*">
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Company --}}
                <div id="panel-company" class="pf-panel">
                    <div class="pf-form-grid">
                        <div class="pf-field">
                            <label class="pf-label">Company Name</label>
                            <input type="text" name="company_name" value="{{ $user->company_name }}" class="pf-input" placeholder="e.g. Acme Corp">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">VAT Number</label>
                            <input type="text" name="vat_number" value="{{ $user->vat_number }}" class="pf-input" placeholder="e.g. DE123456789">
                        </div>
                        <div class="pf-field pf-field-full">
                            <label class="pf-label">Company Address</label>
                            <input type="text" name="company_address" value="{{ $user->company_address ?? '' }}" class="pf-input" placeholder="Full business address">
                        </div>
                        <div class="pf-field pf-field-full">
                            <label class="pf-label">Company Website</label>
                            <div class="pf-input-prefix-wrap">
                                <span class="pf-input-prefix"><i class="fas fa-globe"></i></span>
                                <input type="url" name="company_website" value="{{ $user->company_website ?? '' }}" class="pf-input pf-input-has-prefix" placeholder="https://example.com">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Social --}}
                <div id="panel-social" class="pf-panel">
                    <div class="pf-social-hint">
                        <i class="fas fa-info-circle"></i>
                        Enter your social profile links below in JSON format.
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Social Links (JSON)</label>
                        <textarea name="social_links" rows="7" class="pf-input pf-mono" placeholder='{"twitter": "https://twitter.com/...", "linkedin": "https://linkedin.com/in/..."}'>{{ is_array($user->social_links) ? json_encode($user->social_links, JSON_PRETTY_PRINT) : ($user->social_links ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Security --}}
                <div id="panel-security" class="pf-panel">
                    <div class="pf-alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Leave password fields empty if you don't want to change your password.</span>
                    </div>
                    <div class="pf-form-grid">
                        <div class="pf-field pf-field-full">
                            <label class="pf-label">Current Password</label>
                            <input type="password" name="oldpassword" class="pf-input" placeholder="Enter current password">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">New Password</label>
                            <input type="password" name="password" class="pf-input" placeholder="New password">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="pf-input" placeholder="Repeat new password">
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="pf-form-footer">
                    <button type="submit" class="ct-btn ct-btn-primary pf-submit-btn">
                        <i class="fas fa-save"></i> <span class="pf-btn-label">Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Layout */
    .pf-grid { display: grid; grid-template-columns: 300px 1fr; gap: 1.25rem; align-items: start; }
    @media (max-width: 900px) { .pf-grid { grid-template-columns: 1fr; } }

    /* Profile card */
    .pf-card { overflow: visible; }
    .pf-cover {
        height: 80px;
        background: linear-gradient(135deg, var(--ct-primary) 0%, #334155 50%, #475569 100%);
        border-radius: 16px 16px 0 0;
    }
    .pf-avatar-wrap { position: relative; display: flex; justify-content: center; margin-top: -32px; margin-bottom: 0.75rem; }
    .pf-avatar-ring {
        width: 72px; height: 72px; border-radius: 50%;
        border: 3px solid var(--ct-white);
        background: linear-gradient(135deg, var(--ct-accent), #65a30d);
        box-shadow: 0 4px 14px rgba(0,0,0,.12);
        overflow: hidden; display: flex; align-items: center; justify-content: center;
    }
    .pf-avatar-img { width: 100%; height: 100%; object-fit: cover; }
    .pf-avatar-initials { font-size: 1.25rem; font-weight: 700; color: #fff; }
    .pf-online-dot {
        position: absolute; bottom: 4px; right: calc(50% - 30px);
        width: 13px; height: 13px; border-radius: 50%;
        background: #22c55e; border: 2px solid var(--ct-white);
        box-shadow: 0 0 0 2px rgba(34,197,94,.25);
    }
    .pf-card-body { padding: 0 1.25rem 1.5rem; }
    .pf-name { font-size: 1rem; font-weight: 700; color: var(--ct-gray-900); text-align: center; }
    .pf-email { font-size: 0.75rem; color: var(--ct-gray-500); text-align: center; margin-top: 2px; }
    .pf-divider { height: 1px; background: var(--ct-gray-100); margin: 1rem 0; }
    .pf-info-list { display: flex; flex-direction: column; gap: 0.875rem; }
    .pf-info-row { display: flex; align-items: flex-start; gap: 0.75rem; }
    .pf-info-icon {
        width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
        background: var(--ct-gray-100); color: var(--ct-primary);
        display: flex; align-items: center; justify-content: center; font-size: 0.75rem;
    }
    .pf-info-label { font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ct-gray-400); font-weight: 600; }
    .pf-info-val { font-size: 0.8125rem; font-weight: 600; color: var(--ct-gray-800); margin-top: 1px; }

    /* Tabs */
    .pf-tabs { display: flex; border-bottom: 1px solid var(--ct-gray-200); padding: 0 0.25rem; }
    .pf-tab {
        display: inline-flex; align-items: center; gap: 0.375rem;
        padding: 0.875rem 1rem; font-size: 0.8125rem; font-weight: 500;
        color: var(--ct-gray-500); border-bottom: 2px solid transparent;
        background: none; border-top: none; border-left: none; border-right: none;
        cursor: pointer; transition: color .15s, border-color .15s; white-space: nowrap;
    }
    .pf-tab:hover { color: var(--ct-gray-800); }
    .pf-tab.active { color: var(--ct-primary); border-bottom-color: var(--ct-accent); font-weight: 600; }
    .pf-tab i { font-size: 0.75rem; }

    /* Panels */
    .pf-panel { display: none; padding: 1.5rem; }
    .pf-panel-active { display: block; }

    /* Form */
    .pf-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 640px) { .pf-form-grid { grid-template-columns: 1fr; } }
    .pf-field { display: flex; flex-direction: column; gap: 0.375rem; }
    .pf-field-full { grid-column: 1 / -1; }
    .pf-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--ct-gray-500); }
    .pf-input {
        width: 100%; padding: 0.55rem 0.75rem;
        border: 1px solid var(--ct-gray-200); border-radius: 8px;
        font-size: 0.875rem; color: var(--ct-gray-800);
        background: var(--ct-gray-50); box-sizing: border-box;
        transition: border-color .15s, background .15s, box-shadow .15s;
    }
    .pf-input:focus { outline: none; background: var(--ct-white); border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(132,204,22,.15); }
    textarea.pf-input { resize: vertical; }
    .pf-mono { font-family: 'SF Mono', Menlo, Monaco, monospace; font-size: 0.8125rem; }

    .pf-input-prefix-wrap { position: relative; }
    .pf-input-prefix { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--ct-gray-400); font-size: 0.8125rem; }
    .pf-input-has-prefix { padding-left: 2rem; }

    /* Upload */
    .pf-upload {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 0.25rem; padding: 1.5rem; border: 2px dashed var(--ct-gray-200);
        border-radius: 10px; cursor: pointer; transition: border-color .15s, background .15s;
        background: var(--ct-gray-50);
    }
    .pf-upload:hover { border-color: var(--ct-accent); background: #f7fde8; }
    .pf-upload-icon { font-size: 1.5rem; color: var(--ct-gray-400); margin-bottom: 0.25rem; }
    .pf-upload-text { font-size: 0.875rem; color: var(--ct-gray-600); }
    .pf-upload-text strong { color: var(--ct-primary); }
    .pf-upload-hint { font-size: 0.75rem; color: var(--ct-gray-400); }
    .pf-file-input { display: none; }

    /* Alert */
    .pf-alert {
        display: flex; align-items: flex-start; gap: 0.625rem;
        padding: 0.875rem 1rem; background: #fffbeb;
        border: 1px solid #fde68a; border-radius: 10px;
        font-size: 0.875rem; color: #92400e; margin-bottom: 1.25rem;
    }
    .pf-alert i { color: #f59e0b; margin-top: 1px; flex-shrink: 0; }

    /* Social hint */
    .pf-social-hint {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.75rem 1rem; background: #eff6ff;
        border: 1px solid #bfdbfe; border-radius: 10px;
        font-size: 0.8125rem; color: #1e40af; margin-bottom: 1rem;
    }
    .pf-social-hint i { color: #3b82f6; }

    /* Footer */
    .pf-form-footer {
        display: flex; justify-content: flex-end;
        padding: 1rem 1.5rem; border-top: 1px solid var(--ct-gray-100);
        background: var(--ct-gray-50); border-radius: 0 0 16px 16px;
    }
    .pf-submit-btn { min-width: 140px; justify-content: center; }

    /* Error */
    span.input-error { display: block; font-size: 0.75rem; color: #ef4444; margin-top: 3px; }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function () {

    // Tab switching
    $('.pf-tab').on('click', function () {
        var tab = $(this).data('tab');
        $('.pf-tab').removeClass('active');
        $(this).addClass('active');
        $('.pf-panel').removeClass('pf-panel-active').hide();
        $('#panel-' + tab).addClass('pf-panel-active').show();
    });

    // File name preview
    $('.pf-file-input').on('change', function () {
        var name = $(this).val().split('\\').pop();
        if (name) {
            $(this).closest('.pf-upload').find('.pf-upload-text').html('<strong style="color:var(--ct-accent)">' + name + '</strong>');
        }
    });

    // AJAX form submission
    $('body').on('submit', '.profile_form', function (event) {
        event.preventDefault();
        $('span.input-error').remove();
        var url = $(this).attr('data-url');
        var formData = new FormData(this);
        formData.append('_method', 'PATCH');
        var btn = $(this).find('.pf-submit-btn');
        var origLabel = btn.find('.pf-btn-label').text();
        btn.prop('disabled', true).find('.pf-btn-label').text('Saving…');

        $.ajax({
            type: 'POST', url: url, data: formData,
            processData: false, contentType: false,
            success: function (data) {
                if (data.success) {
                    if (typeof toast !== 'undefined') toast.success('Profile updated successfully');
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    if (typeof toast !== 'undefined') toast.error('Update failed. Please check your input.');
                    btn.prop('disabled', false).find('.pf-btn-label').text(origLabel);
                }
            },
            error: function (response) {
                if (typeof toast !== 'undefined') toast.error('Please resolve errors');
                btn.prop('disabled', false).find('.pf-btn-label').text(origLabel);
                if (response.responseText) {
                    try {
                        var errors = JSON.parse(response.responseText).errors;
                        $.each(errors, function (name, error) {
                            $('[name="' + name + '"]').after('<span class="input-error">' + error[0] + '</span>');
                        });
                    } catch(e) {}
                }
            }
        });
    });
});
</script>
@endsection
