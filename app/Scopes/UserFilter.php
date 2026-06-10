<?php

namespace App\Scopes;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserFilter
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder)
    {
        // ?type=customer|rider|admin — resolved against the roles table so the
        // mapping isn't hardcoded. Unknown values are ignored rather than 500ing.
        if ($this->request->filled('type')) {
            $roleId = Role::where('name', $this->request->query('type'))->value('id');
            if ($roleId) {
                $builder->where('users.role_id', $roleId);
            }
        }

        if ($this->request->session()->has('userType')) {
            if ($this->request->session()->get('userType') == "weekly_user") {
                $builder->where('users.isWeekly', 1);
            } else {
                $builder->where('users.isWeekly', 0);
            }
        }

        return $builder;
    }
}
