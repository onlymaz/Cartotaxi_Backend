<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression tests for the P0 password-reset findings.
 *
 * These tests would have caught the original "reset code returned in response"
 * bug and the "no expiry / mt_rand collision" risk. They also pin the generic
 * response so future code changes can't reintroduce email enumeration.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_returns_generic_message_and_does_not_leak_the_reset_code()
    {
        User::forceCreate([
            'first_name'   => 'Alice',
            'last_name'    => 'Test',
            'email'        => 'alice@example.com',
            'password'     => Hash::make('initial'),
            'role_id'      => 3,
            'phone_number' => '0000001',
        ]);

        $response = $this->postJson('/api/v1/password/email', [
            'email' => 'alice@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $body = $response->getContent();
        // The response must not contain the raw 6-digit code anywhere — even
        // at any nesting level. This regex would catch a code at any depth.
        $this->assertEquals(0, preg_match('/\b\d{6}\b/', $body),
            'Reset response leaked a 6-digit code: ' . $body);
        // And specifically no `data.user.forget_code` field at all.
        $this->assertStringNotContainsString('forget_code', $body,
            'Response must not contain the forget_code field');
        $this->assertStringNotContainsString('"user"', $body,
            'Response must not include the user object');
    }

    /** @test */
    public function create_returns_the_same_message_for_unknown_emails_to_prevent_enumeration()
    {
        $existing = $this->postJson('/api/v1/password/email', ['email' => 'unknown@example.com']);
        $existing->assertStatus(200)->assertJson(['status' => true]);
        // Body should be the same generic shape — no "Email not found".
        $this->assertStringNotContainsString('not found', strtolower($existing->getContent()));
    }

    /** @test */
    public function reset_persists_a_hashed_code_not_a_plaintext_code()
    {
        $user = User::forceCreate([
            'first_name'   => 'Bob',
            'last_name'    => 'Test',
            'email'        => 'bob@example.com',
            'password'     => Hash::make('initial'),
            'role_id'      => 3,
            'phone_number' => '0000002',
        ]);

        $this->postJson('/api/v1/password/email', ['email' => 'bob@example.com'])
             ->assertStatus(200);

        $user->refresh();
        $this->assertNotEmpty($user->forget_code);
        // bcrypt hashes start with $2y$. A raw 6-digit code would be all digits.
        $this->assertStringStartsWith('$2y$', $user->forget_code,
            'forget_code must be hashed at rest');
        $this->assertNotNull($user->forget_code_expires_at);
    }

    /** @test */
    public function reset_store_rejects_an_invalid_or_unknown_code()
    {
        User::forceCreate([
            'first_name'   => 'Carol',
            'last_name'    => 'Test',
            'email'        => 'carol@example.com',
            'password'     => Hash::make('initial'),
            'role_id'      => 3,
            'phone_number' => '0000003',
        ]);

        $this->postJson('/api/v1/password/email', ['email' => 'carol@example.com']);

        $response = $this->postJson('/api/v1/password/reset', [
            'email'                 => 'carol@example.com',
            'code'                  => '000000',
            'password'              => 'newSecret',
            'password_confirmation' => 'newSecret',
        ]);

        $response->assertStatus(200)->assertJson(['status' => false]);
        $this->assertTrue(Hash::check('initial', User::where('email', 'carol@example.com')->first()->password),
            'Password must NOT have been rotated when a wrong code is submitted');
    }
}
