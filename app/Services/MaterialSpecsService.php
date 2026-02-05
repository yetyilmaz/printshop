<?php

namespace App\Services;

class MaterialSpecsService
{
    /**
     * Get material specifications
     */
    public function getSpecs(string $type): array
    {
        return match($type) {
            'PLA' => $this->defaultSpecs([
                'toughness' => 60,
                'strength' => 65,
                'stiffness' => 75,
                'heat_resistance' => '60°C',
                'qualities' => 'Экологичный, биоразлагаемый, лёгкий в печати, широкая цветовая гамма',
                'use_cases' => 'Прототипы, декоративные изделия, фигурки, бытовые предметы, игрушки',
                'image_path' => 'images/materials/3d-printed-pla-filament-1024x773.jpg',
            ]),
            'ABS' => $this->defaultSpecs([
                'toughness' => 85,
                'strength' => 80,
                'stiffness' => 70,
                'heat_resistance' => '98°C',
                'qualities' => 'Ударопрочный, термостойкий, хорошая обрабатываемость, можно склеивать',
                'use_cases' => 'Корпуса электроники, механические детали, автомобильные запчасти, инструменты',
                'image_path' => 'images/materials/3d-printed-abs-filament.jpg',
            ]),
            'PETG' => $this->defaultSpecs([
                'toughness' => 80,
                'strength' => 75,
                'stiffness' => 65,
                'heat_resistance' => '80°C',
                'qualities' => 'Прозрачный, химически стойкий, пищевой сертификат, влагостойкий',
                'use_cases' => 'Упаковка продуктов, ёмкости, защитные экраны, медицинские изделия',
                'image_path' => 'images/materials/3d-printed-petg-filament-1024x774.jpg',
            ]),
            'TPU' => $this->defaultSpecs([
                'toughness' => 95,
                'strength' => 50,
                'stiffness' => 20,
                'heat_resistance' => '70°C',
                'qualities' => 'Гибкий, резиноподобный, износостойкий, высокая эластичность',
                'use_cases' => 'Уплотнители, чехлы для телефонов, гибкие шарниры, амортизаторы',
                'image_path' => 'images/materials/3d-printed-flexible-filament.jpg',
            ]),
            'Nylon' => $this->defaultSpecs([
                'toughness' => 90,
                'strength' => 90,
                'stiffness' => 60,
                'heat_resistance' => '85°C',
                'qualities' => 'Износостойкий, низкое трение, высокая усталостная прочность',
                'use_cases' => 'Шестерни, подшипники, функциональные детали машин, втулки',
                'image_path' => 'images/materials/3d-printed-nylon-filament-1024x774.jpg',
            ]),
            'Carbon Fiber Filled' => $this->defaultSpecs([
                'toughness' => 88,
                'strength' => 92,
                'stiffness' => 80,
                'heat_resistance' => '90°C',
                'qualities' => 'Увеличенная жёсткость, минимальное коробление, готовый матовый эффект',
                'use_cases' => 'Конструкционные детали, дроны, корпуса инструментов, литые прототипы',
                'image_path' => 'images/materials/3d-printed-carbon-fiber-filament-1024x774.jpg',
            ]),
            'ASA' => $this->defaultSpecs([
                'toughness' => 78,
                'strength' => 74,
                'stiffness' => 72,
                'heat_resistance' => '95°C',
                'qualities' => 'Защита от УФ, стабильность цвета, высокая стойкость к погодным условиям',
                'use_cases' => 'Внешние корпуса, уличные маркировки, водо- и морозостойкие детали',
                'image_path' => 'images/materials/3d-printed-asa-filament-1024x774.jpg',
            ]),
            'Polycarbonate' => $this->defaultSpecs([
                'toughness' => 95,
                'strength' => 96,
                'stiffness' => 80,
                'heat_resistance' => '130°C',
                'qualities' => 'Очень высокая прочность, прозрачность, стойкость к ударам',
                'use_cases' => 'Защитные экраны, фарфоровые корпуса, конструкционные элементы',
                'image_path' => 'images/materials/3d-printed-polycarbonate-filament-1024x774.jpg',
            ]),
            
            default => $this->defaultSpecs([
                'toughness' => 50,
                'strength' => 50,
                'stiffness' => 50,
                'heat_resistance' => '60°C',
                'qualities' => 'Универсальный материал для различных задач',
                'use_cases' => 'Широкий спектр применений'
            ])
        };
    }

    public function getAllMaterials(): array
    {
        $catalog = [
            ['name' => 'PLA', 'type' => 'PLA'],
            ['name' => 'ABS', 'type' => 'ABS'],
            ['name' => 'PETG', 'type' => 'PETG'],
            ['name' => 'TPU', 'type' => 'TPU'],
            ['name' => 'Nylon', 'type' => 'Nylon'],
            ['name' => 'Carbon Fiber Filled', 'type' => 'Carbon Fiber Filled'],
            ['name' => 'ASA', 'type' => 'ASA'],
            ['name' => 'Polycarbonate', 'type' => 'Polycarbonate'],
        ];

        return array_map(fn($entry) => array_merge($entry, ['specs' => $this->getSpecs($entry['type'])]), $catalog);
    }

    private function defaultSpecs(array $overrides): array
    {
        return array_merge([
            'toughness' => 50,
            'strength' => 50,
            'stiffness' => 50,
            'heat_resistance' => '60°C',
            'qualities' => 'Универсальный материал для различных задач',
            'use_cases' => 'Широкий спектр применений',
            'image_path' => 'images/materials/3d-printed-pla-filament-1024x773.jpg',
        ], $overrides);
    }
}
