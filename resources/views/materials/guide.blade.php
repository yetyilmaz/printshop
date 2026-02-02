<x-app-layout>
    <div class="max-w-[1120px] mx-auto px-4 py-8">
        <!-- Header -->
        <div class="glass-card p-8 mb-6">
            <div class="mb-4">
                <a href="{{ route('home') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors">
                    ← Вернуться на главную
                </a>
            </div>
            <h1 class="text-4xl font-bold tracking-[-0.02em] mb-4">Гид по материалам</h1>
            <p class="text-black/60 text-lg leading-relaxed max-w-[70ch]">
                Выбор правильного материала критически важен для успеха вашего проекта. Каждый пластик имеет уникальные свойства, которые делают его оптимальным для определённых задач.
            </p>
        </div>

        <!-- Materials Grid -->
        @if($materials->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($materials as $material)
            <div class="glass-card p-6 hover:bg-white/70 transition-all duration-300 border border-gray-200">
                <!-- Material Header -->
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h2 class="text-2xl font-[650] tracking-[-0.02em] mb-2">{{ $material->name }}</h2>
                        <div class="inline-block px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-xs font-medium text-blue-700">
                            {{ $material->type }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-[650] text-blue-600">{{ number_format($material->price_per_cm3, 2) }} ₸</div>
                        <div class="text-xs text-black/50">за см³</div>
                    </div>
                </div>

                @php
                $specs = app(\App\Services\MaterialSpecsService::class)->getSpecs($material->type);
                @endphp

                <!-- Property Bars -->
                <div class="space-y-3 mb-5">
                    <!-- Toughness -->
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-xs font-medium text-black/70">Ударопрочность</span>
                        </div>
                        <div class="h-5 bg-gray-100 rounded-full overflow-hidden relative border border-gray-300">
                            <div class="h-full bg-gradient-to-r from-red-400 to-red-500 rounded-full transition-all" 
                                 style="width: {{ $specs['toughness'] }}%"></div>
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-1/4 h-full border-r border-white/30"></div>
                                <div class="w-1/4 h-full border-r border-white/30"></div>
                                <div class="w-1/4 h-full border-r border-white/30"></div>
                                <div class="w-1/4 h-full"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Strength -->
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-xs font-medium text-black/70">Прочность</span>
                        </div>
                        <div class="h-5 bg-gray-100 rounded-full overflow-hidden relative border border-gray-300">
                            <div class="h-full bg-gradient-to-r from-green-400 to-green-500 rounded-full transition-all" 
                                 style="width: {{ $specs['strength'] }}%"></div>
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-1/4 h-full border-r border-white/30"></div>
                                <div class="w-1/4 h-full border-r border-white/30"></div>
                                <div class="w-1/4 h-full border-r border-white/30"></div>
                                <div class="w-1/4 h-full"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Stiffness -->
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-xs font-medium text-black/70">Жёсткость</span>
                        </div>
                        <div class="h-5 bg-gray-100 rounded-full overflow-hidden relative border border-gray-300">
                            <div class="h-full bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-full transition-all" 
                                 style="width: {{ $specs['stiffness'] }}%"></div>
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-1/4 h-full border-r border-white/30"></div>
                                <div class="w-1/4 h-full border-r border-white/30"></div>
                                <div class="w-1/4 h-full border-r border-white/30"></div>
                                <div class="w-1/4 h-full"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Heat Resistance -->
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs font-medium text-black/70">Термостойкость</span>
                        <span class="text-sm font-[640] text-orange-600">{{ $specs['heat_resistance'] }}</span>
                    </div>
                </div>

                <!-- Qualities -->
                <div class="mb-4 pb-4 border-b border-black/10">
                    <div class="text-xs font-medium text-black/70 mb-2">Особенности</div>
                    <p class="text-sm text-black/80 leading-relaxed">{{ $specs['qualities'] }}</p>
                </div>

                <!-- Use Cases -->
                <div>
                    <div class="text-xs font-medium text-black/70 mb-2">Лучше всего подходит для</div>
                    <p class="text-sm text-black/80 leading-relaxed">{{ $specs['use_cases'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="glass-card p-12 text-center">
            <div class="text-black/50 mb-4">Материалы ещё не добавлены</div>
            @auth
            <a href="{{ route('admin.materials.create') }}" class="btn-primary inline-flex items-center justify-center gap-2.5 rounded-[18px] px-5 py-2.5 text-[13px] font-medium transition-all active:scale-[0.98]">
                Добавить материал
            </a>
            @endauth
        </div>
        @endif

        <!-- Comparison Guide -->
        <div class="glass-card p-8 mt-6">
            <h2 class="text-2xl font-[650] tracking-[-0.02em] mb-4">Как выбрать материал?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <div class="text-4xl mb-3">🎨</div>
                    <h3 class="font-[640] mb-2">Для декора и моделей</h3>
                    <p class="text-sm text-black/60">PLA — идеальный выбор для декоративных изделий, фигурок и прототипов. Лёгок в печати, доступен в разных цветах.</p>
                </div>
                <div>
                    <div class="text-4xl mb-3">⚙️</div>
                    <h3 class="font-[640] mb-2">Для механических деталей</h3>
                    <p class="text-sm text-black/60">ABS и Nylon — прочные материалы для функциональных деталей, корпусов и запчастей. Выдерживают нагрузки и высокие температуры.</p>
                </div>
                <div>
                    <div class="text-4xl mb-3">🔧</div>
                    <h3 class="font-[640] mb-2">Для гибких элементов</h3>
                    <p class="text-sm text-black/60">TPU — эластичный материал для уплотнителей, чехлов и гибких соединений. Износостойкий и резиноподобный.</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="glass-card p-6 flex flex-wrap justify-between items-center gap-4 mt-6">
            <div>
                <div class="font-[650] text-base tracking-[-0.02em]">Не уверены в выборе материала?</div>
                <div class="text-[13px] text-black/60 mt-1">Наши специалисты помогут подобрать оптимальный вариант для вашей задачи.</div>
            </div>
            <a href="{{ route('order.create') }}"
                class="btn-primary inline-flex items-center justify-center gap-2.5 rounded-[18px] px-5 py-2.5 text-[13px] font-medium transition-all active:scale-[0.98]">
                Создать заказ
            </a>
        </div>
    </div>
</x-app-layout>
