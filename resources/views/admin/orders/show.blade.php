<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Order #') . $order->id }}
            </h2>
            <a href="{{ route('admin.orders.index') }}"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Back to List') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status class="mb-4" :status="session('success')" />

            <!-- Order Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Customer Info</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $order->contact_info['name'] ?? ($order->user->name ?? 'Guest') }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $order->contact_info['email'] ?? ($order->user->email ?? '') }}
                                {{ isset($order->contact_info['phone']) ? ' / ' . $order->contact_info['phone'] : ''}}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Task / Request Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Task Details</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
                        @if(isset($order->contact_info['product_type']))
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Product Type</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $order->contact_info['product_type'] }}
                            </dd>
                        </div>
                        @endif

                        @if(isset($order->contact_info['approx_size']))
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Approx. Size</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $order->contact_info['approx_size'] }}
                            </dd>
                        </div>
                        @endif

                        @if(isset($order->contact_info['description']))
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description / Comment</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $order->contact_info['description'] }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Order Items -->
            @if($order->items->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Items (STL)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        File</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Material</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Settings</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Qty</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Est. Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 text-sm">
                                            <a href="{{ route('admin.orders.download', $item->file->id) }}" class="text-indigo-600 hover:text-indigo-900 underline">
                                                {{ $item->file->original_name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="flex flex-col gap-1">
                                                <span class="font-medium">{{ $item->material->name }}</span>
                                                @if($item->color)
                                                <div class="flex items-center gap-2">
                                                    <div class="w-4 h-4 rounded-full border border-gray-200 shadow-sm"
                                                         style="{{ $item->color->hex_code === 'transparent' 
                                                            ? 'background-image: repeating-conic-gradient(#e5e7eb 0% 25%, white 0% 50%) 50% / 6px 6px' 
                                                            : 'background-color: ' . $item->color->hex_code }}">
                                                    </div>
                                                    <span class="text-xs text-gray-500">{{ $item->color->name }}</span>
                                                </div>
                                                @else
                                                <span class="text-xs text-gray-400">(Default Color)</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            Qual: {{ $item->quality_preset }}<br>
                                            Infill: {{ $item->infill_percentage }}%
                                        </td>
                                        <td class="px-6 py-4 text-sm">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ number_format($item->item_price, 0, ',', ' ') }} ₸</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Additional Files -->
            @if($order->additionalFiles->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Additional Files / Blueprints</h3>
                    <ul class="border border-gray-200 dark:border-gray-700 rounded-md divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $itemFileIds = $order->items->pluck('file_id')->toArray();
                            $hasAdditionalFiles = false;
                        @endphp
                        @foreach($order->additionalFiles as $file)
                            @if(in_array($file->id, $itemFileIds)) @continue @endif
                            @php $hasAdditionalFiles = true; @endphp
                            <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                <div class="w-0 flex-1 flex items-center">
                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="ml-2 flex-1 w-0 truncate">
                                        {{ $file->original_name }}
                                        @php
                                            $fileSize = 'N/A';
                                            try {
                                                if ($file->file_path && \Storage::exists($file->file_path)) {
                                                    $fileSize = number_format(\Storage::size($file->file_path) / 1024, 2) . ' KB';
                                                } elseif ($file->file_path && file_exists(storage_path('app/' . $file->file_path))) {
                                                    $fileSize = number_format(filesize(storage_path('app/' . $file->file_path)) / 1024, 2) . ' KB';
                                                }
                                            } catch (\Exception $e) {
                                                $fileSize = 'N/A';
                                            }
                                        @endphp
                                        <span class="text-xs text-gray-500 ml-2">({{ $fileSize }})</span>
                                    </span>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <a href="{{ route('admin.orders.download', $file->id) }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                                        Download
                                    </a>
                                </div>
                            </li>
                        @endforeach
                        
                        @if(!$hasAdditionalFiles)
                            <li class="pl-3 pr-4 py-3 text-sm text-gray-500 italic">No additional files linked (all files are order items).</li>
                        @endif
                    </ul>
                </div>
            </div>
            @endif

            <!-- Admin Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Update Order</h3>
                    <form method="POST" action="{{ route('admin.orders.update', $order->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    @foreach(['needs_review', 'quoted', 'printing', 'paid', 'done', 'delivered', 'cancelled'] as $status)
                                        <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="total_price" :value="__('Final Price (₸)')" />
                                <x-text-input id="total_price" class="block mt-1 w-full" type="number" step="0.01" name="total_price"
                                    :value="old('total_price', $order->total_price)" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="admin_notes" :value="__('Admin Notes')" />
                            <textarea id="admin_notes" name="admin_notes"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                rows="3">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Update Order') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>