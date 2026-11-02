<?php

namespace Database\Seeders;

use App\Models\WasteCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WasteCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Organic Waste',
                'slug' => 'organic-waste',
                'description' => 'Biodegradable organic waste such as food scraps, leftover vegetables, fruits, and garden compost.',
                'is_active' => true,
            ],
            [
                'name' => 'Plastic Waste',
                'slug' => 'plastic-waste',
                'description' => 'Non-biodegradable plastics including bottles, containers, bags, and recyclable plastic packaging.',
                'is_active' => true,
            ],
            [
                'name' => 'Paper Waste',
                'slug' => 'paper-waste',
                'description' => 'Cardboard, newspapers, office papers, magazines, and recyclable dry paper packaging.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            WasteCategory::firstOrCreate(
                ['slug' => $categoryData['slug']],
                array_merge($categoryData, ['uuid' => (string) Str::uuid()])
            );
        }
    }
}
