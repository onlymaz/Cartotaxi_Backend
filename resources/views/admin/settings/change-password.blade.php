@extends('layouts.modern')
@section('title')
    <title>Change Password | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Change Password</h1>
        <p class="ct-page-subtitle">Update your account password to keep it secure</p>
    </div>
    <div class="ct-page-actions">
        <a href="{{ route('settings.profile_view', $user->id) }}" class="ct-btn ct-btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>
</div>

<div style="max-width: 480px;">
    <div class="ct-card">
        <div class="cp-head">
            <div class="cp-icon"><i class="fas fa-shield-alt"></i></div>
            <div>
                <div class="cp-head-title">Password Security</div>
                <div class="cp-head-sub">Choose a strong password you don't use elsewhere</div>
            </div>
        </div>

        <div class="cp-alert">
            <i class="fas fa-info-circle"></i>
            <span>Leave all fields empty if you only want to update profile info on the other page.</span>
        </div>

        <form class="profile_form" style="padding: 0 1.5rem 1.5rem;" data-url="{{ route('settings.profile_update', $user->id) }}">
            @csrf
            <div class="cp-field">
                <label class="cp-label">Current Password</label>
                <div class="cp-input-wrap">
                    <i class="fas fa-lock cp-input-icon"></i>
                    <input type="password" name="oldpassword" class="cp-input" placeholder="Enter your current password">
                </div>
            </div>

            <div class="cp-divider"><span>New Password</span></div>

            <div class="cp-field">
                <label class="cp-label">New Password</label>
                <div class="cp-input-wrap">
                    <i class="fas fa-key cp-input-icon"></i>
                    <input type="password" name="password" class="cp-input" placeholder="Enter new password" id="new_password">
                </div>
                <div class="cp-strength" id="strength-bar" style="display:none;">
                    <div class="cp-strength-track"><div class="cp-strength-fill" id="strength-fill"></div></div>
                    <span class="cp-strength-label" id="strength-label"></span>
                </div>
            </div>

            <div class="cp-field">
                <label class="cp-label">Confirm New Password</label>
                <div class="cp-input-wrap">
                    <i class="fas fa-check-circle cp-input-icon"></i>
                    <input type="password" name="password_confirmation" class="cp-input" placeholder="Repeat new password">
                </div>
            </div>

            <button type="submit" class="ct-btn ct-btn-primary cp-submit">
                <i class="fas fa-save"></i> <span class="cp-btn-label">Update Password</span>
            </button>
        </form>
    </div>
</div>

<style>
    .cp-head { display: flex; align-items: center; gap: 1rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--ct-gray-100); }
    .cp-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, var(--ct-primary), #334155); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; flex-shrink: 0; }
    .cp-head-title { font-weight: 700; color: var(--ct-gray-900); font-size: 0.9375rem; }
    .cp-head-sub { font-size: 0.75rem; color: var(--ct-gray-500); margin-top: 1px; }

    .cp-alert { display: flex; align-items: flex-start; gap: 0.625rem; margin: 1.25rem 1.5rem 0; padding: 0.75rem 1rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; font-size: 0.8125rem; color: #1e40af; }
    .cp-alert i { color: #3b82f6; flex-shrink: 0; margin-top: 1px; }

    .cp-field { margin: 1.25rem 0 0; }
    .cp-label { display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--ct-gray-500); margin-bottom: 0.4rem; }
    .cp-input-wrap { position: relative; }
    .cp-input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--ct-gray-400); font-size: 0.8125rem; }
    .cp-input {
        width: 100%; padding: 0.6rem 0.75rem 0.6rem 2.25rem;
        border: 1px solid var(--ct-gray-200); border-radius: 8px;
        font-size: 0.875rem; color: var(--ct-gray-800);
        background: var(--ct-gray-50); box-sizing: border-box;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .cp-input:focus { outline: none; background: var(--ct-white); border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(132,204,22,.15); }

    .cp-divider { display: flex; align-items: center; gap: 0.75rem; margin: 1.5rem 0 0; }
    .cp-divider::before, .cp-divider::after { content:''; flex:1; height:1px; background: var(--ct-gray-100); }
    .cp-divider span { font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ct-gray-400); font-weight: 600; white-space: nowrap; }

    .cp-strength { margin-top: 0.5rem; display: flex; align-items: center; gap: 0.5rem; }
    .cp-strength-track { flex: 1; height: 4px; background: var(--ct-gray-100); border-radius: 999px; overflow: hidden; }
    .cp-strength-fill { height: 100%; border-radius: 999px; transition: width .3s, background .3s; }
    .cp-strength-label { font-size: 0.6875rem; font-weight: 600; white-space: nowrap; }

    .cp-submit { width: 100%; justify-content: center; margin-top: 1.75rem; }

    span.input-error { display: block; font-size: 0.75rem; color: #ef4444; margin-top: 3px; }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    // Password strength meter
    $('#new_password').on('input', function () {
        var val = $(this).val();
        var bar = $('#strength-bar');
        var fill = $('#strength-fill');
        var label = $('#strength-label');

        if (!val) { bar.hide(); return; }
        bar.show();

        var score = 0;
        if (val.length >= 8)            score++;
        if (/[A-Z]/.test(val))          score++;
        if (/[0-9]/.test(val))          score++;
        if (/[^A-Za-z0-9]/.test(val))  score++;

        var widths = ['25%', '50%', '75%', '100%'];
        var colors = ['#ef4444', '#f59e0b', '#3b82f6', '#22c55e'];
        var labels = ['Weak', 'Fair', 'Good', 'Strong'];

        fill.css({ width: widths[score - 1] || '25%', background: colors[score - 1] || '#ef4444' });
        label.text(labels[score - 1] || 'Weak').css('color', colors[score - 1] || '#ef4444');
    });

    // AJAX submit
    $('body').on('submit', '.profile_form', function (event) {
        event.preventDefault();
        $('span.input-error').remove();
        var url = $(this).attr('data-url');
        var formData = new FormData(this);
        formData.append('_token', $('meta[name=csrf-token]').attr('content'));
        formData.append('_method', 'PATCH');

        var btn = $(this).find('.cp-submit');
        btn.prop('disabled', true).find('.cp-btn-label').text('Saving…');

        $.ajax({
            type: 'POST', url: url, data: formData,
            processData: false, contentType: false,
            success: function (data) {
                if (data.success) {
                    if (typeof toast !== 'undefined') toast.success('Password updated successfully');
                    $('input[type=password]').val('');
                    $('#strength-bar').hide();
                } else {
                    if (typeof toast !== 'undefined') toast.error(data.message || 'Update failed');
                }
                btn.prop('disabled', false).find('.cp-btn-label').text('Update Password');
            },
            error: function (response) {
                if (typeof toast !== 'undefined') toast.error('Please resolve following errors');
                btn.prop('disabled', false).find('.cp-btn-label').text('Update Password');
                try {
                    var errors = JSON.parse(response.responseText).errors;
                    $.each(errors, function (name, error) {
                        $('.cp-input[name="' + name + '"]').after('<span class="input-error">' + error[0] + '</span>');
                    });
                } catch(e) {}
            }
        });
    });
});
</script>
@endsection
