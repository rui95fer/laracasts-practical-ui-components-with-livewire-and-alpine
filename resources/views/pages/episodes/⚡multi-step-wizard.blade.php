<?php

use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Session;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.editor')]
#[Title('Episode 04 - Build a Multi-Step Wizard')]
class extends Component
{
    #[Session]
    public string $name = '';

    #[Session]
    public string $category = '';

    #[Session]
    public string $description = '';

    #[Session]
    public string $price = '';

    #[Session]
    public string $url = '';

    #[Session]
    public int $currentStep = 1;

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
        if ($this->currentStep !== 3 || ! in_array($step, [1, 2], true)) {
            return;
        }

        $this->transition('backward');
        $this->currentStep = $step;
    }

    public function submit(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20'],
            'price' => ['required', 'numeric', 'gt:0'],
            'url' => ['required', 'url'],
        ]);

        $this->reset();

        Flux::modal('create-product')->close();
        $this->dispatch('toast', message: 'Product created', type: 'success');
    }

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
};
?>

<div class="min-h-screen bg-stone-100 px-6 py-8 text-zinc-950 sm:px-10 lg:px-16 dark:bg-zinc-950 dark:text-zinc-100">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-5xl flex-col">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-900/10 pb-5 dark:border-white/10">
            <div>
                <p class="text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">
                    Episode 04
                </p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Build a multi-step wizard</p>
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
                    Episode 04
                </p>
                <h1 class="mt-5 text-4xl leading-tight font-medium tracking-tight sm:text-6xl">
                    Build a multi-step wizard
                </h1>
                <p class="mt-6 text-lg leading-8 text-zinc-600 dark:text-zinc-300">
                    Keep a long product form focused with step-specific validation, a preview screen, persisted state,
                    and directional transitions.
                </p>

                <div class="mt-12 border-t border-zinc-900/10 pt-8 dark:border-white/10">
                    <flux:modal.trigger name="create-product">
                        <flux:button variant="primary">Create product</flux:button>
                    </flux:modal.trigger>
                </div>
            </section>

            <aside class="border-l border-zinc-900/10 pl-6 text-sm leading-6 text-zinc-500 dark:border-white/10 dark:text-zinc-400">
                <p class="font-medium text-zinc-900 dark:text-zinc-100">Try it out</p>
                <p class="mt-2">Close the modal halfway through and open it again. The current step and entered values remain available.</p>
                <p class="mt-6">The form is intentionally demo-only: submitting resets the wizard and shows a toast instead of writing to a database.</p>
            </aside>
        </main>

        <footer class="border-t border-zinc-900/10 pt-5 text-xs text-zinc-500 dark:border-white/10 dark:text-zinc-500">
            Livewire state + Flux modal + directional view transitions
        </footer>
    </div>

    <flux:modal name="create-product" class="w-full max-w-2xl">
        <div class="max-h-[85vh] overflow-y-auto">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Create product</flux:heading>
                    <flux:subheading class="mt-1">Tell us about the product you want to launch.</flux:subheading>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between gap-4 border-b border-zinc-200 pb-4 dark:border-white/10">
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    Step {{ $currentStep }} of 3
                </p>

                <div class="flex items-center gap-2" aria-label="Wizard progress">
                    @foreach ([1, 2, 3] as $step)
                        <span
                            wire:key="progress-step-{{ $step }}"
                            @class([
                                'flex size-7 items-center justify-center rounded-full text-xs font-semibold',
                                'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950' => $currentStep === $step,
                                'bg-zinc-100 text-zinc-500 dark:bg-white/10 dark:text-zinc-400' => $currentStep !== $step,
                            ])
                        >
                            {{ $step }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="mt-6">
                @if ($currentStep === 1)
                    <div wire:key="wizard-step-1" wire:transition="form" class="space-y-6">
                        <flux:field>
                            <flux:label>Product name</flux:label>
                            <flux:input wire:model.live.debounce.300ms="name" placeholder="Acme Analytics" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Category</flux:label>
                            <flux:select wire:model.live="category" placeholder="Choose a category">
                                <flux:select.option value="saas">SaaS</flux:select.option>
                                <flux:select.option value="e-commerce">E-commerce</flux:select.option>
                                <flux:select.option value="services">Services</flux:select.option>
                            </flux:select>
                            <flux:error name="category" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Description</flux:label>
                            <flux:textarea wire:model.live.debounce.300ms="description" rows="5" placeholder="What problem does this product solve?" />
                            <flux:error name="description" />
                        </flux:field>
                    </div>
                @elseif ($currentStep === 2)
                    <div wire:key="wizard-step-2" wire:transition="form" class="space-y-6">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>Price</flux:label>
                                <flux:input wire:model.live.debounce.300ms="price" type="number" min="0" step="0.01" placeholder="49.00" />
                                <flux:error name="price" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Product URL</flux:label>
                                <flux:input wire:model.live.debounce.300ms="url" type="url" placeholder="https://example.com" />
                                <flux:error name="url" />
                            </flux:field>
                        </div>
                    </div>
                @else
                    <div wire:key="wizard-step-3" wire:transition="form" class="space-y-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <flux:heading size="sm">Product details</flux:heading>
                                <flux:subheading class="mt-1">Review the first step before submitting.</flux:subheading>
                            </div>

                            <flux:button type="button" variant="ghost" size="sm" wire:click="goToStep(1)">
                                Edit
                            </flux:button>
                        </div>

                        <dl class="grid gap-4 rounded-lg border border-zinc-200 p-4 text-sm dark:border-white/10">
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">Product name</dt>
                                <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $name }}</dd>
                            </div>
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">Category</dt>
                                <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $category }}</dd>
                            </div>
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">Description</dt>
                                <dd class="mt-1 whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $description }}</dd>
                            </div>
                        </dl>

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <flux:heading size="sm">Pricing and link</flux:heading>
                                <flux:subheading class="mt-1">These details will be shown with the product.</flux:subheading>
                            </div>

                            <flux:button type="button" variant="ghost" size="sm" wire:click="goToStep(2)">
                                Edit
                            </flux:button>
                        </div>

                        <dl class="grid gap-4 rounded-lg border border-zinc-200 p-4 text-sm sm:grid-cols-2 dark:border-white/10">
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">Price</dt>
                                <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $price }}</dd>
                            </div>
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">Product URL</dt>
                                <dd class="mt-1 truncate">
                                    <a
                                        href="{{ $url }}"
                                        target="_blank"
                                        rel="noreferrer"
                                        class="text-amber-700 underline underline-offset-4 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300"
                                    >
                                        {{ $url }}
                                    </a>
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif
            </div>

            <div class="mt-8 flex items-center justify-between gap-3 border-t border-zinc-200 pt-6 dark:border-white/10">
                <div>
                    @if ($currentStep > 1)
                        <flux:button
                            type="button"
                            variant="ghost"
                            wire:click="previousStep"
                            wire:loading.attr="disabled"
                            wire:target="previousStep"
                        >
                            Back
                        </flux:button>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <flux:modal.close>
                        <flux:button type="button" variant="filled">Cancel</flux:button>
                    </flux:modal.close>

                    @if ($currentStep < 3)
                        <flux:button
                            type="button"
                            variant="primary"
                            wire:click="nextStep"
                            wire:loading.attr="disabled"
                            wire:target="nextStep"
                        >
                            Next
                        </flux:button>
                    @else
                        <flux:button
                            type="button"
                            variant="primary"
                            wire:click="submit"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                        >
                            Create product
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    </flux:modal>
</div>
