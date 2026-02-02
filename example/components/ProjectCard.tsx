"use client";

import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Model3DViewer } from "./Model3DViewer";
import { ProjectDetailModal } from "./ProjectDetailModal";

export function ProjectCard({ project, categoryName }: {
  project: any;
  categoryName: string;
}) {
  const [isModalOpen, setIsModalOpen] = useState(false);

  return (
    <>
      <motion.button
        initial={{ opacity: 0, scale: 0.95 }}
        whileInView={{ opacity: 1, scale: 1 }}
        viewport={{ once: true }}
        whileHover={{ y: -5, boxShadow: "0 10px 30px -10px rgba(0,0,0,0.1)" }}
        transition={{ duration: 0.3 }}
        onClick={() => setIsModalOpen(true)}
        className="rounded-2xl border bg-white overflow-hidden w-full text-left group"
      >
        {project.glbModel ? (
          <div style={{ aspectRatio: "1", backgroundColor: "#f5f5f5" }}>
            <Model3DViewer
              modelPath={project.glbModel}
              width="100%"
              height="100%"
              autoRotate={true}
            />
          </div>
        ) : project.images && project.images.length > 0 ? (
          <div className="aspect-square bg-gray-100 overflow-hidden relative">
            <img
              src={project.images[0]}
              alt={project.title}
              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              onError={(e) => {
                (e.target as HTMLImageElement).src =
                  "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Crect fill='%23e5e5e5' width='300' height='300'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23999' font-size='16'%3EИзображение не загрузилось%3C/text%3E%3C/svg%3E";
              }}
            />
            <div className="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300" />
          </div>
        ) : (
          <div className="aspect-square bg-gray-100 flex items-center justify-center text-gray-400">
            Нет изображения
          </div>
        )}
        <div className="p-4">
          <div className="text-xs text-blue-600 font-medium mb-1">{categoryName}</div>
          <h3 className="font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">{project.title}</h3>
          {project.description && (
            <p className="text-sm text-gray-600 mt-1 line-clamp-2">
              {project.description}
            </p>
          )}
        </div>
      </motion.button>

      <AnimatePresence>
        {isModalOpen && (
          <ProjectDetailModal
            project={project}
            isOpen={isModalOpen}
            onClose={() => setIsModalOpen(false)}
          />
        )}
      </AnimatePresence>
    </>
  );
}

