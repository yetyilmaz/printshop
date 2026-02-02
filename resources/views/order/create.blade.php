<x-app-layout>
    <!-- Three.js Dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/STLLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

    <script>
        window.serverMaterials = {!! json_encode($materials->map(fn($m) => [
            'id' => (string) $m->id,
            'label' => $m->name,
            'price' => $m->price_per_cm3,
            'multiplier' => $m->time_multiplier,
            'colors' => $m->colors->map(fn($c) => [
                'id' => (string) $c->id, 
                'name' => $c->name, 
                'hex' => $c->hex_code
            ])
        ])) !!};
    </script>
    <div x-data="{ 
        tab: '{{ old('order_type', 'stl') }}',
        productType: '{{ old('product_type', 'Другое') }}',
        approxSize: '{{ old('approx_size', 'С ладонь') }}',
        fileName: '',
        fileSize: '',
        blueprintFiles: [],
        addBlueprints(files) {
            Array.from(files).forEach(file => {
                const isImage = file.type.startsWith('image/');
                if (isImage) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.blueprintFiles.push({ file, url: e.target.result, isImage: true, name: file.name });
                        this.syncBlueprints();
                    };
                    reader.readAsDataURL(file);
                } else {
                    this.blueprintFiles.push({ file, url: null, isImage: false, name: file.name });
                    this.syncBlueprints();
                }
            });
        },
        removeBlueprint(index) {
            this.blueprintFiles.splice(index, 1);
            this.syncBlueprints();
        },
        syncBlueprints() {
            const dt = new DataTransfer();
            this.blueprintFiles.forEach(f => dt.items.add(f.file));
            document.getElementById('blueprints-input').files = dt.files;
        }
    }" class="max-w-[1120px] mx-auto px-6 py-12">

        <!-- Tab Switcher -->
        <div class="flex justify-center mb-12">
            <div class="inline-flex p-1.5 bg-black/5 rounded-[22px] border border-black/5">
                <button @click="tab = 'stl'"
                    :class="tab === 'stl' ? 'bg-white shadow-glass-sm text-black' : 'text-black/40 hover:text-black/60'"
                    class="px-8 py-2.5 rounded-[18px] text-[13px] font-[650] transition-all duration-300">
                    У меня есть 3D-модель (STL)
                </button>
                <button @click="tab = 'assist'"
                    :class="tab === 'assist' ? 'bg-white shadow-glass-sm text-black' : 'text-black/40 hover:text-black/60'"
                    class="px-8 py-2.5 rounded-[18px] text-[13px] font-[650] transition-all duration-300">
                    Нет модели / Нужна помощь
                </button>
            </div>
        </div>

        <form action="{{ route('order.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="order_type" :value="tab">

            @if($errors->any())
                <div class="w-full mb-8 p-6 rounded-[22px] bg-red-50 border border-red-100 text-red-600 text-[14px]">
                    <strong>Ошибка:</strong> Пожалуйста, проверьте форму на наличие ошибок.
                    <ul class="mt-2 list-disc list-inside text-[13px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-12 gap-8 lg:gap-12 items-start">
                <!-- Left Column: Form -->
                <div class="col-span-12 md:col-span-8 glass-card p-10 sm:p-12 transition-all duration-300">

                    <div class="mb-10">
                        <h1 class="text-[32px] font-[750] tracking-[-0.04em] text-black mb-3">Оформление заказа</h1>
                        <p class="text-[15px] text-black/45 leading-relaxed max-w-[480px]">
                            Загрузи STL и выбери параметры. Демо сохранит заказ в браузере и выдаст страницу оплаты.
                        </p>
                    </div>

                    <!-- Common Fields: Name & Phone -->
                    <div class="space-y-6 mb-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-[12px] text-black/45 mb-2 pl-1">Имя</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}"
                                    placeholder="Например: Михаил" required
                                    class="input-glass h-[52px] px-5 focus:ring-0 @error('name') border-red-500 @enderror">
                                @error('name') <p class="text-[11px] text-red-500 mt-1 pl-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="phone" class="block text-[12px] text-black/45 mb-2 pl-1">Телефон</label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                    placeholder="+7 ... "
                                    class="input-glass h-[52px] px-5 focus:ring-0 @error('phone') border-red-500 @enderror">
                                @error('phone') <p class="text-[11px] text-red-500 mt-1 pl-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label for="email" class="block text-[12px] text-black/45 mb-2 pl-1">Email (обязательно для
                                связи)</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                placeholder="ivan@mail.ru" required
                                class="input-glass h-[52px] px-5 focus:ring-0 @error('email') border-red-500 @enderror">
                            @error('email') <p class="text-[11px] text-red-500 mt-1 pl-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Tab 1: STL Upload -->
                    <div x-show="tab === 'stl'" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="space-y-8">
                            <!-- STL File Dropzone -->
                            <div>
                                <label class="block text-[12px] text-black/45 mb-2 pl-1">STL файл</label>
                                <div
                                    class="relative group border border-black/10 rounded-[22px] p-6 bg-white flex justify-between items-center gap-6 transition-all hover:bg-black/5 hover:border-black/20">
                                    
                                    <!-- 3D Preview Thumbnail -->
                                    <div x-show="fileName" id="small-preview-container" @click.stop="$dispatch('open-3d-modal')"
                                         class="w-16 h-16 rounded-[14px] bg-gray-50 overflow-hidden cursor-pointer shadow-sm border border-black/10 hover:scale-105 transition-transform shrink-0 relative z-10">
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <div>
                                            <div class="font-[650] tracking-[-0.02em] text-[15px] text-black"
                                                x-text="fileName || 'Перетащи файл сюда'"></div>
                                            <div class="text-[12px] text-black/45 mt-0.5"
                                                x-text="fileSize || 'или выбери через кнопку справа • .stl'"></div>
                                        </div>
                                    </div>
                                    <div class="shrink-0">
                                        <div
                                            class="px-5 py-2.5 rounded-[12px] border border-black/10 flex items-center justify-center text-[13px] font-semibold text-black/60 group-hover:bg-black/5 transition-all">
                                            Выбрать файл
                                        </div>
                                    </div>
                                    <input id="file" type="file" name="file" accept=".stl,.obj,.3mf" @change="
                                    const f = $event.target.files[0];
                                    if(f) {
                                        fileName = f.name;
                                        fileSize = (f.size / (1024*1024)).toFixed(2) + ' MB';
                                        $dispatch('file-selected', { size: f.size });
                                        
                                        // Initialize 3D Preview
                                        window.initStlPreview(f);
                                    }
                                " class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                </div>
                                @error('file') <p class="text-[11px] text-red-500 mt-1 pl-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Assistance -->
                    <div x-show="tab === 'assist'" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">

                        <!-- Product Type -->
                        <div>
                            <label class="block text-[12px] text-black/45 mb-3 pl-1">Тип изделия</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="type in ['Запчасть', 'Корпус', 'Крепление', 'Декор', 'Другое']">
                                    <button type="button" @click="productType = type"
                                        :class="productType === type ? 'bg-black text-white' : 'bg-black/5 text-black hover:bg-black/10'"
                                        class="px-5 py-2.5 rounded-[18px] text-[13px] font-medium transition-all"
                                        x-text="type">
                                    </button>
                                </template>
                            </div>
                            <div x-show="productType === 'Другое'" class="mt-4 animate-fade-in">
                                <input type="text" name="product_type_other" placeholder="Укажите что это..."
                                    class="input-glass h-[52px] px-5 focus:ring-0">
                            </div>
                            <input type="hidden" name="product_type" :value="productType">
                            @error('product_type') <p class="text-[11px] text-red-500 mt-1 pl-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Approximate Size -->
                        <div>
                            <label class="block text-[12px] text-black/45 mb-3 pl-1">Примерный размер</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="size in ['С ладонь', 'С телефон', 'Больше']">
                                    <button type="button" @click="approxSize = size"
                                        :class="approxSize === size ? 'bg-black text-white' : 'bg-black/5 text-black hover:bg-black/10'"
                                        class="flex-1 min-w-[120px] px-5 py-2.5 rounded-[18px] text-[13px] font-medium transition-all"
                                        x-text="size">
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="approx_size" :value="approxSize">
                            @error('approx_size') <p class="text-[11px] text-red-500 mt-1 pl-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <div class="mt-8 space-y-8">
                        <!-- Description -->
                        <div>
                            <label class="block text-[12px] text-black/45 mb-2 pl-1">Комментарий / Описание задачи</label>
                            <textarea name="description"
                                placeholder="Опишите, для чего деталь, какие будут нагрузки, важен ли внешний вид..."
                                class="input-glass min-h-[120px] px-5 py-4 resize-none focus:ring-0 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                            @error('description') <p class="text-[11px] text-red-500 mt-1 pl-1">{{ $message }}</p> @enderror
                        </div>

                    <!-- Blueprints Upload -->
                    <div>
                        <label class="block text-[12px] text-black/45 mb-2 pl-1">Примеры / Чертежи
                            (опционально)</label>
                        <div class="flex flex-wrap gap-4">
                            <!-- Dropzone Button -->
                            <div
                                class="relative group w-20 h-20 rounded-[22px] border border-dashed border-black/20 flex items-center justify-center hover:bg-black/5 transition-all overflow-hidden bg-white/60">
                                <svg class="w-6 h-6 text-black/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                <input id="blueprints-input" type="file" name="blueprints[]" multiple
                                    class="absolute inset-0 opacity-0 cursor-pointer"
                                    @change="addBlueprints($event.target.files)">
                            </div>

                            <!-- Previews List -->
                            <template x-for="(bp, index) in blueprintFiles" :key="index">
                                <div class="relative w-20 h-20 group">
                                    <!-- Image Preview -->
                                    <template x-if="bp.isImage">
                                        <img :src="bp.url" class="w-full h-full object-cover rounded-[20px] border border-black/5 shadow-sm">
                                    </template>
                                    
                                    <!-- File Placeholder (for non-images) -->
                                    <template x-if="!bp.isImage">
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-black/5 rounded-[20px] border border-black/5 px-2 text-center">
                                            <svg class="w-6 h-6 text-black/20 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-[9px] text-black/40 truncate w-full" x-text="bp.name"></span>
                                        </div>
                                    </template>

                                    <!-- Delete Button (Red Cross) -->
                                    <button type="button" @click="removeBlueprint(index)"
                                        class="absolute -top-1.5 -right-1.5 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-600 transition-all z-10">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Right Column: Calculation / Info -->
                <div class="col-span-12 md:col-span-4 glass-card p-10 sm:p-12 md:sticky md:top-24 transition-all duration-300">

            <!-- Tab 1 Right Column content -->
            <div x-show="tab === 'stl'" class="space-y-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-[18px] font-[650] tracking-[-0.02em] text-black">Параметры и расчёт</h2>
                        <p class="text-[12px] text-black/40 mt-1">Автоматический расчёт по геометрии</p>
                    </div>
                    <span id="pillEl"
                        class="inline-flex items-center px-3 py-1.5 rounded-full border border-black/10 bg-white/55 text-[11px] text-black/60 font-medium animate-glow transition-all duration-300">
                        Ожидаю STL...
                    </span>
                </div>

                <!-- Material Selection (Alpine Custom Dropdown) -->
                <div x-data="{
                        open: false,
                        selected: '',
                        label: 'Выберите материал',
                        price: 0,
                        multiplier: 1,
                        options: window.serverMaterials || [],
                        init() {
                             // Attempt to pre-select if there is an old value
                             let old = '{{ old('material_id') }}';
                             if(old) {
                                 let found = this.options.find(o => o.id == old);
                                 if(found) this.select(found);
                             } else if (this.options.length > 0) {
                                 // Select first by default if nothing selected
                                 this.select(this.options[0]);
                             }
                        },
                        select(opt) {
                            this.selected = opt.id;
                            this.label = opt.label;
                            this.price = opt.price;
                            this.multiplier = opt.multiplier;
                            this.open = false;
                            
                            let input = document.getElementById('material_id');
                            if(input) {
                                input.value = opt.id;
                                input.dataset.price = opt.price;
                                input.dataset.multiplier = opt.multiplier;
                            }
                            
                            // Set global state for late-initializing components
                            window.currentMaterialColors = opt.colors;
                            
                            // Dispatch event to update colors
                            $dispatch('material-changed', { colors: opt.colors });

                            window.calculatePrice();
                        }
                    }" class="relative">
                    <label class="block text-[12px] text-black/45 mb-2 pl-1">Материал</label>
                    <input type="hidden" id="material_id" name="material_id" x-model="selected">
                    
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="input-glass !bg-white focus:!bg-white h-[52px] px-5 flex items-center justify-between text-left focus:ring-0 cursor-pointer">
                        <span x-text="label" :class="selected ? 'text-black' : 'text-black/40'"></span>
                        <div class="pointer-events-none text-black/30">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translateY-2"
                        x-transition:enter-end="opacity-100 translateY-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translateY-0"
                        x-transition:leave-end="opacity-0 translateY-2"
                        class="absolute z-50 w-full mt-2 bg-white/90 backdrop-blur-md border border-white/20 rounded-[18px] shadow-glass overflow-hidden">
                        <div class="max-h-[200px] overflow-y-auto custom-scrollbar">
                            <template x-for="opt in options" :key="opt.id">
                                <div @click="select(opt)" 
                                    class="px-5 py-3 hover:bg-black/5 cursor-pointer text-[14px] text-black/80 transition-colors border-b border-black/5 last:border-0"
                                    :class="selected == opt.id ? 'bg-black/5 font-medium' : ''">
                                    <span x-text="opt.label"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    @error('material_id') <p class="text-[11px] text-red-500 mt-1 pl-1">{{ $message }}</p> @enderror
                </div>

                <!-- Color Selection (Dependent on Material) -->
                <div x-data='{
                    open: false,
                    selected: "",
                    label: "Любой / Стандартный",
                    options: [],
                    init() {
                        let old = "{{ old("color_id") }}";
                        
                        if (window.currentMaterialColors) {
                            this.updateOptions(window.currentMaterialColors);
                        }
                    },
                    updateOptions(newColors) {
                        this.options = newColors || [];
                        let old = "{{ old("color_id") }}";
                        if (old && this.options.find(c => c.id == old)) {
                            let found = this.options.find(c => c.id == old);
                            this.select(found);
                        } else if (this.options.length > 0) {
                            this.select(this.options[0]);
                        } else {
                            this.selected = "";
                            this.label = "Нет выбора";
                        }
                    },
                    select(opt) {
                        this.selected = opt.id;
                        this.label = opt.name;
                        this.open = false;
                    }
                }' 
                @material-changed.window="updateOptions($event.detail.colors)"
                class="relative"
                x-show="options.length > 0"
                x-transition>
                    <label class="block text-[12px] text-black/45 mb-2 pl-1">Цвет</label>
                    <input type="hidden" name="color_id" x-model="selected">
                    
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="input-glass !bg-white focus:!bg-white h-[52px] px-5 flex items-center justify-between text-left focus:ring-0 cursor-pointer">
                        <div class="flex items-center gap-2">
                             <!-- Color Circle Preview -->
                             <template x-if="selected">
                                <div class="w-4 h-4 rounded-full border border-black/10 shrink-0" 
                                     :style="(options.find(c => c.id == selected)?.hex === 'transparent') 
                                        ? 'background-image: repeating-conic-gradient(#e5e7eb 0% 25%, white 0% 50%) 50% / 6px 6px' 
                                        : 'background-color: ' + (options.find(c => c.id == selected)?.hex || '#ccc')"></div>
                             </template>
                             <span x-text="label" :class="selected ? 'text-black' : 'text-black/40'"></span>
                        </div>
                        <div class="pointer-events-none text-black/30">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translateY-2"
                        x-transition:enter-end="opacity-100 translateY-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translateY-0"
                        x-transition:leave-end="opacity-0 translateY-2"
                        class="absolute z-50 w-full mt-2 bg-white/90 backdrop-blur-md border border-white/20 rounded-[18px] shadow-glass overflow-hidden">
                        <div class="max-h-[200px] overflow-y-auto custom-scrollbar">
                            <template x-for="opt in options" :key="opt.id">
                                <div @click="select(opt)" 
                                    class="px-5 py-3 hover:bg-black/5 cursor-pointer flex items-center gap-3 transition-colors border-b border-black/5 last:border-0"
                                    :class="selected == opt.id ? 'bg-black/5 font-medium' : ''">
                                    <div class="w-5 h-5 rounded-full border border-black/10 shadow-sm shrink-0" 
                                         :style="opt.hex === 'transparent' 
                                            ? 'background-image: repeating-conic-gradient(#e5e7eb 0% 25%, white 0% 50%) 50% / 6px 6px' 
                                            : 'background-color: ' + opt.hex"></div>
                                    <span x-text="opt.name" class="text-[14px] text-black/80"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Quality Selection (Alpine Custom Dropdown) -->
                <div x-data="{
                    open: false,
                    selected: '',
                    label: 'Выберите качество',
                    multiplier: 1.0,
                    options: [
                        @foreach($qualities as $quality)
                        { id: '{{ $quality->slug }}', label: '{{ $quality->label }}', multiplier: {{ $quality->multiplier }} },
                        @endforeach
                    ],
                    init() {
                         let old = '{{ old('quality') }}';
                         // Default to standard if available
                         if (!old && this.options.length > 0) {
                             let standard = this.options.find(o => o.id == 'standard');
                             this.select(standard || this.options[0]);
                         } else if (old) {
                             let found = this.options.find(o => o.id == old);
                             if(found) this.select(found);
                         } else {
                             let input = document.getElementById('quality');
                            if(input) {
                                input.dataset.multiplier = 1.0;
                            }
                         }
                    },
                    select(opt) {
                        this.selected = opt.id;
                        this.label = opt.label;
                        this.multiplier = opt.multiplier;
                        this.open = false;
                        
                        let input = document.getElementById('quality');
                        if(input) {
                            input.value = opt.id;
                            input.dataset.multiplier = opt.multiplier;
                        }
                        window.calculatePrice();
                    }
                }" class="relative">
                    <label class="block text-[12px] text-black/45 mb-2 pl-1">Качество</label>
                    <input type="hidden" id="quality" name="quality" value="" x-model="selected" data-multiplier="1.0">
                    
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="input-glass !bg-white focus:!bg-white h-[52px] px-5 flex items-center justify-between text-left focus:ring-0 cursor-pointer">
                        <span x-text="label" class="text-black"></span>
                        <div class="pointer-events-none text-black/30">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translateY-2"
                        x-transition:enter-end="opacity-100 translateY-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translateY-0"
                        x-transition:leave-end="opacity-0 translateY-2"
                        class="absolute z-50 w-full mt-2 bg-white/90 backdrop-blur-md border border-white/20 rounded-[18px] shadow-glass overflow-hidden">
                        <div class="py-1">
                            <template x-for="opt in options" :key="opt.id">
                                <div @click="select(opt)" 
                                    class="px-5 py-3 hover:bg-black/5 cursor-pointer text-[14px] text-black/80 transition-colors border-b border-black/5 last:border-0"
                                    :class="selected == opt.id ? 'bg-black/5 font-medium' : ''">
                                    <span x-text="opt.label"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Infill Selection (Alpine Custom Dropdown) -->
                <div x-data="{
                    open: false,
                    selected: '',
                    label: 'Выберите плотность',
                    multiplier: 1.0,
                    options: [
                        @foreach($infills as $infill)
                        { id: '{{ $infill->slug }}', label: '{{ $infill->label }}', multiplier: {{ $infill->multiplier }} },
                        @endforeach
                    ],
                    init() {
                         let old = '{{ old('infill') }}';
                         // Default to first option if no old value
                         if (!old && this.options.length > 0) {
                             // Prefer standard/20 if exists
                             let standard = this.options.find(o => o.id == '20');
                             this.select(standard || this.options[0]);
                         } else if (old) {
                             let found = this.options.find(o => o.id == old);
                             if (found) this.select(found);
                         } else {
                            let input = document.getElementById('infill');
                            if(input) input.dataset.multiplier = 1.0;
                         }
                    },
                    select(opt) {
                        this.selected = opt.id;
                        this.label = opt.label;
                        this.multiplier = opt.multiplier;
                        this.open = false;
                        
                        let input = document.getElementById('infill');
                        if(input) {
                            input.value = opt.id;
                            input.dataset.multiplier = opt.multiplier;
                        }
                        window.calculatePrice();
                    }
                }" class="relative">
                    <label class="block text-[12px] text-black/45 mb-2 pl-1">Плотность заполнения</label>
                    <input type="hidden" id="infill" name="infill" value="20" x-model="selected" data-multiplier="1.0">

                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="input-glass !bg-white focus:!bg-white h-[52px] px-5 flex items-center justify-between text-left focus:ring-0 cursor-pointer">
                        <span x-text="label" class="text-black"></span>
                        <div class="pointer-events-none text-black/30">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translateY-2"
                        x-transition:enter-end="opacity-100 translateY-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translateY-0"
                        x-transition:leave-end="opacity-0 translateY-2"
                        class="absolute z-50 w-full mt-2 bg-white/90 backdrop-blur-md border border-white/20 rounded-[18px] shadow-glass overflow-hidden">
                        <div class="py-1">
                            <template x-for="opt in options" :key="opt.id">
                                <div @click="select(opt)" 
                                    class="px-5 py-3 hover:bg-black/5 cursor-pointer text-[14px] text-black/80 transition-colors border-b border-black/5 last:border-0"
                                    :class="selected == opt.id ? 'bg-black/5 font-medium' : ''">
                                    <span x-text="opt.label"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    @error('infill') <p class="text-[11px] text-red-500 mt-1 pl-1">{{ $message }}</p> @enderror
                </div>

                <!-- Quantity (Moved here) -->
                <div>
                    <label for="quantity" class="block text-[12px] text-black/45 mb-2 pl-1">Количество</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1"
                        oninput="window.calculatePrice()" class="input-glass h-[52px] px-5 font-medium focus:ring-0">
                </div>

                <div class="space-y-4 pt-4">
                    <div class="flex justify-between items-center text-[13px] text-black/45">
                        <span>Оценка веса / времени</span>
                        <span id="estSub" class="font-medium text-black">-</span>
                    </div>
                    <div class="flex justify-between items-center text-[13px] text-black/45">
                        <span>Скидка</span>
                        <span id="discSub" class="font-medium text-black">-</span>
                    </div>
                    <div class="flex justify-between items-center pt-5 border-t border-black/5 mt-4">
                        <span class="text-[15px] font-semibold text-black/60">Итого</span>
                        <span id="price-display" class="text-[18px] font-bold text-black">-</span>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-[#0a0a0a] text-white font-bold py-4 rounded-[22px] shadow-glass-sm hover:brightness-95 transition-all active:scale-[0.98]">
                        Создать заказ
                    </button>
                </div>
            </div>

            <!-- Tab 2 Right Column content -->
            <div x-show="tab === 'assist'" class="space-y-8 animate-fade-in">
                <div>
                    <h2 class="text-[18px] font-[650] tracking-[-0.02em] text-black">Ручная Оценка</h2>
                    <p class="text-[12px] text-black/40 mt-1">Идеально, если у вас нет 3D-модели или нужна
                        консультация инженера.</p>
                </div>

                <div
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-full border border-black/5 bg-black/[0.02] text-[11px] font-medium text-black/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                    Бесплатная консультация
                </div>

                <div class="p-5 rounded-[22px] bg-blue-50/40 border border-blue-100 flex items-start gap-4">
                    <div
                        class="w-10 h-10 shrink-0 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-[12px] text-blue-800/70 leading-relaxed font-medium">
                        Опишите задачу максимально подробно. Мы подберём технологию и материал под ваш бюджет.
                    </p>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-[#0a0a0a] text-white font-bold py-4 rounded-[22px] shadow-glass-sm hover:brightness-95 transition-all active:scale-[0.98]">
                        Отправить на оценку
                    </button>
                    <p class="text-[10px] text-black/30 text-center mt-4">Мы свяжемся с вами и предложим
                        оптимальный
                        вариант</p>
                </div>
            </div>

        </div>

        </div> <!-- Closing Grid -->

        <!-- Hidden Inputs for actual form submission -->
        <input type="hidden" name="estimated_price" id="estimated_price" value="0">
        <input type="hidden" name="volume_cm3" id="volume_cm3" value="0">
        </form>
    </div> <!-- Closing Outer Container -->

    <script>
        window.updatePill = function(state) {
            const pill = document.getElementById('pillEl');
            if(!pill) return;
            
            if (state === 'initial') {
                pill.className = 'inline-flex items-center px-3 py-1.5 rounded-full border border-black/10 bg-white/55 text-[11px] text-black/60 font-medium animate-glow transition-all duration-300';
                pill.innerHTML = 'Ожидаю STL...';
            } else if (state === 'waiting_params') {
                pill.className = 'inline-flex items-center px-3 py-1.5 rounded-full border border-black/10 bg-white/55 text-[11px] text-black/60 font-medium transition-all duration-300';
                pill.innerHTML = 'Выберите параметры...';
            } else if (state === 'calculating') {
                pill.className = 'inline-flex items-center px-3 py-1.5 rounded-full border border-black/10 bg-white/55 text-[11px] text-black/60 font-medium transition-all duration-300';
                pill.innerHTML = `
                    <svg class="animate-spin-subtle h-3.5 w-3.5 mr-2 text-black/40" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Идёт расчёт...
                `;
            } else if (state === 'ready') {
                pill.className = 'inline-flex items-center px-3 py-1.5 rounded-full border border-green-200 bg-green-50 text-[11px] text-green-600 font-semibold transition-all duration-300 shadow-sm shadow-green-100';
                pill.innerHTML = `
                    <svg class="h-3.5 w-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Расчёт готов
                `;
            }
        };

        // Use window explicitly to make functions globally accessible
        let pillTimeout = null;
        window.calculatePrice = function () {
            const materialSelect = document.getElementById('material_id');
            const volumeInput = document.getElementById('volume_cm3');
            const quantityInput = document.getElementById('quantity');
            const qualitySelect = document.getElementById('quality');
            const infillSelect = document.getElementById('infill');

            const priceDisplay = document.getElementById('price-display');
            const estSub = document.getElementById('estSub');
            const discSub = document.getElementById('discSub');
            const hiddenPriceInput = document.getElementById('estimated_price');

            if (!materialSelect || !volumeInput || !quantityInput || !qualitySelect || !infillSelect) return;

            // Updated to fallback to dataset directly for custom Alpine dropdowns
            const materialPrice = parseFloat(materialSelect.dataset.price) || 
                                  parseFloat(materialSelect.selectedOptions?.[0]?.dataset.price) || 0;
            
            const materialMultiplier = parseFloat(materialSelect.dataset.multiplier) ||
                                       parseFloat(materialSelect.selectedOptions?.[0]?.dataset.multiplier) || 1.0;
            
            const volume = parseFloat(volumeInput.value) || 0;
            const quantity = parseInt(quantityInput.value) || 1;
            
            const qualityMultiplier = parseFloat(qualitySelect.dataset.multiplier) ||
                                      parseFloat(qualitySelect.selectedOptions?.[0]?.dataset.multiplier) || 1.0;
            
            const infillMultiplier = parseFloat(infillSelect.dataset.multiplier) ||
                                     parseFloat(infillSelect.selectedOptions?.[0]?.dataset.multiplier) || 1.0;

            if (materialPrice > 0 && volume > 0) {
                const itemCost = (volume * materialPrice * qualityMultiplier * infillMultiplier * materialMultiplier);
                const totalCost = itemCost * quantity;

                const weight = Math.round(volume * 1.25); // mock weight
                const hours = Math.round(volume * 0.5 * qualityMultiplier * materialMultiplier); // weighted time

                estSub.textContent = `${weight}г • ${hours}ч`;
                discSub.textContent = quantity >= 5 ? '8%' : '0%';

                const finalPrice = quantity >= 5 ? totalCost * 0.92 : totalCost;

                priceDisplay.textContent = Math.round(finalPrice).toLocaleString('ru-RU') + ' ₸';
                hiddenPriceInput.value = Math.round(finalPrice);

                // Update styling of the button if price calculated
                const btn = document.querySelector('[x-show="tab === \'stl\'"] button');
                if (btn) btn.style.backgroundColor = '#0a0a0a';

                // Manage Pill States
                if (pillTimeout) clearTimeout(pillTimeout);
                window.updatePill('calculating');
                pillTimeout = setTimeout(() => {
                    window.updatePill('ready');
                }, 800);
            } else {
                priceDisplay.textContent = '-';
                estSub.textContent = '-';
                discSub.textContent = '-';
                hiddenPriceInput.value = 0;

                const btn = document.querySelector('[x-show="tab === \'stl\'"] button');
                if (btn) btn.style.backgroundColor = '#878787';

                if (pillTimeout) clearTimeout(pillTimeout);
                if (volume > 0) {
                    window.updatePill('waiting_params');
                } else {
                    window.updatePill('initial');
                }
            }
        };

        const signedVolumeOfTriangle = (p1, p2, p3) => {
            return (p1.x * p2.y * p3.z - p1.x * p3.y * p2.z - p2.x * p1.y * p3.z + p2.x * p3.y * p1.z + p3.x * p1.y * p2.z - p3.x * p2.y * p1.z) / 6.0;
        };

        const calculateSTLVolume = (buffer) => {
            let volume = 0;
            const dv = new DataView(buffer);
            const header = new TextDecoder().decode(buffer.slice(0, 5));

            if (header.toLowerCase() === 'solid') {
                const text = new TextDecoder().decode(buffer);
                const lines = text.split('\n');
                let vertices = [];
                for (let line of lines) {
                    line = line.trim().toLowerCase();
                    if (line.startsWith('vertex ')) {
                        const parts = line.split(/\s+/).filter(p => p !== '');
                        vertices.push({ x: parseFloat(parts[1]), y: parseFloat(parts[2]), z: parseFloat(parts[3]) });
                        if (vertices.length === 3) {
                            volume += signedVolumeOfTriangle(vertices[0], vertices[1], vertices[2]);
                            vertices = [];
                        }
                    }
                }
            } else {
                if (buffer.byteLength < 84) return 0;
                const faceCount = dv.getUint32(80, true);
                let offset = 84;
                for (let i = 0; i < faceCount; i++) {
                    if (offset + 50 > buffer.byteLength) break;
                    offset += 12; // Normal
                    const v1 = { x: dv.getFloat32(offset, true), y: dv.getFloat32(offset + 4, true), z: dv.getFloat32(offset + 8, true) }; offset += 12;
                    const v2 = { x: dv.getFloat32(offset, true), y: dv.getFloat32(offset + 4, true), z: dv.getFloat32(offset + 8, true) }; offset += 12;
                    const v3 = { x: dv.getFloat32(offset, true), y: dv.getFloat32(offset + 4, true), z: dv.getFloat32(offset + 8, true) }; offset += 12;
                    offset += 2; // Attribute count
                    volume += signedVolumeOfTriangle(v1, v2, v3);
                }
            }
            return Math.abs(volume) / 1000.0; // mm3 to cm3
        };

        // Listen for internal file events
        window.addEventListener('file-selected', (e) => {
            const pillEl = document.getElementById('pillEl');
            const fileInput = document.getElementById('file');
            const file = fileInput.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    const buffer = event.target.result;
                    const volume = calculateSTLVolume(buffer);

                    document.getElementById('volume_cm3').value = volume.toFixed(3);
                    window.calculatePrice();
                };
                reader.readAsArrayBuffer(file);
            }
        });

        // --- Three.js Logic ---
        let smallRenderer, largeRenderer;
        let smallScene, smallCamera, smallMesh, smallReqId;
        let largeScene, largeCamera, largeMesh, largeControls, largeReqId;
        let currentStlGeometry = null;

        window.initStlPreview = function(file) {
            const container = document.getElementById('small-preview-container');
            container.innerHTML = '';
            
            const width = container.clientWidth;
            const height = container.clientHeight;

            // Scene & Camera
            smallScene = new THREE.Scene();
            smallScene.background = new THREE.Color(0xf9fafb); // gray-50
            
            smallCamera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
            smallCamera.position.z = 10;

            // Lights
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
            smallScene.add(ambientLight);
            const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
            dirLight.position.set(5, 10, 7);
            smallScene.add(dirLight);

            // Renderer
            smallRenderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            smallRenderer.setSize(width, height);
            smallRenderer.setPixelRatio(window.devicePixelRatio);
            container.appendChild(smallRenderer.domElement);

            // Loader
            const loader = new THREE.STLLoader();
            const url = URL.createObjectURL(file);
            
            loader.load(url, function (geometry) {
                currentStlGeometry = geometry; // Save for large viewer
                
                const material = new THREE.MeshPhongMaterial({ 
                    color: 0x4B5563, 
                    specular: 0x111111, 
                    shininess: 200 
                });
                smallMesh = new THREE.Mesh(geometry, material);

                // Center geometry
                geometry.computeBoundingBox();
                geometry.center();

                // Scale to fit
                const boundingBox = geometry.boundingBox;
                const size = new THREE.Vector3();
                boundingBox.getSize(size);
                const maxDim = Math.max(size.x, size.y, size.z);
                const scale = 5 / maxDim; // Fit within 5 units
                smallMesh.scale.set(scale, scale, scale);

                smallScene.add(smallMesh);

                // Animation Loop (Auto Rotate)
                if (smallReqId) cancelAnimationFrame(smallReqId);
                const animate = function () {
                    smallReqId = requestAnimationFrame(animate);
                    smallMesh.rotation.y += 0.01;
                    smallMesh.rotation.x += 0.005;
                    smallRenderer.render(smallScene, smallCamera);
                };
                animate();
            });
        };

        window.initLargeViewer = function() {
            if (!currentStlGeometry) return;

            const container = document.getElementById('large-preview-container');
            container.innerHTML = '';
            
            // Wait for transition or check visibility
            const width = container.clientWidth;
            const height = container.clientHeight;

            // Scene setup
            largeScene = new THREE.Scene();
            largeScene.background = new THREE.Color(0xf0f0f0);
            
            // Grid helper
            const gridHelper = new THREE.GridHelper(20, 20, 0xddd, 0xeee);
            largeScene.add(gridHelper);

            largeCamera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
            largeCamera.position.set(8, 5, 8);

            // Controls
            largeRenderer = new THREE.WebGLRenderer({ antialias: true });
            largeRenderer.setSize(width, height);
            largeRenderer.setPixelRatio(window.devicePixelRatio);
            container.appendChild(largeRenderer.domElement);
            
            largeControls = new THREE.OrbitControls(largeCamera, largeRenderer.domElement);
            largeControls.enableDamping = true;

            // Lights
            const hemiLight = new THREE.HemisphereLight( 0xffffff, 0x444444 );
            hemiLight.position.set( 0, 20, 0 );
            largeScene.add( hemiLight );

            const dirLight = new THREE.DirectionalLight( 0xffffff );
            dirLight.position.set( -3, 10, -10 );
            largeScene.add( dirLight );

            // Mesh
            const material = new THREE.MeshPhongMaterial({ 
                color: 0x3b82f6, 
                specular: 0x111111, 
                shininess: 200 
            });
            largeMesh = new THREE.Mesh(currentStlGeometry, material); // reuse geometry
            
            // Re-scale logic (geometry is already centered, but scale was on mesh)
            const boundingBox = currentStlGeometry.boundingBox;
            const size = new THREE.Vector3();
            boundingBox.getSize(size);
            const maxDim = Math.max(size.x, size.y, size.z);
            const scale = 5 / maxDim;
            largeMesh.scale.set(scale, scale, scale);
            
            // Fix rotation for viewer - usually upright
            largeMesh.rotation.x = -Math.PI / 2; 

            largeScene.add(largeMesh);

            // Animation
            if (largeReqId) cancelAnimationFrame(largeReqId);
            const animate = function () {
                largeReqId = requestAnimationFrame(animate);
                largeControls.update();
                largeRenderer.render(largeScene, largeCamera);
            };
            animate();
            
            // Handle window resize locally for this modal
            const resizeObserver = new ResizeObserver(() => {
                 if(!container) return;
                 const w = container.clientWidth;
                 const h = container.clientHeight;
                 largeRenderer.setSize(w, h);
                 largeCamera.aspect = w / h;
                 largeCamera.updateProjectionMatrix();
            });
            resizeObserver.observe(container);
        };
    </script>

    <!-- 3D Modals -->
    <div x-data="{ show: false }" 
        @open-3d-modal.window="show = true; setTimeout(() => window.initLargeViewer(), 300)"
        @keydown.escape.window="show = false"
        x-show="show" 
        style="display: none;"
        class="fixed inset-0 z-[100] flex items-center justify-center px-4 py-6 sm:px-0">
        
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity transform" @click="show = false">
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
        </div>

        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="bg-white rounded-[24px] overflow-hidden shadow-2xl transform transition-all sm:max-w-4xl sm:w-full h-[80vh] flex flex-col relative z-50">
            
            <!-- Header -->
            <div class="px-8 py-5 border-b border-gray-100 flex justify-between items-center bg-white z-10">
                <h3 class="text-lg font-[650] text-gray-900">Просмотр модели</h3>
                <button @click="show = false" class="text-gray-400 hover:text-gray-500 focus:outline-none bg-gray-50 rounded-full p-2 hover:bg-gray-100 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Viewer Container -->
            <div id="large-preview-container" class="flex-1 bg-gray-50 relative overflow-hidden">
                <!-- Canvas will be injected here -->
                <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm">Loading 3D View...</div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-5 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                <p class="text-[12px] font-medium text-gray-500">ЛКМ - Вращение • ПКМ - Перемещение • Колесо - Зум</p>
                <button @click="show = false" class="px-6 py-2.5 bg-black text-white text-[13px] font-semibold rounded-[16px] hover:bg-black/80 transition-colors shadow-lg shadow-black/10">
                    Закрыть
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 5px rgba(0,0,0,0.05), inset 0 0 0 1px rgba(0,0,0,0.05); }
            50% { box-shadow: 0 0 15px rgba(10,10,10,0.12), inset 0 0 0 1px rgba(10,10,10,0.1); }
        }
        .animate-glow {
            animation: glow 2s infinite ease-in-out;
        }

        @keyframes rotate-subtle {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-subtle {
            animation: rotate-subtle 1.5s linear infinite;
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</x-app-layout>