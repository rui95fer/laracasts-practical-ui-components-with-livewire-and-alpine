<?php

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.editor')]
#[Title('Episode 07 - Notification Center')]
class extends Component
{
    private const DEMO_USER_EMAIL = 'test@example.com';

    public ?int $latestLoadedId = null;

    public int $unreadCount = 0;

    #[Computed]
    public function notifications(): Collection
    {
        $query = $this->notificationQuery()
            ->with('actor')
            ->latest('id');

        if ($this->latestLoadedId === null) {
            return $query->limit(100)->get();
        }

        return $query
            ->where('id', '>', $this->latestLoadedId)
            ->get();
    }

    public function mount(): void
    {
        $this->latestLoadedId = $this->notifications->first()?->id;
        $this->updateUnreadCount();
    }

    public function checkForNew(): void
    {
        $latestId = $this->notificationQuery()->max('id');
        $this->updateUnreadCount();

        if ($latestId === null) {
            return;
        }

        $latestId = (int) $latestId;

        if ($this->latestLoadedId !== null && $latestId <= $this->latestLoadedId) {
            return;
        }

        $this->renderIsland('notifications', mode: 'prepend');
        $this->latestLoadedId = $latestId;
    }

    #[Renderless]
    public function markAsRead(int $notificationId): void
    {
        $this->notificationQuery()
            ->whereKey($notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->updateUnreadCount();
    }

    #[Renderless]
    public function markAllAsRead(): void
    {
        $this->notificationQuery()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->unreadCount = 0;
        $this->dispatch('notifications-marked-read');
    }

    #[Renderless]
    public function simulateIncomingNotification(): void
    {
        $demoUser = $this->demoUser();
        $actor = User::query()
            ->where('id', '!=', $demoUser->id)
            ->inRandomOrder()
            ->first() ?? $demoUser;

        Notification::factory()
            ->for($demoUser, 'user')
            ->create([
                'actor_id' => $actor->id,
            ]);
    }

    /**
     * @return Builder<Notification>
     */
    private function notificationQuery(): Builder
    {
        return Notification::query()->forUser($this->demoUser());
    }

    private function updateUnreadCount(): void
    {
        $this->unreadCount = $this->notificationQuery()
            ->whereNull('read_at')
            ->count();
    }

    private function demoUser(): User
    {
        return User::query()
            ->where('email', self::DEMO_USER_EMAIL)
            ->firstOrFail();
    }
};
?>

<div wire:poll.10s="checkForNew" class="min-h-screen bg-stone-100 px-6 py-8 text-zinc-950 sm:px-10 lg:px-16 dark:bg-zinc-950 dark:text-zinc-100">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-5xl flex-col">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-900/10 pb-5 dark:border-white/10">
            <div>
                <p class="text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">
                    Episode 07
                </p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Notification center</p>
            </div>

            <div class="flex items-center gap-4">
                <a
                    href="{{ route('home') }}"
                    class="text-sm text-zinc-500 underline decoration-zinc-300 underline-offset-4 hover:text-zinc-950 dark:text-zinc-400 dark:decoration-zinc-700 dark:hover:text-white"
                >
                    All episodes
                </a>

                <div
                    x-data="{ open: false }"
                    x-on:click.outside="open = false"
                    x-on:keydown.escape.window="open = false"
                    class="relative"
                >
                    <button
                        type="button"
                        x-on:click="open = ! open"
                        x-bind:aria-expanded="open"
                        aria-controls="notification-menu"
                        class="relative inline-flex size-10 items-center justify-center border border-zinc-900/15 bg-white text-zinc-700 transition hover:border-amber-600 hover:text-amber-800 focus:outline-hidden focus:ring-2 focus:ring-amber-600/20 dark:border-white/15 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-amber-400 dark:hover:text-amber-200 dark:focus:ring-amber-400/20"
                    >
                        <span class="sr-only">Toggle notifications</span>
                        <flux:icon.bell class="size-5" />
                        <span
                            x-cloak
                            x-show="$wire.unreadCount > 0"
                            class="absolute -top-2 -right-2 inline-flex min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 py-0.5 text-[10px] leading-none font-semibold text-white"
                        >
                            <span x-text="$wire.unreadCount > 99 ? '99+' : $wire.unreadCount"></span>
                        </span>
                    </button>

                    <div
                        id="notification-menu"
                        x-cloak
                        x-show="open"
                        x-transition.origin.top.right
                        class="absolute right-0 z-30 mt-3 w-[min(24rem,calc(100vw-2rem))] overflow-hidden border border-zinc-900/10 bg-white shadow-2xl shadow-zinc-900/10 dark:border-white/10 dark:bg-zinc-900 dark:shadow-black/30"
                    >
                        <div class="flex items-start justify-between gap-4 border-b border-zinc-900/10 px-4 py-4 dark:border-white/10">
                            <div>
                                <p class="font-medium text-zinc-950 dark:text-white">Notifications</p>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">New activity appears every 10 seconds.</p>
                            </div>

                            <button
                                type="button"
                                wire:click="markAllAsRead"
                                class="shrink-0 text-xs font-medium text-blue-600 transition hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                Mark all as read
                            </button>
                        </div>

                        <div class="max-h-[28rem] overflow-y-auto divide-y divide-zinc-900/10 dark:divide-white/10">
                            @island(name: 'notifications')
                                @forelse ($this->notifications as $notification)
                                    <div class="grid animate-slide-down">
                                        <div class="min-h-0 overflow-hidden">
                                            <x-notification-item
                                                :notification="$notification"
                                                wire:key="notification-{{ $notification->id }}"
                                            />
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-6 py-12 text-center">
                                        <flux:icon.bell class="mx-auto size-8 text-zinc-300 dark:text-zinc-600" />
                                        <p class="mt-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">No notifications yet</p>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">New activity will show up here.</p>
                                    </div>
                                @endforelse
                            @endisland
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="grid flex-1 items-start gap-10 py-12 lg:grid-cols-[minmax(0,1fr)_15rem] lg:gap-16 lg:py-20">
            <section class="max-w-3xl">
                <p class="text-sm font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-400">
                    Episode 07
                </p>
                <h1 class="mt-5 text-4xl leading-tight font-medium tracking-tight sm:text-6xl">Notification center</h1>
                <p class="mt-6 text-lg leading-8 text-zinc-600 dark:text-zinc-300">
                    Keep an activity dropdown current with Livewire polling, while Alpine makes read states and new-item animations feel immediate.
                </p>

                <div class="mt-12 border-t border-zinc-900/10 pt-8 dark:border-white/10">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-zinc-950 dark:text-white">Live activity simulator</p>
                            <p class="mt-1 max-w-md text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                Create a notification without refreshing the list, then let the next poll prepend it to the dropdown.
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="simulateIncomingNotification"
                            wire:loading.attr="disabled"
                            wire:target="simulateIncomingNotification"
                            class="inline-flex items-center gap-2 border border-amber-600 bg-amber-100 px-4 py-2.5 text-sm font-medium text-amber-950 transition hover:bg-amber-200 disabled:cursor-wait disabled:opacity-60 dark:border-amber-400 dark:bg-amber-950 dark:text-amber-100 dark:hover:bg-amber-900"
                        >
                            <span wire:loading.remove wire:target="simulateIncomingNotification">Simulate notification</span>
                            <span wire:loading wire:target="simulateIncomingNotification">Creating...</span>
                        </button>
                    </div>

                    <div class="mt-8 grid gap-3 sm:grid-cols-3">
                        <div class="border border-zinc-900/10 bg-white p-4 dark:border-white/10 dark:bg-zinc-900">
                            <p class="text-xs font-semibold tracking-[0.15em] text-zinc-400 uppercase dark:text-zinc-500">Poll interval</p>
                            <p class="mt-2 text-lg font-medium text-zinc-950 dark:text-white">10 seconds</p>
                        </div>
                        <div class="border border-zinc-900/10 bg-white p-4 dark:border-white/10 dark:bg-zinc-900">
                            <p class="text-xs font-semibold tracking-[0.15em] text-zinc-400 uppercase dark:text-zinc-500">Unread now</p>
                            <p class="mt-2 text-lg font-medium text-zinc-950 dark:text-white" x-text="$wire.unreadCount"></p>
                        </div>
                        <div class="border border-zinc-900/10 bg-white p-4 dark:border-white/10 dark:bg-zinc-900">
                            <p class="text-xs font-semibold tracking-[0.15em] text-zinc-400 uppercase dark:text-zinc-500">List updates</p>
                            <p class="mt-2 text-lg font-medium text-zinc-950 dark:text-white">Island prepend</p>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="border-l border-zinc-900/10 pl-6 text-sm leading-6 text-zinc-500 dark:border-white/10 dark:text-zinc-400">
                <p class="font-medium text-zinc-950 dark:text-zinc-100">Try it out</p>
                <p class="mt-2">Open the bell to inspect the initial notifications and unread badge.</p>
                <p class="mt-6">Click the simulator, wait for the next poll, and watch the new item slide into the top of the island.</p>
                <p class="mt-6">Click an item to mark it read, or use the bulk action to synchronize every row through an Alpine event.</p>
            </aside>
        </main>

        <footer class="border-t border-zinc-900/10 pt-5 text-xs text-zinc-500 dark:border-white/10 dark:text-zinc-500">
            Livewire polling + islands + Alpine read state
        </footer>
    </div>
</div>
