<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.editor')]
#[Title('Episode 03 - Toast Notifications')]
class extends Component {
    public string $displayName = 'Taylor';

    public function save(): void
    {
        $this->validate();

        $this->dispatch(
            'toast',
            message: 'Display name updated',
            type: 'success',
        );
    }

    public function showToast(string $type): void
    {
        $messages = [
            'success' => 'Everything worked as expected.',
            'warning' => 'This action needs your attention.',
            'info' => 'Here is a little more context.',
            'error' => 'Something went wrong. Try again.',
        ];

        if (!array_key_exists($type, $messages)) {
            return;
        }

        $this->dispatch('toast', message: $messages[$type], type: $type);
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'displayName' => ['required', 'string', 'max:80'],
        ];
    }
};
?>

<div class="min-h-screen bg-stone-100 px-6 py-8 text-zinc-950 sm:px-10 lg:px-16 dark:bg-zinc-950 dark:text-zinc-100">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-5xl flex-col">
        <header
            class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-900/10 pb-5 dark:border-white/10">
            <div>
                <a
                    href="{{ route('home') }}"
                    class="text-sm font-semibold tracking-[0.2em] text-zinc-900 uppercase dark:text-zinc-100"
                >
                    Practical UI Components
                </a>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Episode 03 / Toast notifications</p>
            </div>

            <a href="{{ route('home') }}"
               class="text-sm text-zinc-500 underline decoration-zinc-300 underline-offset-4 hover:text-zinc-950 dark:text-zinc-400 dark:decoration-zinc-700 dark:hover:text-white">
                All episodes
            </a>
        </header>

        <main class="grid flex-1 items-start gap-10 py-12 lg:grid-cols-[minmax(0,1fr)_15rem] lg:gap-16 lg:py-20">
            <section class="max-w-3xl">
                <p class="text-sm font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">Episode
                    03</p>
                <h1 class="mt-5 text-4xl leading-tight font-medium tracking-tight sm:text-6xl">Toast notifications</h1>
                <p class="mt-6 text-lg leading-8 text-zinc-600 dark:text-zinc-300">
                    Dispatch a named Livewire event, let Alpine own the short-lived state, and keep notification
                    presentation reusable.
                </p>

                <form wire:submit="save" class="mt-12 border-t border-zinc-900/10 pt-8 dark:border-white/10">
                    <label for="display-name" class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Display
                        name</label>
                    <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                        <input
                            id="display-name"
                            wire:model="displayName"
                            type="text"
                            class="min-w-0 flex-1 border border-zinc-900/15 bg-white px-4 py-3 text-zinc-950 outline-hidden transition focus:border-amber-600 focus:ring-2 focus:ring-amber-600/20 dark:border-white/15 dark:bg-zinc-900 dark:text-white dark:focus:border-amber-400 dark:focus:ring-amber-400/20"
                        >
                        <button
                            type="submit"
                            class="bg-zinc-950 px-5 py-3 text-sm font-medium text-white transition hover:bg-amber-700 disabled:cursor-wait disabled:opacity-50 dark:bg-white dark:text-zinc-950 dark:hover:bg-amber-300"
                            wire:loading.attr="disabled"
                        >
                            Save setting
                        </button>
                    </div>
                    @error('displayName')
                    <p class="mt-2 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </form>

                <div class="mt-12 border-t border-zinc-900/10 pt-8 dark:border-white/10">
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Try each notification type</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach (['success', 'warning', 'info', 'error'] as $type)
                            <button
                                type="button"
                                wire:click="showToast('{{ $type }}')"
                                class="border border-zinc-900/15 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:border-amber-700 hover:text-amber-700 dark:border-white/15 dark:text-zinc-300 dark:hover:border-amber-400 dark:hover:text-amber-400"
                            >
                                {{ ucfirst($type) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </section>

            <aside
                class="border-l border-zinc-900/10 pl-6 text-sm leading-6 text-zinc-500 dark:border-white/10 dark:text-zinc-400">
                <p class="font-medium text-zinc-900 dark:text-zinc-100">What is happening?</p>
                <p class="mt-2">The save action dispatches a typed <code
                        class="text-xs text-zinc-700 dark:text-zinc-300">toast</code> event.</p>
                <p class="mt-6">The reusable toast component listens on <code
                        class="text-xs text-zinc-700 dark:text-zinc-300">window</code>, stacks multiple messages, and
                    removes each one after its exit animation.</p>
            </aside>
        </main>

        <footer class="border-t border-zinc-900/10 pt-5 text-xs text-zinc-500 dark:border-white/10 dark:text-zinc-500">
            Livewire events + Alpine-owned notification state
        </footer>
    </div>
</div>
