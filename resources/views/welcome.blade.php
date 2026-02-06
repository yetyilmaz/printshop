<x-app-layout>
    <div class="max-w-[1120px] mx-auto px-4 py-8">
        <!-- Hero Section -->
        <section class="glass-card relative overflow-hidden p-8 sm:p-12 mb-4">
            <!-- Background Gradients -->
            <div class="pointer-events-none absolute inset-0 opacity-60">
                <div
                    class="absolute right-[10%] top-[10%] w-[700px] h-[450px] bg-gradient-radial from-black/5 to-transparent blur-3xl">
                </div>
                <div
                    class="absolute left-[10%] top-[10%] w-[900px] h-[500px] bg-gradient-radial from-black/5 to-transparent blur-3xl">
                </div>
            </div>

            <div class="relative z-10">
                <div
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-full border border-black/10 bg-white/55 text-[12px] text-black/75 mb-6">
                    3D печать на заказ • Загрузка файлов • Автоматический расчёт
                </div>

                <h1 class="text-4xl sm:text-[44px] leading-[1.05] font-bold tracking-[-0.04em] mb-4">
                    Профессиональная 3D печать для ваших проектов
                </h1>

                <p class="text-black/60 text-base sm:text-lg leading-relaxed max-w-[56ch] mb-8">
                    Сервис предлагает высококачественную печать из различных пластиков — от бытовых и декоративных изделий до функциональных запчастей для станков и оборудования. Промышленное качество для ваших прототипов и деталей.
                </p>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('order.create') }}"
                        class="btn-primary inline-flex items-center justify-center gap-2.5 rounded-[18px] px-5 py-3 text-[13px] font-medium transition-all active:scale-[0.98]">
                        Создать заказ
                    </a>
                    <a href="{{ route('portfolio') }}" class="btn-glass">
                        Наши работы
                    </a>
                    <a href="{{ route('materials.guide') }}" class="btn-glass opacity-70">
                        Гид по материалам
                    </a>
                </div>
            </div>
        </section>

        <!-- KPI Grid -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="glass-card p-6">
                <div class="font-[640] mb-2">Есть 3D модель?</div>
                <div class="text-[13px] text-black/60">Загрузите STL/OBJ файл при создании заказа — система автоматически рассчитает стоимость на основе материала и объёма. Наши специалисты свяжутся для уточнения деталей и сроков.</div>
            </div>
            <div class="glass-card p-6">
                <div class="font-[640] mb-2">Нет модели?</div>
                <div class="text-[13px] text-black/60">Не беда! Загрузите изображения, чертежи или просто опишите идею — наши специалисты создадут 3D модель и рассчитают стоимость. Мы поможем воплотить любой проект.</div>
            </div>
            <div class="glass-card p-6">
                <div class="font-[640] mb-2">Разные материалы</div>
                <div class="text-[13px] text-black/60">Широкий выбор пластиков с разными свойствами — PLA, ABS, PETG, Nylon и другие. Подберём оптимальный материал для вашей задачи: от декора до прочных механических деталей.</div>
            </div>
        </section>

        <!-- Portfolio Section -->
        @if($podiumProjects->isNotEmpty())
        <section class="glass-card p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-[650] tracking-[-0.02em]">Наши лучшие работы</h2>
                <a href="{{ route('portfolio') }}" class="text-[13px] text-blue-600 hover:text-blue-700 font-medium transition-colors">
                    Смотреть все →
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($podiumProjects as $project)
                <div class="group bg-white/50 rounded-xl overflow-hidden hover:bg-white/70 transition-all duration-300 cursor-pointer border border-gray-200 hover:border-gray-300"
                     onclick="window.location.href='{{ route('portfolio') }}'">
                    <!-- 3D Model Preview -->
                    <div class="aspect-square bg-gradient-to-br from-gray-50 to-gray-100 relative overflow-hidden">
                        @if($project['glb_model'])
                            <div class="model-viewer-container w-full h-full" 
                                 x-init="window.initModelViewer($el, '{{ $project['glb_model'] }}', { autoRotate: true })"
                                 role="img" 
                                 aria-label="3D model of {{ $project['title'] }}">
                            </div>
                        @elseif($project['image'])
                            <img src="{{ $project['image'] }}" 
                                 alt="{{ $project['title'] }}" 
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="flex items-center justify-center h-full text-gray-400 text-sm">
                                Нет изображения
                            </div>
                        @endif
                    </div>

                    <!-- Project Info -->
                    <div class="p-4">
                        <div class="text-xs text-blue-600 font-medium mb-1">Podium</div>
                        <h3 class="font-semibold text-base mb-1 line-clamp-1">{{ $project['title'] }}</h3>
                        @if($project['description'])
                        <p class="text-sm text-gray-600 line-clamp-2">{{ $project['description'] }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @else
        <section class="glass-card p-8 text-center">
            <div class="text-gray-500">Портфолио наполняется</div>
        </section>
        @endif

        <!-- Bottom Callout -->
        <section class="glass-card p-6 flex flex-col gap-4">
            <div>
                <div class="font-[650] text-base tracking-[-0.02em]">Готовы начать работу над проектом?</div>
                <div class="text-[13px] text-black/60 mt-1">Свяжитесь с нами в удобном мессенджере — быстро ответим и поможем оформить заказ.</div>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <a href="https://t.me/reneprint" class="rounded-[16px] border border-black/10 bg-white/70 px-4 py-3 text-sm font-semibold text-black/80 hover:border-black/30 hover:shadow-lg transition">
                    <div class="flex items-center gap-2">
                        <img src="/images/icons/Telegram_2019_Logo.svg" alt="Telegram" class="w-4 h-4">
                        <span>Telegram</span>
                    </div>
                    <span class="text-[12px] text-gray-500">@reneprint</span>
                </a>
                <a href="https://wa.me/77066071331" class="rounded-[16px] border border-black/10 bg-white/70 px-4 py-3 text-sm font-semibold text-black/80 hover:border-black/30 hover:shadow-lg transition">
                    <div class="flex items-center gap-2">
                        <img src="/images/icons/WhatsApp.svg" alt="WhatsApp" class="w-4 h-4">
                        <span>Whatsapp</span>
                    </div>
                    <span class="text-[12px] text-gray-500">+7 706 607 1331</span>
                </a>
                <a href="mailto:orders@reneprint.com" class="rounded-[16px] border border-black/10 bg-white/70 px-4 py-3 text-sm font-semibold text-black/80 hover:border-black/30 hover:shadow-lg transition">
                    <div class="flex items-center gap-2">
                        <img src="/images/icons/Email.svg" alt="Email" class="w-4 h-4">
                        <span>E-mail</span>
                    </div>
                    <span class="text-[12px] text-gray-500">orders@reneprint.com</span>
                </a>
            </div>
        </section>

        <!-- Footer -->
        <footer class="mt-12 text-center text-[12px] text-black/50">
            &copy; {{ date('Y') }} PrintLab • 3D Печать • Казахстан
        </footer>
    </div>
</x-app-layout>