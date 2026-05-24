<div>
    <h1 class="mb-2 text-2xl font-bold text-gray-900">Run migrations</h1>
    <p class="mb-6 text-sm text-gray-600">
        Admin only. Runs <code class="rounded bg-gray-200 px-1">php artisan migrate --force</code>.
    </p>

    <button
        type="button"
        wire:click="run"
        wire:confirm="Run pending database migrations?"
        class="rounded bg-amber-600 px-4 py-2 text-white hover:bg-amber-700"
    >
        Run migrations
    </button>

    @if ($output)
        <pre class="mt-6 overflow-x-auto rounded border border-gray-300 bg-gray-900 p-4 text-sm text-green-400">{{ $output }}</pre>
    @endif
</div>
