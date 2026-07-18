<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    public function test_login_limit_is_scoped_to_the_account_not_shared_by_every_user_on_an_ip(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'first-account@example.test',
            ]);

            $this->assertNotSame(429, $response->status());
        }

        $limited = $this->postJson('/api/v1/login', [
            'email' => 'first-account@example.test',
        ]);

        $limited
            ->assertStatus(429)
            ->assertJson([
                'status' => false,
                'messages' => 'Too many login attempts. Please wait one minute and try again.',
            ]);

        // A different customer on the same public IP must still be able to
        // attempt login; the broader per-IP ceiling is 60/minute.
        $this->postJson('/api/v1/login', [
            'email' => 'second-account@example.test',
        ])->assertStatus(422);
    }

    public function test_password_reset_request_limit_is_scoped_to_the_account(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $response = $this->postJson('/api/v1/password/email', [
                'email' => 'first-reset-account',
            ]);

            $this->assertNotSame(429, $response->status());
        }

        $this->postJson('/api/v1/password/email', [
            'email' => 'first-reset-account',
        ])->assertStatus(429);

        // Another customer on the same IP still receives a normal response.
        $this->postJson('/api/v1/password/email', [
            'email' => 'second-reset-account',
        ])->assertStatus(200);
    }
}
