<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;
use App\Models\Color;

class MaterialColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Materials
        $materials = [
            ['name' => 'PLA', 'slug' => 'pla', 'price_per_cm3' => 0.05, 'type' => 'PLA'],
            ['name' => 'PETG', 'slug' => 'petg', 'price_per_cm3' => 0.07, 'type' => 'PETG'],
            ['name' => 'ABS', 'slug' => 'abs', 'price_per_cm3' => 0.06, 'type' => 'ABS'],
            ['name' => 'TPU (Flexible)', 'slug' => 'tpu', 'price_per_cm3' => 0.10, 'type' => 'TPU'],
        ];

        foreach ($materials as $m) {
            Material::firstOrCreate(['slug' => $m['slug']], $m);
        }

        // Colors
        $colors = [
            ['name' => 'Black', 'hex_code' => '#000000'],
            ['name' => 'White', 'hex_code' => '#FFFFFF'],
            ['name' => 'Grey', 'hex_code' => '#808080'],
            ['name' => 'Red', 'hex_code' => '#FF0000'],
            ['name' => 'Blue', 'hex_code' => '#0000FF'],
        ];

        foreach ($colors as $c) {
            Color::firstOrCreate(['name' => $c['name']], $c);
        }
    }
}
