# Laracasts: Practical UI Components With Livewire and Alpine

## Episode 01 — Getting Started

- **AI can produce working code, but you still need framework knowledge to evaluate its performance, edge cases, current APIs, and code quality.**
  ```php
  use App\Models\Post;

  // Review generated queries before shipping them.
  $posts = Post::query()
      ->with('author')
      ->latest()
      ->paginate(15);
  ```

- **Use Livewire for server-backed state and actions, then use Alpine for small client-side interactions around that state.**
  ```blade
  <div x-data="{ editing: false }">
      <button x-on:click="editing = true">Edit</button>

      <input x-show="editing" wire:model="name">
      <button x-show="editing" wire:click="save">Save</button>
  </div>
  ```

- **Start with focused components that solve common UI problems, such as inline editing, toast notifications, and infinite scroll.**
  ```bash
  php artisan make:livewire inline-edit
  php artisan make:livewire toast-notifications
  php artisan make:livewire infinite-scroll
  ```

- **More advanced components combine state, events, keyboard input, filtering, and drag-and-drop in notification centers, tag inputs, inline search, and Kanban boards.**
  ```blade
  <ul wire:sort="moveCard">
      @foreach ($cards as $card)
          <li wire:key="card-{{ $card->id }}" wire:sort:item="{{ $card->id }}">
              {{ $card->title }}
          </li>
      @endforeach
  </ul>
  ```

- **The course was recorded with Livewire 4.1 and uses single-file components to keep a component's PHP and Blade together.**
  ```php
  <?php

  use Livewire\Component;

  new class extends Component
  {
      public string $query = '';
  };
  ?>

  <input wire:model.live="query" placeholder="Search posts">
  <p>Searching for: {{ $query }}</p>
  ```

- **Learn basic Livewire first so the course can focus on component design rather than framework fundamentals.**
  ```bash
  laravel new ui-components
  cd ui-components
  composer require livewire/livewire
  ```

- **You can choose episodes by the problem you need to solve and reuse the matching component code in another application.**
  ```text
  inline editing     -> inline-edit
  toast alerts       -> toast-notifications
  infinite scroll    -> infinite-scroll
  live updates       -> notification-center
  tag suggestions    -> tag-input
  highlighted search -> inline-search
  drag and drop      -> kanban
  ```

> **Takeaway:** Understand how Livewire and Alpine divide responsibilities so you can build, review, and optimize UI components instead of blindly accepting generated code.
