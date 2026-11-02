<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $citizen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin.test@waste.local',
        ]);
        $this->admin->assignRole(UserRole::Admin->value);

        $this->citizen = User::factory()->create([
            'email' => 'citizen.test@waste.local',
        ]);
        $this->citizen->assignRole(UserRole::User->value);
    }

    public function test_admin_can_list_users_with_pagination(): void
    {
        User::factory()->count(15)->create();

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/v1/users?per_page=10');

        $response->assertStatus(200)
            ->assertHeader('X-Request-ID')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'uuid',
                        'name',
                        'email',
                        'is_active',
                        'roles',
                        'permissions',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'has_next',
                        'has_previous',
                    ],
                    'request_id',
                    'timestamp',
                ],
                'errors',
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    public function test_non_admin_cannot_list_users(): void
    {
        $response = $this->actingAs($this->citizen, 'sanctum')->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        $payload = [
            'name' => 'New Officer',
            'email' => 'officer@waste.local',
            'password' => 'Password123!',
            'role' => UserRole::Admin->value,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/v1/users', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'New Officer',
                    'email' => 'officer@waste.local',
                    'is_active' => true,
                    'roles' => ['admin'],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'officer@waste.local',
            'name' => 'New Officer',
        ]);

        $created = User::where('email', 'officer@waste.local')->first();
        $this->assertNotNull($created);
        $this->assertTrue(Hash::check('Password123!', $created->password));
        $this->assertTrue($created->hasRole(UserRole::Admin->value));
    }

    public function test_admin_can_view_single_user_by_uuid(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Target Citizen',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')->getJson("/api/v1/users/{$targetUser->uuid}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'uuid' => $targetUser->uuid,
                    'name' => 'Target Citizen',
                ],
            ]);
    }

    public function test_admin_can_update_user(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@waste.local',
        ]);
        $targetUser->assignRole(UserRole::User->value);

        $payload = [
            'name' => 'Modified Name',
            'email' => 'modified@waste.local',
            'role' => UserRole::Admin->value,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')->putJson("/api/v1/users/{$targetUser->uuid}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Modified Name',
                    'email' => 'modified@waste.local',
                    'roles' => ['admin'],
                ],
            ]);

        $this->assertTrue($targetUser->fresh()->hasRole(UserRole::Admin->value));
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $targetUser = User::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/users/{$targetUser->uuid}/status", [
                'is_active' => false,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_active' => false,
                ],
            ]);

        $this->assertFalse($targetUser->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_themselves(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/users/{$this->admin->uuid}/status", [
                'is_active' => false,
            ]);

        $response->assertStatus(403);
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_admin_can_delete_user(): void
    {
        $targetUser = User::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/users/{$targetUser->uuid}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('users', [
            'id' => $targetUser->id,
        ]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/users/{$this->admin->uuid}");

        $response->assertStatus(403);
        $this->assertNotSoftDeleted('users', [
            'id' => $this->admin->id,
        ]);
    }

    public function test_user_filtering_by_search_and_role(): void
    {
        $specialUser = User::factory()->create([
            'name' => 'Alexander Supertramp',
            'email' => 'alexander@nature.org',
        ]);
        $specialUser->assignRole(UserRole::Admin->value);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/users?search=Alexander&role=admin');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($specialUser->uuid, $response->json('data.0.uuid'));
    }
}
