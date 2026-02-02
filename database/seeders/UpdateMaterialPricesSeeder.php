<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;

class UpdateMaterialPricesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prices = [
            'pla' => 150,
            'petg' => 180,
            'abs' => 200,
            'tpu' => 300,
        ];

        foreach ($prices as $slug => $price) {
            Material::where('slug', $slug)->update(['price_per_cm3' => $price]);
        }
    }
}
