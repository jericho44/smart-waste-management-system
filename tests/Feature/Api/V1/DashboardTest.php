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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $citizen;

    protected WasteCategory $categoryA;

    protected WasteCategory $categoryB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(UserRole::Admin->value);

        $this->citizen = User::factory()->create();
        $this->citizen->assignRole(UserRole::User->value);

        $this->categoryA = WasteCategory::create([
            'name' => 'Organic Waste',
            'slug' => 'organic-waste',
            'is_active' => true,
        ]);

        $this->categoryB = WasteCategory::create([
            'name' => 'Plastic Waste',
            'slug' => 'plastic-waste',
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_dashboards(): void
    {
        $this->getJson('/api/v1/dashboard/admin')->assertStatus(401);
        $this->getJson('/api/v1/dashboard/user')->assertStatus(401);
    }

    public function test_citizen_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->citizen)
            ->getJson('/api/v1/dashboard/admin')
            ->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard_with_accurate_metrics(): void
    {
        // Create reports
        WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->categoryA->id,
            'title' => 'Pending Organic Report',
            'description' => 'Test pending report',
            'status' => ReportStatus::Pending,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->categoryA->id,
            'title' => 'In Progress Organic Report',
            'description' => 'Test in-progress report',
            'status' => ReportStatus::InProgress,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->categoryB->id,
            'title' => 'Completed Plastic Report',
            'description' => 'Test completed report',
            'status' => ReportStatus::Completed,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->categoryB->id,
            'title' => 'Rejected Plastic Report',
            'description' => 'Test rejected report',
            'status' => ReportStatus::Rejected,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/dashboard/admin');

        $response->assertStatus(200)
            ->assertHeader('X-Request-ID')
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_reports', 4)
            ->assertJsonPath('data.pending_reports', 1)
            ->assertJsonPath('data.in_progress_reports', 1)
            ->assertJsonPath('data.completed_reports', 1)
            ->assertJsonPath('data.rejected_reports', 1)
            ->assertJsonPath('data.total_users', 2)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_reports',
                    'pending_reports',
                    'in_progress_reports',
                    'completed_reports',
                    'rejected_reports',
                    'total_users',
                    'reports_by_category',
                    'reports_by_status',
                    'monthly_statistics',
                    'latest_reports',
                ],
                'meta' => [
                    'request_id',
                    'timestamp',
                ],
            ]);

        $this->assertCount(4, $response->json('data.latest_reports'));
        $this->assertCount(6, $response->json('data.monthly_statistics'));
    }

    public function test_citizen_can_access_user_dashboard_with_only_own_metrics(): void
    {
        $otherCitizen = User::factory()->create();
        $otherCitizen->assignRole(UserRole::User->value);

        // Citizen's reports (2 total: 1 Pending, 1 Completed)
        WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->categoryA->id,
            'title' => 'My Pending Report',
            'description' => 'Mine',
            'status' => ReportStatus::Pending,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->categoryB->id,
            'title' => 'My Completed Report',
            'description' => 'Mine',
            'status' => ReportStatus::Completed,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        // Other citizen's reports (3 total)
        for ($i = 0; $i < 3; $i++) {
            WasteReport::create([
                'user_id' => $otherCitizen->id,
                'waste_category_id' => $this->categoryA->id,
                'title' => 'Other citizen report '.$i,
                'description' => 'Not mine',
                'status' => ReportStatus::Pending,
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]);
        }

        $response = $this->actingAs($this->citizen)->getJson('/api/v1/dashboard/user');

        $response->assertStatus(200)
            ->assertHeader('X-Request-ID')
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_reports', 2)
            ->assertJsonPath('data.pending_reports', 1)
            ->assertJsonPath('data.in_progress_reports', 0)
            ->assertJsonPath('data.completed_reports', 1)
            ->assertJsonPath('data.rejected_reports', 0);

        $latest = $response->json('data.latest_reports');
        $this->assertCount(2, $latest);
        foreach ($latest as $report) {
            $this->assertStringStartsWith('My ', $report['title']);
        }
    }
}
