@if($row->role_id==1)
    Admin
@elseif($row->role_id==2)
    Rider
@elseif($row->role_id==3)
    Customer
@endif
