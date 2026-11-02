<?php

namespace Tests\Feature\Api\V1;

use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Models\ReportImage;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteReport;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WasteReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $citizen;

    protected WasteCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Storage::fake('public');

        $this->admin = User::factory()->create(['email' => 'admin.report@waste.local']);
        $this->admin->assignRole(UserRole::Admin->value);

        $this->citizen = User::factory()->create(['email' => 'citizen.report@waste.local']);
        $this->citizen->assignRole(UserRole::User->value);

        $this->category = WasteCategory::create([
            'name' => 'Organic Category',
            'slug' => 'organic-category',
            'is_active' => true,
        ]);
    }

    public function test_citizen_can_create_report_with_images_and_coordinates(): void
    {
        $image1 = UploadedFile::fake()->image('trash1.jpg', 600, 400);
        $image2 = UploadedFile::fake()->image('trash2.png', 800, 600);

        $payload = [
            'title' => 'Illegal Waste Dump Near Park',
            'description' => 'A large collection of plastic and organic waste accumulated near the park gate.',
            'category_uuid' => $this->category->uuid,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'address' => 'Jl. Taman Suropati No. 5, Jakarta',
            'images' => [$image1, $image2],
        ];

        $response = $this->actingAs($this->citizen, 'sanctum')->postJson('/api/v1/reports', $payload);

        $response->assertStatus(201)
            ->assertHeader('X-Request-ID')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'uuid',
                    'title',
                    'description',
                    'status',
                    'latitude',
                    'longitude',
                    'address',
                    'user',
                    'category',
                    'images',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Illegal Waste Dump Near Park',
                    'status' => 'PENDING',
                    'latitude' => -6.2088,
                    'longitude' => 106.8456,
                ],
            ]);

        $this->assertDatabaseHas('waste_reports', [
            'title' => 'Illegal Waste Dump Near Park',
            'user_id' => $this->citizen->id,
            'status' => 'PENDING',
        ]);

        $report = WasteReport::where('title', 'Illegal Waste Dump Near Park')->first();
        $this->assertNotNull($report);
        $this->assertCount(2, $report->images);

        // Verify status history initialized
        $this->assertDatabaseHas('report_status_histories', [
            'waste_report_id' => $report->id,
            'new_status' => 'PENDING',
            'changed_by' => $this->citizen->id,
        ]);

        // Verify image files exist on fake storage
        foreach ($report->images as $img) {
            Storage::disk('public')->assertExists($img->image_path);
        }
    }

    public function test_citizen_only_sees_their_own_reports(): void
    {
        $otherCitizen = User::factory()->create();
        $otherCitizen->assignRole(UserRole::User->value);

        // 2 reports for our citizen
        WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->category->id,
            'title' => 'My Report 1',
            'description' => 'Desc 1',
            'status' => ReportStatus::Pending->value,
            'latitude' => 0.0,
            'longitude' => 0.0,
        ]);
        WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->category->id,
            'title' => 'My Report 2',
            'description' => 'Desc 2',
            'status' => ReportStatus::Pending->value,
            'latitude' => 0.0,
            'longitude' => 0.0,
        ]);

        // 3 reports for other citizen
        for ($i = 1; $i <= 3; $i++) {
            WasteReport::create([
                'user_id' => $otherCitizen->id,
                'waste_category_id' => $this->category->id,
                'title' => "Other Report $i",
                'description' => 'Desc',
                'status' => ReportStatus::Pending->value,
                'latitude' => 0.0,
                'longitude' => 0.0,
            ]);
        }

        $response = $this->actingAs($this->citizen, 'sanctum')->getJson('/api/v1/reports');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_admin_can_see_all_reports(): void
    {
        $otherCitizen = User::factory()->create();

        WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->category->id,
            'title' => 'Citizen Report',
            'description' => 'Desc',
            'status' => ReportStatus::Pending->value,
            'latitude' => 0.0,
            'longitude' => 0.0,
        ]);

        WasteReport::create([
            'user_id' => $otherCitizen->id,
            'waste_category_id' => $this->category->id,
            'title' => 'Other Citizen Report',
            'description' => 'Desc',
            'status' => ReportStatus::Pending->value,
            'latitude' => 0.0,
            'longitude' => 0.0,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/v1/reports');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_citizen_can_update_pending_report(): void
    {
        $report = WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->category->id,
            'title' => 'Initial Report Title',
            'description' => 'Initial description',
            'status' => ReportStatus::Pending->value,
            'latitude' => 1.23,
            'longitude' => 4.56,
        ]);

        $payload = [
            'title' => 'Corrected Report Title',
            'description' => 'Updated details about the waste site.',
            'category_uuid' => $this->category->uuid,
            'latitude' => 1.24,
            'longitude' => 4.57,
            'address' => 'Updated street',
        ];

        $response = $this->actingAs($this->citizen, 'sanctum')->putJson("/api/v1/reports/{$report->uuid}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Corrected Report Title',
                    'address' => 'Updated street',
                ],
            ]);
    }

    public function test_citizen_cannot_update_in_progress_report(): void
    {
        $report = WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->category->id,
            'title' => 'Work in Progress Report',
            'description' => 'Initial description',
            'status' => ReportStatus::InProgress->value,
            'latitude' => 1.23,
            'longitude' => 4.56,
        ]);

        $payload = [
            'title' => 'Attempting Edit',
            'description' => 'Updated details',
            'category_uuid' => $this->category->uuid,
            'latitude' => 1.24,
            'longitude' => 4.57,
        ];

        $response = $this->actingAs($this->citizen, 'sanctum')->putJson("/api/v1/reports/{$report->uuid}", $payload);

        $response->assertStatus(403);
    }

    public function test_admin_can_transition_status_and_record_history(): void
    {
        $report = WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->category->id,
            'title' => 'Report for Pickup',
            'description' => 'Waste pile',
            'status' => ReportStatus::Pending->value,
            'latitude' => 1.0,
            'longitude' => 1.0,
        ]);

        // 1. PENDING -> IN_PROGRESS
        $response1 = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/reports/{$report->uuid}/status", [
                'status' => 'IN_PROGRESS',
                'notes' => 'Sanitation crew truck #4 dispatched.',
            ]);

        $response1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'IN_PROGRESS',
                ],
            ]);

        $this->assertEquals(ReportStatus::InProgress, $report->fresh()->status);

        // 2. IN_PROGRESS -> COMPLETED
        $response2 = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/reports/{$report->uuid}/status", [
                'status' => 'COMPLETED',
                'notes' => 'Waste cleared successfully.',
            ]);

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'COMPLETED',
                ],
            ]);

        $this->assertEquals(ReportStatus::Completed, $report->fresh()->status);

        // Verify history logs
        $this->assertDatabaseHas('report_status_histories', [
            'waste_report_id' => $report->id,
            'old_status' => 'PENDING',
            'new_status' => 'IN_PROGRESS',
            'notes' => 'Sanitation crew truck #4 dispatched.',
        ]);

        $this->assertDatabaseHas('report_status_histories', [
            'waste_report_id' => $report->id,
            'old_status' => 'IN_PROGRESS',
            'new_status' => 'COMPLETED',
            'notes' => 'Waste cleared successfully.',
        ]);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $report = WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->category->id,
            'title' => 'Report Skipping Step',
            'description' => 'Desc',
            'status' => ReportStatus::Pending->value,
            'latitude' => 1.0,
            'longitude' => 1.0,
        ]);

        // PENDING cannot jump directly to COMPLETED
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/reports/{$report->uuid}/status", [
                'status' => 'COMPLETED',
                'notes' => 'Invalid jump',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->assertEquals(ReportStatus::Pending, $report->fresh()->status);
    }

    public function test_citizen_cannot_transition_status(): void
    {
        $report = WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->category->id,
            'title' => 'Report',
            'description' => 'Desc',
            'status' => ReportStatus::Pending->value,
            'latitude' => 1.0,
            'longitude' => 1.0,
        ]);

        $response = $this->actingAs($this->citizen, 'sanctum')
            ->patchJson("/api/v1/reports/{$report->uuid}/status", [
                'status' => 'IN_PROGRESS',
            ]);

        $response->assertStatus(403);
    }

    public function test_report_image_upload_and_deletion(): void
    {
        $report = WasteReport::create([
            'user_id' => $this->citizen->id,
            'waste_category_id' => $this->category->id,
            'title' => 'Report for Images',
            'description' => 'Desc',
            'status' => ReportStatus::Pending->value,
            'latitude' => 1.0,
            'longitude' => 1.0,
        ]);

        $imageFile = UploadedFile::fake()->image('extra_trash.jpg');

        $uploadResponse = $this->actingAs($this->citizen, 'sanctum')
            ->postJson("/api/v1/reports/{$report->uuid}/images", [
                'images' => [$imageFile],
            ]);

        $uploadResponse->assertStatus(201);
        $imageUuid = $uploadResponse->json('data.0.uuid');
        $this->assertNotEmpty($imageUuid);

        $imageRecord = ReportImage::where('uuid', $imageUuid)->first();
        $this->assertNotNull($imageRecord);
        Storage::disk('public')->assertExists($imageRecord->image_path);

        // Delete the image
        $deleteResponse = $this->actingAs($this->citizen, 'sanctum')
            ->deleteJson("/api/v1/reports/{$report->uuid}/images/{$imageUuid}");

        $deleteResponse->assertStatus(200);
        $this->assertSoftDeleted('report_images', ['id' => $imageRecord->id]);
        Storage::disk('public')->assertMissing($imageRecord->image_path);
    }
}

