<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access denied</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100">
    <div class="max-w-md rounded-lg bg-white p-8 text-center shadow">
        <h1 class="mb-2 text-2xl font-bold text-gray-900">Access denied</h1>
        <p class="mb-6 text-gray-600">
            You do not have permission to access this page.
        </p>
        @auth
            <a
                href="{{ route('purchases.index') }}"
                class="inline-block rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
            >
                Go to purchases
            </a>
        @else
            <a
                href="{{ route('login') }}"
                class="inline-block rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
            >
                Login
            </a>
        @endauth
    </div>
</body>
</html>
