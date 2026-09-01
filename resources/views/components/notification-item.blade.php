@props([
    'notification',
])

@php
    $actor = $notification->actor;
@endphp

<a
    href="{{ $notification->url ?? '#' }}"
    x-data="{ read: @js($notification->read_at !== null) }"
    x-on:notifications-marked-read.window="read = true"
    x-on:click.prevent="
        if (! read) {
            read = true;
            $wire.markAsRead({{ $notification->id }}).then(() => window.location.assign($el.href));
        } else {
            window.location.assign($el.href);
        }
    "
    :class="read ? 'bg-white dark:bg-zinc-900' : 'bg-blue-50 dark:bg-blue-950/30'"
    {{ $attributes->merge(['class' => 'group relative flex cursor-pointer gap-3 px-4 py-4 transition hover:bg-zinc-50 focus:outline-hidden focus:ring-2 focus:ring-inset focus:ring-amber-600 dark:hover:bg-zinc-800/70 dark:focus:ring-amber-400']) }}
>
    <div class="relative shrink-0">
        <div class="flex size-10 items-center justify-center rounded-full bg-amber-100 text-sm font-semibold text-amber-950 dark:bg-amber-950 dark:text-amber-100">
            {{ $actor?->initials() ?? '?' }}
        </div>

        <div class="absolute -right-1 -bottom-1 flex size-5 items-center justify-center rounded-full border-2 border-white bg-zinc-100 text-zinc-600 dark:border-zinc-900 dark:bg-zinc-800 dark:text-zinc-300">
            @switch($notification->type)
                @case('comment')
                    <flux:icon.chat-bubble-left class="size-3" />
                    @break
                @case('like')
                    <flux:icon.heart class="size-3" />
                    @break
                @default
                    <flux:icon.user-plus class="size-3" />
            @endswitch
        </div>
    </div>

    <div class="min-w-0 flex-1">
        <p class="text-sm leading-5 text-zinc-700 dark:text-zinc-300">
            <span class="font-medium text-zinc-950 dark:text-zinc-100">{{ $actor?->name ?? 'Someone' }}</span>
            {{ $notification->message }}
        </p>
        <time datetime="{{ $notification->created_at->toIso8601String() }}" class="mt-1 block text-xs text-zinc-400 dark:text-zinc-500">
            {{ $notification->created_at->diffForHumans() }}
        </time>
    </div>

    <span
        x-cloak
        x-show="! read"
        class="mt-1.5 size-2 shrink-0 rounded-full bg-blue-600 dark:bg-blue-400"
    ></span>
</a>
