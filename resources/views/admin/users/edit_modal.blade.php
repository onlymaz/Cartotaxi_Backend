<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Update User</h5>
        <button type="button" class="close" data-dismiss="modal">
            <i class="fal fa-close"></i>
        </button>
    </div>
    <div class="modal-body">
        <form class="form edit_user" method="POST" action="{{route('users.update',$user->id)}}" data-url="{{route('users.update',$user->id)}}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="row">
                <div class="form-group col-lg-6">
                    <label>{{__('messages.first_name')}}</label>
                    <input type="text" name="first_name" class="form-control" placeholder="{{__('messages.enter')}} {{__('messages.first_name')}}" value="{{$user->first_name}}">
                </div>
                <div class="form-group col-lg-6">
                    <label>{{__('messages.last_name')}}</label>
                    <input type="text" name="last_name" class="form-control" placeholder="{{__('messages.enter')}} {{__('messages.last_name')}}" value="{{$user->last_name}}">
                </div>
                <div class="form-group col-lg-6">
                    <label>{{__('messages.phone')}}</label>
                    <input type="tel" name="phone_number" class="form-control" placeholder="{{__('messages.enter')}} {{__('messages.phone')}}" value="{{$user->phone_number}}">
                </div>
                <div class="form-group col-lg-6">
                    <label>{{__('messages.email')}}</label>
                    <input type="email" name="email" class="form-control" placeholder="{{__('messages.enter')}} {{__('messages.email')}}" value="{{$user->email}}">
                </div>
                <div class="form-group col-lg-6">
                    <label>{{__('messages.password')}}</label>
                    <input type="password" name="password" class="form-control" placeholder="{{__('messages.enter')}} {{__('messages.password')}}" >
                </div>
                <div class="form-group col-lg-6">
                    <label>{{__('messages.password_conformation')}}</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="{{__('messages.enter')}} {{__('messages.password_conformation')}}">
                </div>
                <td>
                <div class="form-group col-lg-6">
                    <label class="form-check-label">{{__('messages.payment_weekly')}}</label>
                    <input type="checkbox" name="payment_weekly_status">
                </div>
                <div class="form-group col-lg-12">
                    <label>{{__('messages.user_role')}}</label>
                    <select name="role_id" class="form-control">
                        @foreach($roles as $role)
                            <option value="{{$role->id}}" @if($user->role_id==$role->id) selected @endif>{{ucfirst($role->name)}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-lg-12">
                    <label>{{__('messages.picture')}}</label>
                    <div class="file-upload">
                        <div class="image-upload-wrap">
                            <input class="file-upload-input" type='file' id="file-upload-input" name="profile_image" onchange="readURL(this);" accept="image/*" />
                            <div class="drag-text">
                                @if($user->profile_image)
                                    <img src="{{url($user->profile_image)}}">
                                @else
                                    <h3>{{__('messages.drag_a_drop_file')}}</h3>
                                @endif

                            </div>
                        </div>
                        <div class="file-upload-content">
                            <div class="img-box">
                                <img class="file-upload-image" src="#" alt="your image" />
                                <div class="image-title-wrap">
                                    <div class="image-title">{{__('messages.upload_image')}}</div>
                                    <a href="#" onclick="removeUpload()" class="remove-image"><i class="fal fa-close"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-lg-12 text-right mt-3">
                    <button data-dismiss="modal" class="btn btn-danger">{{__('messages.cancel')}}</button>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
