<x-app-layout>
    <div class="max-w-[1120px] mx-auto px-6 py-12">
        <div class="mb-10">
            <a href="{{ route('admin.materials.index') }}"
                class="text-[13px] font-medium text-black/45 hover:text-black transition-colors mb-4 inline-block">←
                Back to Materials</a>
            <h1 class="text-[26px] font-[650] tracking-[-0.03em] text-black">Add New Material</h1>
            <p class="text-[13px] text-black/45 mt-1">Define properties for a new 3D printing material.</p>
        </div>

        <div class="glass-card p-10 sm:p-12 max-w-2xl">
            <form action="{{ route('admin.materials.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-[12px] text-black/45 mb-2 pl-1">Material Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        placeholder="e.g., Carbon Fiber Nylon" required class="input-glass h-[52px] px-5 focus:ring-0">
                    @error('name') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-3 sm:grid-cols-3"
                    x-data="{
                        pricePerKg: @json(old('price_per_kg', 0)),
                        density: @json(old('material_density', 1.2)),
                        pricePerCm3: @json(old('price_per_cm3', 0)),
                        updatePrice() {
                            const base = (Number(this.pricePerKg) || 0) * (Number(this.density) || 0) / 1000;
                            const computed = base * 5.0;
                            this.pricePerCm3 = parseFloat(computed.toFixed(4)) || 0;
                        }
                    }"
                    x-init="updatePrice()">
                    <div>
                        <label for="price_per_kg" class="block text-[12px] text-black/45 mb-2 pl-1">Price per kg spool (₸)</label>
                        <input type="number" id="price_per_kg" name="price_per_kg"
                            x-model.number="pricePerKg"
                            @input="updatePrice()"
                            step="0.01" placeholder="10 000" class="input-glass h-[52px] px-5 focus:ring-0">
                        @error('price_per_kg') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                        <p class="text-[10px] text-black/50 mt-1">Enter the reel price and the form will derive the cm³ rate automatically.</p>
                    </div>

                    <div>
                        <label for="material_density" class="block text-[12px] text-black/45 mb-2 pl-1">Density (g/cm³)</label>
                        <input type="number" id="material_density" name="material_density"
                            x-model.number="density"
                            @input="updatePrice()"
                            step="0.01" placeholder="1.24" class="input-glass h-[52px] px-5 focus:ring-0">
                        @error('material_density') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                        <p class="text-[10px] text-black/50 mt-1">Density drives the conversion from spool weight to volume.</p>
                    </div>

                    <div>
                        <label for="price_per_cm3" class="block text-[12px] text-black/45 mb-2 pl-1">Price (₸/cm³)</label>
                        <input type="number" id="price_per_cm3" name="price_per_cm3"
                            x-model.number="pricePerCm3"
                            step="0.0001" placeholder="250" required class="input-glass h-[52px] px-5 focus:ring-0" readonly>
                        @error('price_per_cm3') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                        <p class="text-[10px] text-black/50 mt-1">This field updates based on the spool price and density.</p>
                    </div>
                </div>

                <div>
                    <label for="type" class="block text-[12px] text-black/45 mb-2 pl-1">Type (Optional)</label>
                    <input type="text" id="type" name="type" value="{{ old('type') }}" placeholder="e.g., Engineering"
                        class="input-glass h-[52px] px-5 focus:ring-0">
                    @error('type') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                </div>

                <div x-data="{
                    colors: [
                        { name: 'Black', hex: '#000000' },
                        { name: 'White', hex: '#FFFFFF' }
                    ],
                    addColor() {
                        this.colors.push({ name: '', hex: '#888888' });
                    },
                    removeColor(index) {
                        this.colors.splice(index, 1);
                    }
                }">
                    <div class="flex justify-between items-center mb-3 pl-1">
                        <label class="block text-[12px] text-black/45">Colors</label>
                        <button type="button" @click="addColor()"
                            class="text-[11px] font-bold text-black bg-black/5 px-3 py-1.5 rounded-lg hover:bg-black/10 transition-colors">
                            + Add Color
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(color, index) in colors" :key="index">
                            <div class="flex gap-3 items-center">
                                <div class="relative w-[52px] h-[52px] shrink-0 group">
                                    <input type="color" x-model="color.hex"
                                        x-show="color.hex !== 'transparent'"
                                        @input="color.hex = $event.target.value"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10 p-0 border-0">
                                    <input type="hidden" :name="`colors[${index}][hex]`" x-model="color.hex">

                                    <div class="w-full h-full rounded-[14px] border border-black/10 shadow-sm transition-all overflow-hidden"
                                        :style="color.hex === 'transparent'
                                            ? 'background-color: #fff; background-image: linear-gradient(45deg, #e5e7eb 25%, transparent 25%), linear-gradient(-45deg, #e5e7eb 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #e5e7eb 75%), linear-gradient(-45deg, transparent 75%, #e5e7eb 75%); background-size: 10px 10px; background-position: 0 0, 0 5px, 5px -5px, -5px 0px;'
                                            : `background-color: ${color.hex}`">
                                    </div>

                                    <div x-show="color.hex === 'transparent'" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <span class="text-[10px] bg-black/50 text-white px-1 rounded backdrop-blur-sm">TRNS</span>
                                    </div>
                                </div>

                                <input type="text" x-model="color.name" :name="`colors[${index}][name]`" placeholder="Color Name" required
                                    class="flex-1 h-[52px] bg-black/[0.03] border-transparent rounded-[14px] px-5 text-[14px] font-medium focus:bg-white focus:border-black/10 focus:ring-0 transition-all placeholder:text-black/30">

                                <button type="button"
                                    @click="color.hex = (color.hex === 'transparent' ? '#000000' : 'transparent')"
                                    class="w-[52px] h-[52px] flex flex-col items-center justify-center text-[10px] font-bold text-black/50 hover:text-black hover:bg-black/5 rounded-[14px] transition-all border border-transparent hover:border-black/5"
                                    :class="color.hex === 'transparent' ? 'bg-black/5 text-black border-black/10' : ''"
                                    title="Toggle Transparency">
                                    <div class="w-4 h-4 border border-current mb-0.5 relative overflow-hidden rounded-[2px]">
                                        <div class="absolute inset-0"
                                            style="background: linear-gradient(45deg, currentColor 25%, transparent 25%, transparent 75%, currentColor 75%); background-size: 4px 4px;"></div>
                                    </div>
                                    Clear
                                </button>

                                <button type="button" @click="removeColor(index)"
                                    class="w-[52px] h-[52px] flex items-center justify-center text-black/30 hover:text-red-500 hover:bg-red-50 rounded-[14px] transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit"
                        class="w-full bg-[#0a0a0a] text-white font-bold py-4 rounded-[18px] shadow-glass-sm hover:bg-black/90 transition-all active:scale-[0.98]">
                        Create Material
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout><x-app-layout>
    <div class="max-w-[1120px] mx-auto px-6 py-12">
        <div class="mb-10">
            <a href="{{ route('admin.materials.index') }}"
                class="text-[13px] font-medium text-black/45 hover:text-black transition-colors mb-4 inline-block">←
                Back to Materials</a>
            <h1 class="text-[26px] font-[650] tracking-[-0.03em] text-black">Add New Material</h1>
            <p class="text-[13px] text-black/45 mt-1">Define properties for a new 3D printing material.</p>
        </div>

        <div class="glass-card p-10 sm:p-12 max-w-2xl">
            <form action="{{ route('admin.materials.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="name" class="block text-[12px] text-black/45 mb-2 pl-1">Material Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        placeholder="e.g., Carbon Fiber Nylon" required class="input-glass h-[52px] px-5 focus:ring-0">
                    @error('name') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label for="price_per_kg" class="block text-[12px] text-black/45 mb-2 pl-1">Price per kg spool (₸)</label>
                        <input type="number" id="price_per_kg" name="price_per_kg" value="{{ old('price_per_kg') }}"
                            step="0.01" placeholder="10 000" class="input-glass h-[52px] px-5 focus:ring-0">
                        @error('price_per_kg') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                        <p class="text-[10px] text-black/50 mt-1">Enter the reel price and the form will derive the cm³ rate automatically.</p>
                    </div>

                    <div>
                        <label for="material_density" class="block text-[12px] text-black/45 mb-2 pl-1">Density (g/cm³)</label>
                        <input type="number" id="material_density" name="material_density" value="{{ old('material_density', 1.2) }}"
                            step="0.01" placeholder="1.24" class="input-glass h-[52px] px-5 focus:ring-0">
                        @error('material_density') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                        <p class="text-[10px] text-black/50 mt-1">Density drives the conversion from spool weight to volume.</p>
                    </div>

                    <div>
                        <label for="price_per_cm3" class="block text-[12px] text-black/45 mb-2 pl-1">Price (₸/cm³)</label>
                        <input type="number" id="price_per_cm3" name="price_per_cm3" value="{{ old('price_per_cm3') }}"
                            step="0.01" placeholder="250" required class="input-glass h-[52px] px-5 focus:ring-0" readonly>
                        @error('price_per_cm3') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                        <p class="text-[10px] text-black/50 mt-1">This field updates based on the spool price and density.</p>
                    </div>
                </div>

                <div>
                    <label for="type" class="block text-[12px] text-black/45 mb-2 pl-1">Type (Optional)</label>
                    <input type="text" id="type" name="type" value="{{ old('type') }}" placeholder="e.g., Engineering"
                        class="input-glass h-[52px] px-5 focus:ring-0">
                    @error('type') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                </div>

                <!-- Colors Configuration -->
                <div x-data="{
                    colors: [
                        { name: 'Black', hex: '#000000' },
                        { name: 'White', hex: '#FFFFFF' }
                    ],
                    addColor() {
                        this.colors.push({ name: '', hex: '#888888' });
                    },
                    removeColor(index) {
                        this.colors.splice(index, 1);
                    }
                }">
                    <div class="flex justify-between items-center mb-3 pl-1">
                        <label class="block text-[12px] text-black/45">Colors</label>
                        <button type="button" @click="addColor()" class="text-[11px] font-bold text-black bg-black/5 px-3 py-1.5 rounded-lg hover:bg-black/10 transition-colors">
                            + Add Color
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(color, index) in colors" :key="index">
                            <div class="flex gap-3 items-center">
                                <div class="relative w-[52px] h-[52px] shrink-0 group">
                                    <input type="color" x-model="color.hex"
                                           x-show="color.hex !== 'transparent'"
                                           @input="color.hex = $event.target.value"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10 p-0 border-0">
                                    <input type="hidden" :name="`colors[${index}][hex]`" x-model="color.hex">

                                    <div class="w-full h-full rounded-[14px] border border-black/10 shadow-sm transition-all overflow-hidden"
                                         :style="color.hex === 'transparent'
                                            ? 'background-color: #fff; background-image: linear-gradient(45deg, #e5e7eb 25%, transparent 25%), linear-gradient(-45deg, #e5e7eb 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #e5e7eb 75%), linear-gradient(-45deg, transparent 75%, #e5e7eb 75%); background-size: 10px 10px; background-position: 0 0, 0 5px, 5px -5px, -5px 0px;'
                                            : `background-color: ${color.hex}`">
                                    </div>

                                    <div x-show="color.hex === 'transparent'" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <span class="text-[10px] bg-black/50 text-white px-1 rounded backdrop-blur-sm">TRNS</span>
                                    </div>
                                </div>

                                <input type="text" x-model="color.name" :name="`colors[${index}][name]`" placeholder="Color Name" required
                                    class="flex-1 h-[52px] bg-black/[0.03] border-transparent rounded-[14px] px-5 text-[14px] font-medium focus:bg-white focus:border-black/10 focus:ring-0 transition-all placeholder:text-black/30">

                                <button type="button"
                                    @click="color.hex = (color.hex === 'transparent' ? '#000000' : 'transparent')"
                                    class="w-[52px] h-[52px] flex flex-col items-center justify-center text-[10px] font-bold text-black/50 hover:text-black hover:bg-black/5 rounded-[14px] transition-all border border-transparent hover:border-black/5"
                                    :class="color.hex === 'transparent' ? 'bg-black/5 text-black border-black/10' : ''"
                                    title="Toggle Transparency">
                                    <div class="w-4 h-4 border border-current mb-0.5 relative overflow-hidden rounded-[2px]">
                                        <div class="absolute inset-0"
                                            style="background: linear-gradient(45deg, currentColor 25%, transparent 25%, transparent 75%, currentColor 75%); background-size: 4px 4px;"></div>
                                    </div>
                                    Clear
                                </button>

                                <button type="button" @click="removeColor(index)" class="w-[52px] h-[52px] flex items-center justify-center text-black/30 hover:text-red-500 hover:bg-red-50 rounded-[14px] transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit"
                        class="w-full bg-[#0a0a0a] text-white font-bold py-4 rounded-[18px] shadow-glass-sm hover:bg-black/90 transition-all active:scale-[0.98]">
                        Create Material
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout><x-app-layout>
    <div class="max-w-[1120px] mx-auto px-6 py-12">
        <div class="mb-10">
            <a href="{{ route('admin.materials.index') }}"
                class="text-[13px] font-medium text-black/45 hover:text-black transition-colors mb-4 inline-block">←
                Back to Materials</a>
            <h1 class="text-[26px] font-[650] tracking-[-0.03em] text-black">Add New Material</h1>
            <p class="text-[13px] text-black/45 mt-1">Define properties for a new 3D printing material.</p>
        </div>

        <div class="glass-card p-10 sm:p-12 max-w-2xl">
            <form action="{{ route('admin.materials.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="name" class="block text-[12px] text-black/45 mb-2 pl-1">Material Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        placeholder="e.g., Carbon Fiber Nylon" required class="input-glass h-[52px] px-5 focus:ring-0">
                    @error('name') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label for="price_per_kg" class="block text-[12px] text-black/45 mb-2 pl-1">Price per kg spool (₸)</label>
                        <input type="number" id="price_per_kg" name="price_per_kg" value="{{ old('price_per_kg') }}"
                            step="0.01" placeholder="10 000" class="input-glass h-[52px] px-5 focus:ring-0">
                        <p class="text-[10px] text-black/50 mt-1">Enter the reel price and the form will derive the cm³ rate.</p>
                    </div>

                    <div>
                        <label for="material_density" class="block text-[12px] text-black/45 mb-2 pl-1">Density (g/cm³)</label>
                        <input type="number" id="material_density" name="material_density" value="1.2" step="0.01"
                            placeholder="1.24" class="input-glass h-[52px] px-5 focus:ring-0">
                        <p class="text-[10px] text-black/50 mt-1">Default values adjust when you input the material type.</p>
                    </div>

                    <div>
                        <label for="price_per_cm3" class="block text-[12px] text-black/45 mb-2 pl-1">Price (₸/cm³)</label>
                        <input type="number" id="price_per_cm3" name="price_per_cm3" value="{{ old('price_per_cm3') }}"
                            step="0.01" placeholder="250" required class="input-glass h-[52px] px-5 focus:ring-0" readonly>
                        @error('price_per_cm3') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="type" class="block text-[12px] text-black/45 mb-2 pl-1">Type (Optional)</label>
                    <input type="text" id="type" name="type" value="{{ old('type') }}" placeholder="e.g., Engineering"
                        class="input-glass h-[52px] px-5 focus:ring-0">
                    @error('type') <p class="text-red-500 text-[11px] mt-1 pl-1">{{ $message }}</p> @enderror
                </div>

                <!-- Colors Configuration -->
                <div x-data="{
                    colors: [
                        { name: 'Black', hex: '#000000' },
                        { name: 'White', hex: '#FFFFFF' }
                    ],
                    addColor() {
                        this.colors.push({ name: '', hex: '#888888' });
                    },
                    removeColor(index) {
                        this.colors.splice(index, 1);
                    }
                }">
                    <div class="flex justify-between items-center mb-3 pl-1">
                        <label class="block text-[12px] text-black/45">Colors</label>
                        <button type="button" @click="addColor()" class="text-[11px] font-bold text-black bg-black/5 px-3 py-1.5 rounded-lg hover:bg-black/10 transition-colors">
                            + Add Color
                        </button>
                    </div>
                                           x-show="color.hex !== 'transparent'"
                                           @input="color.hex = $event.target.value"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10 p-0 border-0">
                                    
                                    <!-- Hidden Input for Form Submission -->
                                    <input type="hidden" :name="`colors[${index}][hex]`" x-model="color.hex">

                                    <!-- Visual Preview -->
                                    <div class="w-full h-full rounded-[14px] border border-black/10 shadow-sm transition-all overflow-hidden" 
                                         :style="color.hex === 'transparent' 
                                            ? 'background-color: #fff; background-image: linear-gradient(45deg, #e5e7eb 25%, transparent 25%), linear-gradient(-45deg, #e5e7eb 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #e5e7eb 75%), linear-gradient(-45deg, transparent 75%, #e5e7eb 75%); background-size: 10px 10px; background-position: 0 0, 0 5px, 5px -5px, -5px 0px;' 
                                            : `background-color: ${color.hex}`">
                                    </div>
                                    
                                    <!-- Tooltip/Indicator -->
                                    <div x-show="color.hex === 'transparent'" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <span class="text-[10px] bg-black/50 text-white px-1 rounded backdrop-blur-sm">TRNS</span>
                                    </div>
                                </div>

                                <input type="text" x-model="color.name" :name="`colors[${index}][name]`" placeholder="Color Name" required
                                    class="flex-1 h-[52px] bg-black/[0.03] border-transparent rounded-[14px] px-5 text-[14px] font-medium focus:bg-white focus:border-black/10 focus:ring-0 transition-all placeholder:text-black/30">
                                
                                <!-- Toggle Transparency Button -->
                                <button type="button" 
                                    @click="color.hex = (color.hex === 'transparent' ? '#000000' : 'transparent')"
                                    class="w-[52px] h-[52px] flex flex-col items-center justify-center text-[10px] font-bold text-black/50 hover:text-black hover:bg-black/5 rounded-[14px] transition-all border border-transparent hover:border-black/5"
                                    :class="color.hex === 'transparent' ? 'bg-black/5 text-black border-black/10' : ''"
                                    title="Toggle Transparency">
                                    <div class="w-4 h-4 border border-current mb-0.5 relative overflow-hidden rounded-[2px]">
                                        <div class="absolute inset-0" style="background: linear-gradient(45deg, currentColor 25%, transparent 25%, transparent 75%, currentColor 75%); background-size: 4px 4px;"></div>
                                    </div>
                                    Clear
                                </button>

                                <button type="button" @click="removeColor(index)" class="w-[52px] h-[52px] flex items-center justify-center text-black/30 hover:text-red-500 hover:bg-red-50 rounded-[14px] transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit"
                        class="w-full bg-[#0a0a0a] text-white font-bold py-4 rounded-[18px] shadow-glass-sm hover:bg-black/90 transition-all active:scale-[0.98]">
                        Create Material
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>