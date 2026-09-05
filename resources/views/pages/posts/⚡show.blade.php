<?php

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.editor')]
#[Title('Post')]
class extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        $this->post = $post->load('author');
    }
};
?>

<div class="min-h-screen bg-stone-100 px-6 py-8 text-zinc-950 sm:px-10 lg:px-16 dark:bg-zinc-950 dark:text-zinc-100">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-5xl flex-col">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-900/10 pb-5 dark:border-white/10">
            <p class="text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">Post</p>

            <a
                href="{{ route('episodes.dynamic-search') }}"
                class="text-sm text-zinc-500 underline decoration-zinc-300 underline-offset-4 hover:text-zinc-950 dark:text-zinc-400 dark:decoration-zinc-700 dark:hover:text-white"
            >
                Back to search
            </a>
        </header>

        <main class="flex-1 py-12 sm:py-20">
            <article class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold tracking-[0.15em] text-amber-700 uppercase dark:text-amber-400">
                    <span>{{ str($post->category)->headline() }}</span>
                    <span class="text-zinc-300 dark:text-zinc-700">/</span>
                    <time datetime="{{ $post->created_at?->toDateString() }}" class="font-normal tracking-normal text-zinc-400 normal-case dark:text-zinc-500">
                        {{ $post->created_at?->format('M j, Y') }}
                    </time>
                </div>

                <h1 class="mt-5 text-4xl leading-tight font-medium tracking-tight sm:text-6xl">{{ $post->title }}</h1>
                <p class="mt-6 text-lg leading-8 text-zinc-600 dark:text-zinc-300">{{ $post->excerpt }}</p>

                <div class="mt-8 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    <span>Written by</span>
                    <span class="font-medium text-zinc-950 dark:text-zinc-100">{{ $post->author?->name ?? 'Unknown author' }}</span>
                </div>

                <div class="mt-12 border-t border-zinc-900/10 pt-8 text-base leading-8 whitespace-pre-line text-zinc-700 dark:border-white/10 dark:text-zinc-300">
                    {{ $post->content ?? $post->excerpt }}
                </div>
            </article>
        </main>

        <footer class="border-t border-zinc-900/10 pt-5 text-xs text-zinc-500 dark:border-white/10 dark:text-zinc-500">
            Opened from Episode 08 dynamic search
        </footer>
    </div>
</div>
