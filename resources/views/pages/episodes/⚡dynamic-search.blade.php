<?php

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Session;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.editor')]
#[Title('Episode 08 - Dynamic Search')]
class extends Component
{
    public string $search = '';

    /** @var array<int, string> */
    #[Session]
    public array $recentSearches = [];

    /**
     * @return Collection<int, Post>
     */
    #[Computed]
    public function results(): Collection
    {
        $search = trim($this->search);

        if (Str::length($search) < 2) {
            return new Collection;
        }

        $term = '%'.mb_strtolower($search).'%';

        return Post::query()
            ->with('author')
            ->where('published', true)
            ->where(function (Builder $query) use ($term): void {
                $query
                    ->whereRaw('LOWER(title) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(excerpt) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(content) LIKE ?', [$term])
                    ->orWhereHas('author', function (Builder $author) use ($term): void {
                        $author->whereRaw('LOWER(name) LIKE ?', [$term]);
                    });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();
    }

    public function highlightMatch(string $text): string
    {
        $search = trim($this->search);
        $escapedText = e($text);

        if (Str::length($search) < 2) {
            return $escapedText;
        }

        $escapedSearch = preg_quote(e($search), '/');

        return preg_replace(
            '/'.$escapedSearch.'/iu',
            '<mark class="bg-amber-200 text-amber-950 dark:bg-amber-400 dark:text-amber-950">$0</mark>',
            $escapedText,
        ) ?? $escapedText;
    }

    public function getSnippet(Post $post): string
    {
        $search = trim($this->search);
        $excerpt = $post->excerpt;
        $content = $post->content ?? '';

        if (Str::contains($excerpt, $search, ignoreCase: true)) {
            return $excerpt;
        }

        if (Str::contains($content, $search, ignoreCase: true)) {
            return Str::excerpt($content, $search, ['radius' => 50]);
        }

        return $excerpt;
    }

    #[Renderless]
    public function addToRecentSearches(string $term): void
    {
        $term = trim($term);

        if (Str::length($term) < 2) {
            return;
        }

        $this->recentSearches = collect([$term, ...$this->recentSearches])
            ->unique(fn (string $search): string => mb_strtolower($search))
            ->take(5)
            ->values()
            ->all();
    }

    #[Renderless]
    public function clearRecentSearches(): void
    {
        $this->recentSearches = [];
        $this->dispatch('recent-searches-cleared');
    }

    public function useSearch(string $term): void
    {
        $this->search = $term;
        $this->addToRecentSearches($term);
    }
};
?>

<div
    x-data="{
        open: false,
        highlightedIndex: -1,
        recentSearchesCleared: false,
        moveUp() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
                this.scrollToHighlighted();
            }
        },
        moveDown() {
            const nextResult = this.$refs.resultsList?.children[this.highlightedIndex + 1];

            if (nextResult) {
                this.highlightedIndex++;
                this.scrollToHighlighted();
            }
        },
        scrollToHighlighted() {
            this.$nextTick(() => {
                this.$refs.resultsList?.children[this.highlightedIndex]?.scrollIntoView({ block: 'nearest' });
            });
        },
        selectResult(index) {
            this.highlightedIndex = index;
            this.selectHighlighted();
        },
        async selectHighlighted() {
            if (this.highlightedIndex < 0) {
                return;
            }

            const result = this.$refs.resultsList?.children[this.highlightedIndex];
            const url = result?.dataset.url;

            if (! url) {
                return;
            }

            await $wire.addToRecentSearches($wire.search);
            this.open = false;
            window.location.href = url;
        },
    }"
    x-init="$watch('$wire.search', () => { highlightedIndex = -1; recentSearchesCleared = false })"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false; highlightedIndex = -1"
    x-on:recent-searches-cleared.window="recentSearchesCleared = true"
    class="min-h-screen bg-stone-100 px-6 py-8 text-zinc-950 sm:px-10 lg:px-16 dark:bg-zinc-950 dark:text-zinc-100"
>
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-5xl flex-col">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-900/10 pb-5 dark:border-white/10">
            <div>
                <p class="text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">
                    Episode 08
                </p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Dynamic search</p>
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
                    Episode 08
                </p>
                <h1 class="mt-5 text-4xl leading-tight font-medium tracking-tight sm:text-6xl">Dynamic search</h1>
                <p class="mt-6 text-lg leading-8 text-zinc-600 dark:text-zinc-300">
                    Search published posts as you type, then use the keyboard or pointer to open a result without losing your recent searches.
                </p>

                <div class="mt-12 border-t border-zinc-900/10 pt-8 dark:border-white/10">
                    <label for="dynamic-post-search" class="text-sm font-medium text-zinc-950 dark:text-white">
                        Search posts
                    </label>

                    <div class="relative mt-3">
                        <input
                            id="dynamic-post-search"
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            x-on:focus="open = true"
                            x-on:keydown.arrow-down.prevent="open = true; moveDown()"
                            x-on:keydown.arrow-up.prevent="open = true; moveUp()"
                            x-on:keydown.enter.prevent="selectHighlighted()"
                            x-on:keydown.escape.prevent="open = false; highlightedIndex = -1"
                            x-bind:aria-expanded="open"
                            aria-controls="dynamic-search-results"
                            aria-autocomplete="list"
                            autocomplete="off"
                            placeholder="Search title, content, or author"
                            class="w-full border border-zinc-900/15 bg-white px-4 py-3 text-zinc-950 outline-hidden transition placeholder:text-zinc-400 focus:border-amber-600 focus:ring-2 focus:ring-amber-600/20 dark:border-white/15 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-600 dark:focus:border-amber-400 dark:focus:ring-amber-400/20"
                        >

                        <div
                            id="dynamic-search-results"
                            x-cloak
                            x-show="open"
                            x-transition.origin.top
                            class="absolute inset-x-0 top-full z-30 mt-2 overflow-hidden border border-zinc-900/10 bg-white shadow-2xl shadow-zinc-900/10 dark:border-white/10 dark:bg-zinc-900 dark:shadow-black/30"
                        >
                            <div class="flex items-center justify-between gap-4 border-b border-zinc-900/10 px-4 py-3 dark:border-white/10">
                                <span wire:loading wire:target="search" class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Searching...
                                </span>
                                <span wire:loading.remove wire:target="search" class="text-xs text-zinc-500 dark:text-zinc-400">
                                    @if (Str::length(trim($search)) >= 2)
                                        {{ $this->results->count() }} {{ $this->results->count() === 1 ? 'result' : 'results' }}
                                    @else
                                        Recent searches
                                    @endif
                                </span>

                                @if ($search !== '')
                                    <button
                                        type="button"
                                        wire:click="$set('search', '')"
                                        class="shrink-0 text-xs font-medium text-amber-700 transition hover:text-amber-950 dark:text-amber-400 dark:hover:text-amber-200"
                                    >
                                        Clear
                                    </button>
                                @endif
                            </div>

                            @if (Str::length(trim($search)) < 2)
                                <div x-show="! recentSearchesCleared" class="max-h-72 overflow-y-auto py-2">
                                    @forelse ($recentSearches as $term)
                                        <button
                                            type="button"
                                            wire:key="recent-search-{{ md5($term) }}"
                                            x-on:click="open = true; highlightedIndex = -1; $wire.useSearch(@js($term))"
                                            class="flex w-full items-center justify-between gap-4 px-4 py-3 text-left text-sm text-zinc-700 transition hover:bg-amber-50 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-amber-950/40 dark:hover:text-white"
                                        >
                                            <span>{{ $term }}</span>
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">Recent</span>
                                        </button>
                                    @empty
                                        <p class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                            Type at least two characters to search.
                                        </p>
                                    @endforelse
                                </div>

                                <p x-cloak x-show="recentSearchesCleared" class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No recent searches yet.
                                </p>

                                @if ($recentSearches !== [])
                                    <div class="border-t border-zinc-900/10 px-4 py-2 dark:border-white/10">
                                        <button
                                            type="button"
                                            wire:click="clearRecentSearches"
                                            class="text-xs font-medium text-zinc-500 transition hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white"
                                        >
                                            Clear recent searches
                                        </button>
                                    </div>
                                @endif
                            @elseif ($this->results->isEmpty())
                                <p class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No published posts match "{{ $search }}".
                                </p>
                            @else
                                <ul x-ref="resultsList" role="listbox" class="max-h-96 overflow-y-auto py-2">
                                    @foreach ($this->results as $index => $post)
                                        <li
                                            wire:key="search-result-{{ $post->id }}"
                                            data-url="{{ route('posts.show', $post) }}"
                                            role="option"
                                            x-bind:aria-selected="highlightedIndex === {{ $index }}"
                                        >
                                            <button
                                                type="button"
                                                x-on:mouseenter="highlightedIndex = {{ $index }}"
                                                x-on:focus="highlightedIndex = {{ $index }}"
                                                x-on:click="selectResult({{ $index }})"
                                                :class="{ 'bg-amber-50 dark:bg-amber-950/40': highlightedIndex === {{ $index }} }"
                                                class="block w-full px-4 py-3 text-left transition"
                                            >
                                                <span class="block text-sm font-medium text-zinc-950 dark:text-white">
                                                    {!! $this->highlightMatch($post->title) !!}
                                                </span>
                                                <span class="mt-1 block text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                                                    {!! $this->highlightMatch($this->getSnippet($post)) !!}
                                                </span>
                                                <span class="mt-2 block text-xs text-zinc-400 dark:text-zinc-500">
                                                    @if ($post->author)
                                                        By {!! $this->highlightMatch($post->author->name) !!}
                                                    @else
                                                        Author unavailable
                                                    @endif
                                                </span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <footer class="border-t border-zinc-900/10 px-4 py-2 text-xs text-zinc-500 dark:border-white/10 dark:text-zinc-500">
                                Use the arrow keys to navigate
                            </footer>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="border-l border-zinc-900/10 pl-6 text-sm leading-6 text-zinc-500 dark:border-white/10 dark:text-zinc-400">
                <p class="font-medium text-zinc-950 dark:text-zinc-100">Try it out</p>
                <p class="mt-2">Type at least two characters to query the latest published posts by title, excerpt, content, or author.</p>
                <p class="mt-6">Use the arrow keys to move through results. Press Enter or click a result to open it.</p>
                <p class="mt-6">Clear the search, then restore a previous term from the session-backed recent search list.</p>
            </aside>
        </main>

        <footer class="border-t border-zinc-900/10 pt-5 text-xs text-zinc-500 dark:border-white/10 dark:text-zinc-500">
            Livewire search state + Alpine interaction state
        </footer>
    </div>
</div>
