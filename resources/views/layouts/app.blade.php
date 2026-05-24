<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Purchase Entry' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-100">
    <nav class="border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('purchases.index') }}" class="text-lg font-semibold text-gray-900">
                    Purchase Entry
                </a>
                @auth
                    <a href="{{ route('purchases.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                        Purchases
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('purchases.create') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            New purchase
                        </a>
                        <a href="{{ route('admin.migrations') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            Run migrations
                        </a>
                    @endif
                @endauth
            </div>
            @auth
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">
                        {{ auth()->user()->name }}
                        <span class="rounded bg-gray-200 px-2 py-0.5 text-xs uppercase">
                            {{ auth()->user()->role->value }}
                        </span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                            Logout
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>

    <main class="mx-auto max-w-7xl p-6">
        @if (session('success'))
            <div class="mb-4 rounded border border-green-300 bg-green-100 p-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded border border-red-300 bg-red-100 p-3 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
