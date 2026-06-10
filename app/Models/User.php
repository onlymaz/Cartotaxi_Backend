<?php

namespace App\Models;

use App\Scopes\Filterable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, Filterable, HasFactory;

    /**
     * Privilege/auth-state fields are intentionally OMITTED from $fillable so
     * mass-assignment via User::create($input) / $user->update($input) cannot
     * be coerced into setting them. Legitimate callers (registration, social
     * login, admin user creation) MUST use User::forceCreate() / forceFill()
     * for those fields, or assign them as direct properties before save().
     *
     * Kept out of $fillable: role_id, access_token, provider_id,
     * email_verified_at, confirmed (set only by the verification flow).
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'phone_number', 'profile_image',
        'IsActive', 'lat', 'long', 'device', 'provider', 'confirmation_code',
        'confirmation_code_expires_at',
        'fcm_token', 'forget_code', 'forget_code_expires_at',
        'isWeekly', 'fcm_web_token', 'car_number', 'lang',
        'company_name', 'vat_number', 'bio', 'social_links', 'cover_image', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'access_token', 'forget_code', 'confirmation_code',
        'forget_code_expires_at', 'confirmation_code_expires_at',
    ];

    protected $casts = [
        'email_verified_at'             => 'datetime',
        'forget_code_expires_at'        => 'datetime',
        'confirmation_code_expires_at'  => 'datetime',
        'social_links'                  => 'array',
    ];

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    public function hasRole($roles)
    {
        $this->have_role = $this->getUserRole();

        if ($this->have_role && $this->have_role->name == 'Root') {
            return true;
        }

        if (!$this->have_role) {
            return false;
        }

        if (is_array($roles)) {
            foreach ($roles as $need_role) {
                if ($this->checkIfUserHasRole($need_role)) {
                    return true;
                }
            }
        } else {
            return $this->checkIfUserHasRole($roles);
        }
        return false;
    }

    private function getUserRole()
    {
        return $this->role()->getResults();
    }

    private function checkIfUserHasRole($need_role)
    {
        return strtolower($need_role) == strtolower($this->have_role->name);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'rider_id', 'id')
            ->whereNotIn('order_status', ['accident', 'refused', 'delivered', 'cancel'])
            ->orderBy('id', 'desc');
    }
}
