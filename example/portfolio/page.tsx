"use client";

import { useEffect, useState } from "react";
import { Model3DViewer } from "@/app/components/Model3DViewer";
import { motion, AnimatePresence } from "framer-motion";
import { ChevronLeft, ChevronRight, X } from "lucide-react";

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

export default function PortfolioPage() {
  const [categories, setCategories] = useState<PortfolioCategory[]>([]);
  const [projects, setProjects] = useState<PortfolioProject[]>([]);
  const [selectedCategory, setSelectedCategory] = useState<string>("podium");
  const [loading, setLoading] = useState(true);
  const [selectedProject, setSelectedProject] = useState<PortfolioProject | null>(null);
  const [imageIndex, setImageIndex] = useState(0);

  useEffect(() => {
    loadPortfolio();
  }, []);

  async function loadPortfolio() {
    try {
      const res = await fetch("/api/admin/portfolio");
      const data = await res.json();
      setCategories(data.categories);
      setProjects(data.projects);
    } catch (err) {
      console.error("Ошибка загрузки портфолио:", err);
    } finally {
      setLoading(false);
    }
  }

  const filteredProjects = projects.filter((p) => p.category === selectedCategory);

  if (loading) {
    return <div className="text-gray-600">Загрузка...</div>;
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">Портфолио</h1>
        <p className="text-gray-700 mt-2">Примеры выполненных проектов</p>
      </div>

      {/* Фильтр по категориям */}
      <div className="flex gap-2 overflow-x-auto pb-2">
        {categories.map((cat) => (
          <button
            key={cat.id}
            onClick={() => setSelectedCategory(cat.id)}
            className={`px-4 py-2 rounded-xl whitespace-nowrap transition ${
              selectedCategory === cat.id
                ? "bg-black text-white"
                : "border hover:border-black"
            }`}
          >
            {cat.name}
          </button>
        ))}
      </div>

      {/* Проекты */}
      {filteredProjects.length === 0 ? (
        <div className="rounded-2xl border p-8 text-center text-gray-600">
          В этой категории нет проектов
        </div>
      ) : (
        <div className="grid gap-4 md:grid-cols-3">
          {filteredProjects.map((project) => (
            <motion.div
              key={project.id}
              onClick={() => {
                setSelectedProject(project);
                setImageIndex(0);
              }}
              className="rounded-2xl border overflow-hidden hover:shadow-lg hover:border-black transition cursor-pointer group"
              whileHover={{ y: -4 }}
            >
              {project.glbModel ? (
                <div className="aspect-square bg-gray-100 overflow-hidden relative">
                  <Model3DViewer modelPath={project.glbModel} width="100%" height="100%" />
                  <div className="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition flex items-center justify-center">
                    <span className="text-white text-sm font-medium opacity-0 group-hover:opacity-100 transition">Посмотреть детали</span>
                  </div>
                </div>
              ) : project.images.length > 0 ? (
                <div className="aspect-square bg-gray-100 overflow-hidden relative">
                  <img
                    src={project.images[0]}
                    alt={project.title}
                    className="w-full h-full object-cover group-hover:scale-105 transition"
                    onError={(e) => {
                      (e.target as HTMLImageElement).src =
                        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Crect fill='%23e5e5e5' width='300' height='300'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23999' font-size='16'%3EИзображение не загрузилось%3C/text%3E%3C/svg%3E";
                    }}
                  />
                  <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition flex items-center justify-center">
                    <span className="text-white text-sm font-medium opacity-0 group-hover:opacity-100 transition">Посмотреть детали</span>
                  </div>
                </div>
              ) : (
                <div className="aspect-square bg-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-gray-200 transition relative">
                  Нет изображения
                  <div className="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition flex items-center justify-center">
                    <span className="text-white text-sm font-medium opacity-0 group-hover:opacity-100 transition">Посмотреть детали</span>
                  </div>
                </div>
              )}
              <div className="p-4">
                <h3 className="font-semibold">{project.title}</h3>
                {project.description && (
                  <p className="text-sm text-gray-600 mt-1 line-clamp-2">
                    {project.description}
                  </p>
                )}
              </div>
            </motion.div>
          ))}
        </div>
      )}

      {/* Modal for Project Details */}
      <AnimatePresence>
        {selectedProject && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
            onClick={() => setSelectedProject(null)}
          >
            <motion.div
              initial={{ scale: 0.9, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.9, opacity: 0 }}
              className="bg-white rounded-[24px] w-full max-w-3xl max-h-[90vh] overflow-y-auto shadow-2xl"
              onClick={(e) => e.stopPropagation()}
            >
              {/* Close Button */}
              <div className="sticky top-0 flex justify-end p-4 bg-white border-b z-10">
                <button
                  onClick={() => setSelectedProject(null)}
                  className="p-2 hover:bg-gray-100 rounded-lg transition"
                >
                  <X size={24} />
                </button>
              </div>

              <div className="p-6 space-y-6">
                {/* Main Image/Model */}
                <div className="rounded-[16px] overflow-hidden bg-gray-100 aspect-video flex items-center justify-center relative">
                  {selectedProject.glbModel ? (
                    <Model3DViewer modelPath={selectedProject.glbModel} width="100%" height="100%" />
                  ) : selectedProject.images.length > 0 ? (
                    <>
                      <img
                        src={selectedProject.images[imageIndex]}
                        alt={selectedProject.title}
                        className="w-full h-full object-cover"
                        onError={(e) => {
                          (e.target as HTMLImageElement).src =
                            "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect fill='%23e5e5e5' width='400' height='300'/%3E%3C/svg%3E";
                        }}
                      />
                      {selectedProject.images.length > 1 && (
                        <>
                          <button
                            onClick={() =>
                              setImageIndex((prev) =>
                                prev === 0
                                  ? selectedProject.images.length - 1
                                  : prev - 1
                              )
                            }
                            className="absolute left-4 p-2 bg-white/80 hover:bg-white rounded-full transition"
                          >
                            <ChevronLeft size={20} />
                          </button>
                          <button
                            onClick={() =>
                              setImageIndex((prev) =>
                                prev === selectedProject.images.length - 1
                                  ? 0
                                  : prev + 1
                              )
                            }
                            className="absolute right-4 p-2 bg-white/80 hover:bg-white rounded-full transition"
                          >
                            <ChevronRight size={20} />
                          </button>
                          <div className="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/50 text-white px-3 py-1 rounded-full text-sm">
                            {imageIndex + 1} / {selectedProject.images.length}
                          </div>
                        </>
                      )}
                    </>
                  ) : (
                    <div className="text-gray-400 text-center">
                      <p className="text-lg font-medium">Нет изображения</p>
                    </div>
                  )}
                </div>

                {/* Project Info */}
                <div>
                  <div className="flex items-start justify-between gap-4 mb-4">
                    <div>
                      <h1 className="text-3xl font-bold mb-2">{selectedProject.title}</h1>
                      <p className="text-gray-600 text-sm">
                        Категория:{" "}
                        <span className="font-medium">
                          {categories.find((c) => c.id === selectedProject.category)
                            ?.name || selectedProject.category}
                        </span>
                      </p>
                    </div>
                    {selectedProject.featured && (
                      <span className="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium whitespace-nowrap">
                        ⭐ Избранный
                      </span>
                    )}
                  </div>

                  {selectedProject.description && (
                    <div className="space-y-2">
                      <h3 className="font-semibold text-lg">Описание</h3>
                      <p className="text-gray-700 leading-relaxed">
                        {selectedProject.description}
                      </p>
                    </div>
                  )}

                  {/* Image Gallery */}
                  {selectedProject.images.length > 1 && (
                    <div className="mt-6">
                      <h3 className="font-semibold text-lg mb-3">Галерея</h3>
                      <div className="grid grid-cols-4 gap-2">
                        {selectedProject.images.map((img, idx) => (
                          <button
                            key={idx}
                            onClick={() => setImageIndex(idx)}
                            className={`rounded-lg overflow-hidden aspect-square border-2 transition ${
                              idx === imageIndex
                                ? "border-black"
                                : "border-gray-200 hover:border-gray-400"
                            }`}
                          >
                            <img
                              src={img}
                              alt={`${selectedProject.title} ${idx + 1}`}
                              className="w-full h-full object-cover"
                              onError={(e) => {
                                (e.target as HTMLImageElement).src =
                                  "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect fill='%23e5e5e5' width='100' height='100'/%3E%3C/svg%3E";
                              }}
                            />
                          </button>
                        ))}
                      </div>
                    </div>
                  )}
                </div>

                {/* Action Button */}
                <div className="flex gap-2 pt-4 border-t">
                  <button
                    onClick={() => window.location.href = "/order"}
                    className="flex-1 px-4 py-3 bg-black text-white rounded-lg hover:bg-gray-900 transition font-medium"
                  >
                    Заказать похожий проект
                  </button>
                  <button
                    onClick={() => setSelectedProject(null)}
                    className="px-4 py-3 border rounded-lg hover:bg-gray-50 transition font-medium"
                  >
                    Закрыть
                  </button>
                </div>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}