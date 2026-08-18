<?php

use App\Models\Meeting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.editor')]
#[Title('Meeting notes')]
class extends Component {
    public Meeting $meeting;

    public string $title = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->meeting = Meeting::query()->firstOrFail();
        $this->title = $this->meeting->title ?? '';
        $this->notes = $this->meeting->notes ?? '';
    }

    public function updated(string $property): void
    {
        if (!in_array($property, ['title', 'notes'], true)) {
            return;
        }

        $this->validateOnly($property);

        $this->meeting->update([$property => $this->{$property}]);
        $this->dispatch('meeting-saved');
    }

    /**
     * Get the validation rules for the editable meeting fields.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
};
?>

<div class="min-h-screen bg-stone-100 px-6 py-8 text-zinc-950 sm:px-10 lg:px-16 dark:bg-zinc-950 dark:text-zinc-100">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-5xl flex-col">
        <header class="flex items-center justify-between gap-4 border-b border-zinc-900/10 pb-5 dark:border-white/10">
            <div>
                <p class="text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">
                    Episode 02
                </p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Introducing inline editing</p>
            </div>

            <a href="{{ route('home') }}" class="text-sm text-zinc-500 underline decoration-zinc-300 underline-offset-4 hover:text-zinc-950 dark:text-zinc-400 dark:decoration-zinc-700 dark:hover:text-white">
                All episodes
            </a>

            <div
                x-data="{ saved: false, timeout: null }"
                x-on:meeting-saved.window="
                    clearTimeout(timeout);
                    saved = true;
                    timeout = setTimeout(() => saved = false, 3000);
                "
                class="flex h-6 items-center justify-end text-sm text-zinc-500 dark:text-zinc-400"
                aria-live="polite"
            >
                <span wire:loading.delay wire:target="title,notes">Saving...</span>
                <span x-cloak x-show="saved" x-transition>Saved</span>
            </div>
        </header>

        <main class="grid flex-1 items-start gap-10 py-12 lg:grid-cols-[minmax(0,1fr)_15rem] lg:gap-16 lg:py-20">
            <section class="max-w-3xl">
                <p class="mb-5 text-sm font-medium text-zinc-500 dark:text-zinc-400">Meeting notes</p>

                <label for="meeting-title" class="sr-only">Meeting title</label>
                <textarea
                    id="meeting-title"
                    wire:model.live.debounce.300ms="title"
                    wire:ignore.self
                    x-data="{ resize() { $el.style.height = 'auto'; $el.style.height = `${$el.scrollHeight}px`; } }"
                    x-init="resize()"
                    x-on:input="resize()"
                    rows="1"
                    placeholder="Click here to add a title"
                    aria-invalid="{{ $errors->has('title') ? 'true' : 'false' }}"
                    class="w-full resize-none overflow-hidden border-0 bg-transparent p-0 text-4xl leading-tight font-medium tracking-tight text-zinc-950 placeholder:text-zinc-400 focus:outline-hidden focus:ring-0 sm:text-5xl dark:text-white dark:placeholder:text-zinc-600"
                ></textarea>
                @error('title')
                <p class="mt-2 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
                @enderror

                <div class="mt-10 border-t border-zinc-900/10 pt-8 dark:border-white/10">
                    <label for="meeting-notes" class="sr-only">Meeting notes</label>
                    <textarea
                        id="meeting-notes"
                        wire:model.live.debounce.300ms="notes"
                        wire:ignore.self
                        x-data="{ resize() { $el.style.height = 'auto'; $el.style.height = `${$el.scrollHeight}px`; } }"
                        x-init="resize()"
                        x-on:input="resize()"
                        rows="4"
                        placeholder="Add notes, decisions, and next steps..."
                        aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}"
                        class="w-full resize-none overflow-hidden border-0 bg-transparent p-0 text-lg leading-8 text-zinc-700 placeholder:text-zinc-400 focus:outline-hidden focus:ring-0 dark:text-zinc-300 dark:placeholder:text-zinc-600"
                    ></textarea>
                    @error('notes')
                    <p class="mt-2 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <aside
                class="border-l border-zinc-900/10 pl-6 text-sm leading-6 text-zinc-500 dark:border-white/10 dark:text-zinc-400">
                <p class="font-medium text-zinc-900 dark:text-zinc-100">Try it out</p>
                <p class="mt-2">Edit either field. Changes are saved automatically after you pause typing.</p>
                <p class="mt-6">Livewire owns the data. Alpine handles the small client-side detail: resizing the
                    textareas and showing this status.</p>
            </aside>
        </main>

        <footer class="border-t border-zinc-900/10 pt-5 text-xs text-zinc-500 dark:border-white/10 dark:text-zinc-500">
            Livewire autosave + Alpine textarea resizing
        </footer>
    </div>
</div>
