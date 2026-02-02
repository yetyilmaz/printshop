<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PortfolioItem;
use App\Models\PortfolioImage;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $item1 = PortfolioItem::create([
            'title' => 'Держатель для телефона',
            'slug' => 'phone-stand',
            'description' => 'Печатал из PLA, высота слоя 0.2 мм.'
        ]);

        PortfolioImage::create([
            'portfolio_item_id' => $item1->id,
            'path' => 'portfolio/demo1.jpg',
            'sort_order' => 1,
        ]);

        $item2 = PortfolioItem::create([
            'title' => 'Корпус для электроники',
            'slug' => 'electronics-case',
            'description' => 'Корпус под проект на Arduino, PETG.'
        ]);

        PortfolioImage::create([
            'portfolio_item_id' => $item2->id,
            'path' => 'portfolio/demo2.jpg',
            'sort_order' => 1,
        ]);
    }
}
