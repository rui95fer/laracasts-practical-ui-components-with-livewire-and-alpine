<?php

use App\Models\Tag;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.editor')]
#[Title('Episode 05 - Tag Input')]
class extends Component
{
    public string $search = '';

    /** @var array<int, array{id: int, name: string}> */
    public array $suggestions = [];

    /** @var array<int, array{id: int, name: string}> */
    public array $selectedTags = [];

    public bool $canCreateTag = false;

    public function updatedSearch(): void
    {
        $this->loadSuggestions();
    }

    public function addTag(int $tagId): void
    {
        $tag = Tag::query()->findOrFail($tagId);

        if (collect($this->selectedTags)->contains('id', $tag->id)) {
            return;
        }

        $this->selectedTags[] = [
            'id' => $tag->id,
            'name' => $tag->name,
        ];

        $this->clearSearch();
    }

    public function removeTag(int $tagId): void
    {
        $this->selectedTags = collect($this->selectedTags)
            ->reject(fn (array $tag): bool => $tag['id'] === $tagId)
            ->values()
            ->all();
    }

    public function createTag(): void
    {
        $name = Str::of($this->search)->trim()->toString();

        if ($name === '') {
            return;
        }

        $this->validate([
            'search' => ['required', 'string', 'max:255'],
        ]);

        $normalizedName = Str::lower($name);
        $tag = Tag::query()
            ->whereRaw('LOWER(name) = ?', [$normalizedName])
            ->first();

        if ($tag === null) {
            $tag = Tag::create(['name' => $name]);
        }

        if (! collect($this->selectedTags)->contains('id', $tag->id)) {
            $this->selectedTags[] = [
                'id' => $tag->id,
                'name' => $tag->name,
            ];
        }

        $this->clearSearch();
        $this->dispatch('toast', message: 'Tag added', type: 'success');
    }

    private function loadSuggestions(): void
    {
        $search = Str::of($this->search)->trim()->toString();

        if ($search === '') {
            $this->suggestions = [];
            $this->canCreateTag = false;

            return;
        }

        $normalizedSearch = Str::lower($search);
        $selectedTagIds = collect($this->selectedTags)->pluck('id')->all();

        $this->suggestions = Tag::query()
            ->whereRaw('LOWER(name) LIKE ?', ["%{$normalizedSearch}%"])
            ->whereNotIn('id', $selectedTagIds)
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name'])
            ->map(fn (Tag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
            ])
            ->all();

        $this->canCreateTag = ! Tag::query()
            ->whereRaw('LOWER(name) = ?', [$normalizedSearch])
            ->exists();
    }

    private function clearSearch(): void
    {
        $this->search = '';
        $this->suggestions = [];
        $this->canCreateTag = false;
    }
};
?>

<div class="min-h-screen bg-stone-100 px-6 py-8 text-zinc-950 sm:px-10 lg:px-16 dark:bg-zinc-950 dark:text-zinc-100">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-5xl flex-col">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-900/10 pb-5 dark:border-white/10">
            <div>
                <p class="text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">
                    Episode 05
                </p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Tag input</p>
            </div>

            <a
                href="{{ route('home') }}"
                class="text-sm text-zinc-500 underline decoration-zinc-300 underline-offset-4 hover:text-zinc-950 dark:text-zinc-400 dark:decoration-zinc-700 dark:hover:text-white"
            >
                All episodes
            </a>
        </header>

        <main class="grid flex-1 items-start gap-10 py-12 lg:grid-cols-[minmax(0,1fr)_15rem] lg:gap-16 lg:py-20">
            <section class="max-w-3xl">
                <p class="text-sm font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">
                    Episode 05
                </p>
                <h1 class="mt-5 text-4xl leading-tight font-medium tracking-tight sm:text-6xl">Tag input</h1>
                <p class="mt-6 text-lg leading-8 text-zinc-600 dark:text-zinc-300">
                    Search existing tags, move through suggestions with your keyboard, or create a new tag without leaving the field.
                </p>

                <div class="mt-12 border-t border-zinc-900/10 pt-8 dark:border-white/10">
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Selected interests</p>

                    <div class="mt-4 flex min-h-10 flex-wrap items-center gap-2" aria-live="polite">
                        @forelse ($selectedTags as $tag)
                            <span
                                wire:key="selected-tag-{{ $tag['id'] }}"
                                class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-950 dark:bg-amber-950 dark:text-amber-100"
                            >
                                <span>{{ $tag['name'] }}</span>
                                <button
                                    type="button"
                                    wire:click="removeTag({{ $tag['id'] }})"
                                    class="inline-flex size-5 items-center justify-center rounded-full text-xs text-amber-800 transition hover:bg-amber-200 hover:text-amber-950 dark:text-amber-200 dark:hover:bg-amber-900 dark:hover:text-amber-50"
                                    aria-label="Remove {{ $tag['name'] }}"
                                >
                                    x
                                </button>
                            </span>
                        @empty
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">No tags selected yet.</span>
                        @endforelse
                    </div>

                    <div
                        x-data="{
                            open: false,
                            highlightedIndex: -1,
                            maxIndex() {
                                return $wire.canCreateTag
                                    ? $wire.suggestions.length
                                    : $wire.suggestions.length - 1;
                            },
                            moveDown() {
                                this.highlightedIndex = Math.min(this.highlightedIndex + 1, this.maxIndex());
                            },
                            moveUp() {
                                this.highlightedIndex = Math.max(this.highlightedIndex - 1, -1);
                            },
                            selectHighlighted() {
                                if (this.highlightedIndex < 0) {
                                    return;
                                }

                                if (this.highlightedIndex < $wire.suggestions.length) {
                                    $wire.addTag($wire.suggestions[this.highlightedIndex].id);
                                } else if ($wire.canCreateTag) {
                                    $wire.createTag();
                                }

                                this.open = false;
                                this.highlightedIndex = -1;
                            },
                        }"
                        x-on:click.outside="open = false; highlightedIndex = -1"
                        class="relative mt-6"
                    >
                        <label for="tag-search" class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            Add a tag
                        </label>

                        <input
                            id="tag-search"
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            role="combobox"
                            aria-controls="tag-suggestions"
                            aria-autocomplete="list"
                            autocomplete="off"
                            x-bind:aria-expanded="open"
                            x-on:input="open = true; highlightedIndex = -1"
                            x-on:focus="open = true"
                            x-on:keydown.escape.prevent="open = false; highlightedIndex = -1"
                            x-on:keydown.arrow-down.prevent="moveDown()"
                            x-on:keydown.arrow-up.prevent="moveUp()"
                            x-on:keydown.enter.prevent="selectHighlighted()"
                            placeholder="Try art, gaming, or something new"
                            class="mt-3 w-full border border-zinc-900/15 bg-white px-4 py-3 text-zinc-950 outline-hidden transition placeholder:text-zinc-400 focus:border-amber-600 focus:ring-2 focus:ring-amber-600/20 dark:border-white/15 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-600 dark:focus:border-amber-400 dark:focus:ring-amber-400/20"
                        >

                        <div class="mt-2 flex min-h-5 items-center justify-between gap-4 text-sm">
                            @error('search')
                            <p class="text-red-700 dark:text-red-400">{{ $message }}</p>
                            @else
                            <span class="text-zinc-500 dark:text-zinc-400">Use the arrow keys, then press Enter.</span>
                            @enderror
                            <span wire:loading wire:target="search" class="text-zinc-500 dark:text-zinc-400">Searching...</span>
                        </div>

                        <div
                            id="tag-suggestions"
                            x-cloak
                            x-show="open && $wire.search.length > 0 && ($wire.suggestions.length > 0 || $wire.canCreateTag)"
                            x-transition
                            class="absolute right-0 left-0 z-20 mt-2 overflow-hidden border border-zinc-900/10 bg-white shadow-xl shadow-zinc-900/10 dark:border-white/10 dark:bg-zinc-900 dark:shadow-black/20"
                        >
                            @if (count($suggestions) > 0)
                                <ul role="listbox" class="divide-y divide-zinc-900/10 dark:divide-white/10">
                                    @foreach ($suggestions as $index => $suggestion)
                                        <li
                                            wire:key="suggestion-{{ $suggestion['id'] }}"
                                            id="tag-suggestion-{{ $suggestion['id'] }}"
                                            role="option"
                                            :aria-selected="highlightedIndex === {{ $index }}"
                                        >
                                            <button
                                                type="button"
                                                wire:click="addTag({{ $suggestion['id'] }})"
                                                x-on:mouseenter="highlightedIndex = {{ $index }}"
                                                x-on:click="open = false; highlightedIndex = -1"
                                                :class="{ 'bg-amber-50 dark:bg-white/10': highlightedIndex === {{ $index }} }"
                                                class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm text-zinc-700 transition hover:bg-amber-50 dark:text-zinc-200 dark:hover:bg-white/10"
                                            >
                                                <span>{{ $suggestion['name'] }}</span>
                                                <span class="text-xs text-zinc-400 dark:text-zinc-500">Add</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($canCreateTag)
                                <button
                                    type="button"
                                    wire:key="create-tag-option"
                                    wire:click="createTag"
                                    x-on:mouseenter="highlightedIndex = {{ count($suggestions) }}"
                                    x-on:click="open = false; highlightedIndex = -1"
                                    :class="{ 'bg-amber-50 dark:bg-white/10': highlightedIndex === {{ count($suggestions) }} }"
                                    class="flex w-full items-center justify-between gap-3 border-t border-zinc-900/10 px-4 py-3 text-left text-sm font-medium text-amber-800 transition hover:bg-amber-50 dark:border-white/10 dark:text-amber-300 dark:hover:bg-white/10"
                                >
                                    <span>Create "{{ $search }}"</span>
                                    <span class="text-xs text-amber-600 dark:text-amber-400">New tag</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <aside class="border-l border-zinc-900/10 pl-6 text-sm leading-6 text-zinc-500 dark:border-white/10 dark:text-zinc-400">
                <p class="font-medium text-zinc-900 dark:text-zinc-100">Try it out</p>
                <p class="mt-2">Type a few letters to filter the seeded tags, then use Up and Down to highlight an option.</p>
                <p class="mt-6">Press Enter to select the highlighted tag. If no exact match exists, move to the create option and press Enter again.</p>
            </aside>
        </main>

        <footer class="border-t border-zinc-900/10 pt-5 text-xs text-zinc-500 dark:border-white/10 dark:text-zinc-500">
            Livewire suggestions + Alpine keyboard navigation
        </footer>
    </div>
</div>
