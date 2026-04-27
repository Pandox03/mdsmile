@props(['title' => config('app.name')])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-black text-zinc-100 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12">
        <a href="/" class="mb-8">
            <img src="{{ asset('images/mdsmile-logo.png') }}" alt="MdSmile" class="h-16 w-auto object-contain" />
        </a>
        <main class="w-full max-w-md">
            {{ $slot }}
        </main>
        <p class="mt-8 text-sm text-zinc-600">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </p>
    </div>
    @livewireScripts
</body>
</html>
