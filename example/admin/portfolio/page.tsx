"use client";

import { useState, useEffect } from "react";
import { Model3DViewer } from "@/app/components/Model3DViewer";

type PortfolioCategory = {
  id: string;
  name: string;
  description: string;
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

export default function AdminPortfolioPage() {
  const [password, setPassword] = useState("");
  const [isAuth, setIsAuth] = useState(false);
  const [categories, setCategories] = useState<PortfolioCategory[]>([]);
  const [projects, setProjects] = useState<PortfolioProject[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [tab, setTab] = useState<"projects" | "categories">("projects");

  async function handleLogin(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      const res = await fetch("/api/admin/portfolio");
      if (!res.ok) throw new Error("Ошибка загрузки");
      const data = await res.json();
      setCategories(data.categories);
      setProjects(data.projects);
      setIsAuth(true);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  async function loadPortfolio() {
    try {
      const res = await fetch("/api/admin/portfolio");
      if (!res.ok) throw new Error("Ошибка загрузки");
      const data = await res.json();
      setCategories(data.categories);
      setProjects(data.projects);
    } catch (err: any) {
      setError(err.message);
    }
  }

  if (!isAuth) {
    return (
      <div className="max-w-md mx-auto mt-20">
        <div className="rounded-2xl border p-6">
          <h1 className="text-2xl font-semibold mb-4">Портфолио</h1>
          <p className="text-sm text-gray-600 mb-4">
            Введи пароль от админки для редактирования портфолио
          </p>
          <form onSubmit={handleLogin} className="space-y-4">
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Пароль"
              className="w-full border rounded-xl p-2"
              disabled={loading}
            />
            {error && <div className="text-red-600 text-sm">{error}</div>}
            <button
              type="submit"
              disabled={loading}
              className="w-full bg-black text-white rounded-xl py-2 disabled:bg-gray-400"
            >
              {loading ? "Проверка..." : "Войти"}
            </button>
          </form>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-semibold">Управление портфолио</h1>
        <button
          onClick={() => {
            setIsAuth(false);
            setPassword("");
          }}
          className="px-4 py-2 border rounded-xl text-sm"
        >
          Выход
        </button>
      </div>

      {/* Табы */}
      <div className="flex gap-4 border-b">
        <button
          onClick={() => setTab("projects")}
          className={`px-4 py-2 font-medium ${
            tab === "projects" ? "border-b-2 border-black" : "text-gray-600"
          }`}
        >
          Проекты ({projects.length})
        </button>
        <button
          onClick={() => setTab("categories")}
          className={`px-4 py-2 font-medium ${
            tab === "categories" ? "border-b-2 border-black" : "text-gray-600"
          }`}
        >
          Разделы ({categories.length})
        </button>
      </div>

      {error && <div className="text-red-600 bg-red-50 p-4 rounded-xl">{error}</div>}

      {/* Таб Проектов */}
      {tab === "projects" && (
        <ProjectsTab
          projects={projects}
          categories={categories}
          password={password}
          onUpdate={loadPortfolio}
          setError={setError}
        />
      )}

      {/* Таб Разделов */}
      {tab === "categories" && (
        <CategoriesTab
          categories={categories}
          password={password}
          onUpdate={loadPortfolio}
          setError={setError}
        />
      )}
    </div>
  );
}

function ProjectsTab({
  projects,
  categories,
  password,
  onUpdate,
  setError,
}: {
  projects: PortfolioProject[];
  categories: PortfolioCategory[];
  password: string;
  onUpdate: () => void;
  setError: any;
}) {
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [form, setForm] = useState<Partial<PortfolioProject>>({
    title: "",
    description: "",
    category: "podium",
    images: [],
    featured: false,
  });
  const [selectedGlb, setSelectedGlb] = useState<{ name: string; path: string } | null>(null);
  const [uploadingGlb, setUploadingGlb] = useState(false);
  const [glbError, setGlbError] = useState("");
  const [dragActive, setDragActive] = useState(false);

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
      setForm({ ...form, glbModel: data.storedPath } as Partial<PortfolioProject>);
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
    const data =
      action === "updateProject"
        ? { id: editingId, updates: form }
        : form;

    try {
      const res = await fetch("/api/admin/portfolio", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ password, action, data }),
      });
      if (!res.ok) throw new Error("Ошибка");
      setShowForm(false);
      setEditingId(null);
      setSelectedGlb(null);
      setForm({
        title: "",
        description: "",
        category: "podium",
        images: [],
        featured: false,
      });
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
      <div className="rounded-2xl border p-6 space-y-4">
        <div className="flex justify-between items-center">
          <h2 className="text-xl font-semibold">
            {editingId ? "Редактирование" : "Новый проект"}
          </h2>
          <button
            onClick={() => {
              setShowForm(false);
              setEditingId(null);
              setSelectedGlb(null);
              setGlbError("");
              setForm({
                title: "",
                description: "",
                category: "podium",
                images: [],
                featured: false,
              });
            }}
            className="text-gray-600 underline text-sm"
          >
            Отмена
          </button>
        </div>

        <div className="space-y-3">
          <div>
            <label className="block text-sm font-medium mb-1">Название *</label>
            <input
              type="text"
              value={form.title || ""}
              onChange={(e) => setForm({ ...form, title: e.target.value })}
              placeholder="Название проекта"
              className="w-full border rounded-xl p-2"
            />
          </div>

          <div>
            <label className="block text-sm font-medium mb-1">Описание</label>
            <textarea
              value={form.description || ""}
              onChange={(e) => setForm({ ...form, description: e.target.value })}
              placeholder="Описание проекта"
              className="w-full border rounded-xl p-2 min-h-24"
            />
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-sm font-medium mb-1">Раздел</label>
              <select
                value={form.category || "podium"}
                onChange={(e) => setForm({ ...form, category: e.target.value })}
                className="w-full border rounded-xl p-2"
              >
                {categories.map((cat) => (
                  <option key={cat.id} value={cat.id}>
                    {cat.name}
                  </option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">Избранное</label>
              <label className="flex items-center gap-2">
                <input
                  type="checkbox"
                  checked={form.featured || false}
                  onChange={(e) => setForm({ ...form, featured: e.target.checked })}
                  className="rounded"
                />
                <span className="text-sm">Показать на главной</span>
              </label>
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium mb-1">Ссылки на изображения (через запятую)</label>
            <textarea
              value={form.images?.join(", ") || ""}
              onChange={(e) =>
                setForm({
                  ...form,
                  images: e.target.value
                    .split(",")
                    .map((s) => s.trim())
                    .filter((s) => s),
                })
              }
              placeholder="/uploads/image1.jpg, /uploads/image2.jpg"
              className="w-full border rounded-xl p-2 min-h-16 text-xs"
            />
          </div>

          {/* GLB 3D Model Upload */}
          <div>
            <label className="block text-sm font-medium mb-1">3D Модель (GLB файл)</label>
            <div
              onDragEnter={handleDrag}
              onDragLeave={handleDrag}
              onDragOver={handleDrag}
              onDrop={handleDrop}
              className={`border-2 border-dashed rounded-xl p-6 text-center transition-colors ${
                dragActive ? "border-blue-500 bg-blue-50" : "border-gray-300"
              } ${uploadingGlb ? "opacity-60 pointer-events-none" : ""}`}
            >
              <input
                type="file"
                accept=".glb"
                onChange={(e) => e.target.files && handleGlbUpload(e.target.files[0])}
                className="hidden"
                id="glb-input"
              />
              <label htmlFor="glb-input" className="cursor-pointer block">
                <div className="text-sm text-gray-600">
                  {uploadingGlb ? "Загрузка..." : "Перетащите GLB файл сюда или"}
                </div>
                <div className="text-sm font-medium text-blue-600 mt-1">нажмите для выбора файла</div>
              </label>
            </div>
            {glbError && <div className="text-sm text-red-600 mt-1">{glbError}</div>}
            {selectedGlb && (
              <div className="mt-2 p-2 bg-green-50 border border-green-200 rounded-lg flex justify-between items-center">
                <div className="text-sm text-green-800">✓ {selectedGlb.name}</div>
                <button
                  type="button"
                  onClick={() => {
                    setSelectedGlb(null);
                    setForm({ ...form, glbModel: undefined } as Partial<PortfolioProject>);
                  }}
                  className="text-sm text-red-600 hover:text-red-800"
                >
                  Удалить
                </button>
              </div>
            )}
          </div>

          <button
            onClick={handleSave}
            className="w-full px-4 py-2 bg-black text-white rounded-xl"
          >
            {editingId ? "Обновить" : "Создать"}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <button
        onClick={() => {
          setShowForm(true);
          setEditingId(null);
          setForm({
            title: "",
            description: "",
            category: "podium",
            images: [],
            featured: false,
          });
        }}
        className="px-4 py-2 bg-black text-white rounded-xl"
      >
        + Новый проект
      </button>

      <div className="grid grid-cols-1 gap-4">
        {projects.map((project) => {
          console.log('Project:', project.title, 'has glbModel:', project.glbModel);
          return (
          <div key={project.id} className="rounded-2xl border p-4 flex gap-4">
            {/* 3D Model Preview or Image */}
            <div className="w-32 h-32 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0 relative">
              {project.glbModel ? (
                <div className="w-full h-full border-2 border-green-500">
                  <Model3DViewer
                    modelPath={project.glbModel}
                    width="100%"
                    height="100%"
                    autoRotate={true}
                    enableZoom={false}
                  />
                </div>
              ) : project.images.length > 0 ? (
                <img
                  src={project.images[0]}
                  alt={project.title}
                  className="w-full h-full object-cover"
                  onError={(e) => {
                    (e.target as HTMLImageElement).src =
                      "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect fill='%23f0f0f0' width='100' height='100'/%3E%3C/svg%3E";
                  }}
                />
              ) : (
                <div className="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                  Нет превью
                </div>
              )}
            </div>

            <div className="flex-1">
              <div className="flex justify-between items-start mb-2">
                <div>
                  <h3 className="font-semibold">{project.title}</h3>
                  <p className="text-xs text-gray-600">
                    {categories.find((c) => c.id === project.category)?.name}
                  </p>
                </div>
                {(project.featured || project.category === "podium") && (
                  <span className="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">
                    ⭐ Избранное
                  </span>
                )}
              </div>

              {project.description && (
                <p className="text-sm text-gray-600 mb-2 line-clamp-2">
                  {project.description}
                </p>
              )}

              <p className="text-xs text-gray-500 mb-3">
                {project.images.length} изображений •{" "}
                {new Date(project.createdAt).toLocaleDateString("ru-RU")}
              </p>

              <div className="flex gap-2">
                <button
                  onClick={() => handleEdit(project)}
                  className="text-blue-600 text-sm underline"
                >
                  Редактировать
                </button>
                <button
                  onClick={() => handleDelete(project.id)}
                  className="text-red-600 text-sm underline"
                >
                  Удалить
                </button>
              </div>
            </div>
          </div>
        );
        })}
      </div>

      {projects.length === 0 && (
        <div className="text-gray-600 text-center py-8">Проектов нет</div>
      )}
    </div>
  );
}

function CategoriesTab({
  categories,
  password,
  onUpdate,
  setError,
}: {
  categories: PortfolioCategory[];
  password: string;
  onUpdate: () => void;
  setError: any;
}) {
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ name: "", description: "" });
  const [loading, setLoading] = useState(false);

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
      setForm({ name: "", description: "" });
      setShowForm(false);
      await onUpdate();
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteCategory = async (id: string) => {
    if (!confirm("Удалить этот раздел?")) return;
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
    <div className="space-y-4">
      {!showForm && (
        <button
          onClick={() => setShowForm(true)}
          className="px-4 py-2 bg-black text-white rounded-xl"
        >
          + Новый раздел
        </button>
      )}

      {showForm && (
        <div className="rounded-2xl border p-6 space-y-3 bg-blue-50">
          <input
            type="text"
            value={form.name}
            onChange={(e) => setForm({ ...form, name: e.target.value })}
            placeholder="Название раздела"
            className="w-full border rounded-xl p-2"
          />
          <textarea
            value={form.description}
            onChange={(e) => setForm({ ...form, description: e.target.value })}
            placeholder="Описание раздела"
            className="w-full border rounded-xl p-2 min-h-16"
          />
          <div className="flex gap-2">
            <button
              onClick={handleAddCategory}
              disabled={loading}
              className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl disabled:bg-gray-400"
            >
              {loading ? "Добавление..." : "Добавить"}
            </button>
            <button
              onClick={() => {
                setShowForm(false);
                setForm({ name: "", description: "" });
              }}
              className="flex-1 px-4 py-2 border rounded-xl"
            >
              Отмена
            </button>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 gap-4">
        {categories.map((cat) => (
          <div key={cat.id} className="rounded-2xl border p-4">
            <div className="flex justify-between items-start mb-2">
              <div>
                <h3 className="font-semibold">{cat.name}</h3>
                <p className="text-xs text-gray-600">ID: {cat.id}</p>
              </div>
              {!["podium", "car-parts", "industrial", "prototype"].includes(cat.id) && (
                <button
                  onClick={() => handleDeleteCategory(cat.id)}
                  className="text-red-600 text-sm underline"
                >
                  Удалить
                </button>
              )}
            </div>
            {cat.description && (
              <p className="text-sm text-gray-600">{cat.description}</p>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}
