"use client";

import { useState, useEffect } from "react";
import { Trash2, Edit2, Plus, X, Upload, Star, Folder, ArrowLeft, MoreVertical } from "lucide-react";

type PortfolioCategory = {
    id: string;
    name: string;
    description: string;
    emoji?: string;
};

type PortfolioProject = {
    id: string;
    title: string;
    description: string;
    category: string;
    images: string[];
    glbModel?: string;
    featured: boolean;
    createdAt: string;
};

export function PortfolioManager({ password }: { password: string }) {
    const [categories, setCategories] = useState<PortfolioCategory[]>([]);
    const [projects, setProjects] = useState<PortfolioProject[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [loaded, setLoaded] = useState(false);

    // Navigation State
    const [currentCategory, setCurrentCategory] = useState<PortfolioCategory | null>(null);

    async function loadPortfolio() {
        setLoading(true);
        setError(null);
        try {
            const res = await fetch("/api/admin/portfolio");
            if (!res.ok) throw new Error("Ошибка загрузки");
            const data = await res.json();
            setCategories(data.categories);
            setProjects(data.projects);
            setLoaded(true);
        } catch (err: any) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    }

    // Auto-load on mount
    useEffect(() => {
        loadPortfolio();
    }, []);

    if (loading && !loaded) {
        return <div className="text-center py-10 text-[rgba(10,10,10,0.5)]">Загрузка портфолио...</div>;
    }

    return (
        <div className="space-y-6 animate-in fade-in duration-500">
            {error && <div className="text-red-600 bg-red-50 p-3 rounded-[18px]">{error}</div>}

            {currentCategory ? (
                <CategoryDetailView
                    category={currentCategory}
                    projects={projects.filter(p => p.category === currentCategory.id)}
                    allCategories={categories}
                    password={password}
                    onUpdate={loadPortfolio}
                    onBack={() => setCurrentCategory(null)}
                    setError={setError}
                />
            ) : (
                <CategoriesGridView
                    categories={categories}
                    counts={categories.reduce((acc, cat) => {
                        acc[cat.id] = projects.filter(p => p.category === cat.id).length;
                        return acc;
                    }, {} as Record<string, number>)}
                    password={password}
                    onUpdate={loadPortfolio}
                    onSelectCategory={setCurrentCategory}
                    setError={setError}
                />
            )}
        </div>
    );
}

const EMOJI_CATEGORIES = {
    "Популярные": ["📁", "📂", "🏆", "🚗", "🏭", "⚙️", "🎨", "🩺", "🥇", "🥈", "🥉", "📦", "📅", "📈"],
    "Объекты": ["💡", "🔦", "🔋", "🔌", "💻", "🖥️", "🖨️", "⌨️", "🖱️", "📱", "📷", "📹", "📺", "📻", "🎙️", "💎", "💍", "🔨", "🛠️", "🔧", "🪛", "🧱", "⚙️", "🗜️", "⚖️", "🧭", "🕰️", "⌛", "📡", "🔭", "🔬", "🧵", "🧶", "🧷", "✂️", "🖊️", "✏️", "📝", "🔐", "🔑", "🗑️"],
    "Транспорт": ["🚗", "🚕", "🚙", "🚌", "🏎️", "🚓", "🚑", "🚒", "🚐", "🚚", "🚛", "🚜", "🛵", "🚲", "🛴", "🚂", "✈️", "🚀", "🛸", "🚁", "🛶", "🚤", "🛳️"],
    "Здания": ["🏠", "🏡", "🏢", "🏣", "🏥", "🏦", "🏨", "🏪", "🏫", "🏭", "🏯", "🏰", "💒", "🗼", "🗽", "⛪", "🕌", "🛕", "🕍", "⛩️", "🏗️", "🏟️", "🏛️"],
    "Природа": ["🌲", "🌳", "🌴", "🌵", "🌷", "🌸", "🌹", "🌻", "🌼", "🔥", "💧", "⚡", "❄️", "🌪️", "🌫️", "🌬️", "🌍", "🌎", "🌏", "🌑", "☀️", "⭐", "🌈", "🌊"],
    "Разное": ["🎨", "🎭", "🖼️", "🧵", "🧶", "👗", "👕", "👖", "👔", "👓", "🕶️", "🥼", "🦺", "🩺", "💊", "💉", "🩸", "🧬", "🦠", "🧫", "🧪", "❤️", "🧡", "💛", "💚", "💙", "💜", "🖤", "🤍", "🤎", "💯", "💢", "💥", "💫", "💦", "💤", "💬", "🎵", "🎶"]
};

// Helper to get emoji for category
function getCategoryEmoji(cat: PortfolioCategory): string {
    if (cat.emoji) return cat.emoji;

    // Fallback for older categories
    switch (cat.id) {
        case "podium": return "🏆";
        case "car-parts": return "🚗";
        case "industrial": return "🏭";
        case "prototype": return "⚙️";
        case "art": return "🎨";
        case "medical": return "🩺";
        default: return "📁";
    }
}

function CategoriesGridView({
    categories,
    counts,
    password,
    onUpdate,
    onSelectCategory,
    setError
}: {
    categories: PortfolioCategory[];
    counts: Record<string, number>;
    password: string;
    onUpdate: () => void;
    onSelectCategory: (cat: PortfolioCategory) => void;
    setError: any;
}) {
    const [showForm, setShowForm] = useState(false);
    const [form, setForm] = useState({ name: "", description: "", emoji: "📁" });
    const [loading, setLoading] = useState(false);
    const [showEmojiPicker, setShowEmojiPicker] = useState(false);

    const handleAddCategory = async () => {
        if (!form.name.trim()) {
            setError("Введите название раздела");
            return;
        }

        setLoading(true);
        try {
            const res = await fetch("/api/admin/portfolio", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ password, action: "addCategory", data: form }),
            });
            if (!res.ok) throw new Error("Ошибка");
            setForm({ name: "", description: "", emoji: "📁" });
            setShowForm(false);
            await onUpdate();
        } catch (err: any) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleDeleteCategory = async (e: React.MouseEvent, id: string) => {
        e.stopPropagation();
        if (!confirm("Удалить этот раздел? Все проекты внутри него останутся, но могут потерять категорию.")) return;
        try {
            const res = await fetch("/api/admin/portfolio", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ password, action: "deleteCategory", data: { id } }),
            });
            if (!res.ok) throw new Error("Ошибка");
            await onUpdate();
        } catch (err: any) {
            setError(err.message);
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h2 className="text-xl font-bold">Разделы портфолио</h2>
                {!showForm && (
                    <button
                        onClick={() => {
                            setForm({ name: "", description: "", emoji: "📁" });
                            setShowForm(true);
                        }}
                        className="btn btn-primary text-sm"
                    >
                        <Plus size={16} />
                        Создать раздел
                    </button>
                )}
            </div>

            {showForm && (
                <div className="glass p-6 space-y-4 bg-blue-50/50 animate-in slide-in-from-top-2 relative">
                    <h3 className="font-semibold text-sm">Новый раздел</h3>
                    <div className="flex gap-4">
                        <div className="relative">
                            <button
                                onClick={() => setShowEmojiPicker(!showEmojiPicker)}
                                className="w-[60px] h-[60px] text-3xl flex items-center justify-center bg-white rounded-[18px] border border-[rgba(10,10,10,0.1)] hover:bg-gray-50 transition-colors"
                            >
                                {form.emoji}
                            </button>
                            {showEmojiPicker && (
                                <div className="absolute top-0 left-full ml-2 w-[320px] h-[300px] bg-white rounded-[18px] shadow-2xl border border-[rgba(10,10,10,0.1)] overflow-hidden z-50 flex flex-col">
                                    <div className="p-3 bg-gray-50 border-b text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Выберите иконку
                                    </div>
                                    <div className="overflow-y-auto flex-1 p-2">
                                        {Object.entries(EMOJI_CATEGORIES).map(([category, emojis]) => (
                                            <div key={category} className="mb-4">
                                                <div className="text-xs font-medium text-gray-400 mb-2 px-1">{category}</div>
                                                <div className="grid grid-cols-7 gap-1">
                                                    {emojis.map((emoji, idx) => (
                                                        <button
                                                            key={`${category}-${idx}`} // Use index to guarantee uniqueness if user wants simpler logic, but better to dedup source. 
                                                            // Actually, let's trust the source is clean or use unique keys.
                                                            // Using emoji as key caused validation error because of duplicates in my previous list.
                                                            // I will use composite key.
                                                            onClick={() => {
                                                                setForm({ ...form, emoji });
                                                                setShowEmojiPicker(false);
                                                            }}
                                                            className="aspect-square hover:bg-blue-50 hover:scale-110 transition-transform rounded-[8px] flex items-center justify-center text-xl cursor-pointer"
                                                        >
                                                            {emoji}
                                                        </button>
                                                    ))}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                        <div className="flex-1 space-y-3">
                            <input
                                type="text"
                                value={form.name}
                                onChange={(e) => setForm({ ...form, name: e.target.value })}
                                placeholder="Название раздела (например: 'Прототипы')"
                                className="input-field"
                                autoFocus
                            />
                            <textarea
                                value={form.description}
                                onChange={(e) => setForm({ ...form, description: e.target.value })}
                                placeholder="Описание (необязательно)"
                                className="input-field min-h-[60px]"
                            />
                        </div>
                    </div>
                    <div className="flex gap-2 justify-end">
                        <button
                            onClick={() => setShowForm(false)}
                            className="btn"
                        >
                            Отмена
                        </button>
                        <button
                            onClick={handleAddCategory}
                            disabled={loading}
                            className="btn btn-primary"
                        >
                            {loading ? "Создание..." : "Создать раздел"}
                        </button>
                    </div>
                </div>
            )}

            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                {categories.map((cat) => (
                    <div
                        key={cat.id}
                        onClick={() => onSelectCategory(cat)}
                        className="rounded-[24px] border border-[rgba(10,10,10,0.08)] bg-white p-5 cursor-pointer hover:shadow-lg hover:border-[rgba(10,10,10,0.15)] hover:-translate-y-1 transition-all group relative overflow-hidden"
                    >
                        <div className="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            {!["podium", "car-parts", "industrial", "prototype"].includes(cat.id) && (
                                <button
                                    onClick={(e) => handleDeleteCategory(e, cat.id)}
                                    className="p-2 text-red-500 hover:bg-red-50 rounded-full"
                                    title="Удалить раздел"
                                >
                                    <Trash2 size={16} />
                                </button>
                            )}
                        </div>

                        <div className="mb-4 w-12 h-12 rounded-[18px] bg-[rgba(10,10,10,0.03)] flex items-center justify-center text-2xl group-hover:bg-blue-50 transition-colors">
                            {getCategoryEmoji(cat)}
                        </div>

                        <div>
                            <h3 className="font-bold text-lg mb-1">{cat.name}</h3>
                            <p className="text-sm text-[rgba(10,10,10,0.5)]">{counts[cat.id] || 0} проектов</p>
                        </div>

                        {cat.description && (
                            <p className="mt-4 text-xs text-[rgba(10,10,10,0.4)] line-clamp-2">{cat.description}</p>
                        )}
                    </div>
                ))}
            </div>
        </div>
    )
}

function CategoryDetailView({
    category,
    projects,
    allCategories,
    password,
    onUpdate,
    onBack,
    setError
}: {
    category: PortfolioCategory;
    projects: PortfolioProject[];
    allCategories: PortfolioCategory[];
    password: string;
    onUpdate: () => void;
    onBack: () => void;
    setError: any;
}) {
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState<string | null>(null);
    const [form, setForm] = useState<Partial<PortfolioProject>>({
        title: "",
        description: "",
        category: category.id,
        images: [],
        featured: false,
    });
    const [selectedGlb, setSelectedGlb] = useState<{ name: string; path: string } | null>(null);
    const [uploadingGlb, setUploadingGlb] = useState(false);
    const [glbError, setGlbError] = useState("");
    const [dragActive, setDragActive] = useState(false);

    // Reset form when opening/closing
    useEffect(() => {
        if (!showForm) {
            setForm({
                title: "",
                description: "",
                category: category.id, // Always default to current category
                images: [],
                featured: false,
            });
            setEditingId(null);
            setSelectedGlb(null);
        }
    }, [showForm, category.id]);

    const handleGlbUpload = async (file: File) => {
        if (!file.name.endsWith('.glb')) {
            setGlbError("Пожалуйста, выберите GLB файл (.glb)");
            return;
        }

        if (file.size > 100 * 1024 * 1024) {
            setGlbError("Файл слишком большой (максимум 100 MB)");
            return;
        }

        setUploadingGlb(true);
        setGlbError("");

        try {
            const formData = new FormData();
            formData.append("file", file);

            const res = await fetch("/api/upload/glb", {
                method: "POST",
                body: formData,
            });

            if (!res.ok) throw new Error("Ошибка при загрузке файла");

            const data = await res.json();
            setSelectedGlb({ name: file.name, path: data.storedPath });
            setForm(prev => ({ ...prev, glbModel: data.storedPath }));
        } catch (err: any) {
            setGlbError(err.message || "Ошибка при загрузке файла");
        } finally {
            setUploadingGlb(false);
        }
    };

    const handleDrag = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        if (e.type === "dragenter" || e.type === "dragover") {
            setDragActive(true);
        } else if (e.type === "dragleave") {
            setDragActive(false);
        }
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);

        const files = e.dataTransfer.files;
        if (files && files.length > 0) {
            handleGlbUpload(files[0]);
        }
    };

    const handleSave = async () => {
        if (!form.title?.trim()) {
            setError("Введите название проекта");
            return;
        }

        const action = editingId ? "updateProject" : "addProject";
        const data = action === "updateProject" ? { id: editingId, updates: form } : form;

        try {
            const res = await fetch("/api/admin/portfolio", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ password, action, data }),
            });
            if (!res.ok) throw new Error("Ошибка");
            setShowForm(false);
            await onUpdate();
        } catch (err: any) {
            setError(err.message);
        }
    };

    const handleEdit = (project: PortfolioProject) => {
        setForm(project);
        setEditingId(project.id);
        if (project.glbModel) {
            const fileName = project.glbModel.split("/").pop() || "model.glb";
            setSelectedGlb({ name: fileName, path: project.glbModel });
        } else {
            setSelectedGlb(null);
        }
        setShowForm(true);
    };

    const handleDelete = async (id: string) => {
        if (!confirm("Удалить этот проект?")) return;
        try {
            const res = await fetch("/api/admin/portfolio", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ password, action: "deleteProject", data: { id } }),
            });
            if (!res.ok) throw new Error("Ошибка");
            await onUpdate();
        } catch (err: any) {
            setError(err.message);
        }
    };

    if (showForm) {
        return (
            <div className="glass p-6 space-y-4 animate-in slide-in-from-bottom-2">
                <div className="flex justify-between items-center">
                    <h2 className="text-lg font-bold">
                        {editingId ? "Редактирование проекта" : "Новый проект"}
                    </h2>
                    <button onClick={() => setShowForm(false)} className="text-sm text-[rgba(10,10,10,0.5)] hover:text-black">
                        Отмена
                    </button>
                </div>

                <div className="space-y-4">
                    <div>
                        <label className="block text-xs font-medium text-[rgba(10,10,10,0.5)] mb-1">Название *</label>
                        <input
                            type="text"
                            value={form.title || ""}
                            onChange={(e) => setForm({ ...form, title: e.target.value })}
                            className="input-field"
                            placeholder="Название проекта"
                        />
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-[rgba(10,10,10,0.5)] mb-1">Описание</label>
                        <textarea
                            value={form.description || ""}
                            onChange={(e) => setForm({ ...form, description: e.target.value })}
                            className="input-field min-h-[100px]"
                            placeholder="Описание"
                        />
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-medium text-[rgba(10,10,10,0.5)] mb-1">Раздел</label>
                            <div className="relative">
                                <select
                                    value={form.category || category.id}
                                    onChange={(e) => setForm({ ...form, category: e.target.value })}
                                    className="input-field appearance-none"
                                >
                                    {allCategories.map(cat => (
                                        <option key={cat.id} value={cat.id}>{cat.name}</option>
                                    ))}
                                </select>
                                <div className="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none opacity-50">▼</div>
                            </div>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-[rgba(10,10,10,0.5)] mb-1">Опции</label>
                            <label className="flex items-center gap-2 p-2 border border-[rgba(10,10,10,0.1)] rounded-[12px] bg-white/50 cursor-pointer hover:bg-white/80 transition-colors">
                                <input
                                    type="checkbox"
                                    checked={form.featured || false}
                                    onChange={(e) => setForm({ ...form, featured: e.target.checked })}
                                    className="rounded text-black focus:ring-black"
                                />
                                <span className="text-sm">Показать на главной</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label className="block text-xs font-medium text-[rgba(10,10,10,0.5)] mb-1">Изображения (URL)</label>
                        <input
                            type="text"
                            value={form.images?.join(", ") || ""}
                            onChange={(e) => setForm({
                                ...form,
                                images: e.target.value.split(",").map(s => s.trim()).filter(Boolean)
                            })}
                            className="input-field"
                            placeholder="https://example.com/image.jpg, ..."
                        />
                    </div>

                    {/* GLB Upload */}
                    <div>
                        <label className="block text-xs font-medium text-[rgba(10,10,10,0.5)] mb-1">3D Модель (GLB)</label>
                        <div
                            onDragEnter={handleDrag}
                            onDragLeave={handleDrag}
                            onDragOver={handleDrag}
                            onDrop={handleDrop}
                            className={`border-2 border-dashed rounded-[18px] p-6 text-center transition-all ${dragActive ? "border-blue-500 bg-blue-50" : "border-[rgba(10,10,10,0.1)] bg-white/30 hover:bg-white/60 hover:border-[rgba(10,10,10,0.3)]"
                                } ${uploadingGlb ? "opacity-60 pointer-events-none" : ""}`}
                        >
                            <input
                                type="file"
                                accept=".glb"
                                onChange={(e) => e.target.files && handleGlbUpload(e.target.files[0])}
                                className="hidden"
                                id="glb-input"
                            />
                            <label htmlFor="glb-input" className="cursor-pointer block flex flex-col items-center gap-2">
                                <div className="w-10 h-10 rounded-full bg-[rgba(10,10,10,0.05)] flex items-center justify-center">
                                    <Upload size={18} className="opacity-40" />
                                </div>
                                <div>
                                    <div className="text-sm font-medium">
                                        {uploadingGlb ? "Загрузка..." : "Нажми или перетащи GLB"}
                                    </div>
                                </div>
                            </label>
                        </div>
                        {glbError && <div className="text-xs text-red-600 mt-1">{glbError}</div>}
                        {selectedGlb && (
                            <div className="mt-2 p-2 bg-green-50 border border-green-100 rounded-[12px] flex justify-between items-center animate-in fade-in slide-in-from-top-1">
                                <div className="text-xs text-green-800 font-medium flex items-center gap-2">
                                    <span className="w-4 h-4 rounded-full bg-green-200 flex items-center justify-center text-[10px]">✓</span>
                                    {selectedGlb.name}
                                </div>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSelectedGlb(null);
                                        setForm({ ...form, glbModel: undefined } as Partial<PortfolioProject>);
                                    }}
                                    className="text-xs text-red-600 hover:text-red-800 p-1"
                                >
                                    <X size={14} />
                                </button>
                            </div>
                        )}
                    </div>

                    <button
                        onClick={handleSave}
                        className="btn btn-primary w-full"
                    >
                        {editingId ? "Сохранить изменения" : "Добавить проект"}
                    </button>
                </div>
            </div>
        )
    }

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-4">
                <button
                    onClick={onBack}
                    className="w-10 h-10 rounded-full bg-white border border-[rgba(10,10,10,0.08)] flex items-center justify-center hover:bg-[rgba(10,10,10,0.02)] transition-colors"
                >
                    <ArrowLeft size={18} />
                </button>
                <div>
                    <h2 className="text-xl font-bold">{category.name}</h2>
                    <p className="text-xs text-[rgba(10,10,10,0.5)]">{projects.length} проектов</p>
                </div>
                <button
                    onClick={() => setShowForm(true)}
                    className="btn btn-primary ml-auto text-sm"
                >
                    <Plus size={16} />
                    Добавить проект
                </button>
            </div>

            {projects.length === 0 ? (
                <div className="text-[rgba(10,10,10,0.4)] text-center py-20 border-2 border-dashed border-[rgba(10,10,10,0.1)] rounded-[24px]">
                    В этом разделе пока нет проектов. Добавьте первый!
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {projects.map((project) => (
                        <div key={project.id} className="glass p-4 gap-4 flex flex-col relative group">
                            {(project.featured) && (
                                <div className="absolute top-4 right-4 text-yellow-500 bg-yellow-50 p-1 rounded-full pointer-events-none z-10" title="Избранное">
                                    <Star size={14} fill="currentColor" />
                                </div>
                            )}

                            <div className="flex gap-4">
                                {project.images.length > 0 && (
                                    <div className="w-24 h-24 bg-gray-100 rounded-[14px] overflow-hidden flex-shrink-0 border border-[rgba(10,10,10,0.05)]">
                                        <img
                                            src={project.images[0]}
                                            alt={project.title}
                                            className="w-full h-full object-cover"
                                            onError={(e) => {
                                                (e.target as HTMLImageElement).src =
                                                    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect fill='%23f0f0f0' width='100' height='100'/%3E%3C/svg%3E";
                                            }}
                                        />
                                    </div>
                                )}

                                <div className="flex-1 min-w-0">
                                    <h3 className="font-bold text-sm truncate pr-8 mb-1">{project.title}</h3>
                                    <p className="text-xs text-[rgba(10,10,10,0.6)] line-clamp-2 leading-relaxed h-[36px]">
                                        {project.description || "Нет описания"}
                                    </p>
                                    <div className="mt-2 text-[10px] text-[rgba(10,10,10,0.4)]">
                                        {new Date(project.createdAt).toLocaleDateString("ru-RU")}
                                    </div>
                                </div>
                            </div>

                            <div className="flex gap-2 pt-3 border-t border-[rgba(10,10,10,0.06)] mt-auto">
                                <button
                                    onClick={() => handleEdit(project)}
                                    className="btn btn-ghost h-[32px] flex-1 text-xs"
                                >
                                    <Edit2 size={13} />
                                    Редактировать
                                </button>
                                <button
                                    onClick={() => handleDelete(project.id)}
                                    className="btn h-[32px] px-3 text-red-600 hover:bg-red-50 border-transparent"
                                >
                                    <Trash2 size={14} />
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    )
}
