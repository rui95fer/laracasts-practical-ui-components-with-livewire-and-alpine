<div
    x-data="{
        toasts: [],
        nextId: 0,
        add(event) {
            const id = ++this.nextId;
            const types = ['success', 'warning', 'info', 'error'];

            this.toasts.push({
                id,
                message: event.detail.message,
                type: types.includes(event.detail.type) ? event.detail.type : 'info',
                show: true,
            });

            setTimeout(() => this.dismiss(id), 6000);
        },
        dismiss(id) {
            const toast = this.toasts.find((toast) => toast.id === id);

            if (! toast || ! toast.show) {
                return;
            }

            toast.show = false;

            setTimeout(() => {
                const index = this.toasts.findIndex((toast) => toast.id === id);

                if (index !== -1) {
                    this.toasts.splice(index, 1);
                }
            }, 500);
        },
    }"
    x-on:toast.window="add($event)"
    class="pointer-events-none fixed top-5 right-5 z-50 flex w-[min(24rem,calc(100vw-2.5rem))] flex-col-reverse gap-3"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            class="pointer-events-auto flex items-start justify-between gap-4 border px-4 py-3 text-sm shadow-lg shadow-zinc-900/10 dark:shadow-black/30"
            :class="{
                'animate-fade-in-down': toast.show,
                'animate-fade-out-up': ! toast.show,
                'border-emerald-500 bg-emerald-50 text-emerald-900 dark:border-emerald-400/50 dark:bg-emerald-950 dark:text-emerald-100': toast.type === 'success',
                'border-amber-500 bg-amber-50 text-amber-900 dark:border-amber-400/50 dark:bg-amber-950 dark:text-amber-100': toast.type === 'warning',
                'border-sky-500 bg-sky-50 text-sky-900 dark:border-sky-400/50 dark:bg-sky-950 dark:text-sky-100': toast.type === 'info',
                'border-red-500 bg-red-50 text-red-900 dark:border-red-400/50 dark:bg-red-950 dark:text-red-100': toast.type === 'error',
            }"
            role="status"
        >
            <span x-text="toast.message"></span>
            <button
                type="button"
                x-on:click="dismiss(toast.id)"
                class="shrink-0 text-current/60 transition hover:text-current"
                aria-label="Dismiss notification"
            >
                X
            </button>
        </div>
    </template>
</div>
