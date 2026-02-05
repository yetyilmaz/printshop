<x-app-layout>
    <div class="max-w-[1120px] mx-auto px-6 py-12">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-[26px] font-[650] tracking-[-0.03em] text-black">Manage Materials</h1>
                <p class="text-[13px] text-black/45 mt-1">Configure pricing and time complexity for 3D printing.</p>
 
                <div class="glass-card overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-black/5">
                                <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider">
                                    Material
                                    Name</th>
                                <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider">
                                    Price
                                    (₸/cm³)</th>
                                <th class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider">
                                    Type</th>
                                <th
                                    class="px-8 py-5 text-[12px] font-semibold text-black/45 uppercase tracking-wider text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materials as $material)
                                <tr class="border-b border-black/5 hover:bg-black/[0.02] transition-colors">
                                    <td class="px-8 py-5">
                                        <span class="text-[15px] font-semibold text-black">{{ $material->name }}</span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span
                                            class="text-[14px] text-black/60 font-medium">{{ number_format($material->price_per_cm3, 2) }}
                                            ₸</span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full bg-black/5 text-[11px] font-medium text-black/60">
                                            {{ $material->type ?? 'General' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right space-x-2">
                                        <a href="{{ route('admin.materials.edit', $material) }}"
                                            class="text-[13px] font-medium text-black/45 hover:text-black transition-colors">Edit</a>
                                        <form action="{{ route('admin.materials.destroy', $material) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-[13px] font-medium text-red-500/60 hover:text-red-500 transition-colors"
                                                onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
</x-app-layout>