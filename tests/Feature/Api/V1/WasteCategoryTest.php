<?php

namespace Tests\Feature\Api\V1;

use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteReport;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WasteCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $citizen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(UserRole::Admin->value);

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::User->value);
    }

    public function test_anyone_can_list_active_categories(): void
    {
        WasteCategory::create([
            'name' => 'Active Category',
            'slug' => 'active-category',
            'is_active' => true,
        ]);

        WasteCategory::create([
            'name' => 'Inactive Category',
            'slug' => 'inactive-category',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'uuid',
                        'name',
                        'slug',
                        'is_active',
                        'created_at',
                    ],
                ],
            ]);

        // Non-admin list should only include active category
        $slugs = collect($response->json('data'))->pluck('slug')->all();
        $this->assertContains('active-category', $slugs);
        $this->assertNotContains('inactive-category', $slugs);
    }

    public function test_can_filter_categories_by_is_active_string_boolean(): void
    {
        WasteCategory::create([
            'name' => 'Active Test Cat',
            'slug' => 'active-test-cat',
            'is_active' => true,
        ]);

        WasteCategory::create([
            'name' => 'Inactive Test Cat',
            'slug' => 'inactive-test-cat',
            'is_active' => false,
        ]);

        // Filter is_active=false as admin
        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/v1/categories?page=1&per_page=10&is_active=false');
        $response->assertStatus(200);
        $slugs = collect($response->json('data'))->pluck('slug')->all();
        $this->assertContains('inactive-test-cat', $slugs);
        $this->assertNotContains('active-test-cat', $slugs);

        // Filter is_active=true
        $responseTrue = $this->getJson('/api/v1/categories?page=1&per_page=10&is_active=true');
        $responseTrue->assertStatus(200);
        $slugsTrue = collect($responseTrue->json('data'))->pluck('slug')->all();
        $this->assertContains('active-test-cat', $slugsTrue);
        $this->assertNotContains('inactive-test-cat', $slugsTrue);
    }

    public function test_admin_can_create_waste_category(): void
    {
        $payload = [
            'name' => 'E-Waste Materials',
            'description' => 'Electronic components, cables, and circuitry',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/v1/categories', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'E-Waste Materials',
                    'slug' => 'e-waste-materials',
                    'is_active' => true,
                ],
            ]);

        $this->assertDatabaseHas('waste_categories', [
            'slug' => 'e-waste-materials',
            'name' => 'E-Waste Materials',
        ]);
    }

    public function test_non_admin_cannot_create_waste_category(): void
    {
        $payload = [
            'name' => 'Hazardous Waste',
        ];

        $response = $this->actingAs($this->citizen, 'sanctum')->postJson('/api/v1/categories', $payload);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_waste_category(): void
    {
        $category = WasteCategory::create([
            'name' => 'Old Title',
            'slug' => 'old-title',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Updated Title',
            'slug' => 'updated-title',
            'description' => 'Updated description text',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/categories/{$category->uuid}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Title',
                    'slug' => 'updated-title',
                ],
            ]);
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $category = WasteCategory::create([
            'name' => 'Plastic Bottles',
            'slug' => 'plastic-bottles',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/categories/{$category->uuid}/status", [
                'is_active' => false,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_active' => false,
                ],
            ]);

        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_admin_can_delete_category_without_reports(): void
    {
        $category = WasteCategory::create([
            'name' => 'Empty Category',
            'slug' => 'empty-category',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/categories/{$category->uuid}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('waste_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_cannot_delete_category_with_existing_reports(): void
    {
        $category = WasteCategory::create([
            'name' => 'Reported Category',
            'slug' => 'reported-category',
            'is_active' => true,
        ]);

        WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $category->id,
            'title' => 'Sample Waste Pile',
            'description' => 'A large pile of waste on the street corner',
            'status' => ReportStatus::Pending->value,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/categories/{$category->uuid}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);

        $this->assertNotSoftDeleted('waste_categories', [
            'id' => $category->id,
        ]);
    }
}
