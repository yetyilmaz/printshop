"use client";

import { useEffect, useRef, useState } from "react";
import * as THREE from "three";
import { GLTFLoader } from "three/examples/jsm/loaders/GLTFLoader.js";
import { STLLoader } from "three/examples/jsm/loaders/STLLoader.js";

interface Model3DViewerProps {
  modelPath: string;
  width?: string;
  height?: string;
  autoRotate?: boolean;
  enableZoom?: boolean;
}

export function Model3DViewer({
  modelPath,
  width = "100%",
  height = "400px",
  autoRotate = true,
  enableZoom = false,
}: Model3DViewerProps) {
  const containerRef = useRef<HTMLDivElement>(null);
  const sceneRef = useRef<THREE.Scene | null>(null);
  const rendererRef = useRef<THREE.WebGLRenderer | null>(null);
  const modelRef = useRef<THREE.Group | null>(null);
  const cameraRef = useRef<THREE.PerspectiveCamera | null>(null);
  const animationIdRef = useRef<number | null>(null);
  const isDraggingRef = useRef(false);
  const previousMouseRef = useRef({ x: 0, y: 0 });
  const domElementRef = useRef<HTMLCanvasElement | null>(null);
  const listenersRef = useRef<{ element: HTMLCanvasElement; type: string; listener: (e: Event) => void }[]>([]);
  const isVisibleRef = useRef(true);
  const cleanupDoneRef = useRef(false);
  const zoomRef = useRef(1); // 1 = normal, < 1 = zoomed out, > 1 = zoomed in
  const baseCameraZRef = useRef(0); // Store base camera Z distance
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  // Setup visibility observer
  useEffect(() => {
    const container = containerRef.current;
    if (!container) return;

    const observer = new IntersectionObserver(
      ([entry]) => {
        isVisibleRef.current = entry.isIntersecting;
      },
      { threshold: 0.1 }
    );

    observer.observe(container);

    return () => {
      observer.unobserve(container);
      observer.disconnect();
    };
  }, []);

  useEffect(() => {
    if (!containerRef.current) return;

    // Reset cleanup flag when component mounts
    cleanupDoneRef.current = false;
    isVisibleRef.current = true;

    try {
      const container = containerRef.current;

      // Scene setup
      const scene = new THREE.Scene();
      scene.background = new THREE.Color(0xf5f5f5);
      sceneRef.current = scene;

      // Get container dimensions
      const rect = container.getBoundingClientRect();
      const containerWidth = rect.width || 400;
      const containerHeight = rect.height || 400;

      if (containerWidth < 10 || containerHeight < 10) {
        setError("Контейнер имеет слишком малые размеры");
        return;
      }

      // Camera
      const camera = new THREE.PerspectiveCamera(
        75,
        containerWidth / containerHeight,
        0.01,
        1000
      );
      camera.position.set(0, 0, 5);
      cameraRef.current = camera;

      // Renderer
      const renderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: true,
        powerPreference: "high-performance",
        precision: "highp",
      });
      renderer.setSize(containerWidth, containerHeight);
      renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
      
      // Remove any existing canvas first
      const existingCanvas = container.querySelector('canvas');
      if (existingCanvas && existingCanvas.parentNode === container) {
        try {
          container.removeChild(existingCanvas);
        } catch (e) {
          // Ignore
        }
      }
      
      container.appendChild(renderer.domElement);
      domElementRef.current = renderer.domElement as HTMLCanvasElement;
      rendererRef.current = renderer;

      // Lighting
      const ambientLight = new THREE.AmbientLight(0xffffff, 1.2);
      scene.add(ambientLight);

      const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
      directionalLight.position.set(10, 10, 10);
      scene.add(directionalLight);

      // Add back light for better visibility
      const backLight = new THREE.DirectionalLight(0xffffff, 0.6);
      backLight.position.set(-5, -5, -5);
      scene.add(backLight);

      // Determine file type and load with appropriate loader
      const fileExtension = modelPath.toLowerCase().split('.').pop();
      
      const onModelLoaded = (geometry: THREE.BufferGeometry | THREE.Group | any) => {
        let model: THREE.Group;
        
        // Handle different return types from different loaders
        if (geometry instanceof THREE.BufferGeometry) {
          // STLLoader returns BufferGeometry
          model = new THREE.Group();
          const material = new THREE.MeshPhongMaterial({ 
            color: 0x4a90e2,
            shininess: 100,
            side: THREE.DoubleSide
          });
          const mesh = new THREE.Mesh(geometry, material);
          model.add(mesh);
        } else if (geometry.scene) {
          // GLTFLoader returns { scene, ... }
          model = geometry.scene;
        } else {
          // Fallback
          model = geometry;
        }

        // Add to scene first
        scene.add(model);
        modelRef.current = model;

        // Get model bounds BEFORE scaling
        const boxBefore = new THREE.Box3().setFromObject(model);
        const sizeBefore = boxBefore.getSize(new THREE.Vector3());

        // Scale up if model is too small
        const maxDimBefore = Math.max(sizeBefore.x, sizeBefore.y, sizeBefore.z);
        if (maxDimBefore < 0.1) {
          const scale = 1 / maxDimBefore; // Scale to 1 unit
          model.scale.multiplyScalar(scale);
        }

        // Get model bounds AFTER scaling
        const box = new THREE.Box3().setFromObject(model);
        const center = box.getCenter(new THREE.Vector3());
        const size = box.getSize(new THREE.Vector3());
        
        // Center the model at origin by adjusting all meshes' geometry positions
        // This ensures the object is centered regardless of how it was exported
        model.traverse((child: any) => {
          if (child instanceof THREE.Mesh && child.geometry) {
            // For each mesh, translate its geometry so it's centered
            child.geometry.translate(-center.x, -center.y, -center.z);
          }
        });
        
        // Reset model position to origin after geometry adjustment
        model.position.set(0, 0, 0);
        model.rotation.set(0, 0, 0);

        // Recalculate bounding box with centered geometry
        const box2 = new THREE.Box3().setFromObject(model);
        const boundingSphere = new THREE.Sphere();
        box2.getBoundingSphere(boundingSphere);
        const radius = boundingSphere.radius;

        // Calculate camera distance so model fills ~70% of viewport
        // Model diameter (2*radius) should be 70% of viewport height
        const vFOV = camera.fov * Math.PI / 180; // Convert to radians
        const viewportCoverage = 0.7; // Model should cover 70% of viewport
        
        // viewportHeight = 2 * distance * tan(FOV/2)
        // 2*radius = viewportCoverage * viewportHeight
        // 2*radius = viewportCoverage * 2 * distance * tan(FOV/2)
        // distance = radius / (viewportCoverage * tan(FOV/2))
        
        const cameraZ = radius / (viewportCoverage * Math.tan(vFOV / 2));
        
        // Store base camera Z for zoom calculations
        baseCameraZRef.current = cameraZ;
        zoomRef.current = 1;
        
        console.log("Model3DViewer: Radius:", radius);
        console.log("Model3DViewer: Target coverage: 70% of viewport");
        console.log("Model3DViewer: Calculated distance:", cameraZ);

        // Position camera to look at model center (0, 0, 0)
        // Camera should be centered on viewport, looking at origin
        camera.position.set(0, 0, cameraZ);
        camera.lookAt(0, 0, 0);
        
        // Force a render after camera update
        renderer.render(scene, camera);

        setLoading(false);
      };

      // Load model with appropriate loader
      if (fileExtension === 'stl') {
        const stlLoader = new STLLoader();
        stlLoader.load(
          modelPath,
          onModelLoaded,
          (progress) => {
            // Silent progress tracking
          },
          (error) => {
            setLoading(false);
            const errorMsg = error instanceof Error ? error.message : "Не удалось загрузить STL модель";
            setError(`Ошибка: ${errorMsg}`);
          }
        );
      } else {
        // Use GLTF loader for .gltf and .glb
        const gltfLoader = new GLTFLoader();
        gltfLoader.load(
          modelPath,
          onModelLoaded,
          (progress) => {
            // Silent progress tracking
          },
          (error) => {
            setLoading(false);
            const errorMsg = error instanceof Error ? error.message : "Не удалось загрузить модель";
            setError(`Ошибка: ${errorMsg}`);
          }
        );
      }

      // Mouse controls
      const onMouseDown = (e: Event) => {
        const mouseEvent = e as MouseEvent;
        isDraggingRef.current = true;
        previousMouseRef.current = { x: mouseEvent.clientX, y: mouseEvent.clientY };
      };

      const onWheel = (e: Event) => {
        if (!enableZoom) return;
        
        const wheelEvent = e as WheelEvent;
        wheelEvent.preventDefault();
        
        const zoomSpeed = 0.1;
        const direction = wheelEvent.deltaY > 0 ? 1 : -1;
        
        zoomRef.current = Math.max(0.2, Math.min(5, zoomRef.current + direction * zoomSpeed));
        
        if (cameraRef.current && baseCameraZRef.current > 0) {
          cameraRef.current.position.z = baseCameraZRef.current / zoomRef.current;
        }
      };

      const onMouseMove = (e: Event) => {
        const mouseEvent = e as MouseEvent;
        if (isDraggingRef.current && modelRef.current) {
          const deltaX = mouseEvent.clientX - previousMouseRef.current.x;
          const deltaY = mouseEvent.clientY - previousMouseRef.current.y;

          modelRef.current.rotation.y += deltaX * 0.01;
          modelRef.current.rotation.x += deltaY * 0.01;

          previousMouseRef.current = { x: mouseEvent.clientX, y: mouseEvent.clientY };
        }
      };

      const onMouseUp = () => {
        isDraggingRef.current = false;
      };

      const onMouseLeave = () => {
        isDraggingRef.current = false;
      };

      // Store references to all event listeners for cleanup
      const canvas = renderer.domElement as HTMLCanvasElement;
      
      // Use capture phase to ensure events are captured
      canvas.addEventListener("mousedown", onMouseDown, false);
      canvas.addEventListener("mousemove", onMouseMove, false);
      canvas.addEventListener("mouseup", onMouseUp, false);
      canvas.addEventListener("mouseleave", onMouseLeave, false);
      if (enableZoom) {
        canvas.addEventListener("wheel", onWheel, { passive: false });
      }
      
      // Keep track of listeners for cleanup
      listenersRef.current = [
        { element: canvas, type: "mousedown", listener: onMouseDown },
        { element: canvas, type: "mousemove", listener: onMouseMove },
        { element: canvas, type: "mouseup", listener: onMouseUp },
        { element: canvas, type: "mouseleave", listener: onMouseLeave },
      ];
      if (enableZoom) {
        // Note: wheel listener is stored separately since it has different options
      };

      // Animation loop
      let frameCount = 0;
      const animate = () => {
        // Stop loop only if cleanup is done
        if (cleanupDoneRef.current) {
          return;
        }

        animationIdRef.current = requestAnimationFrame(animate);

        // Check if renderer still exists and canvas is in DOM
        if (!rendererRef.current || !containerRef.current || !domElementRef.current) {
          return;
        }

        // Check if canvas is still in DOM
        if (!containerRef.current.contains(domElementRef.current)) {
          return;
        }

        // Skip update if not visible, but keep animation loop running
        if (!isVisibleRef.current) {
          return;
        }

        if (autoRotate && modelRef.current && !isDraggingRef.current) {
          modelRef.current.rotation.y += 0.004;
        }

        if (frameCount === 0) {
          console.log("Model3DViewer: First frame rendering");
          console.log("Model3DViewer: Camera pos:", camera.position.x, camera.position.y, camera.position.z);
          console.log("Model3DViewer: Model visible:", modelRef.current ? "yes" : "no");
          if (modelRef.current) {
            console.log("Model3DViewer: Model position:", modelRef.current.position);
            console.log("Model3DViewer: Model scale:", modelRef.current.scale);
            
            // Log model bounds in first frame
            const box = new THREE.Box3().setFromObject(modelRef.current);
            const size = box.getSize(new THREE.Vector3());
            console.log("Model3DViewer: Model size in first frame X:", size.x, "Y:", size.y, "Z:", size.z);
          }
        }
        frameCount++;

        renderer.render(scene, camera);
      };

      animate();

      // Handle resize
      const handleResize = () => {
        if (!containerRef.current) return;
        const newRect = containerRef.current.getBoundingClientRect();
        const newWidth = newRect.width || containerWidth;
        const newHeight = newRect.height || containerHeight;

        if (newWidth > 0 && newHeight > 0) {
          camera.aspect = newWidth / newHeight;
          camera.updateProjectionMatrix();
          renderer.setSize(newWidth, newHeight);
        }
      };

      window.addEventListener("resize", handleResize);

      // Cleanup
      return () => {
        // Guard: if cleanup already done, don't do it again
        if (cleanupDoneRef.current) {
          return;
        }

        console.log("Model3DViewer: Cleanup started");
        cleanupDoneRef.current = true;
        isVisibleRef.current = false;
        
        window.removeEventListener("resize", handleResize);
        
        // Remove wheel listener if enabled
        if (enableZoom && domElementRef.current) {
          try {
            domElementRef.current.removeEventListener("wheel", onWheel, false as any);
          } catch (e) {
            // Ignore
          }
        }
        
        // Remove all event listeners FIRST
        listenersRef.current.forEach(({ element, type, listener }) => {
          try {
            element.removeEventListener(type, listener, false);
          } catch (e) {
            // Ignore
          }
        });
        listenersRef.current = [];

        // Cancel animation frame IMMEDIATELY
        if (animationIdRef.current) {
          cancelAnimationFrame(animationIdRef.current);
          animationIdRef.current = null;
        }

        // Clear scene first
        if (sceneRef.current) {
          try {
            sceneRef.current.clear();
            sceneRef.current.traverse((child: any) => {
              if (child.geometry) child.geometry.dispose();
              if (child.material) {
                if (Array.isArray(child.material)) {
                  child.material.forEach((mat: any) => mat.dispose());
                } else {
                  child.material.dispose();
                }
              }
            });
          } catch (e) {
            // Ignore
          }
          sceneRef.current = null;
        }

        // Dispose renderer BEFORE removing from DOM
        if (rendererRef.current) {
          try {
            rendererRef.current.dispose();
            rendererRef.current = null;
          } catch (e) {
            // Ignore
          }
        }

        if (cameraRef.current) {
          cameraRef.current = null;
        }

        if (modelRef.current) {
          modelRef.current = null;
        }

        // Remove canvas from DOM LAST
        if (domElementRef.current) {
          try {
            const canvas = domElementRef.current;
            // Use multiple checks to avoid race conditions
            if (canvas && canvas.parentNode) {
              try {
                canvas.parentNode.removeChild(canvas);
              } catch (removeError) {
                // Element may have already been removed by React
                // This is safe to ignore
              }
            }
          } catch (e) {
            // Silently ignore
          } finally {
            domElementRef.current = null;
          }
        }
        
        console.log("Model3DViewer: Cleanup complete");
      };
    } catch (err) {
      console.error("Model3DViewer: Initialization error:", err);
      setError(`Ошибка инициализации: ${err instanceof Error ? err.message : String(err)}`);
      setLoading(false);
    }
  }, [modelPath, autoRotate]);

  return (
    <div
      ref={containerRef}
      style={{
        width,
        height,
        borderRadius: "12px",
        overflow: "hidden",
        position: "relative",
        backgroundColor: "#f5f5f5",
        pointerEvents: "auto",
      }}
    >
      {loading && !error && (
        <div
          style={{
            position: "absolute",
            top: "50%",
            left: "50%",
            transform: "translate(-50%, -50%)",
            textAlign: "center",
            zIndex: 10,
          }}
        >
          <div style={{ color: "#999", fontSize: "14px" }}>
            Загрузка 3D модели...
          </div>
        </div>
      )}
      {error && (
        <div
          style={{
            position: "absolute",
            top: "50%",
            left: "50%",
            transform: "translate(-50%, -50%)",
            textAlign: "center",
            color: "#dc2626",
            fontSize: "12px",
            padding: "16px",
            zIndex: 10,
          }}
        >
          <div>{error}</div>
        </div>
      )}
    </div>
  );
}
