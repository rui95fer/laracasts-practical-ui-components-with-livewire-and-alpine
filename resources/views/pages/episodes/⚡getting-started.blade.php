<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.editor')]
#[Title('Episode 01 - Getting Started')]
class extends Component
{
};
?>

<div class="min-h-screen bg-stone-100 px-6 py-8 text-zinc-950 sm:px-10 lg:px-16 dark:bg-zinc-950 dark:text-zinc-100">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-5xl flex-col">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-900/10 pb-5 dark:border-white/10">
            <div>
                <a href="{{ route('home') }}" class="text-sm font-semibold tracking-[0.2em] text-zinc-900 uppercase dark:text-zinc-100">
                    Practical UI Components
                </a>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Episode 01 / Getting started</p>
            </div>

            <a href="{{ route('home') }}" class="text-sm text-zinc-500 underline decoration-zinc-300 underline-offset-4 hover:text-zinc-950 dark:text-zinc-400 dark:decoration-zinc-700 dark:hover:text-white">
                All episodes
            </a>
        </header>

        <main class="flex-1 py-12 sm:py-20">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">Episode 01</p>
                <h1 class="mt-5 text-4xl leading-tight font-medium tracking-tight sm:text-6xl">Getting started</h1>
                <p class="mt-6 text-lg leading-8 text-zinc-600 dark:text-zinc-300">
                    Learn the division of responsibility that makes Livewire and Alpine work well together before building larger components.
                </p>
            </div>

            <div class="mt-12 grid gap-px overflow-hidden border border-zinc-900/10 bg-zinc-900/10 md:grid-cols-2 dark:border-white/10 dark:bg-white/10">
                <article class="bg-white p-6 sm:p-8 dark:bg-zinc-900">
                    <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">01</p>
                    <h2 class="mt-5 text-xl font-medium">Review generated code</h2>
                    <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                        AI can produce working code, but framework knowledge is still needed to evaluate queries, edge cases, APIs, and quality before shipping.
                    </p>
                </article>

                <article class="bg-white p-6 sm:p-8 dark:bg-zinc-900">
                    <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">02</p>
                    <h2 class="mt-5 text-xl font-medium">Give each tool a clear job</h2>
                    <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                        Keep server-backed state and actions in Livewire, then use Alpine for small client-side interactions around that state.
                    </p>
                </article>

                <article class="bg-white p-6 sm:p-8 dark:bg-zinc-900">
                    <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">03</p>
                    <h2 class="mt-5 text-xl font-medium">Start with focused components</h2>
                    <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                        Inline editing, toast notifications, and infinite scroll are useful starting points because each component solves a focused UI problem.
                    </p>
                </article>

                <article class="bg-white p-6 sm:p-8 dark:bg-zinc-900">
                    <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">04</p>
                    <h2 class="mt-5 text-xl font-medium">Keep components together</h2>
                    <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                        Livewire 4 single-file components keep a component's PHP state and Blade template close enough to reason about as one unit.
                    </p>
                </article>
            </div>

            <blockquote class="mt-12 border-l-2 border-amber-600 pl-6 text-lg leading-8 text-zinc-700 dark:border-amber-400 dark:text-zinc-300">
                Understand how Livewire and Alpine divide responsibilities so you can build, review, and optimize UI components instead of blindly accepting generated code.
            </blockquote>
        </main>

        <footer class="border-t border-zinc-900/10 pt-5 text-xs text-zinc-500 dark:border-white/10 dark:text-zinc-500">
            Next: <a href="{{ route('episodes.inline-editing') }}" class="underline underline-offset-4 hover:text-zinc-950 dark:hover:text-white">Introducing inline editing</a>
        </footer>
    </div>
</div>
