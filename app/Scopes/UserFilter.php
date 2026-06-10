<?php

namespace App\Scopes;

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
        if ($this->request->session()->has('userType')) {
            if ($this->request->session()->get('userType') == "weekly_user") {
                $builder->where('u.isWeekly', 1);
            } else {
                $builder->where('u.isWeekly', 0);
            }
        }

        return $builder;
    }
}
