import * as THREE from 'three';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';

export class ModelViewer {
    constructor(container, options = {}) {
        this.container = container;
        this.options = {
            autoRotate: options.autoRotate !== undefined ? options.autoRotate : true,
            enableZoom: options.enableZoom !== undefined ? options.enableZoom : true,
            enableInteraction: options.enableInteraction !== undefined ? options.enableInteraction : true,
            backgroundColor: options.backgroundColor || 0xf5f5f5,
            ...options
        };
        
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.model = null;
        this.animationId = null;
        this.isDragging = false;
        this.previousMouse = { x: 0, y: 0 };
        this.rotation = { x: 0, y: 0 };
        this.autoRotationSpeed = 0.005;
        
        this.init();
    }
    
    init() {
        const rect = this.container.getBoundingClientRect();
        const width = rect.width || 400;
        const height = rect.height || 400;
        
        // Scene
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(this.options.backgroundColor);
        
        // Camera
        this.camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 1000);
        this.camera.position.set(0, 0, 5);
        
        // Renderer
        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        this.renderer.setSize(width, height);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.container.appendChild(this.renderer.domElement);
        
        // Lights
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
        this.scene.add(ambientLight);
        
        const directionalLight1 = new THREE.DirectionalLight(0xffffff, 0.6);
        directionalLight1.position.set(5, 5, 5);
        this.scene.add(directionalLight1);
        
        const directionalLight2 = new THREE.DirectionalLight(0xffffff, 0.4);
        directionalLight2.position.set(-5, -5, -5);
        this.scene.add(directionalLight2);
        
        // Event listeners
        if (this.options.enableInteraction) {
            this.setupInteraction();
        }
        
        // Handle window resize
        window.addEventListener('resize', () => this.onWindowResize());
    }
    
    loadModel(url) {
        const loader = new GLTFLoader();
        
        return new Promise((resolve, reject) => {
            loader.load(
                url,
                (gltf) => {
                    // Remove old model if exists
                    if (this.model) {
                        this.scene.remove(this.model);
                    }
                    
                    this.model = gltf.scene;
                    
                    // Center and scale the model
                    const box = new THREE.Box3().setFromObject(this.model);
                    const center = box.getCenter(new THREE.Vector3());
                    const size = box.getSize(new THREE.Vector3());
                    
                    const maxDim = Math.max(size.x, size.y, size.z);
                    const scale = 2 / maxDim;
                    this.model.scale.setScalar(scale);
                    
                    this.model.position.x = -center.x * scale;
                    this.model.position.y = -center.y * scale;
                    this.model.position.z = -center.z * scale;
                    
                    this.scene.add(this.model);
                    this.animate();
                    
                    resolve(this.model);
                },
                (progress) => {
                    // Progress callback
                },
                (error) => {
                    console.error('Error loading model:', error);
                    reject(error);
                }
            );
        });
    }
    
    setupInteraction() {
        const canvas = this.renderer.domElement;
        
        canvas.addEventListener('mousedown', (e) => {
            this.isDragging = true;
            this.previousMouse = { x: e.clientX, y: e.clientY };
        });
        
        canvas.addEventListener('mousemove', (e) => {
            if (!this.isDragging) return;
            
            const deltaX = e.clientX - this.previousMouse.x;
            const deltaY = e.clientY - this.previousMouse.y;
            
            this.rotation.y += deltaX * 0.01;
            this.rotation.x += deltaY * 0.01;
            
            this.previousMouse = { x: e.clientX, y: e.clientY };
        });
        
        canvas.addEventListener('mouseup', () => {
            this.isDragging = false;
        });
        
        canvas.addEventListener('mouseleave', () => {
            this.isDragging = false;
        });
        
        // Touch events for mobile
        canvas.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                this.isDragging = true;
                this.previousMouse = { 
                    x: e.touches[0].clientX, 
                    y: e.touches[0].clientY 
                };
            }
        });
        
        canvas.addEventListener('touchmove', (e) => {
            if (!this.isDragging || e.touches.length !== 1) return;
            
            const deltaX = e.touches[0].clientX - this.previousMouse.x;
            const deltaY = e.touches[0].clientY - this.previousMouse.y;
            
            this.rotation.y += deltaX * 0.01;
            this.rotation.x += deltaY * 0.01;
            
            this.previousMouse = { 
                x: e.touches[0].clientX, 
                y: e.touches[0].clientY 
            };
        });
        
        canvas.addEventListener('touchend', () => {
            this.isDragging = false;
        });
        
        // Zoom with mouse wheel
        if (this.options.enableZoom) {
            canvas.addEventListener('wheel', (e) => {
                e.preventDefault();
                const delta = e.deltaY * 0.001;
                this.camera.position.z = Math.max(2, Math.min(10, this.camera.position.z + delta));
            });
        }
    }
    
    animate() {
        this.animationId = requestAnimationFrame(() => this.animate());
        
        if (this.model) {
            if (this.options.autoRotate && !this.isDragging) {
                this.model.rotation.y += this.autoRotationSpeed;
            } else if (this.isDragging || this.rotation.x !== 0 || this.rotation.y !== 0) {
                this.model.rotation.y = this.rotation.y;
                this.model.rotation.x = this.rotation.x;
            }
        }
        
        this.renderer.render(this.scene, this.camera);
    }
    
    onWindowResize() {
        const rect = this.container.getBoundingClientRect();
        const width = rect.width || 400;
        const height = rect.height || 400;
        
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }
    
    dispose() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
        
        if (this.renderer) {
            this.renderer.dispose();
            if (this.renderer.domElement.parentNode) {
                this.renderer.domElement.parentNode.removeChild(this.renderer.domElement);
            }
        }
        
        if (this.model) {
            this.scene.remove(this.model);
        }
    }
}
