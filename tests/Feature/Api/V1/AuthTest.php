<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_user_can_register_successfully(): void
    {
        $payload = [
            'name' => 'Jane Citizen',
            'email' => 'jane.citizen@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201)
            ->assertHeader('X-Request-ID')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'uuid',
                        'name',
                        'email',
                        'is_active',
                        'roles',
                        'permissions',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                    'token_type',
                ],
                'meta' => [
                    'request_id',
                    'timestamp',
                ],
                'errors',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'name' => 'Jane Citizen',
                        'email' => 'jane.citizen@example.com',
                        'is_active' => true,
                        'roles' => ['user'],
                    ],
                    'token_type' => 'Bearer',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane.citizen@example.com',
            'name' => 'Jane Citizen',
        ]);

        $user = User::where('email', 'jane.citizen@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('SecurePass123!', $user->password));
        $this->assertTrue($user->hasRole(UserRole::User->value));
    }

    public function test_user_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $payload = [
            'name' => 'Another User',
            'email' => 'duplicate@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_register_with_short_password(): void
    {
        $payload = [
            'name' => 'Short Pass',
            'email' => 'short@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'active.citizen@example.com',
            'password' => Hash::make('CorrectPassword123!'),
            'is_active' => true,
        ]);
        $user->assignRole(UserRole::User->value);

        $payload = [
            'email' => 'active.citizen@example.com',
            'password' => 'CorrectPassword123!',
        ];

        $response = $this->postJson('/api/v1/auth/login', $payload);

        $response->assertStatus(200)
            ->assertHeader('X-Request-ID')
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'uuid' => $user->uuid,
                        'email' => 'active.citizen@example.com',
                    ],
                    'token_type' => 'Bearer',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $payload = [
            'email' => 'user@example.com',
            'password' => 'WrongPassword',
        ];

        $response = $this->postJson('/api/v1/auth/login', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->inactive()->create([
            'email' => 'inactive.user@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $payload = [
            'email' => 'inactive.user@example.com',
            'password' => 'Password123!',
        ];

        $response = $this->postJson('/api/v1/auth/login', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::User->value);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => ['user'],
                ],
            ]);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old.email@example.com',
        ]);
        $user->assignRole(UserRole::User->value);

        $payload = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/auth/profile', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Name',
                    'email' => 'updated.email@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ]);
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $payload = [
            'current_password' => 'OldPassword123!',
            'new_password' => 'NewSecurePassword123!',
            'new_password_confirmation' => 'NewSecurePassword123!',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/auth/change-password', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);

        $this->assertTrue(Hash::check('NewSecurePassword123!', $user->fresh()->password));
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
}

