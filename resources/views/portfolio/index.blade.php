<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-12" x-data="portfolioPage()">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold mb-3">Портфолио</h1>
            <p class="text-gray-600 text-lg">Примеры наших завершенных 3D печатных проектов</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-6 rounded-2xl">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Category Filter -->
        <div class="flex gap-3 overflow-x-auto pb-3 mb-8">
            @foreach($categories as $category)
                <button @click="selectedCategory = '{{ $category->id }}'"
                        :class="selectedCategory === '{{ $category->id }}' ? 'bg-black text-white' : 'border border-gray-300 hover:border-black'"
                        class="px-5 py-2.5 rounded-xl whitespace-nowrap transition-all font-medium text-sm"
                        style="border-radius: 12px;">
                    {{ $category->emoji ?? '📂' }} {{ $category->name }}
                </button>
            @endforeach
        </div>

        <!-- Projects Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <template x-for="project in filteredProjects" :key="project.id">
                <div @click="openProject(project)"
                     class="border border-gray-200 overflow-hidden hover:shadow-2xl hover:border-gray-400 transition-all cursor-pointer group"
                     style="border-radius: 20px;">
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
             class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4"
             style="backdrop-filter: blur(10px);">
            <div @click.stop 
                 class="bg-white rounded-[24px] overflow-hidden shadow-2xl transform transition-all sm:max-w-4xl sm:w-full h-[80vh] flex flex-col relative z-50 max-w-[96vw]">
                <!-- Header -->
                <div class="px-8 py-4 border-b border-gray-100 flex justify-between items-center bg-white z-10">
                    <div>
                        <h3 class="text-lg font-[650] text-gray-900" x-text="selectedProject?.title"></h3>
                        <p class="text-sm text-gray-500" x-text="selectedProject?.category"></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <template x-if="selectedProject?.material">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gradient-to-r from-gray-200 to-gray-400 text-gray-800 text-xs font-semibold border border-gray-500 shadow-inner">
                                <span x-text="selectedProject?.material"></span>
                            </span>
                        </template>
                        <button @click="closeProject" class="text-gray-400 hover:text-gray-500 focus:outline-none bg-gray-50 rounded-full p-2 hover:bg-gray-100 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Viewer + Details -->
                <div class="flex-1 flex overflow-hidden">
                    <div class="w-full lg:w-[62%] bg-gray-50 relative flex items-center justify-center" style="min-height: calc(80vh - 120px);">
                        <template x-if="selectedProject && selectedProject.glb_model">
                            <div class="absolute inset-0 flex items-center justify-center"
                                 x-init="
                                     window.initModelViewer($el, selectedProject.glb_model, {
                                         autoRotate: false,
                                         enableInteraction: true,
                                         enableZoom: true
                                     })
                                 "
                                 x-cleanup="if ($el._viewer) $el._viewer.dispose()"></div>
                        </template>
                        <template x-if="selectedProject && !selectedProject.glb_model && currentImages.length > 0">
                            <div class="w-full h-full relative">
                                <img :src="currentImages[imageIndex]" :alt="selectedProject.title" class="w-full h-full object-contain">
                                <template x-if="currentImages.length > 1">
                                    <div>
                                        <button @click="prevImage" class="absolute left-4 top-1/2 -translate-y-1/2 p-3 bg-white/90 hover:bg-white transition" style="border-radius: 50%;">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                        </button>
                                        <button @click="nextImage" class="absolute right-4 top-1/2 -translate-y-1/2 p-3 bg-white/90 hover:bg-white transition" style="border-radius: 50%;">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/60 text-white px-3 py-1.5 text-sm" style="border-radius: 20px;">
                                            <span x-text="imageIndex + 1"></span> / <span x-text="currentImages.length"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                    </div>
                    <div class="w-full lg:w-[38%] bg-white overflow-y-auto p-8">
                        <div class="space-y-6">
                            <template x-if="selectedProject?.description">
                                <div class="space-y-2">
                                    <h3 class="font-bold text-lg">Описание</h3>
                                    <p class="text-gray-700 leading-relaxed" x-text="selectedProject.description"></p>
                                </div>
                            </template>
                            <template x-if="currentImages.length > 0">
                                <div>
                                    <h3 class="font-bold text-lg mb-3">Галерея изображений</h3>
                                    <div class="grid grid-cols-3 gap-3">
                                        <template x-for="(img, idx) in currentImages" :key="idx">
                                            <button @click="imageIndex = idx"
                                                    :class="idx === imageIndex ? 'ring-2 ring-black' : 'hover:ring-2 hover:ring-gray-400'"
                                                    class="overflow-hidden aspect-square transition"
                                                    style="border-radius: 12px;">
                                                <img :src="img" :alt="'Image ' + (idx + 1)" class="w-full h-full object-cover">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-8 py-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                    <p class="text-[12px] font-medium text-gray-500">ЛКМ — вращение • ПКМ — перемещение • колесо — масштаб</p>
                    <div class="flex gap-3">
                        <a href="{{ route('order.create') }}" class="px-5 py-2.5 bg-black text-white text-[13px] font-semibold rounded-[16px] hover:bg-black/80 transition-colors shadow-lg shadow-black/10">Заказать похожий проект</a>
                        <button @click="closeProject" class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-[16px] hover:border-black hover:text-black transition-colors">Закрыть</button>
                    </div>
                </div>
            </div>
    </div>

    <script>
        function portfolioPage() {
            return {
                selectedCategory: '{{ $categories->first()?->id ?? "" }}',
                selectedProject: null,
                imageIndex: 0,
                projects: @json($projectsData),
                
                get filteredProjects() {
                    return this.projects.filter(p => p.category_id == this.selectedCategory);
                },
                
                get currentImages() {
                    return this.selectedProject?.images || [];
                },
                
                openProject(project) {
                    this.selectedProject = project;
                    this.imageIndex = 0;
                },
                
                closeProject() {
                    this.selectedProject = null;
                    this.imageIndex = 0;
                },
                
                nextImage() {
                    this.imageIndex = this.imageIndex === this.currentImages.length - 1 
                        ? 0 
                        : this.imageIndex + 1;
                },
                
                prevImage() {
                    this.imageIndex = this.imageIndex === 0 
                        ? this.currentImages.length - 1 
                        : this.imageIndex - 1;
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
    </style>
</x-app-layout>