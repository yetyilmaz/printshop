<?php

namespace Database\Seeders;

use App\Models\PortfolioCategory;
use Illuminate\Database\Seeder;

class PortfolioCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Podium',
                'slug' => 'podium',
                'emoji' => '🏆',
                'description' => 'Featured works displayed on the homepage',
                'is_featured' => true,
                'order' => 1,
            ],
            [
                'name' => 'Car Parts',
                'slug' => 'car-parts',
                'emoji' => '🚗',
                'description' => 'Custom 3D printed car parts and accessories',
                'is_featured' => false,
                'order' => 2,
            ],
            [
                'name' => 'Machinery Parts',
                'slug' => 'machinery-parts',
                'emoji' => '🏭',
                'description' => 'Heavy machinery parts and components',
                'is_featured' => false,
                'order' => 3,
            ],
        ];

        foreach ($categories as $category) {
            PortfolioCategory::create($category);
        }
    }
}
