<x-app-layout>
    <div class="max-w-[1120px] mx-auto px-6 py-12">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-[26px] font-[650] tracking-[-0.03em] text-black">Manage Colors</h1>
                <p class="text-[13px] text-black/45 mt-1">Define standard colors available for materials.</p>
            </div>
            <a href="{{ route('admin.colors.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-[18px] px-6 py-2.5 text-[13px] font-bold bg-[#0a0a0a] text-white shadow-glass-sm hover:bg-black/90 transition-all active:scale-[0.98]">
                Add Color
            </a>
        </div>

        <div class="glass-card overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-black/5">
                        <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider">Color Name</th>
                        <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider">Hex Code</th>
                        <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($colors as $color)
                        <tr class="border-b border-black/5 hover:bg-black/[0.02] transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full border border-black/10 shadow-sm" style="background-color: {{ $color->hex_code }}"></div>
                                    <span class="text-[15px] font-semibold text-black">{{ $color->name }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="font-mono text-[13px] text-black/60 bg-black/5 px-2 py-1 rounded">{{ $color->hex_code }}</span>
                            </td>
                            <td class="px-8 py-5 text-right space-x-2">
                                <a href="{{ route('admin.colors.edit', $color) }}" class="text-[12px] font-bold text-black hover:text-blue-600 transition-colors">Edit</a>
                                <form action="{{ route('admin.colors.destroy', $color) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[12px] font-bold text-red-500 hover:text-red-700 transition-colors ml-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
