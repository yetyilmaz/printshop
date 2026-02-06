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

        <!-- Projects Grid -->
        <template x-if="filteredProjects.length === 0">
            <div class="border-2 border-dashed border-gray-200 p-12 text-center text-gray-600"
                 style="border-radius: 24px;">
                <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-lg">В этой категории пока нет проектов</p>
            </div>
        </template>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <template x-for="project in filteredProjects" :key="project.id">
                <div @click="openProject(project)"
                     class="border border-gray-200 overflow-hidden hover:shadow-2xl hover:border-gray-400 transition-all cursor-pointer group"
                     style="border-radius: 20px;">
                    <!-- 3D Model or Image Preview -->
                    <div class="aspect-square bg-gray-100 overflow-hidden relative flex items-center justify-center">
                        <template x-if="project.glb_model">
                            <div class="w-full h-full"
                                 x-init="
                                     window.initModelViewer($el, project.glb_model, { 
                                         autoRotate: true, 
                                         enableInteraction: false,
                                         enableZoom: false
                                     })
                                 "
                                 x-cleanup="if ($el._viewer) $el._viewer.dispose()">
                            </div>
                        </template>
                        <template x-if="!project.glb_model && project.image">
                            <img :src="project.image" 
                                 :alt="project.title"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </template>
                        <template x-if="!project.glb_model && !project.image">
                            <div class="text-gray-400 text-center p-4">
                                <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm">Нет модели</p>
                            </div>
                        </template>
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition flex items-center justify-center">
                            <span class="text-white text-sm font-medium opacity-0 group-hover:opacity-100 transition px-4 py-2 bg-black/60 rounded-lg">
                                Нажмите, чтобы просмотреть детали
                            </span>
                        </div>
                    </div>
                    
                    <!-- Project Info -->
                    <div class="p-5">
                        <h3 class="font-bold text-lg mb-2" x-text="project.title"></h3>
                        <template x-if="project.description">
                            <p class="text-sm text-gray-600 line-clamp-2 mb-2" x-text="project.description"></p>
                        </template>
                        <p class="text-xs text-gray-500" x-text="project.category"></p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Project Detail Modal -->
        <div x-show="selectedProject" 
             x-cloak
             @click="closeProject"
             class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4"
             style="backdrop-filter: blur(8px);">
            <div @click.stop 
                 class="bg-white w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col md:flex-row"
                 style="border-radius: 24px;">
                
                <!-- Left: 3D Model Viewer -->
                <div class="flex-1 bg-gray-50 relative flex items-center justify-center min-h-[400px] md:min-h-0">
                    <template x-if="selectedProject && selectedProject.glb_model">
                        <div class="w-full h-full absolute inset-0"
                             x-init="
                                 window.initModelViewer($el, selectedProject.glb_model, { 
                                     autoRotate: false, 
                                     enableInteraction: true,
                                     enableZoom: true
                                 })
                             "
                             x-cleanup="if ($el._viewer) $el._viewer.dispose()">
                        </div>
                    </template>
                    <template x-if="selectedProject && !selectedProject.glb_model && currentImages.length > 0">
                        <div class="w-full h-full relative">
                            <img :src="currentImages[imageIndex]" 
                                 :alt="selectedProject.title"
                                 class="w-full h-full object-contain">
                            <template x-if="currentImages.length > 1">
                                <div>
                                    <button @click="prevImage"
                                            class="absolute left-4 top-1/2 -translate-y-1/2 p-3 bg-white/90 hover:bg-white transition"
                                            style="border-radius: 50%;">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                    <button @click="nextImage"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 p-3 bg-white/90 hover:bg-white transition"
                                            style="border-radius: 50%;">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/60 text-white px-3 py-1.5 text-sm"
                                         style="border-radius: 20px;">
                                        <span x-text="imageIndex + 1"></span> / <span x-text="currentImages.length"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    
                    <!-- Interaction Hint for 3D Models -->
                    <template x-if="selectedProject && selectedProject.glb_model">
                        <div class="absolute bottom-4 left-4 bg-black/60 text-white px-4 py-2 text-sm"
                             style="border-radius: 12px;">
                            <span class="block">🖋️ Перетащите чтобы повернуть</span>
                            <span class="block">🖋️ Прокрутите чтобы увеличить</span>
                        </div>
                    </template>
                </div>

                <!-- Right: Project Details -->
                <div class="w-full md:w-[400px] flex-shrink-0 bg-white overflow-y-auto p-8">
                    <!-- Close Button -->
                    <button @click="closeProject"
                            class="absolute top-4 right-4 p-2 hover:bg-gray-100 transition z-10"
                            style="border-radius: 10px;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="space-y-6 mt-8" x-show="selectedProject">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h1 class="text-3xl font-bold mb-2" x-text="selectedProject?.title"></h1>
                                <p class="text-gray-600">
                                    <span class="font-medium" x-text="selectedProject?.category"></span>
                                </p>
                                <p class="text-gray-500 text-sm mt-1" x-text="selectedProject?.material"></p>
                            </div>
                            <template x-if="selectedProject?.featured">
                                <span class="px-3 py-1.5 bg-yellow-100 text-yellow-800 text-sm font-medium whitespace-nowrap"
                                      style="border-radius: 20px;">
                                    ⭐ Избранное
                                </span>
                            </template>
                        </div>

                        <template x-if="selectedProject?.description">
                            <div class="space-y-2">
                                <h3 class="font-bold text-lg">Описание</h3>
                                <p class="text-gray-700 leading-relaxed" x-text="selectedProject.description"></p>
                            </div>
                        </template>

                        <!-- Image Gallery Thumbnails -->
                        <template x-if="currentImages.length > 0">
                            <div>
                                <h3 class="font-bold text-lg mb-3">Галерея изображений</h3>
                                <div class="grid grid-cols-3 gap-3">
                                    <template x-for="(img, idx) in currentImages" :key="idx">
                                        <button @click="imageIndex = idx"
                                                :class="idx === imageIndex ? 'ring-2 ring-black' : 'hover:ring-2 hover:ring-gray-400'"
                                                class="overflow-hidden aspect-square transition"
                                                style="border-radius: 12px;">
                                            <img :src="img" :alt="'Image ' + (idx + 1)"
                                                 class="w-full h-full object-cover">
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Action Button -->
                        <div class="pt-6 border-t">
                            <a href="{{ route('order.create') }}"
                               class="block w-full px-6 py-3 bg-black text-white text-center font-semibold hover:bg-gray-900 transition"
                               style="border-radius: 12px;">
                                Заказать похожий проект
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
