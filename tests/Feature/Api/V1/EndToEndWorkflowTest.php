<?php

namespace Tests\Feature\Api\V1;

use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\WasteCategory;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EndToEndWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected WasteCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin@waste.local',
            'password' => Hash::make('AdminPass123!'),
        ]);
        $this->admin->assignRole(UserRole::Admin->value);

        $this->category = WasteCategory::create([
            'name' => 'Plastic Waste',
            'slug' => 'plastic-waste',
            'description' => 'Recyclable plastics',
            'is_active' => true,
        ]);
    }

    public function test_complete_waste_management_lifecycle(): void
    {
        // 1. Citizen registers
        $registerResponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Citizen',
            'email' => 'jane@waste.local',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'jane@waste.local');

        $citizenToken = $registerResponse->json('data.token');
        $citizenHeaders = ['Authorization' => 'Bearer '.$citizenToken];

        // 2. Citizen fetches categories
        $categoriesResponse = $this->getJson('/api/v1/categories');
        $categoriesResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // 3. Citizen creates a report
        $createReportResponse = $this->withHeaders($citizenHeaders)->postJson('/api/v1/reports', [
            'title' => 'Plastic Litter in Community Park',
            'description' => 'Pile of discarded plastic bottles near the playground',
            'category_uuid' => $this->category->uuid,
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'address' => 'Jl. Sudirman Park, Jakarta',
        ]);

        $createReportResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', ReportStatus::Pending->value);

        $reportUuid = $createReportResponse->json('data.uuid');
        $this->assertNotEmpty($reportUuid);

        // 4. Citizen CANNOT transition report status (must return 403)
        $citizenStatusAttempt = $this->withHeaders($citizenHeaders)->patchJson("/api/v1/reports/{$reportUuid}/status", [
            'status' => ReportStatus::InProgress->value,
            'notes' => 'Attempting self-approval',
        ]);
        $citizenStatusAttempt->assertStatus(403);

        // 5. Admin logs in
        app('auth')->forgetGuards();
        $adminLoginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@waste.local',
            'password' => 'AdminPass123!',
        ]);
        $adminLoginResponse->assertStatus(200);
        $adminToken = $adminLoginResponse->json('data.token');
        $adminHeaders = ['Authorization' => 'Bearer '.$adminToken];

        // 6. Admin transitions status: PENDING -> IN_PROGRESS
        app('auth')->forgetGuards();
        $transitionToInProgress = $this->withHeaders($adminHeaders)->patchJson("/api/v1/reports/{$reportUuid}/status", [
            'status' => ReportStatus::InProgress->value,
            'notes' => 'Assigned cleanup crew team alpha',
        ]);

        $transitionToInProgress->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', ReportStatus::InProgress->value);

        // 7. Citizen CANNOT edit or delete once report is IN_PROGRESS
        app('auth')->forgetGuards();
        $citizenEditAttempt = $this->withHeaders($citizenHeaders)->putJson("/api/v1/reports/{$reportUuid}", [
            'title' => 'Altered title after dispatch',
            'description' => 'Altered description',
            'category_uuid' => $this->category->uuid,
            'latitude' => -6.2000,
            'longitude' => 106.8166,
        ]);
        $citizenEditAttempt->assertStatus(403);

        app('auth')->forgetGuards();
        $citizenDeleteAttempt = $this->withHeaders($citizenHeaders)->deleteJson("/api/v1/reports/{$reportUuid}");
        $citizenDeleteAttempt->assertStatus(403);

        // 8. Admin transitions status: IN_PROGRESS -> COMPLETED
        app('auth')->forgetGuards();
        $transitionToCompleted = $this->withHeaders($adminHeaders)->patchJson("/api/v1/reports/{$reportUuid}/status", [
            'status' => ReportStatus::Completed->value,
            'notes' => 'Waste collected and sorted successfully',
        ]);
        $transitionToCompleted->assertStatus(200)
            ->assertJsonPath('data.status', ReportStatus::Completed->value);

        // 9. Invalid transition rejected: COMPLETED -> IN_PROGRESS (Terminal status rule)
        app('auth')->forgetGuards();
        $invalidTransition = $this->withHeaders($adminHeaders)->patchJson("/api/v1/reports/{$reportUuid}/status", [
            'status' => ReportStatus::InProgress->value,
            'notes' => 'Illegal rollback attempt',
        ]);
        $invalidTransition->assertStatus(422)
            ->assertJsonPath('success', false);

        // 10. Verify audit status history chain
        app('auth')->forgetGuards();
        $historyResponse = $this->withHeaders($citizenHeaders)->getJson("/api/v1/reports/{$reportUuid}/history");
        $historyResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $historyData = $historyResponse->json('data');
        $this->assertCount(3, $historyData); // Initial creation (null -> PENDING), PENDING -> IN_PROGRESS, IN_PROGRESS -> COMPLETED
        $this->assertEquals(ReportStatus::Completed->value, $historyData[2]['new_status']);

        // 11. Admin dashboard accurately reflects completed report
        app('auth')->forgetGuards();
        $adminDashboard = $this->withHeaders($adminHeaders)->getJson('/api/v1/dashboard/admin');
        $adminDashboard->assertStatus(200)
            ->assertJsonPath('data.total_reports', 1)
            ->assertJsonPath('data.completed_reports', 1)
            ->assertJsonPath('data.pending_reports', 0);

        // 12. Citizen dashboard accurately reflects own completed report
        app('auth')->forgetGuards();
        $citizenDashboard = $this->withHeaders($citizenHeaders)->getJson('/api/v1/dashboard/user');
        $citizenDashboard->assertStatus(200)
            ->assertJsonPath('data.total_reports', 1)
            ->assertJsonPath('data.completed_reports', 1);

        // 13. Citizen logs out
        app('auth')->forgetGuards();
        $logoutResponse = $this->withHeaders($citizenHeaders)->postJson('/api/v1/auth/logout');
        $logoutResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // 14. Access with revoked token is rejected
        app('auth')->forgetGuards();
        $revokedAttempt = $this->withHeaders($citizenHeaders)->getJson('/api/v1/auth/me');
        $revokedAttempt->assertStatus(401);
    }
}
