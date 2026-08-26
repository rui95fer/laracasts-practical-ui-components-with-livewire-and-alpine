# Project Context

This repository is a Laracasts course demo for practical Livewire and Alpine UI components. Keep this file focused on stable project facts that help an agent orient itself quickly. Verify details in the source before making changes.

## Stack

- PHP 8.4 and Laravel 13.25.
- Livewire 4.4 with Flux 2.16.
- Tailwind CSS 4.3 and Vite 8.
- Pest 5.1 for tests.
- SQLite is the local database.

## Structure

- `resources/views/pages/episodes/` contains the full-page Livewire single-file components. Episode page filenames use the Livewire 4 `⚡` prefix.
- `routes/web.php` registers episode pages with `Route::livewire()`.
- `config/episodes.php` drives the episode catalog rendered at the home route.
- `resources/views/layouts/editor.blade.php` provides the shared page layout, Vite assets, Livewire, Flux, and the reusable toast.
- `resources/css/app.css` contains the Tailwind v4 CSS-first setup, shared colors, animations, and dark-mode variant.
- `tests/Feature/` contains Pest feature and Livewire component tests. There is no existing browser test suite.

## Conventions

- Keep episode behavior in the component's single Blade file unless the project gains a clear reason to extract it.
- Use inline Alpine for transient client-side state and Livewire for server-backed state and actions.
- Match existing responsive layouts, zinc/amber styling, and `dark:` variants.
- Use `wire:key` for rendered loops and named Livewire events for client-side feedback.
- Use model factories in tests and `RefreshDatabase` through `tests/Pest.php`.
- Do not add dependencies without approval.

## Episode 06

- The infinite-scroll demo is a standalone page at `episodes.infinite-scroll`.
- Its feed is backed by `App\Models\Post` and uses `category()` and `search()` scopes.
- Livewire 4 islands handle page-sized append updates; Alpine handles scroll position, end-of-feed state, and the scroll-to-top control.
- The feed must use a stable named island, keyed posts, `wire:island.append`, and `wire:intersect` together.

## Verification

- Run focused tests with `php artisan test --compact tests/Feature/<TestFile>.php`.
- Format changed PHP with `vendor/bin/pint --dirty --format agent`.
- Inspect routes with `php artisan route:list`.
- Run `git diff --check` before finishing.
- The app is served by Laravel Herd; do not start a separate application server.
