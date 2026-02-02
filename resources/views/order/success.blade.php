<x-app-layout>
    <div class="max-w-[1120px] mx-auto px-6 py-24 flex flex-col items-center justify-center min-h-[70vh]">
        <div class="glass-card p-12 text-center max-w-lg w-full relative overflow-hidden">
            <!-- Decorative Background Element -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-black/[0.03] rounded-full blur-3xl"></div>

            <div
                class="w-20 h-20 bg-[#0a0a0a] rounded-[28px] flex items-center justify-center mx-auto mb-8 shadow-glass-sm animate-fade-in">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-[32px] font-[750] tracking-[-0.04em] text-black mb-1 animate-fade-in"
                style="animation-delay: 0.1s">Заказ Оформлен!</h1>
            @if($orderId)
                <div class="text-[14px] font-semibold text-black/60 mb-6 animate-fade-in" style="animation-delay: 0.15s">
                    Номер заказа: #{{ $orderId }}
                </div>
            @endif

            <div class="space-y-6 animate-fade-in" style="animation-delay: 0.2s">
                <p class="text-[15px] text-black/45 leading-relaxed">
                    Спасибо за ваш заказ. Мы получили все данные и уже начали их обрабатывать.
                </p>

                <div class="p-6 rounded-[22px] bg-black/[0.02] border border-black/5 inline-block w-full">
                    <div class="text-[12px] text-black/30 uppercase tracking-widest font-bold mb-1">Статус заказа</div>
                    <div class="text-[14px] text-black font-[650]">Ожидает оценки инженером</div>
                </div>

                <p class="text-[13px] text-black/35">
                    Наш менеджер свяжется с вами по указанному номеру в ближайшее время для подтверждения деталей.
                </p>
            </div>

            <div class="pt-10 flex flex-col sm:flex-row gap-4 animate-fade-in" style="animation-delay: 0.3s">
                <a href="{{ route('home') }}"
                    class="flex-1 px-8 py-4 bg-black text-white rounded-[22px] font-bold text-[14px] hover:bg-black/90 transition-all active:scale-[0.98] shadow-glass-sm">
                    На главную
                </a>
                <a href="{{ route('portfolio') }}"
                    class="flex-1 px-8 py-4 bg-black/5 text-black border border-black/5 rounded-[22px] font-bold text-[14px] hover:bg-black/10 transition-all active:scale-[0.98]">
                    Работы
                </a>
            </div>
        </div>
    </div>

    <style>
        .animate-fade-in {
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</x-app-layout>