<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyTokenMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_existing_opaque_mobile_token_is_upgraded_without_rejecting_the_request(): void
    {
        $legacyToken = str_repeat('legacy-mobile-token-', 3);
        $user = User::factory()->create([
            'role_id' => 3,
            'confirmed' => 1,
            'IsActive' => 1,
        ]);
        $user->access_token = $legacyToken;
        $user->save();

        $response = $this->withHeader('Authorization', 'Bearer ' . $legacyToken)
            ->postJson('/api/v1/user-object');

        $response->assertOk();
        $upgradedToken = $response->headers->get('X-Access-Token');

        $this->assertNotNull($upgradedToken);
        $this->assertCount(3, explode('.', $upgradedToken));
        $this->assertSame($legacyToken, $user->fresh()->access_token);
    }

    public function test_an_unknown_opaque_token_is_still_rejected(): void
    {
        $this->withHeader('Authorization', 'Bearer ' . str_repeat('unknown-token-', 3))
            ->postJson('/api/v1/user-object')
            ->assertUnauthorized();
    }
}
