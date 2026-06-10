@extends('layouts.cargotaxi')

@section('title')
    <title> {{__('messages.site_setting')}} | {{ config('app.name', 'Laravel') }}</title>
@stop
@section('content')
<div class="nova-card">
    <div class="nova-card-header">
        <h1 class="nova-page-title">{{__('messages.site_setting')}}</h1>
        <p class="nova-page-subtitle">Manage your site logo, company details, and settings</p>
    </div>
    <div class="nova-card-body">
        <form class="form setting_form" data-url="{{route('settings.update',$setting->id)}}">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="nova-label">Site Name</label>
                <input type="text" class="nova-input" name="site_name" placeholder="Enter Site Name" value="{{$setting->site_name}}">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="nova-label">Site Logo</label>
                <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.75rem;">Upload your company logo. This will appear in the sidebar and throughout the application.</p>
                <div class="file-upload">
                    <div class="image-upload-wrap site_logo">
                        <input name="site_logo" class="file-upload-input site_logo" id="site_logo" type="file" onchange="siteURL(this,'.site_logo');" accept="image/*">
                        <div class="drag-text">
                            @if($setting->site_logo)
                                <img src="{{url($setting->site_logo)}}">
                            @else
                                <h3>{{__('messages.drag_a_drop_file')}}</h3>
                            @endif


                        </div>
                    </div>
                    <div class="file-upload-content site_logo">
                        <div class="img-box">
                            <img class="file-upload-image site_logo" src="#" alt="your image">
                            <div class="image-title-wrap site_logo">
                                <div class="image-title site_logo">Uploaded Image</div>
                                <a href="#" onclick="removeUpload('.site_logo')" class="remove-image"><i class="fal fa-close"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="nova-label">{{__('messages.site_icon')}}</label>
                <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.75rem;">Upload a favicon for browser tabs.</p>
                <div class="file-upload">
                    <div class="image-upload-wrap site_icon">
                        <input name="site_icon" class="file-upload-input site_icon" id="site_icon" type="file" onchange="siteURL(this,'.site_icon');" accept="image/*" value="12">
                        <div class="drag-text">
                            @if($setting->site_icon)
                                <img src="{{url($setting->site_icon)}}">
                            @else
                                <h3>{{__('messages.drag_a_drop_file')}}</h3>
                            @endif
                        </div>
                    </div>
                    <div class="file-upload-content site_icon">
                        <div class="img-box">
                            <img class="file-upload-image site_icon" src="#" alt="your image">
                            <div class="image-title-wrap ">
                                <div class="image-title site_icon">Uploaded Image</div>
                                <a href="#" onclick="removeUpload('.site_icon')" class="remove-image"><i class="fal fa-close"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="nova-label">Playstore Link</label>
                <input type="text" name="playstore_link" class="nova-input" placeholder="Enter Playstore link" value="{{$setting->playstore_link}}">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="nova-label">Appstore Link</label>
                <input type="text" name="appstore_link" class="nova-input" placeholder="Enter Appstore link" value="{{$setting->appstore_link}}">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="nova-label">SOS Number</label>
                <input type="text" name="sos_number" class="nova-input" value="{{$setting->sos_number}}">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="nova-label">{{__('messages.contact_number')}}</label>
                <input type="text" name="contact_number" class="nova-input" value="{{$setting->contact_number}}">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="nova-label">{{__('messages.contact_email')}}</label>
                <input type="email" name="contact_email" class="nova-input" value="{{$setting->contact_email}}">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="nova-label">{{__('messages.help_content')}}</label>
                <input type="text" name="help_content" class="nova-input" placeholder="Enter Help Content" value="{{$setting->help_content}}">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="nova-label">Google Map Key</label>
                <input type="text" name="google_map_key" class="nova-input" value="{{$setting->google_map_key}}">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="nova-label">Social Login</label>
                <select class="nova-select" name="social_login">
                    <option value="1" @if($setting->social_login==1) selected @endif>{{__('messages.enable')}}</option>
                    <option value="0" @if($setting->social_login==0) selected @endif>{{__('messages.disable')}}</option>
                </select>
            </div>
            <div class="form-group" style="display: flex; gap: 0.75rem; margin-top: 2rem;">
                <button type="button" class="nova-btn nova-btn-secondary" onclick="window.history.back()">{{__('messages.cancel')}}</button>
                <button type="submit" class="nova-btn nova-btn-primary">{{__('messages.update_setting')}}</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
    <script type="text/javascript">
        function siteURL(input,icon) {
            if (input.files && input.files[0]) {

                var reader = new FileReader();

                reader.onload = function(e) {
                    $('.image-upload-wrap'+icon).hide();

                    $('.file-upload-image'+icon).attr('src', e.target.result);
                    $('.file-upload-content'+icon).show();

                    $('.image-title'+icon).html(input.files[0].name);
                };

                reader.readAsDataURL(input.files[0]);

            } else {
                removeUpload(icon);
            }
        }
        function removeUpload(icon) {
            /*$('.file-upload-input').replaceWith($('.file-upload-input').clone());*/
            $('.file-upload-content'+icon).hide();
            $('.image-upload-wrap'+icon).show();
        }
        $(document).ready(function () {

            $('.image-upload-wrap').bind('dragover', function () {
                $('.image-upload-wrap').addClass('image-dropping');
            });
            $('.image-upload-wrap').bind('dragleave', function () {
                $('.image-upload-wrap').removeClass('image-dropping');
            });
            // Toast notification helper
            function showToast(type, message) {
                var colors = {
                    success: { bg: 'rgba(16, 185, 129, 0.1)', border: '#10b981', text: '#047857' },
                    error: { bg: 'rgba(239, 68, 68, 0.1)', border: '#ef4444', text: '#b91c1c' }
                };
                var c = colors[type] || colors.success;
                var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
                var el = $('<div style="position:fixed;top:1rem;right:1rem;z-index:9999;padding:1rem 1.5rem;background:'+c.bg+';border:1px solid '+c.border+';border-radius:0.75rem;display:flex;align-items:center;gap:0.75rem;animation:slideDown 0.3s ease;"><i class="fas '+icon+'" style="color:'+c.text+';"></i><span style="color:'+c.text+';font-weight:500;">'+message+'</span></div>');
                $('body').append(el);
                setTimeout(() => el.fadeOut(300, function() { $(this).remove(); }), 3000);
            }
            
            // Toast object for compatibility
            if(typeof toast === 'undefined') {
                window.toast = {
                    success: function(msg) { showToast('success', msg); },
                    error: function(msg) { showToast('error', msg); }
                };
            }
            
            $('body').on('submit','.setting_form',function(event) {
                event.preventDefault();
                
                // Show loading state
                var submitBtn = $(this).find('button[type="submit"]');
                var originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

                var elem    =   $(this);
                var url     =   elem.attr('data-url');
                
                if(!url) {
                    showToast('error', 'Form URL is missing');
                    submitBtn.prop('disabled', false).html(originalText);
                    return;
                }
                
                var fileInput = document.getElementById('site_logo');
                var file = fileInput ? fileInput.files[0] : null;
                var fileInput2 = document.getElementById('site_icon');
                var file2 = fileInput2 ? fileInput2.files[0] : null;
                var data    =   $('.setting_form')[0];
                var formData = new FormData(data);
                
                if(file) {
                    formData.append('site_logo', file);
                }
                if(file2) {
                    formData.append('site_icon', file2);
                }
                
                formData.append('_token', $('meta[name=csrf-token]').attr("content"));
                formData.append('_method', 'PATCH');
                
                console.log('Submitting to:', url);
                
                $.ajax({
                    type:'POST',
                    url:url,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success:function(data) {
                        console.log('Success response:', data);
                        if(data && data.success){
                            showToast('success', 'Site Settings updated successfully');
                            setTimeout(function() {
                            location.reload();
                            }, 1000);
                        } else {
                            showToast('error', 'Update failed. Please try again.');
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function (response, exception) {
                        console.error('Error response:', response);
                        console.error('Exception:', exception);
                        
                        showToast('error', 'Please resolve following errors');
                        
                        var errors = {};
                        try {
                            if(response.responseJSON && response.responseJSON.errors) {
                                errors = response.responseJSON.errors;
                            } else if(response.responseText) {
                                var responseData = JSON.parse(response.responseText);
                                errors = responseData.errors || {};
                            }
                        } catch(e) {
                            console.error('Error parsing response:', e);
                            showToast('error', 'An error occurred. Please check console for details.');
                        }
                        
                        // Remove old errors
                        $('span.input-error').remove();
                        $('.nova-input, .nova-select').removeClass('border-red-500');
                        
                        // Display new errors
                        $.each(errors, function(name, errorArray) {
                            var errorMsg = Array.isArray(errorArray) ? errorArray[0] : errorArray;
                            var input = $('input[name="'+name+'"], select[name="'+name+'"]');
                            if(input.length > 0) {
                                input.addClass('border-red-500');
                                input.after('<span class="input-error '+name+'" style="display:block;color:#ef4444;font-size:0.875rem;margin-top:0.25rem;">'+errorMsg+'</span>');
                            }
                        });
                        
                        submitBtn.prop('disabled', false).html(originalText);
                    },
                });
            });
        })
    </script>
    <style>
        .border-red-500 {
            border-color: #ef4444 !important;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endsection
