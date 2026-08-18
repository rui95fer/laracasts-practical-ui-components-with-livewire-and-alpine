<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.editor')]
#[Title('Episodes')]
class extends Component
{
    /**
     * @var array<string, array{number: string, title: string, description: string, route: string}>
     */
    public array $episodes = [];

    public function mount(): void
    {
        $this->episodes = config('episodes', []);
    }
};
?>

<div class="min-h-screen bg-stone-100 px-6 py-8 text-zinc-950 sm:px-10 lg:px-16 dark:bg-zinc-950 dark:text-zinc-100">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-5xl flex-col">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-900/10 pb-5 dark:border-white/10">
            <a href="{{ route('home') }}" class="text-sm font-semibold tracking-[0.2em] text-zinc-900 uppercase dark:text-zinc-100">
                Practical UI Components
            </a>

            <p class="text-sm text-zinc-500 dark:text-zinc-400">Livewire + Alpine</p>
        </header>

        <main class="flex-1 py-12 sm:py-20">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">
                    Course notes
                </p>
                <h1 class="mt-5 text-4xl leading-tight font-medium tracking-tight sm:text-6xl">
                    Build interfaces that know where state belongs.
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-zinc-600 dark:text-zinc-300">
                    Work through the episodes by the UI problem you need to solve, then take the component pattern with you.
                </p>
            </div>

            <div class="mt-12 grid gap-4 md:grid-cols-3">
                @foreach ($episodes as $slug => $episode)
                    <a
                        wire:key="episode-{{ $slug }}"
                        href="{{ route($episode['route']) }}"
                        class="group flex min-h-72 flex-col justify-between border border-zinc-900/10 bg-white p-6 transition hover:-translate-y-1 hover:border-amber-700/50 hover:shadow-xl hover:shadow-zinc-900/5 dark:border-white/10 dark:bg-zinc-900 dark:hover:border-amber-400/50 dark:hover:shadow-black/20"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-4 text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">
                                <span>Episode {{ $episode['number'] }}</span>
                                <span class="text-zinc-400 transition group-hover:translate-x-1 dark:text-zinc-600">-&gt;</span>
                            </div>
                            <h2 class="mt-8 text-2xl leading-tight font-medium text-zinc-950 dark:text-zinc-100">
                                {{ $episode['title'] }}
                            </h2>
                            <p class="mt-4 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                {{ $episode['description'] }}
                            </p>
                        </div>

                        <span class="mt-8 text-sm font-medium text-zinc-900 group-hover:text-amber-700 dark:text-zinc-100 dark:group-hover:text-amber-400">
                            Open episode
                        </span>
                    </a>
                @endforeach
            </div>
        </main>

        <footer class="border-t border-zinc-900/10 pt-5 text-xs text-zinc-500 dark:border-white/10 dark:text-zinc-500">
            Learn the pattern. Review the tradeoffs. Ship the useful part.
        </footer>
    </div>
</div>
