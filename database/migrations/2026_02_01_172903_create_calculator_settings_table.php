<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calculator_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // quality, infill
            $table->string('slug'); // identification: high, draft, 10, 20
            $table->string('label'); // Display name
            $table->decimal('multiplier', 8, 2);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default values
        $now = now();
        $defaults = [
            // Quality
            ['category' => 'quality', 'slug' => 'draft', 'label' => 'Draft (0.28mm)', 'multiplier' => 0.8, 'sort_order' => 1],
            ['category' => 'quality', 'slug' => 'standard', 'label' => 'Standard (0.20mm)', 'multiplier' => 1.0, 'sort_order' => 2],
            ['category' => 'quality', 'slug' => 'high', 'label' => 'High Detail (0.12mm)', 'multiplier' => 1.2, 'sort_order' => 3],

            // Infill
            ['category' => 'infill', 'slug' => '10', 'label' => '10% - Легкое (Декор)', 'multiplier' => 0.9, 'sort_order' => 1],
            ['category' => 'infill', 'slug' => '20', 'label' => '20% - Стандарт (Баланс)', 'multiplier' => 1.0, 'sort_order' => 2],
            ['category' => 'infill', 'slug' => '40', 'label' => '40% - Прочное', 'multiplier' => 1.2, 'sort_order' => 3],
            ['category' => 'infill', 'slug' => '60', 'label' => '60% - Усиленное', 'multiplier' => 1.4, 'sort_order' => 4],
            ['category' => 'infill', 'slug' => '80', 'label' => '80% - Почти монолит', 'multiplier' => 1.7, 'sort_order' => 5],
            ['category' => 'infill', 'slug' => '100', 'label' => '100% - Монолит', 'multiplier' => 2.0, 'sort_order' => 6],
        ];

        foreach ($defaults as $default) {
            DB::table('calculator_settings')->insert(array_merge($default, ['created_at' => $now, 'updated_at' => $now]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculator_settings');
    }
};
