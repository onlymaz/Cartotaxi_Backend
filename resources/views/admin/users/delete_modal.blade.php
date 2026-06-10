<div class="modal-content">
    <div class="modal-body">
        <form class="delete_user" data-url="{{route('users.destroy',$user->id)}}">
        <div class="sweetalert">
            <i class="fal fa-exclamation-circle text-warning"></i>
            <h3>Are you sure?</h3>
            <p>Once deleted, you will not be able to recover {{$user->full_name}} !</p>
            <div class="footer">
                <button data-dismiss="modal" class="btn">Cancel</button>
                    <button type="submit" class="btn btn-danger">Ok</button>
            </div>
        </div>
        </form>
    </div>
</div>
