<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression test for P0-5: privilege fields (role_id, access_token) are not
 * mass-assignable. If a future change accidentally re-adds these to $fillable,
 * this test fails — keeping the lockdown enforced.
 */
class MassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function role_id_cannot_be_set_via_mass_assignment()
    {
        // Attempt to mass-assign role_id to admin (1). The model should drop
        // it because role_id is not in $fillable.
        $user = User::create([
            'first_name'   => 'Eve',
            'last_name'    => 'Attacker',
            'email'        => 'eve@example.com',
            'password'     => 'irrelevant',
            'phone_number' => '0000099',
            'role_id'      => 1, // <-- attempted privilege escalation
        ]);

        $this->assertNotEquals(1, $user->fresh()->role_id,
            'role_id must not be settable via mass assignment');
    }

    /** @test */
    public function access_token_cannot_be_set_via_mass_assignment()
    {
        $user = User::create([
            'first_name'   => 'Mallory',
            'last_name'    => 'Attacker',
            'email'        => 'mallory@example.com',
            'password'     => 'irrelevant',
            'phone_number' => '0000100',
            'access_token' => 'forged-token-xxxxxxxxx',
        ]);

        $this->assertNull($user->fresh()->access_token,
            'access_token must not be settable via mass assignment');
    }

    /** @test */
    public function force_create_is_the_supported_path_for_role_assignment()
    {
        // Legitimate callers (registration, social login) use forceCreate to
        // bypass $fillable when setting server-controlled fields.
        $user = User::forceCreate([
            'first_name'   => 'Admin',
            'last_name'    => 'Bootstrap',
            'email'        => 'bootstrap@example.com',
            'password'     => 'irrelevant',
            'phone_number' => '0000101',
            'role_id'      => 1,
        ]);

        $this->assertSame(1, (int) $user->fresh()->role_id);
    }
}
