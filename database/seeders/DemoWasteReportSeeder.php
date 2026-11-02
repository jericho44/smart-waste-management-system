<?php

namespace Database\Seeders;

use App\Enums\ReportStatus;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoWasteReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Avoid duplicate seeding
        if (WasteReport::where('title', 'Overflowing Bin at Taman Menteng')->exists()) {
            return;
        }

        $citizen = User::where('email', 'user@waste.local')->first() ?? User::first();
        $admin = User::where('email', 'admin@waste.local')->first() ?? User::first();

        $organic = WasteCategory::where('slug', 'organic-waste')->first();
        $plastic = WasteCategory::where('slug', 'plastic-waste')->first();
        $paper = WasteCategory::where('slug', 'paper-waste')->first();

        if (! $citizen || ! $organic || ! $plastic || ! $paper) {
            return;
        }

        $reportsData = [
            [
                'title' => 'Overflowing Bin at Taman Menteng',
                'description' => 'Public trash bins in the park are overflowing with food waste and fallen leaves after the weekend night market.',
                'status' => ReportStatus::InProgress,
                'category_id' => $organic->id,
                'latitude' => -6.1969,
                'longitude' => 106.8296,
                'address' => 'Jl. HOS. Cokroaminoto, Menteng, Jakarta Pusat',
                'created_at' => now()->subDays(3),
                'history' => [
                    [
                        'old_status' => null,
                        'new_status' => ReportStatus::Pending,
                        'notes' => 'Report submitted by citizen',
                        'changed_by' => $citizen->id,
                        'created_at' => now()->subDays(3),
                    ],
                    [
                        'old_status' => ReportStatus::Pending,
                        'new_status' => ReportStatus::InProgress,
                        'notes' => 'Dispatched pickup team truck #04 to site',
                        'changed_by' => $admin?->id,
                        'created_at' => now()->subDays(2),
                    ],
                ],
            ],
            [
                'title' => 'Plastic Bottle Accumulation near Ciliwung Riverbank',
                'description' => 'Heavy plastic beverage bottles and packaging trapped along the river barrier next to the pedestrian bridge.',
                'status' => ReportStatus::Pending,
                'category_id' => $plastic->id,
                'latitude' => -6.2154,
                'longitude' => 106.8582,
                'address' => 'Bukit Duri, Tebet, Jakarta Selatan',
                'created_at' => now()->subHours(6),
                'history' => [
                    [
                        'old_status' => null,
                        'new_status' => ReportStatus::Pending,
                        'notes' => 'Report submitted by citizen',
                        'changed_by' => $citizen->id,
                        'created_at' => now()->subHours(6),
                    ],
                ],
            ],
            [
                'title' => 'Discarded Cardboard Boxes Blocking Sidewalk',
                'description' => 'Several large flattened paper shipping boxes left outside the commercial building near the bus stop.',
                'status' => ReportStatus::Completed,
                'category_id' => $paper->id,
                'latitude' => -6.1825,
                'longitude' => 106.8285,
                'address' => 'Jl. Kebon Sirih No. 12, Gambir, Jakarta Pusat',
                'created_at' => now()->subDays(7),
                'history' => [
                    [
                        'old_status' => null,
                        'new_status' => ReportStatus::Pending,
                        'notes' => 'Report submitted by citizen',
                        'changed_by' => $citizen->id,
                        'created_at' => now()->subDays(7),
                    ],
                    [
                        'old_status' => ReportStatus::Pending,
                        'new_status' => ReportStatus::InProgress,
                        'notes' => 'Assigned cleanup personnel',
                        'changed_by' => $admin?->id,
                        'created_at' => now()->subDays(6),
                    ],
                    [
                        'old_status' => ReportStatus::InProgress,
                        'new_status' => ReportStatus::Completed,
                        'notes' => 'Sidewalk cleared and cardboard sent to recycling center',
                        'changed_by' => $admin?->id,
                        'created_at' => now()->subDays(5),
                    ],
                ],
            ],
            [
                'title' => 'Illegal Industrial Scrap Disposal',
                'description' => 'Suspicious vehicle dumped industrial components in residential alleyway.',
                'status' => ReportStatus::Rejected,
                'category_id' => $plastic->id,
                'latitude' => -6.1754,
                'longitude' => 106.8272,
                'address' => 'Medan Merdeka Barat, Gambir, Jakarta Pusat',
                'created_at' => now()->subDays(10),
                'history' => [
                    [
                        'old_status' => null,
                        'new_status' => ReportStatus::Pending,
                        'notes' => 'Report submitted by citizen',
                        'changed_by' => $citizen->id,
                        'created_at' => now()->subDays(10),
                    ],
                    [
                        'old_status' => ReportStatus::Pending,
                        'new_status' => ReportStatus::Rejected,
                        'notes' => 'Non-household industrial waste. Forwarded to Environmental Department enforcement team.',
                        'changed_by' => $admin?->id,
                        'created_at' => now()->subDays(9),
                    ],
                ],
            ],
            [
                'title' => 'Food Court Wet Waste Accumulation',
                'description' => 'Leftover organic waste and biodegradable containers require immediate collection before odor intensifies.',
                'status' => ReportStatus::Completed,
                'category_id' => $organic->id,
                'latitude' => -6.2255,
                'longitude' => 106.8001,
                'address' => 'Senayan, Kebayoran Baru, Jakarta Selatan',
                'created_at' => now()->subMonth(),
                'history' => [
                    [
                        'old_status' => null,
                        'new_status' => ReportStatus::Pending,
                        'notes' => 'Report submitted by citizen',
                        'changed_by' => $citizen->id,
                        'created_at' => now()->subMonth(),
                    ],
                    [
                        'old_status' => ReportStatus::Pending,
                        'new_status' => ReportStatus::InProgress,
                        'notes' => 'Collection crew en route',
                        'changed_by' => $admin?->id,
                        'created_at' => now()->subMonth()->addHours(2),
                    ],
                    [
                        'old_status' => ReportStatus::InProgress,
                        'new_status' => ReportStatus::Completed,
                        'notes' => 'Collected and transported to composting facility',
                        'changed_by' => $admin?->id,
                        'created_at' => now()->subMonth()->addHours(5),
                    ],
                ],
            ],
        ];

        foreach ($reportsData as $item) {
            $report = WasteReport::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $citizen->id,
                'waste_category_id' => $item['category_id'],
                'title' => $item['title'],
                'description' => $item['description'],
                'status' => $item['status'],
                'latitude' => $item['latitude'],
                'longitude' => $item['longitude'],
                'address' => $item['address'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['created_at'],
            ]);

            foreach ($item['history'] as $hist) {
                ReportStatusHistory::create([
                    'uuid' => (string) Str::uuid(),
                    'waste_report_id' => $report->id,
                    'changed_by' => $hist['changed_by'],
                    'old_status' => $hist['old_status'],
                    'new_status' => $hist['new_status'],
                    'notes' => $hist['notes'],
                    'created_at' => $hist['created_at'],
                ]);
            }
        }
    }
}
