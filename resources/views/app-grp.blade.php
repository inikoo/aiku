<!DOCTYPE html>
<html class="h-full"  lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @env(['staging', 'production'])
        @include('new-relic', ['appID' => Config::get('new-relic.application_id.grp')])
        @endenv
        <title inertia>{{ config('app.name', 'Aiku') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fira-sans:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
        
        <link rel="icon" type="image/png" href="{{ url('favicons/favicon.png') }}">
        <link rel="icon" href="{{ url('favicons/favicon.svg') }}" type="image/svg+xml">
        @routes('grp')
        {{Vite::useHotFile('grp.hot')->useBuildDirectory('grp')->withEntryPoints(['resources/js/app-grp.js'])}}
        @inertiaHead
    </head>
    <body class="font-sans antialiased h-full text-slate-700">
        @inertia
    </body>
</html>
