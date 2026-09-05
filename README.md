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

## Episode 04 — Build a Multi-Step Wizard

- **Keep the wizard modal within `85vh` and make its content area scrollable so longer steps do not stretch the viewport.**
  ```blade
  <div class="max-h-[85vh] overflow-y-auto">
      <!-- Current wizard step -->
  </div>
  ```

- **Store every form field and the current step in Livewire so the server controls which part of the wizard is visible.**
  ```php
  public string $name = '';
  public string $category = '';
  public string $description = '';
  public string $price = '';
  public string $url = '';
  public int $currentStep = 1;
  ```

- **Render only the active step and show a progress label so users know where they are in the wizard.**
  ```blade
  <p>Step {{ $currentStep }} of 3</p>

  @if ($currentStep === 1)
      <!-- Name, category, and description -->
  @elseif ($currentStep === 2)
      <!-- Price and URL -->
  @else
      <!-- Preview -->
  @endif
  ```

- **Keep navigation explicit and mark forward or backward movement before changing `currentStep`, while `goToStep()` supports edit links from the preview.**
  ```php
  public function nextStep(): void
  {
      $this->validateStep();

      if ($this->currentStep < 3) {
          $this->transition('forward');
          $this->currentStep++;
      }
  }

  public function previousStep(): void
  {
      if ($this->currentStep > 1) {
          $this->transition('backward');
          $this->currentStep--;
      }
  }

  public function goToStep(int $step): void
  {
      $this->transition('backward');
      $this->currentStep = $step;
  }
  ```

- **Validate only the fields visible in the current step before advancing, because later-step values should not block earlier progress.**
  ```php
  private function validateStep(): void
  {
      $rules = match ($this->currentStep) {
          1 => [
              'name' => ['required', 'string', 'max:255'],
              'category' => ['required', 'string', 'max:255'],
              'description' => ['required', 'string', 'min:20'],
          ],
          2 => [
              'price' => ['required', 'numeric', 'gt:0'],
              'url' => ['required', 'url'],
          ],
          default => [],
      };

      $this->validate($rules);
  }
  ```

- **Render each validation error beside its input so a blocked Next action tells the user what to fix.**
  ```blade
  <input wire:model="name">

  @error('name')
      <p class="text-sm text-red-600">{{ $message }}</p>
  @enderror
  ```

- **Apply `#[Session]` to every wizard property so a refresh restores both the entered values and the current step.**
  ```php
  use Livewire\Attributes\Session;

  #[Session]
  public string $name = '';

  #[Session]
  public int $currentStep = 1;
  ```

- **Reset the wizard after the final submission and dispatch the existing toast event to confirm the result.**
  ```php
  public function submit(): void
  {
      $this->reset();
      $this->dispatch('toast', message: 'Product created', type: 'success');
  }
  ```

- **Give every step the same named `wire:transition` so Livewire treats the replacing step content as one view transition.**
  ```blade
  <div wire:transition="form">
      <!-- Step 1 -->
  </div>

  <div wire:transition="form">
      <!-- Step 2 -->
  </div>
  ```

- **Use separate view-transition keyframes for forward and backward navigation so the old form exits and the new form enters from the correct side.**
  ```css
  @keyframes slide-out-left {
      to { transform: translateX(-100%); }
  }

  @keyframes slide-in-right {
      from { transform: translateX(100%); }
  }

  @keyframes slide-out-right {
      to { transform: translateX(100%); }
  }

  @keyframes slide-in-left {
      from { transform: translateX(-100%); }
  }

  html:active-view-transition-type(forward) {
      &::view-transition-old(form) {
          animation: 300ms ease-in-out both slide-out-left;
      }

      &::view-transition-new(form) {
          animation: 300ms ease-in-out both slide-in-right;
      }
  }

  html:active-view-transition-type(backward) {
      &::view-transition-old(form) {
          animation: 300ms ease-in-out both slide-out-right;
      }

      &::view-transition-new(form) {
          animation: 300ms ease-in-out both slide-in-left;
      }
  }
  ```

- **Clip the named transition group so sliding forms stay inside the modal instead of overflowing its borders.**
  ```css
  ::view-transition-group(form) {
      overflow: clip;
  }
  ```

> **Takeaway:** A Livewire wizard can combine step-specific validation, session-persisted state, preview editing, and directional view transitions without adding a client-side form framework.

## Episode 05 — Tag Input

- **Load up to five matching suggestions when the search changes, and exclude selected IDs so users cannot select the same tag twice.**
  ```php
  public function updatedSearch(): void
  {
      if ($this->search === '') {
          $this->suggestions = [];

          return;
      }

      $selectedTagIds = collect($this->selectedTags)->pluck('id')->all();

      $this->suggestions = Tag::query()
          ->where('name', 'like', "%{$this->search}%")
          ->whereNotIn('id', $selectedTagIds)
          ->limit(5)
          ->get(['id', 'name'])
          ->toArray();
  }
  ```

- **Keep selected tags as ID/name records, guard `addTag()` against duplicates, and clear the search after selection.**
  ```php
  public function addTag(int $tagId): void
  {
      $tag = Tag::query()->findOrFail($tagId);

      if (collect($this->selectedTags)->contains('id', $tag->id)) {
          return;
      }

      $this->selectedTags[] = $tag->only(['id', 'name']);
      $this->search = '';
      $this->suggestions = [];
  }
  ```

- **Render selected tags with stable keys and delegate removal by ID so Livewire can reconcile the changing list.**
  ```blade
  @foreach ($selectedTags as $tag)
      <span wire:key="selected-tag-{{ $tag['id'] }}">
          {{ $tag['name'] }}
          <button type="button" wire:click="removeTag({{ $tag['id'] }})">
              Remove
          </button>
      </span>
  @endforeach
  ```

- **Filter a removed tag ID from `selectedTags` and reindex the array so the remaining pills render predictably.**
  ```php
  public function removeTag(int $tagId): void
  {
      $this->selectedTags = collect($this->selectedTags)
          ->reject(fn (array $tag): bool => $tag['id'] === $tagId)
          ->values()
          ->all();
  }
  ```

- **Let Alpine own dropdown visibility for client-side interactions while Livewire owns the search and suggestions.**
  ```blade
  <div x-data="{ open: false }" x-on:click.outside="open = false">
      <input
          wire:model.live.debounce.300ms="search"
          x-on:focus="open = true"
          x-on:keydown.escape="open = false"
      >

      <div x-show="open && $wire.search.length > 0">
          <!-- Suggestions and the create option -->
      </div>
  </div>
  ```

- **Track the highlighted option with a zero-based Alpine index and prevent arrow keys from moving the input caret.**
  ```blade
  <input
      x-on:keydown.arrow-down.prevent="highlightedIndex = Math.min(highlightedIndex + 1, $wire.suggestions.length - 1)"
      x-on:keydown.arrow-up.prevent="highlightedIndex = Math.max(highlightedIndex - 1, -1)"
  >
  ```

- **Bind each suggestion's highlighted class to its loop index and give every row a stable `wire:key`.**
  ```blade
  @foreach ($suggestions as $index => $suggestion)
      <button
          type="button"
          wire:key="suggestion-{{ $suggestion['id'] }}"
          wire:click="addTag({{ $suggestion['id'] }})"
          :class="{ 'bg-blue-100': highlightedIndex === {{ $index }} }"
      >
          {{ $suggestion['name'] }}
      </button>
  @endforeach
  ```

- **Handle Enter on the client, call `$wire.addTag()` for the highlighted suggestion, and reset Alpine state afterward.**
  ```blade
  <input x-on:keydown.enter.prevent="
      if (highlightedIndex >= 0 && highlightedIndex < $wire.suggestions.length) {
          $wire.addTag($wire.suggestions[highlightedIndex].id);
          open = false;
          highlightedIndex = -1;
      }
  ">
  ```

- **Create tags idempotently by trimming the input, reusing case-insensitive matches, and dispatching the existing toast event after selection.**
  ```php
  public function createTag(): void
  {
      $name = trim($this->search);

      if ($name === '') {
          return;
      }

      $tag = Tag::query()
          ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
          ->first();

      if ($tag === null) {
          $tag = Tag::create(['name' => $name]);
      }

      if (! collect($this->selectedTags)->contains('id', $tag->id)) {
          $this->selectedTags[] = $tag->only(['id', 'name']);
      }

      $this->search = '';
      $this->suggestions = [];
      $this->dispatch('toast', message: 'Tag added', type: 'success');
  }
  ```

- **Show the create option for non-empty searches without a case-insensitive exact match, even when there are no suggestions.**
  ```blade
  <div x-show="open && $wire.search.length > 0">
      @if ($canCreateTag)
          <button type="button" wire:click="createTag">
              Create "{{ $search }}"
          </button>
      @endif
  </div>
  ```

- **Treat the create row as one extra keyboard option so arrow navigation and Enter can select either a suggestion or a new tag.**
  ```js
  function moveDown() {
      const maxIndex = $wire.canCreateTag
          ? $wire.suggestions.length
          : $wire.suggestions.length - 1;

      if (highlightedIndex < maxIndex) {
          highlightedIndex++;
      }
  }

  function selectHighlighted() {
      if (highlightedIndex < 0) {
          return;
      }

      if (highlightedIndex < $wire.suggestions.length) {
          $wire.addTag($wire.suggestions[highlightedIndex].id);
      } else {
          $wire.createTag();
      }
  }
  ```

> **Takeaway:** A robust tag input keeps persistence and validation in Livewire while Alpine handles transient dropdown state and keyboard behavior.

## Episode 06 — Infinite Scroll

- **Fetch one page at a time from a computed property so each infinite-scroll request retrieves only the next batch of posts.**
  ```php
  #[Computed]
  public function posts()
  {
      return Post::query()
          ->category($this->category)
          ->search($this->search)
          ->latest()
          ->forPage($this->page, $this->perPage)
          ->get();
  }
  ```

- **Do not increase `perPage` to fake appending, because every request would re-query and return all previously loaded posts.**
  ```php
  // Avoid sending the entire accumulated feed on every request.
  $this->perPage += 5;
  ```

- **Use a named Livewire island with append mode so new results are added to the existing feed instead of replacing it.**
  ```blade
  @island(name: 'posts')
      @foreach ($this->posts as $post)
          <article wire:key="post-{{ $post->id }}">
              {{ $post->title }}
          </article>
      @endforeach
  @endisland

  <button wire:click="loadMore" wire:island.append="posts">
      Load more
  </button>
  ```

- **Increment the page number in `loadMore()` so the island requests the next page without changing the page size.**
  ```php
  public function loadMore(): void
  {
      $this->page++;
  }
  ```

- **Replace the manual load button with `wire:intersect` when the next page should load as the user reaches the feed's end.**
  ```blade
  <div wire:intersect="loadMore" class="h-12"></div>
  ```

- **Scope a loading indicator to `loadMore` so slow requests give feedback without affecting unrelated component updates.**
  ```blade
  <div wire:loading wire:target="loadMore">
      Loading more posts...
  </div>
  ```

- **Reset pagination and re-render the posts island whenever a search or category filter changes, then return the user to the top of the results.**
  ```php
  public function updated(string $property): void
  {
      if (! in_array($property, ['search', 'category'], true)) {
          return;
      }

      $this->page = 1;
      $this->renderIsland('posts');
      $this->dispatch('scroll-to-top');
  }
  ```

- **Use a debounced live model for search so the feed updates while typing without sending a request for every keystroke.**
  ```blade
  <input
      wire:model.live.debounce.300ms="search"
      placeholder="Search posts"
  >
  ```

- **Set the category from filter buttons and use an empty string to represent all categories.**
  ```blade
  <p>{{ $category !== '' ? $category : 'All Categories' }}</p>

  <button type="button" wire:click="$set('category', '')">
      All Categories
  </button>
  <button type="button" wire:click="$set('category', 'technology')">
      Technology
  </button>
  ```

- **Let Alpine manage client-only scroll state by showing a smooth scroll-to-top button only after the user has moved down the page.**
  ```blade
  <div
      x-data="{ showScrollTop: false }"
      x-on:scroll.window="showScrollTop = window.scrollY > 500"
      x-on:scroll-to-top.window="window.scrollTo({ top: 0, behavior: 'smooth' })"
  >
      <button
          x-show="showScrollTop"
          x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
      >
          Scroll to top
      </button>
  </div>
  ```

- **Dispatch an end-of-feed event when the last page contains fewer posts than requested so the client can stop requesting more posts.**
  ```php
  public function loadMore(): void
  {
      $this->page++;

      if ($this->posts->count() < $this->perPage) {
          $this->dispatch('end-of-feed');
      }
  }
  ```

- **Listen for the end-of-feed event in Alpine and replace the intersection sentinel with a clear message.**
  ```blade
  <div
      x-data="{ ended: false }"
      x-on:end-of-feed.window="ended = true"
  >
      <div x-show="! ended" wire:intersect="loadMore"></div>
      <p x-show="ended">You've reached the end.</p>
  </div>
  ```

- **Show an empty state when the current filter has no posts instead of rendering an empty feed.**
  ```blade
  @if ($this->posts->isEmpty())
      <p>No posts found.</p>
  @else
      @island(name: 'posts')
          @foreach ($this->posts as $post)
              <article wire:key="post-{{ $post->id }}">
                  {{ $post->title }}
              </article>
          @endforeach
      @endisland
  @endif
  ```

> **Takeaway:** Efficient infinite scroll combines page-sized queries with Livewire island appends, while Alpine handles viewport-driven and transient UI state.

## Episode 07 — Notification Center

- **Use a computed property for the initial feed so the component loads only the latest 100 notifications when the dropdown needs them.**
  ```php
  use Livewire\Attributes\Computed;

  #[Computed]
  public function notifications()
  {
      return Notification::query()
          ->latest('id')
          ->limit(100)
          ->get();
  }
  ```

- **Let Alpine own dropdown visibility because toggling, outside clicks, and Escape do not need a server request.**
  ```blade
  <div
      x-data="{ open: false }"
      x-on:click.outside="open = false"
      x-on:keydown.escape.window="open = false"
  >
      <button type="button" x-on:click="open = ! open">Notifications</button>
      <div x-show="open">
          <!-- Notification list -->
      </div>
  </div>
  ```

- **Wrap the list in a named island so refreshing notifications does not recreate older items or the surrounding dropdown.**
  ```blade
  <div wire:poll.10s="checkForNew">
      @island(name: 'notifications')
          @forelse ($this->notifications as $notification)
              <x-notification-item
                  :notification="$notification"
                  wire:key="notification-{{ $notification->id }}"
              />
          @empty
              <p>No notifications yet.</p>
          @endforelse
      @endisland
  </div>
  ```

- **Track the newest loaded ID and prepend only newer rows, rendering the island before advancing the cursor so the computed property sees the previous ID.**
  ```php
  public ?int $latestLoadedId = null;

  public function mount(): void
  {
      $this->latestLoadedId = $this->notifications->first()?->id;
  }

  public function checkForNew(): void
  {
      $latestId = Notification::query()->max('id');

      if ($latestId === null || ($this->latestLoadedId !== null && $latestId <= $this->latestLoadedId)) {
          return;
      }

      $this->renderIsland('notifications', mode: 'prepend');
      $this->latestLoadedId = $latestId;
  }
  ```

- **Switch the computed query to an ID cursor after initialization so each poll retrieves only notifications added since the last check.**
  ```php
  #[Computed]
  public function notifications()
  {
      $query = Notification::query()->latest('id');

      return $this->latestLoadedId === null
          ? $query->limit(100)->get()
          : $query->where('id', '>', $this->latestLoadedId)->get();
  }
  ```

- **Use a grid-row animation for new items because CSS cannot interpolate `height` from zero to `auto`.**
  ```css
  @theme {
      --animate-slide-down: slide-down 0.5s ease-out forwards;
  }

  @keyframes slide-down {
      from { grid-template-rows: 0fr; }
      to { grid-template-rows: 1fr; }
  }
  ```

- **Keep the animated content inside an overflowing child with `min-height: 0`, which makes the grid animation expand cleanly.**
  ```blade
  <div class="grid animate-slide-down">
      <div class="min-h-0 overflow-hidden">
          <x-notification-item :notification="$notification" />
      </div>
  </div>
  ```

- **Keep the unread count in Livewire and recalculate it from `read_at` on mount and after polling.**
  ```php
  public int $unreadCount = 0;

  private function updateUnreadCount(): void
  {
      $this->unreadCount = Notification::query()
          ->whereNull('read_at')
          ->count();
  }
  ```

- **Cap the badge at `99+` and hide it when the count is zero so the header stays compact.**
  ```blade
  <span x-cloak x-show="$wire.unreadCount > 0">
      <span x-text="$wire.unreadCount > 99 ? '99+' : $wire.unreadCount"></span>
  </span>
  ```

- **Mirror `read_at` into Alpine and bind unread styling locally so a notification can change appearance without a full list render.**
  ```blade
  <div
      x-data="{ read: @js($notification->read_at !== null) }"
      :class="{ 'bg-blue-50': ! read }"
      x-on:click="
          if (! read) {
              read = true;
              $wire.markAsRead({{ $notification->id }});
          }
      "
  >
      <span x-show="! read" class="size-2 rounded-full bg-blue-500"></span>
      {{ $notification->message }}
  </div>
  ```

- **Make individual read updates renderless because Alpine already handles the visual change and the server only needs to persist it.**
  ```php
  use Livewire\Attributes\Renderless;

  #[Renderless]
  public function markAsRead(int $notificationId): void
  {
      Notification::query()
          ->whereKey($notificationId)
          ->whereNull('read_at')
          ->update(['read_at' => now()]);

      $this->updateUnreadCount();
  }
  ```

- **Use one renderless bulk action to update every unread row and dispatch an event for Alpine to synchronize each item.**
  ```php
  #[Renderless]
  public function markAllAsRead(): void
  {
      Notification::query()->whereNull('read_at')->update(['read_at' => now()]);
      $this->unreadCount = 0;
      $this->dispatch('notifications-marked-read');
  }
  ```

- **Listen for the bulk-read event on `window` so each item updates its local state even when the parent does not re-render.**
  ```blade
  <div
      x-data="{ read: @js($notification->read_at !== null) }"
      x-on:notifications-marked-read.window="read = true"
  >
      <!-- Notification content -->
  </div>
  ```

> **Takeaway:** Use Livewire for notification queries, polling, persistence, and counts, while Alpine handles dropdown state, optimistic visual updates, and animations.

## Episode 08 — Dynamic Search

- **Search only after the input has at least two characters, matching the title, excerpt, content, or author before returning the ten latest published posts.**
  ```php
  #[Computed]
  public function results()
  {
      if (strlen($this->search) < 2) {
          return collect();
      }

      return Post::query()
          ->where('published', true)
          ->where(function (Builder $query): void {
              $term = "%{$this->search}%";

              $query
                  ->where('title', 'like', $term)
                  ->orWhere('excerpt', 'like', $term)
                  ->orWhere('content', 'like', $term)
                  ->orWhereHas('author', fn (Builder $author) => $author->where('name', 'like', $term));
          })
          ->latest()
          ->limit(10)
          ->get();
  }
  ```

- **Let Alpine control whether the dropdown is open while Livewire owns the live search value, loading state, and clear action.**
  ```blade
  <div x-data="{ open: false }" x-on:click.outside="open = false">
      <input
          wire:model.live="search"
          x-on:focus="open = true"
          x-on:keydown.escape="open = false"
      >

      <div x-show="open">
          <span wire:loading wire:target="search">Searching...</span>
          <button type="button" wire:click="$set('search', '')">Clear</button>
      </div>
  </div>
  ```

- **Use `-1` as the no-selection state and prevent arrow keys from moving the input caret so navigation can use a zero-based index.**
  ```blade
  <div
      x-data="{
          highlightedIndex: -1,
          moveUp() {
              if (this.highlightedIndex > 0) {
                  this.highlightedIndex--;
              }
          },
          moveDown() {
              if (this.$refs.resultsList.children[this.highlightedIndex + 1]) {
                  this.highlightedIndex++;
              }
          },
      }"
  >
      <input
          x-on:keydown.arrow-down.prevent="moveDown()"
          x-on:keydown.arrow-up.prevent="moveUp()"
      >

      <ul x-ref="resultsList"></ul>
  </div>
  ```

- **Use the same index for mouse and keyboard interaction, and give the active row a visual class plus a stable `wire:key`.**
  ```blade
  @foreach ($this->results as $index => $post)
      <li wire:key="search-result-{{ $post->id }}">
          <button
              type="button"
              x-on:mouseenter="highlightedIndex = {{ $index }}"
              :class="{ 'bg-blue-50': highlightedIndex === {{ $index }} }"
          >
              {{ $post->title }}
          </button>
      </li>
  @endforeach
  ```

- **Scroll the active result into view after changing the index, using `nearest` to avoid jumping the whole list.**
  ```js
  moveDown() {
      if (this.$refs.resultsList.children[this.highlightedIndex + 1]) {
          this.highlightedIndex++;
          this.$nextTick(() => this.scrollToHighlighted());
      }
  }

  scrollToHighlighted() {
      this.$refs.resultsList.children[this.highlightedIndex]
          ?.scrollIntoView({ block: 'nearest' });
  }
  ```

- **Add a small dropdown footer that teaches the keyboard shortcut so keyboard navigation is discoverable.**
  ```blade
  <footer class="border-t px-4 py-2 text-xs text-zinc-500">
      Use the arrow keys to navigate
  </footer>
  ```

- **Escape the original text before wrapping case-insensitive matches in `<mark>`, and skip highlighting until the query is meaningful.**
  ```php
  public function highlightMatch(string $text): string
  {
      $search = trim($this->search);

      if (strlen($search) < 2) {
          return e($text);
      }

      return preg_replace(
          '/' . preg_quote($search, '/') . '/i',
          '<mark class="bg-yellow-200">$0</mark>',
          e($text),
      ) ?? e($text);
  }
  ```

- **Render highlighted output as HTML only at the display boundary because `highlightMatch()` has already escaped the original text.**
  ```blade
  <h3>{!! $this->highlightMatch($post->title) !!}</h3>
  <p>{!! $this->highlightMatch($this->getSnippet($post)) !!}</p>
  <p>{!! $this->highlightMatch($post->author->name) !!}</p>
  ```

- **Prefer the excerpt when it contains the query; otherwise use `Str::excerpt()` around a content match so deep matches have useful context.**
  ```php
  public function getSnippet(Post $post): string
  {
      if (Str::contains($post->excerpt, $this->search, ignoreCase: true)) {
          return $post->excerpt;
      }

      if (Str::contains($post->content, $this->search, ignoreCase: true)) {
          return Str::excerpt($post->content, $this->search, ['radius' => 50]);
      }

      return $post->excerpt;
  }
  ```

- **Use `#[Session]` for recent searches when they should survive refreshes without adding a database table or changing the URL.**
  ```php
  use Livewire\Attributes\Session;

  #[Session]
  public array $recentSearches = [];
  ```

- **Normalize recent terms case-insensitively, move repeated terms to the front, and keep only the five most recent entries.**
  ```php
  #[Renderless]
  public function addToRecentSearches(string $term): void
  {
      $term = trim($term);

      if (strlen($term) < 2) {
          return;
      }

      $this->recentSearches = collect([$term, ...$this->recentSearches])
          ->unique(fn (string $search): string => mb_strtolower($search))
          ->take(5)
          ->values()
          ->all();
  }
  ```

- **Keep clearing recent searches renderless because it changes session state without changing the current results markup.**
  ```php
  #[Renderless]
  public function clearRecentSearches(): void
  {
      $this->recentSearches = [];
  }
  ```

- **Show recent searches when the query is empty or too short, and let users restore a term or clear the whole list.**
  ```blade
  @if (strlen($search) < 2)
      @forelse ($recentSearches as $term)
          <button type="button" x-on:click="$wire.useSearch(@js($term))">
              {{ $term }}
          </button>
      @empty
          <p>Type at least two characters to search.</p>
      @endforelse

      @if ($recentSearches !== [])
          <button type="button" wire:click="clearRecentSearches">
              Clear recent searches
          </button>
      @endif
  @else
      <!-- Results or no-results state -->
  @endif
  ```

- **When a recent term is selected, assign it to `search` and send it through the same recent-search path.**
  ```php
  public function useSearch(string $term): void
  {
      $this->search = $term;
      $this->addToRecentSearches($term);
  }
  ```

- **On Enter or click, use the highlighted result only when the index is non-negative, save the query, close the dropdown, and navigate to the result.**
  ```blade
  <div x-data="{
      open: false,
      highlightedIndex: -1,
      selectHighlighted() {
          if (this.highlightedIndex < 0) {
              return;
          }

          const result = $wire.results[this.highlightedIndex];

          if (! result) {
              return;
          }

          $wire.addToRecentSearches($wire.search);
          this.open = false;
          window.location.href = result.url;
      },
  }">
      <input x-on:keydown.enter.prevent="selectHighlighted()">
      <button type="button" x-on:click="selectHighlighted()">Open selected result</button>
  </div>
  ```

- **Watch `$wire.search` and reset the highlight whenever the term changes so a new result set cannot inherit a stale index.**
  ```blade
  <div
      x-data="{ highlightedIndex: -1 }"
      x-init="$watch('$wire.search', () => highlightedIndex = -1)"
  >
      <!-- Search input and results -->
  </div>
  ```

> **Takeaway:** A polished inline search keeps querying and persistence in Livewire while Alpine handles immediate dropdown, keyboard, pointer, and scrolling feedback.
