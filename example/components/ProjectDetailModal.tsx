"use client";

import { useState } from "react";
import { Model3DViewer } from "./Model3DViewer";
import { motion } from "framer-motion";

interface ProjectDetailModalProps {
  project: {
    id: string;
    title: string;
    description: string;
    glbModel?: string;
    images: string[];
  };
  isOpen: boolean; // kept for compatibility but not used for return null
  onClose: () => void;
}

export function ProjectDetailModal({ project, isOpen, onClose }: ProjectDetailModalProps) {
  const [scrolled, setScrolled] = useState(false);

  // Note: Conditional rendering is now handled by parent AnimatePresence, so we simply render.

  const handleScroll = (e: React.UIEvent<HTMLDivElement>) => {
    const element = e.currentTarget;
    setScrolled(element.scrollTop > 100);
  };

  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      className="fixed inset-0 z-50 bg-black/50 overflow-y-auto backdrop-blur-sm"
      onClick={onClose}
    >
      <motion.div
        layoutId={`card-${project.id}`}
        className="min-h-screen flex flex-col bg-white overflow-hidden shadow-2xl"
        transition={{ type: "spring", bounce: 0.15, duration: 0.5 }}
        onClick={(e) => e.stopPropagation()}
      >
        {/* Close button - always visible */}
        <button
          onClick={onClose}
          className="fixed top-4 right-4 z-50 bg-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-100"
        >
          ✕
        </button>



        {/* Content */}
        <div className="flex-1 flex flex-col md:flex-row overflow-hidden relative">
          {/* 3D Model Area */}
          <div className="flex-1 bg-gray-50 flex items-center justify-center min-h-[300px] md:min-h-auto relative">
            {project.glbModel ? (
              <Model3DViewer
                modelPath={project.glbModel}
                width="100%"
                height="100%"
                autoRotate={false}
                enableZoom={true}
              />
            ) : project.images.length > 0 ? (
              <img
                src={project.images[0]}
                alt={project.title}
                className="w-full h-full object-contain p-4"
              />
            ) : (
              <div className="text-gray-400 text-center">
                <div className="mb-2">Нет изображения</div>
              </div>
            )}

            {/* Header on Mobile floating or top */}
          </div>

          {/* Project info - Scrollable Sidebar */}
          <div
            className="w-full md:w-[400px] flex-shrink-0 bg-white border-l border-gray-100 p-8 overflow-y-auto custom-scrollbar"
            onScroll={handleScroll}
          >
            <div className="space-y-6">
              <div>
                <h1 className="text-2xl font-bold mb-3 leading-tight">{project.title}</h1>
                <p className="text-gray-600 leading-relaxed text-[15px]">
                  {project.description}
                </p>
              </div>

              {project.images.length > 1 && (
                <div>
                  <h3 className="text-xs font-bold uppercase tracking-wider mb-3 text-gray-400">
                    Галерея
                  </h3>
                  <div className="grid grid-cols-2 gap-2">
                    {project.images.map((img, idx) => (
                      <img
                        key={idx}
                        src={img}
                        alt={`${project.title} ${idx + 1}`}
                        className="w-full aspect-square object-cover rounded-lg border border-gray-100"
                      />
                    ))}
                  </div>
                </div>
              )}

              <button
                onClick={onClose}
                className="w-full bg-black text-white rounded-xl py-4 font-medium hover:bg-gray-800 transition-colors mt-auto"
              >
                Закрыть
              </button>
            </div>
          </div>
        </div>
      </motion.div>
    </motion.div>
  );
}
