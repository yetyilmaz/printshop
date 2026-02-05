<nav x-data="{ open: false }" class="glass-nav">
    <!-- Primary Navigation Menu -->
    <div class="max-w-[1120px] mx-auto px-4">
        <div class="flex items-center justify-between h-[72px]">
            <!-- Logo (Left) -->
            <div class="flex-1 flex items-center">
                <a href="{{ route('home') }}" class="text-[24px] font-[650] tracking-[-0.02em] text-black flex items-end gap-1">
                    <span style="font-family: 'Segoe Script', 'Segoe UI', cursive; font-style: italic; color: #d81010; line-height: 1;">René</span>
                    <span style="font-family: 'Courier New', Courier, monospace; color: #050505; line-height: 1;">print</span>
                </a>
            </div>

            <!-- Navigation Links (Center) -->
            <div class="hidden sm:flex items-center gap-8">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="text-[13px] font-medium text-black/45 hover:text-black transition-colors {{ request()->routeIs('dashboard') ? 'text-black font-semibold' : '' }}">
                        Панель
                    </a>
                    <a href="{{ route('admin.portfolio.index') }}"
                        class="text-[13px] font-medium text-black/45 hover:text-black transition-colors {{ request()->routeIs('admin.portfolio.*') ? 'text-black font-semibold' : '' }}">
                        Портфолио
                    </a>
                    <a href="{{ route('admin.orders.index') }}"
                        class="text-[13px] font-medium text-black/45 hover:text-black transition-colors {{ request()->routeIs('admin.orders.*') ? 'text-black font-semibold' : '' }}">
                        Заказы
                    </a>
                    <a href="{{ route('admin.materials.index') }}"
                        class="text-[13px] font-medium text-black/45 hover:text-black transition-colors {{ request()->routeIs('admin.materials.*') ? 'text-black font-semibold' : '' }}">
                        Материалы
                    </a>
                    <a href="{{ route('admin.calculator.index') }}"
                        class="text-[13px] font-medium text-black/45 hover:text-black transition-colors {{ request()->routeIs('admin.calculator.*') ? 'text-black font-semibold' : '' }}">
                        Настройки калькулятора
                    </a>
                @endauth
            </div>

            <!-- Settings / CTA (Right) -->
            <div class="flex-1 flex items-center justify-end gap-3">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="btn-glass gap-2 pr-2">
                                <div>{{ Auth::user()->name }}</div>
                                <svg class="fill-current h-4 w-4 opacity-30" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                Профиль
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    Выйти
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('order.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-[18px] px-6 py-2.5 text-[13px] font-bold bg-[#0a0a0a] text-white shadow-glass-sm hover:bg-black/90 transition-all active:scale-[0.98]">
                        Оформить заказ
                    </a>
                @endauth
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="md:hidden flex items-center ml-4">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-[14px] text-black/40 hover:text-black hover:bg-black/5 transition-colors">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        class="md:hidden bg-white/95 backdrop-blur-xl border-b border-black/5">
        <div class="pt-2 pb-6 space-y-1 px-4">
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    Панель
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.portfolio.index')"
                    :active="request()->routeIs('admin.portfolio.*')">
                    Портфолио
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">
                    Заказы
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.materials.index')"
                    :active="request()->routeIs('admin.materials.*')">
                    Материалы
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.calculator.index')"
                    :active="request()->routeIs('admin.calculator.*')">
                    Настройки калькулятора
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('portfolio')" :active="request()->routeIs('portfolio')">
                    Портфолио
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('order.create')" :active="request()->routeIs('order.create')">
                    Оформить заказ
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('login')">
                    Админ
                </x-responsive-nav-link>
            @endauth
        </div>
    </div>
</nav>