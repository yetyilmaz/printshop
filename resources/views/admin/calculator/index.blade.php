<x-app-layout>
    <div class="max-w-[1120px] mx-auto px-6 py-12">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-[26px] font-[650] tracking-[-0.03em] text-black">Calculator Manager</h1>
                <p class="text-[13px] text-black/45 mt-1">Regulate parameters for the STL price calculator.</p>
            </div>
            
            <!-- Quick Add Button (Optional, maybe for later) -->
        </div>

        @if(session('success'))
            <div class="mb-8 p-4 bg-green-50 border border-green-100 rounded-[18px] text-green-800 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.calculator.bulk-update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Quality Settings -->
            <div class="glass-card overflow-hidden mb-12">
                <div class="px-8 py-6 border-b border-black/5 bg-black/[0.02]">
                    <h2 class="text-[18px] font-bold text-black">Quality Presets</h2>
                    <p class="text-[12px] text-black/40">Adjust multipliers based on layer height/quality.</p>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-black/5">
                            <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider">Internal Slug</th>
                            <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider w-1/3">Display Label</th>
                            <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider w-32">Multiplier</th>
                            <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider text-right">Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($qualitySettings as $index => $setting)
                            <tr class="border-b border-black/5 hover:bg-black/[0.02] transition-colors">
                                <td class="px-8 py-5">
                                    <span class="text-[14px] font-mono text-black/60">{{ $setting->slug }}</span>
                                    <input type="hidden" name="settings[{{ $setting->id }}][id]" value="{{ $setting->id }}">
                                </td>
                                <td class="px-8 py-5">
                                    <input type="text" name="settings[{{ $setting->id }}][label]" value="{{ $setting->label }}"
                                        class="w-full bg-white/50 border border-black/10 rounded-lg px-3 py-2 text-sm focus:ring-0 focus:border-black/30">
                                </td>
                                <td class="px-8 py-5">
                                    <input type="number" step="0.01" name="settings[{{ $setting->id }}][multiplier]" value="{{ $setting->multiplier }}"
                                        class="w-full bg-white/50 border border-black/10 rounded-lg px-3 py-2 text-sm font-mono focus:ring-0 focus:border-black/30">
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <input type="checkbox" name="settings[{{ $setting->id }}][is_active]" value="1" {{ $setting->is_active ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-black shadow-sm focus:ring-black">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Infill Settings -->
            <div class="glass-card overflow-hidden mb-12">
                <div class="px-8 py-6 border-b border-black/5 bg-black/[0.02]">
                    <h2 class="text-[18px] font-bold text-black">Infill Density Presets</h2>
                    <p class="text-[12px] text-black/40">Multipliers for material usage based on infill percentage.</p>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-black/5">
                            <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider">Internal Slug</th>
                            <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider w-1/3">Display Label</th>
                            <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider w-32">Multiplier</th>
                            <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider text-right">Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($infillSettings as $index => $setting)
                            <tr class="border-b border-black/5 hover:bg-black/[0.02] transition-colors">
                                <td class="px-8 py-5">
                                    <span class="text-[14px] font-mono text-black/60">{{ $setting->slug }}</span>
                                    <input type="hidden" name="settings[{{ $setting->id }}][id]" value="{{ $setting->id }}">
                                </td>
                                <td class="px-8 py-5">
                                    <input type="text" name="settings[{{ $setting->id }}][label]" value="{{ $setting->label }}"
                                        class="w-full bg-white/50 border border-black/10 rounded-lg px-3 py-2 text-sm focus:ring-0 focus:border-black/30">
                                </td>
                                <td class="px-8 py-5">
                                    <input type="number" step="0.01" name="settings[{{ $setting->id }}][multiplier]" value="{{ $setting->multiplier }}"
                                        class="w-full bg-white/50 border border-black/10 rounded-lg px-3 py-2 text-sm font-mono focus:ring-0 focus:border-black/30">
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <input type="checkbox" name="settings[{{ $setting->id }}][is_active]" value="1" {{ $setting->is_active ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-black shadow-sm focus:ring-black">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end sticky bottom-6 z-20">
                <button type="submit"
                    class="bg-[#0a0a0a] text-white font-bold py-3 px-8 rounded-[18px] shadow-glass-sm hover:brightness-110 transition-all active:scale-[0.98]">
                    Save All Changes
                </button>
            </div>

        </form>
    </div>
</x-app-layout>
