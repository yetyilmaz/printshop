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
            'PLA' => [
                'toughness' => 60,
                'strength' => 65,
                'stiffness' => 75,
                'heat_resistance' => '60°C',
                'qualities' => 'Экологичный, биоразлагаемый, лёгкий в печати, широкая цветовая гамма',
                'use_cases' => 'Прототипы, декоративные изделия, фигурки, бытовые предметы, игрушки'
            ],
            'ABS' => [
                'toughness' => 85,
                'strength' => 80,
                'stiffness' => 70,
                'heat_resistance' => '98°C',
                'qualities' => 'Ударопрочный, термостойкий, хорошая обрабатываемость, можно склеивать',
                'use_cases' => 'Корпуса электроники, механические детали, автомобильные запчасти, инструменты'
            ],
            'PETG' => [
                'toughness' => 80,
                'strength' => 75,
                'stiffness' => 65,
                'heat_resistance' => '80°C',
                'qualities' => 'Прозрачный, химически стойкий, пищевой сертификат, влагостойкий',
                'use_cases' => 'Упаковка продуктов, ёмкости, защитные экраны, медицинские изделия'
            ],
            'TPU' => [
                'toughness' => 95,
                'strength' => 50,
                'stiffness' => 20,
                'heat_resistance' => '70°C',
                'qualities' => 'Гибкий, резиноподобный, износостойкий, высокая эластичность',
                'use_cases' => 'Уплотнители, чехлы для телефонов, гибкие шарниры, амортизаторы'
            ],
            'Nylon' => [
                'toughness' => 90,
                'strength' => 90,
                'stiffness' => 60,
                'heat_resistance' => '85°C',
                'qualities' => 'Износостойкий, низкое трение, высокая усталостная прочность',
                'use_cases' => 'Шестерни, подшипники, функциональные детали машин, втулки'
            ],
            default => [
                'toughness' => 50,
                'strength' => 50,
                'stiffness' => 50,
                'heat_resistance' => '60°C',
                'qualities' => 'Универсальный материал для различных задач',
                'use_cases' => 'Широкий спектр применений'
            ]
        };
    }
}
