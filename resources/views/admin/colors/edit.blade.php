<x-app-layout>
    <div class="max-w-[700px] mx-auto px-6 py-12">
        <div class="mb-10">
            <h1 class="text-[26px] font-[650] tracking-[-0.03em] text-black">Edit Color</h1>
            <p class="text-[13px] text-black/45 mt-1">Update color details.</p>
        </div>

        <div class="glass-card p-8">
            <form action="{{ route('admin.colors.update', $color) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    <!-- Color Name -->
                    <div>
                        <label for="name" class="block text-[13px] font-semibold text-black/70 mb-2">Color Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $color->name) }}" required
                            class="w-full h-12 px-4 rounded-[14px] bg-black/[0.03] border-transparent focus:bg-white focus:border-black/10 focus:ring-0 transition-all text-[15px] font-medium placeholder:text-black/30">
                    </div>

                    <!-- Hex Code -->
                    <div>
                        <label for="hex_code" class="block text-[13px] font-semibold text-black/70 mb-2">Hex Code</label>
                        <div class="flex gap-4">
                            <input type="color" id="hex_picker" value="{{ old('hex_code', $color->hex_code) }}" 
                                class="w-12 h-12 rounded-[14px] cursor-pointer border-0 p-0 overflow-hidden" 
                                oninput="document.getElementById('hex_code').value = this.value">
                            <input type="text" name="hex_code" id="hex_code" value="{{ old('hex_code', $color->hex_code) }}" required pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$"
                                class="flex-1 h-12 px-4 rounded-[14px] bg-black/[0.03] border-transparent focus:bg-white focus:border-black/10 focus:ring-0 transition-all text-[15px] font-medium font-mono placeholder:text-black/30"
                                oninput="document.getElementById('hex_picker').value = this.value">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <a href="{{ route('admin.colors.index') }}" 
                           class="h-12 px-6 inline-flex items-center justify-center rounded-[14px] text-[13px] font-bold text-black/60 hover:text-black hover:bg-black/5 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="h-12 px-8 inline-flex items-center justify-center rounded-[18px] bg-[#0a0a0a] text-white text-[13px] font-bold shadow-glass-sm hover:brightness-110 transition-all active:scale-[0.98]">
                            Update Color
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
