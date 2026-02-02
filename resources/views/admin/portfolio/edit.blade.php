<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Редактировать элемент портфолио
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.portfolio.update', $portfolio->id) }}"
                        enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="title" value="Название" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                :value="old('title', $portfolio->title)" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="slug" value="URL" />
                            <x-text-input id="slug" class="block mt-1 w-full" type="text" name="slug"
                                :value="old('slug', $portfolio->slug)" />
                            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="category_id" value="Категория" />
                            <select id="category_id" name="category_id" required
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Выберите категорию</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $portfolio->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="material" value="Материал" />
                            <x-text-input id="material" class="block mt-1 w-full" type="text" name="material"
                                :value="old('material', $portfolio->material)" />
                            <x-input-error :messages="$errors->get('material')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="glb_model" value="3D модель (.glb)" />
                            @if($portfolio->glb_model)
                                <div class="mb-2 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Текущая модель: {{ basename($portfolio->glb_model) }}</p>
                                    <label class="inline-flex items-center mt-2 text-sm">
                                        <input type="checkbox" name="delete_glb_model" value="1"
                                            class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                        <span class="ml-2 text-red-600 dark:text-red-400">Удалить текущую 3D модель</span>
                                    </label>
                                </div>
                            @endif
                            <input id="glb_model" type="file" name="glb_model" accept=".glb"
                                class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Загрузите 3D модель в формате GLB для интерактивного просмотра</p>
                            <x-input-error :messages="$errors->get('glb_model')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="description" value="Описание" />
                            <textarea id="description" name="description"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                rows="4">{{ old('description', $portfolio->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label value="Текущие изображения" class="mb-2" />
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach($portfolio->images as $image)
                                    <div class="relative group">
                                        <img src="{{ Storage::url($image->path) }}" alt=""
                                            class="w-full h-32 object-cover rounded-lg">
                                        <div class="absolute inset-x-0 bottom-0 p-2 bg-black bg-opacity-50 rounded-b-lg">
                                            <label class="inline-flex items-center text-white text-xs">
                                                <input type="checkbox" name="delete_images[]" value="{{ $image->id }}"
                                                    class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                                <span class="ml-2">Удалить</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <x-input-label for="images" value="Добавить новые изображения" />
                            <input id="images" type="file" name="images[]" multiple accept="image/*"
                                class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                            <x-input-error :messages="$errors->get('images')" class="mt-2" />
                        </div>

                        <div class="flex items-center">
                            <input id="featured" type="checkbox" name="featured" value="1" {{ old('featured', $portfolio->featured) ? 'checked' : '' }}
                                class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                            <label for="featured" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                Отметить как избранное
                            </label>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Обновить</x-primary-button>
                            <a href="{{ route('admin.portfolio.index') }}"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>