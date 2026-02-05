# Project Structure Reference

## Tooling & Dependencies
- [`composer.json`](composer.json#L1-L88) pins PHP 8.2 / Laravel 12 and Breeze/Pint/PHPUnit tooling, plus `setup`, `dev`, and `test` scripts that install dependencies, generate keys, run migrations, start queues, and build the Vite assets.
- [`package.json`](package.json#L1-L26) holds the Vite/Tailwind/Three.js stack, the dev dependencies that power the JS build, and the `npm run dev`/`npm run build` entry points that tie into `vite.config.js`.

## Routing & Bootstrapping
- [`routes/web.php`](routes/web.php#L1-L36) wires the public landing pages, portfolio CRUD, materials guide, order flow, dashboard, and the admin resource groups (portfolio, orders, materials, calculator) behind the `auth`/`admin` middleware.
- [`routes/auth.php`](routes/auth.php#L1-L59) is the Breeze authentication surface for register/login/reset flows plus email verification and logout.
- [`routes/console.php`](routes/console.php#L1-L8) keeps the sample `inspire` console command available under `php artisan inspire`.
- [`app/Providers/AppServiceProvider.php`](app/Providers/AppServiceProvider.php#L1-L28) enables strict models when not in production and hooks `OrderObserver` into the `Order` lifecycle.

## HTTP Controllers
### Public & Customer Interfaces
- [`app/Http/Controllers/PublicController.php`](app/Http/Controllers/PublicController.php#L1-L57) orchestrates the homepage showcase, full portfolio list (with JS-friendly payloads), single portfolio detail, and a cached materials guide by delegating to services/repositories.
- [`app/Http/Controllers/OrderController.php`](app/Http/Controllers/OrderController.php#L1-L47) assembles material/quality data, runs the `StoreOrderRequest`, and delegates to `OrderService` for file handling/price calculation before redirecting to the success view.
- [`app/Http/Controllers/DashboardController.php`](app/Http/Controllers/DashboardController.php#L1-L32) surfaces admin metrics (today's visits and grouped order buckets) or a logged-in user’s own order history.

### Admin Console
- [`app/Http/Controllers/Admin/PortfolioController.php`](app/Http/Controllers/Admin/PortfolioController.php#L1-L175) manages portfolio CRUD, including GLB uploads (with validation + deletion), image uploads with sort order, featured flags, and storage cleanup.
- [`app/Http/Controllers/Admin/OrderController.php`](app/Http/Controllers/Admin/OrderController.php#L1-L117) lists orders, shows details, updates statuses/notes, delivers downloads, and deletes orders via the `OrderRepository` abstractions.
- [`app/Http/Controllers/Admin/CalculatorController.php`](app/Http/Controllers/Admin/CalculatorController.php#L1-L71) surfaces quality and infill presets to admins, supports bulk updates, creates new presets, and removes obsolete entries.

## Business Logic & Persistence Helpers
- [`app/Services/OrderService.php`](app/Services/OrderService.php#L1-L123) contains all order-creation logic: cost calculations using material/quality/infill multipliers, storing STL + blueprint files, persisting `OrderItem`s, and firing the Telegram notification while keeping the transaction atomic.
- [`app/Services/MaterialSpecsService.php`](app/Services/MaterialSpecsService.php#L1-L63) provides human-readable toughness/strength/heat-resistance reference blocks for PLA, ABS, PETG, TPU, Nylon, and a default fallback.
- [`app/Services/PortfolioService.php`](app/Services/PortfolioService.php#L1-L30) translates portfolio records into frontend payloads with image/GLB URLs, featured flags, and category metadata for the public gallery.
- [`app/Repositories/OrderRepository.php`](app/Repositories/OrderRepository.php#L1-L69) fetches the order lists, loads relations for detail views, auto-promotes statuses when a price is set, and removes stored files when orders are deleted.
- [`app/Repositories/PortfolioRepository.php`](app/Repositories/PortfolioRepository.php#L1-L64) caches the category counts, podium/featured selections, and fully eager-loaded portfolio item collections.
- [`app/Observers/OrderObserver.php`](app/Observers/OrderObserver.php#L1-L21) dispatches `SyncOrderToGoogleSheet` every time an `Order` is saved (create or update).
- [`app/Jobs/SyncOrderToGoogleSheet.php`](app/Jobs/SyncOrderToGoogleSheet.php#L1-L69) posts summaries of each order to the configured Google Sheets webhook and logs success/failure for auditing.

## Data Model Snapshot
- [`app/Models/Order.php`](app/Models/Order.php#L1-L31) casts `contact_info` as an array and exposes relationships to `User`, `OrderItem`, and `File` so services/controllers can build payloads or purge uploads.

## Frontend & 3D Portfolio Experience
- [`resources/js/model-viewer.js`](resources/js/model-viewer.js#L1-L244) powers the Three.js modal/card viewer with auto-rotation, drag/scroll controls, zoom clamping, model centering, and cleanup after unmounting; it is wired into `resources/js/app.js` and the portfolio blades.
- [`3D_MODEL_IMPLEMENTATION.md`](3D_MODEL_IMPLEMENTATION.md#L1-L164) documents how the GLB viewer is used within the public cards/modal, how admins upload/delete GLBs and images, the storage path expectations, and the new portfolio dataset fields (GLB link + featured flag).
- The Blade templates under `resources/views/portfolio` and `resources/views/admin/portfolio` render the cards, modal, and upload forms described in the doc, falling back to images when a GLB is absent.

## Supporting Infrastructure
- `database/migrations` defines tables for users, orders, materials, colors, files, portfolio categories/items/images, calculator settings, etc., while `database/factories` and `database/seeders` support generating fixtures for tests.
- `config/*` contains settings for queues, mail, logging, caching, and `config/services.php` holds the Google Sheets webhook URL that backs the job.
- Uploaded files land in `storage/app/public/models`, `public/storage`, and `storage/app/blueprints`; `php artisan storage:link` keeps the public symlink in sync.
- `public/build`, `resources/css`, and `resources/js` hold the compiled assets that `npm run build` produces for production.
- `tests/Feature` + `tests/Unit` (driven by `phpunit.xml`) keep coverage across controllers/services.
- `artisan`, `bootstrap`, `vendor`, and `node_modules` provide the runtime entry point, compiled container, Composer autoloading, and JS dependencies for the Laravel/Vite stack.
