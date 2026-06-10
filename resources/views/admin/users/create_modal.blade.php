<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Add User</h5>
        <button type="button" class="close" data-dismiss="modal">
            <i class="fal fa-close"></i>
        </button>
    </div>
    <div class="modal-body">
        <form class="form create_user" method="POST" action="{{route('users.store')}}" data-url="{{route('users.store')}}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="form-group col-lg-6">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Enter First Name">
                </div>
                <div class="form-group col-lg-6">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Enter Last Name">
                </div>
                <div class="form-group col-lg-6">
                    <label>Phone</label>
                    <input type="tel" name="phone_number" class="form-control" placeholder="Enter Phone">
                </div>
                <div class="form-group col-lg-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter Email">
                </div>
                <div class="form-group col-lg-6">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter Password">
                </div>
                <div class="form-group col-lg-6">
                    <label>Password Confirmation</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Enter Password Confirmation">
                </div>
                <div class="form-group col-lg-12">
                    <label>User Role</label>
                    <select name="role_id" class="form-control">
                        @foreach($roles as $role)
                            <option value="{{$role->id}}">{{ucfirst($role->name)}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-lg-12">
                    <label>Picture</label>
                    <div class="file-upload">
                        <div class="image-upload-wrap">
                            <input class="file-upload-input" type='file' id="file-upload-input" name="profile_image" onchange="readURL(this);" accept="image/*" />
                            <div class="drag-text">
                                <h3>Drag and drop a file or select add Image</h3>
                            </div>
                        </div>
                        <div class="file-upload-content">
                            <div class="img-box">
                                <img class="file-upload-image" src="#" alt="your image" />
                                <div class="image-title-wrap">
                                    <div class="image-title">Uploaded Image</div>
                                    <a href="#" onclick="removeUpload()" class="remove-image"><i class="fal fa-close"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-lg-12 text-right mt-3">
                    <button data-dismiss="modal" class="btn btn-danger">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
