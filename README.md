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

## Episode 02 — Introducing Inline Editing

- **Keep editable values in Livewire properties so a read-only-looking page can persist changes on the server.**
  ```php
  public Meeting $meeting;
  public string $title = '';
  public string $notes = '';

  public function mount(): void
  {
      $this->meeting = Meeting::query()->firstOrFail();
      $this->title = $this->meeting->title;
      $this->notes = $this->meeting->notes;
  }
  ```

- **Replace static headings and paragraphs with borderless, bound textareas so users can edit without a separate edit mode.**
  ```blade
  <textarea
      wire:model.live.debounce.300ms="title"
      rows="1"
      placeholder="Click here to add a title"
      class="w-full resize-none border-0 focus:outline-none focus:ring-0"
  ></textarea>
  ```

- **Autosave only the fields that changed, and dispatch an event after the model update so the interface can show feedback.**
  ```php
  public function updated(string $property): void
  {
      if (! in_array($property, ['title', 'notes'], true)) {
          return;
      }

      $this->meeting->update([$property => $this->{$property}]);
      $this->dispatch('notes-saved');
  }
  ```

- **Use Alpine to resize textareas from their `scrollHeight` on initialization and input instead of relying on browser-limited `field-sizing: content`.**
  ```blade
  <textarea
      x-data="{ resize() { $el.style.height = 'auto'; $el.style.height = `${$el.scrollHeight}px`; } }"
      x-init="resize()"
      x-on:input="resize()"
      rows="1"
  ></textarea>
  ```

- **Add `wire:ignore.self` when Alpine owns the textarea height so Livewire re-renders do not reset the client-side dimensions.**
  ```blade
  <textarea
      wire:model.live.debounce.300ms="notes"
      wire:ignore.self
      x-data="{ resize() { $el.style.height = 'auto'; $el.style.height = `${$el.scrollHeight}px`; } }"
      x-init="resize()"
      x-on:input="resize()"
  ></textarea>
  ```

- **Use a delayed, targeted loading indicator to show saving only while `title` or `notes` is being persisted.**
  ```blade
  <div wire:loading.delay wire:target="title,notes">
      Saving...
  </div>
  ```

- **Clear the previous Alpine timeout before scheduling the saved indicator to disappear, otherwise continuous typing creates competing timers.**
  ```blade
  <div
      x-data="{ saved: false, timeout: null }"
      x-on:notes-saved.window="
          clearTimeout(timeout);
          saved = true;
          timeout = setTimeout(() => saved = false, 3000);
      "
      x-show="saved"
  >
      Saved
  </div>
  ```

> **Takeaway:** Seamless inline editing combines Livewire autosave with Alpine-managed textarea sizing and short-lived status feedback.

## Episode 03 — Toast Notifications

- **Dispatch a named Livewire event after saving a setting so the interface can give immediate feedback.**
  ```php
  $this->dispatch(
      'toast',
      message: 'Display name updated',
      type: 'success',
  );
  ```

- **Let Alpine own short-lived toast state and listen on `window` so one component can react to events from any Livewire action.**
  ```blade
  <div
      x-data="{ show: false, message: '', type: '' }"
      x-on:toast.window="
          message = $event.detail.message;
          type = $event.detail.type;
          show = true;
      "
      x-show="show"
      x-text="message"
  ></div>
  ```

- **Extract the notification markup into a Blade component so pages can reuse the same toast UI.**
  ```blade
  <x-toast />
  ```

- **Map semantic types to complete class sets so one component can render success, warning, info, and error states.**
  ```blade
  <div
      :class="{
          'border-green-500 bg-green-50 text-green-800': type === 'success',
          'border-yellow-500 bg-yellow-50 text-yellow-800': type === 'warning',
          'border-blue-500 bg-blue-50 text-blue-800': type === 'info',
          'border-red-500 bg-red-50 text-red-800': type === 'error',
      }"
  ></div>
  ```

- **Store notifications as objects with unique IDs and render them with `x-for` so multiple saves can remain visible.**
  ```blade
  <div
      x-data="{ toasts: [] }"
      x-on:toast.window="
          const id = Date.now();

          toasts.push({
              id,
              message: $event.detail.message,
              type: $event.detail.type,
              show: true,
          });
      "
  >
      <template x-for="toast in toasts" :key="toast.id">
          <div x-text="toast.message"></div>
      </template>
  </div>
  ```

- **Use a reversed vertical flex column with a gap to keep the newest toast on top while older notifications move down.**
  ```blade
  <div class="fixed top-5 right-5 z-50 flex flex-col-reverse gap-3">
  ```

- **Bind separate entry and exit animation utilities to the toast's visibility flag so the notification can animate in and out.**
  ```blade
  <div :class="toast.show ? 'animate-fade-in-down' : 'animate-fade-out-up'">
  ```

- **Register custom keyframes as Tailwind animation utilities when built-in transitions do not provide enough control over entry and exit states.**
  ```css
  @theme {
      --animate-fade-in-down: fade-in-down 0.5s ease-in;
      --animate-fade-out-up: fade-out-up 0.5s ease-out;
  }

  @keyframes fade-in-down {
      from { opacity: 0; transform: translateY(-1rem); }
      to { opacity: 1; transform: translateY(0); }
  }

  @keyframes fade-out-up {
      from { opacity: 1; transform: translateY(0); }
      to { opacity: 0; transform: translateY(-1rem); }
  }
  ```

- **Hide a toast after a delay and remove it only after the exit animation finishes, otherwise the DOM removal cuts the animation short.**
  ```js
  setTimeout(() => {
      const toast = toasts.find((toast) => toast.id === id);

      if (! toast) {
          return;
      }

      toast.show = false;

      setTimeout(() => {
          const index = toasts.findIndex((toast) => toast.id === id);

          if (index !== -1) {
              toasts.splice(index, 1);
          }
      }, 500);
  }, 6000);
  ```

> **Takeaway:** A reusable toast component turns Livewire events into typed, stacked, animated feedback without coupling notification presentation to each individual setting.
