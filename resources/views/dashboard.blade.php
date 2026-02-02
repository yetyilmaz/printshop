<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <!-- Stats Section -->
        <div class="w-full px-6 mb-10">
            <div style="border-radius: 24px;" class="relative overflow-hidden border border-white/40 dark:border-gray-700 bg-white/60 dark:bg-gray-900/60 shadow-2xl shadow-black/10 backdrop-blur-xl">
                <div class="p-8 text-gray-900 dark:text-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Daily Visitors</h3>
                        <div class="flex items-baseline gap-3 mt-1">
                            <p class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $visitsToday }}</p>
                            <span class="text-sm text-gray-500 dark:text-gray-300 font-medium">Unique IP addresses today</span>
                        </div>
                    </div>
                    <div class="h-12 w-12 bg-black/5 dark:bg-white/10 rounded-full flex items-center justify-center text-gray-900 dark:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanban Board Container -->
        <div class="w-full px-6">
            <div style="border-radius: 24px;" class="bg-gray-50 dark:bg-gray-950 shadow-xl overflow-hidden w-full">
                <!-- Header -->
                <div class="bg-white dark:bg-gray-900 px-8 py-6 border-b border-gray-200 dark:border-gray-800 rounded-t-[24px]">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Order Pipeline</h3>
                        <span class="text-sm font-medium text-gray-500 bg-gray-100 dark:bg-gray-800 px-4 py-2 rounded-full">
                            {{ $orders->flatten()->count() }} Active Orders
                        </span>
                    </div>
                </div>

                <!-- Kanban Board -->
                <div class="p-6 w-full">
                    <div data-kanban-board class="w-full grid grid-cols-6 gap-4">
                        @php
                            $columns = [
                                'needs_review' => ['label' => 'Needs Review'],
                                'quoted' => ['label' => 'Quoted'],
                                'printing' => ['label' => 'Printing'],
                                'paid' => ['label' => 'Paid'],
                                'done' => ['label' => 'Ready / Done'],
                                'delivered' => ['label' => 'Delivered'],
                            ];
                        @endphp
                        
                        @foreach($columns as $status => $config)
                            <div class="flex flex-col gap-3">
                                <!-- Column Header -->
                                <div style="border-radius: 16px;" class="bg-white dark:bg-gray-900 px-4 py-3 shadow-sm border border-gray-200 dark:border-gray-800">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 truncate">
                                            {{ $config['label'] }}
                                        </h4>
                                        <span data-status-count="{{ $status }}" class="text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-full flex-shrink-0">
                                            {{ ($orders->get($status) ?? collect())->count() }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Cards Container -->
                                <div data-kanban-column data-status="{{ $status }}" class="flex-1">
                                    <div data-kanban-column-body style="max-height: 650px; overflow-y: auto;" class="flex flex-col gap-3">
                                        @php
                                            $columnOrders = $orders->get($status) ?? collect();
                                        @endphp
                                        @forelse($columnOrders as $order)
                                            <a data-kanban-card 
                                               data-order-id="{{ $order->id }}" 
                                               data-order-status="{{ $status }}" 
                                               draggable="true" 
                                               href="{{ route('admin.orders.show', $order->id) }}" 
                                               style="border-radius: 16px;"
                                               class="kanban-card block bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5 shadow-sm hover:shadow-md hover:border-gray-300 dark:hover:border-gray-700 transition-all duration-200 cursor-grab active:cursor-grabbing min-w-0">
                                                
                                                <!-- Order Info -->
                                                <div class="flex items-center justify-between mb-3">
                                                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500">#{{ $order->id }}</span>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $order->created_at->format('M d, H:i') }}</span>
                                                </div>
                                                
                                                <!-- Customer Info -->
                                                <div class="mb-4 min-w-0">
                                                    <h5 class="font-semibold text-gray-900 dark:text-gray-100 mb-1 truncate">
                                                        {{ $order->contact_info['name'] ?? 'Guest Customer' }}
                                                    </h5>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                                        {{ $order->contact_info['email'] ?? 'No contact info' }}
                                                    </p>
                                                </div>

                                                <!-- Footer -->
                                                <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-800">
                                                    <div>
                                                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">Total</p>
                                                        <p class="font-bold text-gray-900 dark:text-gray-100">
                                                            {{ $order->total_price > 0 ? number_format($order->total_price, 0, ',', ' ') . ' ₸' : '—' }}
                                                        </p>
                                                    </div>
                                                    
                                                    @if($order->items->count() > 0)
                                                        <div class="flex -space-x-1">
                                                            @foreach($order->items->take(3) as $item)
                                                                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 border-2 border-white dark:border-gray-900 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-400">
                                                                    <span class="text-[9px]">STL</span>
                                                                </div>
                                                            @endforeach
                                                            @if($order->items->count() > 3)
                                                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-white dark:border-gray-900 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-400">
                                                                    +{{ $order->items->count() - 3 }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                                                            ASSIST
                                                        </span>
                                                    @endif
                                                </div>
                                            </a>
                                        @empty
                                            <div data-empty-placeholder class="flex flex-col items-center justify-center bg-white dark:bg-gray-900 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800 py-12 text-gray-400 dark:text-gray-600">
                                                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                                </svg>
                                                <p class="text-sm font-medium">Empty</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Delete Zone -->
                <div id="delete-zone" style="display: none;" class="mx-6 mb-6 p-6 bg-red-50 dark:bg-red-900/20 border-2 border-dashed border-red-300 dark:border-red-700 rounded-2xl transition-all duration-200">
                    <div class="flex items-center justify-center gap-3 text-red-600 dark:text-red-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span class="font-semibold text-lg">Drop here to delete</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [data-kanban-column].kanban-column--over {
            background-color: rgb(249, 250, 251);
        }
        
        .dark [data-kanban-column].kanban-column--over {
            background-color: rgb(17, 24, 39);
        }

        .kanban-card.dragging {
            opacity: 0.5;
            transform: scale(0.95) rotate(2deg);
            cursor: grabbing !important;
        }

        #delete-zone.delete-zone--over {
            background-color: rgb(254, 226, 226);
            border-color: rgb(239, 68, 68);
            transform: scale(1.02);
        }

        .dark #delete-zone.delete-zone--over {
            background-color: rgb(127, 29, 29);
            border-color: rgb(220, 38, 38);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const templateRoute = '{{ route('admin.orders.status', ['order' => 'ORDER_ID_REPLACE']) }}';
            const deleteRoute = '{{ route('admin.orders.destroy', ['order' => 'ORDER_ID_REPLACE']) }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.warn('CMS dashboard: CSRF token missing, Kanban drag & drop disabled.');
                return;
            }

            let draggedCard = null;
            let originStatus = null;
            let originColumnBody = null;

            const deleteZone = document.getElementById('delete-zone');

            const getBadge = (status) => document.querySelector(`[data-status-count="${status}"]`);

            const adjustBadge = (element, delta) => {
                if (!element) {
                    return;
                }

                const current = Number(element.textContent.trim()) || 0;
                element.textContent = Math.max(0, current + delta);
            };

            const badgeUpdate = (from, to) => {
                if (from === to) {
                    return;
                }

                adjustBadge(getBadge(from), -1);
                adjustBadge(getBadge(to), 1);
            };

            const updateEmptyPlaceholder = (columnBody) => {
                const cards = columnBody.querySelectorAll('[data-kanban-card]');
                let placeholder = columnBody.querySelector('[data-empty-placeholder]');
                
                if (cards.length === 0) {
                    // Column is empty, show placeholder if it doesn't exist
                    if (!placeholder) {
                        placeholder = document.createElement('div');
                        placeholder.setAttribute('data-empty-placeholder', '');
                        placeholder.className = 'flex flex-col items-center justify-center bg-white dark:bg-gray-900 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800 py-12 text-gray-400 dark:text-gray-600';
                        placeholder.innerHTML = `
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-sm font-medium">Empty</p>
                        `;
                        columnBody.appendChild(placeholder);
                    }
                } else {
                    // Column has cards, remove placeholder if it exists
                    if (placeholder) {
                        placeholder.remove();
                    }
                }
            };

            const updateStatus = async (orderId, status) => {
                const url = templateRoute.replace('ORDER_ID_REPLACE', orderId);

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ status }),
                });

                if (!response.ok) {
                    const errorBody = await response.json().catch(() => ({}));
                    throw new Error(errorBody.message || 'Failed to update status');
                }

                return response.json();
            };

            const columns = document.querySelectorAll('[data-kanban-column]');

            document.querySelectorAll('[data-kanban-card]').forEach((card) => {
                card.addEventListener('dragstart', (event) => {
                    draggedCard = card;
                    originStatus = card.dataset.orderStatus;
                    originColumnBody = card.closest('[data-kanban-column-body]');
                    setTimeout(() => card.classList.add('dragging'), 0);
                    event.dataTransfer?.setData('text/plain', '');
                    
                    // Show delete zone
                    if (deleteZone) {
                        deleteZone.style.display = 'block';
                    }
                });

                card.addEventListener('dragend', () => {
                    card.classList.remove('dragging');
                    card.classList.remove('opacity-60');
                    
                    // Hide delete zone
                    if (deleteZone) {
                        deleteZone.style.display = 'none';
                        deleteZone.classList.remove('delete-zone--over');
                    }
                    
                    setTimeout(() => {
                        draggedCard = null;
                        originStatus = null;
                        originColumnBody = null;
                    }, 100);
                });
            });

            columns.forEach((column) => {
                const columnBody = column.querySelector('[data-kanban-column-body]');
                
                if (!columnBody) return;
                
                // Attach handlers to both column and columnBody for better drop detection
                [column, columnBody].forEach(element => {
                    element.addEventListener('dragover', (event) => {
                        event.preventDefault();
                        event.dataTransfer.dropEffect = 'move';
                    });
                    
                    element.addEventListener('dragenter', (event) => {
                        event.preventDefault();
                        column.classList.add('kanban-column--over');
                    });
                    
                    element.addEventListener('dragleave', (event) => {
                        // Only remove class if leaving the column entirely
                        if (!column.contains(event.relatedTarget)) {
                            column.classList.remove('kanban-column--over');
                        }
                    });
                    
                    element.addEventListener('drop', async (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        column.classList.remove('kanban-column--over');

                        if (!draggedCard || !columnBody) {
                            return;
                        }

                        const newStatus = column.dataset.status;
                        if (!newStatus || newStatus === originStatus) {
                            return;
                        }

                        // Move card immediately to new column for instant feedback
                        columnBody.appendChild(draggedCard);
                        draggedCard.dataset.orderStatus = newStatus;
                        badgeUpdate(originStatus, newStatus);
                        
                        // Update empty placeholders
                        updateEmptyPlaceholder(originColumnBody);
                        updateEmptyPlaceholder(columnBody);
                        
                        draggedCard.classList.add('opacity-60');

                        try {
                            await updateStatus(draggedCard.dataset.orderId, newStatus);
                        } catch (error) {
                            console.error('Kanban status update failed:', error);
                            // Revert on error
                            if (originColumnBody) {
                                originColumnBody.appendChild(draggedCard);
                                draggedCard.dataset.orderStatus = originStatus;
                                badgeUpdate(newStatus, originStatus);
                                
                                // Update empty placeholders after revert
                                updateEmptyPlaceholder(columnBody);
                                updateEmptyPlaceholder(originColumnBody);
                            }
                        } finally {
                            draggedCard.classList.remove('opacity-60');
                        }
                    });
                });
            });

            // Delete zone handlers
            if (deleteZone) {
                deleteZone.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                    deleteZone.classList.add('delete-zone--over');
                });

                deleteZone.addEventListener('dragleave', () => {
                    deleteZone.classList.remove('delete-zone--over');
                });

                deleteZone.addEventListener('drop', async (event) => {
                    event.preventDefault();
                    deleteZone.classList.remove('delete-zone--over');

                    if (!draggedCard) return;

                    // Store local reference before dragend clears it
                    const cardToDelete = draggedCard;
                    const orderId = cardToDelete.dataset.orderId;
                    const statusToUpdate = cardToDelete.dataset.orderStatus;
                    const columnToUpdate = cardToDelete.closest('[data-kanban-column-body]');
                    
                    const confirmDelete = confirm(`Are you sure you want to delete order #${orderId}?`);
                    
                    if (!confirmDelete) return;

                    cardToDelete.classList.add('opacity-60');

                    try {
                        const url = deleteRoute.replace('ORDER_ID_REPLACE', orderId);
                        const response = await fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });

                        const responseData = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(responseData.message || 'Failed to delete order');
                        }

                        // Remove card from DOM
                        cardToDelete.remove();
                        
                        // Update badge count
                        adjustBadge(getBadge(statusToUpdate), -1);
                        
                        // Update empty placeholder
                        if (columnToUpdate) {
                            updateEmptyPlaceholder(columnToUpdate);
                        }
                    } catch (error) {
                        console.error('Order deletion failed:', error);
                        alert('Failed to delete order. Please try again.');
                        if (cardToDelete && cardToDelete.parentNode) {
                            cardToDelete.classList.remove('opacity-60');
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
