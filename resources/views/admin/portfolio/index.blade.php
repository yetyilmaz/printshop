<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Управление портфолио
        </h2>
    </x-slot>

    <div class="py-12" x-data="portfolioManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm" style="border-radius: 24px;">
                <div class="p-8 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-6 animate-in fade-in" style="border-radius: 16px;" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Main View: Category Folders -->
                    <div x-show="!selectedCategory">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold">Категории портфолио</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
                            <!-- All Items Folder -->
                            <div @click="loadAllItems()" 
                                 class="p-6 bg-gradient-to-br from-gray-50 to-gray-100 hover:from-gray-100 hover:to-gray-200 transition-all duration-200 cursor-pointer hover:shadow-lg hover:-translate-y-1 group relative"
                                 style="border-radius: 24px;">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 mb-3 flex items-center justify-center text-4xl" style="border-radius: 18px; background: rgba(0,0,0,0.03);">
                                        📁
                                    </div>
                                    <h4 class="font-bold text-lg mb-1 text-center">Все элементы</h4>
                                    <span class="text-sm text-gray-600">{{ $items->count() }} элементов</span>
                                </div>
                            </div>

                            <!-- Category Folders -->
                            @foreach($categories as $category)
                                <div @click="selectCategory({{ $category->id }}, '{{ $category->name }}', {{ $category->items_count }})"
                                     class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 transition-all duration-200 cursor-pointer hover:shadow-lg hover:-translate-y-1 group relative border border-blue-100"
                                     style="border-radius: 24px;">
                                    @if($category->is_featured)
                                        <div class="absolute top-4 right-4 text-yellow-500 bg-yellow-100 p-1.5" style="border-radius: 50%;" title="Featured">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 mb-3 flex items-center justify-center text-4xl group-hover:bg-blue-100 transition-colors" style="border-radius: 18px; background: rgba(59, 130, 246, 0.1);">
                                            {{ $category->emoji ?? '📂' }}
                                        </div>
                                        <h4 class="font-bold text-lg mb-1 text-center">{{ $category->name }}</h4>
                                        <span class="text-sm text-gray-600">{{ $category->items_count }} элементов</span>
                                    </div>
                                    @if($category->description)
                                        <p class="mt-4 text-xs text-gray-500 line-clamp-2 text-center">{{ $category->description }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center pt-4 border-t">
                            <a href="{{ route('admin.categories.index') }}" class="inline-block text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                Управление категориями →
                            </a>
                        </div>
                    </div>

                    <!-- Category Detail View -->
                    <div x-show="selectedCategory" x-cloak>
                        <div class="flex items-center gap-4 mb-6">
                            <button @click="selectedCategory = null" 
                                    class="w-12 h-12 flex items-center justify-center bg-white border border-gray-200 hover:bg-gray-50 transition-colors"
                                    style="border-radius: 50%;">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold" x-text="categoryName"></h2>
                                <p class="text-sm text-gray-600"><span x-text="itemCount"></span> элементов</p>
                            </div>
                            <a :href="'{{ route('admin.portfolio.create') }}?category=' + selectedCategory"
                               class="px-6 py-3 bg-black text-white font-semibold hover:bg-gray-800 transition-colors flex items-center gap-2"
                               style="border-radius: 12px;">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Добавить проект
                            </a>
                        </div>

                        <!-- Items Grid for Selected Category -->
                        <div id="category-items-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($items as $item)
                                <div x-show="selectedCategory === 'all' || selectedCategory == {{ $item->category_id }}"
                                     class="bg-white border border-gray-200 overflow-hidden hover:shadow-xl transition-all group relative"
                                     style="border-radius: 18px;">
                                    @if($item->featured)
                                        <div class="absolute top-3 right-3 z-10 text-yellow-500 bg-yellow-100 p-1.5" style="border-radius: 50%;" title="Featured">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    
                                    <!-- 3D Model or Image Preview -->
                                    <div class="h-56 overflow-hidden bg-gray-100 relative flex items-center justify-center">
                                        @if($item->glb_model)
                                            <div class="w-full h-full"
                                                 x-init="
                                                     window.initModelViewer($el, '{{ Storage::url($item->glb_model) }}', { 
                                                         autoRotate: true, 
                                                         enableInteraction: false,
                                                         enableZoom: false
                                                     })
                                                 "
                                                 x-cleanup="if ($el._viewer) $el._viewer.dispose()">
                                            </div>
                                        @elseif($item->image)
                                            <img src="{{ Storage::url($item->image) }}" 
                                                 alt="{{ $item->title }}" 
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    
                                    <div class="p-5">
                                        <h4 class="font-bold text-lg mb-2 truncate">{{ $item->title }}</h4>
                                        <p class="text-gray-600 text-sm mb-2 line-clamp-2 min-h-[40px]">{{ $item->description ?? 'Нет описания' }}</p>
                                        <p class="text-gray-500 text-xs mb-4">{{ $item->category->name }} • {{ $item->material }}</p>
                                        
                                        <div class="flex gap-2 pt-4 border-t border-gray-100">
                                            <a href="{{ route('admin.portfolio.edit', $item) }}" 
                                               class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 transition-colors flex items-center justify-center gap-2"
                                               style="border-radius: 10px;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Редактировать
                                            </a>
                                            <form action="{{ route('admin.portfolio.destroy', $item) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Вы уверены, что хотите удалить этот элемент?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-4 transition-colors flex items-center justify-center"
                                                        style="border-radius: 10px;">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Empty State for Category -->
                        <div x-show="itemCount === 0" class="text-center py-20 border-2 border-dashed border-gray-200" style="border-radius: 24px;">
                            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">В этой категории пока нет элементов</h3>
                            <p class="text-gray-500 mb-6">Начните с добавления первого элемента портфолио.</p>
                            <a :href="'{{ route('admin.portfolio.create') }}?category=' + selectedCategory"
                               class="inline-block px-6 py-3 bg-black text-white font-semibold hover:bg-gray-800 transition-colors"
                               style="border-radius: 12px;">
                                Добавить первый элемент
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function portfolioManager() {
            return {
                selectedCategory: {{ request('category') ? "'" . request('category') . "'" : 'null' }},
                categoryName: '{{ $selectedCategory?->name ?? '' }}',
                itemCount: {{ $selectedCategory ? $items->count() : 0 }},
                
                selectCategory(id, name, count) {
                    this.selectedCategory = id;
                    this.categoryName = name;
                    this.itemCount = count;
                    
                    // Update URL without reload
                    const url = new URL(window.location);
                    url.searchParams.set('category', id);
                    window.history.pushState({}, '', url);
                },
                
                loadAllItems() {
                    this.selectedCategory = 'all';
                    this.categoryName = 'All Items';
                    this.itemCount = {{ $items->count() }};
                    
                    // Remove category param from URL
                    const url = new URL(window.location);
                    url.searchParams.delete('category');
                    window.history.pushState({}, '', url);
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .animate-in {
            animation: fadeIn 0.3s ease-in;
        }
    </style>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
