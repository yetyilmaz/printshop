# 3D Model Implementation for Portfolio

## Overview
Added support for .glb 3D models in the portfolio system with interactive Three.js viewer.

## Features Implemented

### 1. Database Schema
- Added `glb_model` field to `portfolio_items` table (nullable string)
- Added `featured` boolean field to mark featured portfolio items
- Migration: `2026_02_01_131828_add_glb_model_to_portfolio_items_table.php`

### 2. 3D Model Viewer (resources/js/model-viewer.js)
A complete Three.js-based viewer with:
- **Auto-rotation**: Models rotate automatically when not being interacted with
- **Mouse/Touch interaction**: Drag to rotate the model on X and Y axes
- **Zoom control**: Mousewheel to zoom in/out
- **Responsive**: Handles window resize events
- **Loading**: Supports .glb files via GLTFLoader
- **Auto-centering**: Models are automatically centered and scaled to fit
- **Cleanup**: Proper disposal of Three.js resources

### 3. Public Portfolio Page (resources/views/portfolio/index.blade.php)
- **Card Preview**: 3D models auto-rotate in portfolio cards (interaction disabled)
- **Modal View**: Clicking a card opens a modal with:
  - Interactive 3D viewer on the left (drag to rotate, scroll to zoom)
  - Project details on the right (title, description, material, category)
  - Image gallery thumbnails if no 3D model is available
  - Featured badge for featured items
  - Interaction hints for 3D controls
- **Fallback**: If no 3D model, displays images with navigation

### 4. Admin Panel Integration

#### Create Form (resources/views/admin/portfolio/create.blade.php)
- File upload input for .glb files
- Featured checkbox to mark items as featured
- Accepts files up to 10MB

#### Edit Form (resources/views/admin/portfolio/edit.blade.php)
- Shows current 3D model filename if exists
- Option to delete existing model
- Upload new model
- Featured checkbox state persistence

#### Controller (app/Http/Controllers/Admin/PortfolioController.php)
- `store()`: Handles glb_model upload to `storage/app/public/models/`
- `update()`: Handles model replacement and deletion
- Validation: `.glb` file type, max 10MB
- Automatic file cleanup when replacing models

### 5. Model Updates
- **PortfolioItem**: Added `glb_model` and `featured` to fillable
- **PublicController**: Includes glb_model URLs in project data with `Storage::url()`

## File Structure
```
resources/
├── js/
│   ├── app.js              # ModelViewer initialization
│   └── model-viewer.js     # Three.js viewer class
└── views/
    ├── portfolio/
    │   └── index.blade.php # Public portfolio with 3D viewer
    └── admin/
        └── portfolio/
            ├── create.blade.php # Create form with glb upload
            └── edit.blade.php   # Edit form with glb upload

storage/app/public/
└── models/                  # 3D model storage directory

public/storage/
└── models/                  # Symlink (created with php artisan storage:link)
```

## Usage Instructions

### For Admins
1. Navigate to Admin > Portfolio > Add Item
2. Fill in the form fields
3. Upload a .glb file in the "3D Model" field
4. Optionally check "Mark as Featured"
5. Add images if needed
6. Save

### For Users
1. Visit the portfolio page
2. See 3D models auto-rotating in cards
3. Click any card to open modal
4. In modal:
   - Drag to rotate the model
   - Scroll to zoom
   - View project details on the right

## Technical Details

### Three.js Configuration
- **Camera**: PerspectiveCamera with FOV 50
- **Renderer**: WebGLRenderer with antialiasing
- **Lighting**: Ambient light (0.8) + 2 directional lights (0.6, 0.4)
- **Controls**: Custom implementation (no OrbitControls dependency)

### Model Loading
- Uses GLTFLoader from Three.js
- Automatically centers models using Box3
- Scales to fit within 2-unit cube
- Returns Promise for async handling

### Interaction
- **Mouse**: Click and drag to rotate (deltaX/Y → rotation.y/x)
- **Touch**: Single touch for rotation
- **Zoom**: Mousewheel adjusts camera distance (min: 2, max: 10)
- **Auto-rotate**: 0.005 rad/frame when not dragging

### Error Handling
- Failed model loads show error message
- Falls back to images if no 3D model
- Console logging for debugging

## Dependencies
- three: ^0.170.0
- @types/three: ^0.170.0

Installed via: `npm install three @types/three`

## Build Commands
```bash
# Development
npm run dev

# Production
npm run build
```

## Storage Setup
If not already done:
```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

## File Upload Limits
- **3D Models (.glb)**: Max 10MB
- **Images**: Max 2MB per image

## Browser Compatibility
Requires WebGL support (all modern browsers). The viewer will not work on very old browsers without WebGL.

## Performance Notes
- Three.js bundle adds ~600KB to the JavaScript bundle
- Consider code splitting for larger applications
- Models should be optimized (compressed, low-poly) for best performance
- Use tools like glTF-Transform or Blender to optimize .glb files before upload

## Future Enhancements
- Model optimization/compression on upload
- Multiple model formats support
- Animation playback if models contain animations
- Environment maps for better lighting
- Model statistics (poly count, file size)
- Thumbnail generation from 3D models
