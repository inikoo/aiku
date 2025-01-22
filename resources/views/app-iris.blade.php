<!DOCTYPE html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $currentPath = request()->path();
            $webpages = request()->get('website')->webpages ?? [];
            $matchedPage = collect($webpages)->firstWhere('url', $currentPath);
        @endphp

        <title inertia></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fira-sans:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="16x16" href="{{ request()->get('website')->imageSources(16, 16)['original'] ?? url('favicons/iris-favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ request()->get('website')->imageSources(32, 32)['original'] ?? url('favicons/iris-favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="48x48" href="{{ request()->get('website')->imageSources(48, 48)['original'] ?? url('favicons/iris-favicon.ico') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ request()->get('website')->imageSources(180, 180)['original'] ?? url('favicons/iris-apple-favicon-180x180.png') }}">



        @if (config('app.env', 'production') === 'staging')
            <!-- Noindex untuk staging environment -->
            <meta name="robots" content="noindex">
        @endif

        <!-- Scripts -->
        @routes('iris')
        {{ Vite::useHotFile('iris.hot')->useBuildDirectory('iris')->withEntryPoints(['resources/js/app-iris.js']) }}
        @inertiaHead
    </head>
    <body class="font-sans antialiased h-full">
        @inertia
    </body>
</html>
