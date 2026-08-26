<?php

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.editor')]
#[Title('Episode 06 - Infinite Scroll')]
class extends Component {
    public int $page = 1;

    public int $perPage = 5;

    public string $search = '';

    public string $category = '';

    /** @var array<int, string> */
    public array $categories = [
        'technology',
        'lifestyle',
        'design',
    ];

    #[Computed]
    public function posts(): Collection
    {
        return Post::query()
            ->category($this->category)
            ->search($this->search)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->forPage($this->page, $this->perPage)
            ->get();
    }

    public function updated(string $property): void
    {
        if (!in_array($property, ['search', 'category'], true)) {
            return;
        }

        $this->page = 1;
        $this->renderIsland('posts');
        $this->dispatch('feed-reset');
        $this->dispatch('scroll-to-top');
    }

    public function loadMore(): void
    {
        $this->page++;

        if ($this->posts->count() < $this->perPage) {
            $this->dispatch('end-of-feed');
        }
    }
};
?>

<div
    x-data="{ showScrollTop: false }"
    x-on:scroll.window="showScrollTop = window.scrollY > 500"
    x-on:scroll-to-top.window="window.scrollTo({ top: 0, behavior: 'smooth' })"
    class="min-h-screen bg-stone-100 px-6 py-8 text-zinc-950 sm:px-10 lg:px-16 dark:bg-zinc-950 dark:text-zinc-100"
>
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-5xl flex-col">
        <header
            class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-900/10 pb-5 dark:border-white/10">
            <div>
                <p class="text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">
                    Episode 06
                </p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Infinite scroll</p>
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
                    Episode 06
                </p>
                <h1 class="mt-5 text-4xl leading-tight font-medium tracking-tight sm:text-6xl">Infinite scroll</h1>
                <p class="mt-6 text-lg leading-8 text-zinc-600 dark:text-zinc-300">
                    Browse a feed that loads the next page as you scroll, while search and category filters reset the
                    results smoothly.
                </p>

                <div class="mt-12 border-t border-zinc-900/10 pt-8 dark:border-white/10">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Post feed</p>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $category !== '' ? str($category)->headline() : 'All categories' }}
                            </p>
                        </div>

                        <span wire:loading wire:target="search,category"
                              class="text-sm text-zinc-500 dark:text-zinc-400">
                            Updating feed...
                        </span>
                    </div>

                    <label for="post-search" class="mt-6 block text-sm font-medium text-zinc-900 dark:text-zinc-100">
                        Search posts
                    </label>
                    <input
                        id="post-search"
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Try design, technology, or UI"
                        class="mt-3 w-full border border-zinc-900/15 bg-white px-4 py-3 text-zinc-950 outline-hidden transition placeholder:text-zinc-400 focus:border-amber-600 focus:ring-2 focus:ring-amber-600/20 dark:border-white/15 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-600 dark:focus:border-amber-400 dark:focus:ring-amber-400/20"
                    >

                    <div class="mt-4 flex flex-wrap gap-2" aria-label="Post categories">
                        <button
                            type="button"
                            wire:click="$set('category', '')"
                            aria-pressed="{{ $category === '' ? 'true' : 'false' }}"
                            @class([
                                'border px-3 py-1.5 text-sm transition',
                                'border-amber-600 bg-amber-100 text-amber-950 dark:border-amber-400 dark:bg-amber-950 dark:text-amber-100' => $category === '',
                                'border-zinc-900/15 text-zinc-600 hover:border-amber-600 hover:text-amber-800 dark:border-white/15 dark:text-zinc-300 dark:hover:border-amber-400 dark:hover:text-amber-200' => $category !== '',
                            ])
                        >
                            All categories
                        </button>

                        @foreach ($categories as $availableCategory)
                            <button
                                type="button"
                                wire:click="$set('category', '{{ $availableCategory }}')"
                                aria-pressed="{{ $category === $availableCategory ? 'true' : 'false' }}"
                                @class([
                                    'border px-3 py-1.5 text-sm transition',
                                    'border-amber-600 bg-amber-100 text-amber-950 dark:border-amber-400 dark:bg-amber-950 dark:text-amber-100' => $category === $availableCategory,
                                    'border-zinc-900/15 text-zinc-600 hover:border-amber-600 hover:text-amber-800 dark:border-white/15 dark:text-zinc-300 dark:hover:border-amber-400 dark:hover:text-amber-200' => $category !== $availableCategory,
                                ])
                            >
                                {{ str($availableCategory)->headline() }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($this->posts->isEmpty())
                    <div
                        class="mt-8 border border-dashed border-zinc-900/20 px-6 py-12 text-center dark:border-white/20">
                        <p class="text-lg font-medium text-zinc-900 dark:text-zinc-100">No posts found</p>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Try another search or choose a
                            different category.</p>
                    </div>
                @endif

                @island(name: 'posts')
                <div class="mt-8 grid gap-4">
                    @foreach ($this->posts as $post)
                        <article
                            wire:key="post-{{ $post->id }}"
                            class="border border-zinc-900/10 bg-white p-5 shadow-sm shadow-zinc-900/5 dark:border-white/10 dark:bg-zinc-900 dark:shadow-black/20"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-3 text-xs font-semibold tracking-[0.15em] text-amber-700 uppercase dark:text-amber-400">
                                <span>{{ str($post->category)->headline() }}</span>
                                <time datetime="{{ $post->created_at->toDateString() }}"
                                      class="font-normal tracking-normal text-zinc-400 normal-case dark:text-zinc-500">
                                    {{ $post->created_at->format('M j, Y') }}
                                </time>
                            </div>
                            <h2 class="mt-3 text-xl font-medium tracking-tight text-zinc-950 dark:text-white">{{ $post->title }}</h2>
                            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $post->excerpt }}</p>
                        </article>
                    @endforeach
                </div>
                @endisland

                @if ($this->posts->isNotEmpty())
                    <div
                        x-data="{ ended: false }"
                        x-on:feed-reset.window="ended = false"
                        x-on:end-of-feed.window="ended = true"
                        class="mt-8 flex min-h-16 flex-col items-center justify-center gap-3 text-center"
                    >
                        <div
                            x-show="! ended"
                            wire:intersect="loadMore"
                            wire:island.append="posts"
                            class="flex min-h-16 w-full items-center justify-center"
                        >
                            <span wire:loading wire:target="loadMore"
                                  class="inline-flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                <span
                                    class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                                Loading more posts...
                            </span>
                        </div>

                        <p x-cloak x-show="ended" class="text-sm text-zinc-500 dark:text-zinc-400">
                            You've reached the end of the feed.
                        </p>
                    </div>
                @endif
            </section>

            <aside class="border-l border-zinc-900/10 pl-6 text-sm leading-6 text-zinc-500 dark:border-white/10 dark:text-zinc-400">
                <p class="font-medium text-zinc-900 dark:text-zinc-100">Try it out</p>
                <p class="mt-2">Scroll toward the bottom to load the next five posts without replacing the ones already visible.</p>
                <p class="mt-6">Change the category or search for a phrase to reset the feed and return to the top.</p>
            </aside>
        </main>

        <footer class="border-t border-zinc-900/10 pt-5 text-xs text-zinc-500 dark:border-white/10 dark:text-zinc-500">
            Livewire islands + Alpine scroll state
        </footer>
    </div>

    <button
        type="button"
        x-cloak
        x-show="showScrollTop"
        x-transition
        x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed right-6 bottom-6 z-10 border border-zinc-900/15 bg-white px-4 py-2 text-sm font-medium text-zinc-800 shadow-lg shadow-zinc-900/10 transition hover:border-amber-600 hover:text-amber-800 dark:border-white/15 dark:bg-zinc-900 dark:text-zinc-100 dark:shadow-black/20 dark:hover:border-amber-400 dark:hover:text-amber-200"
    >
        Scroll to top
    </button>
</div>
