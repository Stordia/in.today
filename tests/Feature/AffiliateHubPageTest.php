<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\GlobalRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateHubPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_access_affiliate_hub(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::PlatformAdmin,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/affiliate-hub');

        $response->assertStatus(200);
        $response->assertSee('Affiliate Hub');
        $response->assertSee('Overview');
        $response->assertSee('How Affiliates Work');
    }

    public function test_regular_user_cannot_access_affiliate_hub(): void
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::User,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/affiliate-hub');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_affiliate_hub(): void
    {
        $response = $this->get('/admin/affiliate-hub');

        // Should redirect to login or return 403
        $response->assertStatus(302);
    }
}
