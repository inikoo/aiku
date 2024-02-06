<!DOCTYPE html>
<html class="h-full"  lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @env(['staging', 'production'])
        @include('new-relic', ['appID' => Config::get('new-relic.application_id.grp')])
        @endenv
        <title inertia>{{ config('app.name', 'Aiku') }}</title>
        <link rel="icon" type="image/png" href="{{ url('favicons/favicon.png') }}">
        <link rel="icon" href="{{ url('favicons/favicon.svg') }}" type="image/svg+xml">
        @routes('grp')
        {{Vite::useHotFile('grp.hot')->useBuildDirectory('grp')->withEntryPoints(['resources/js/app-grp.js'])}}
        @inertiaHead
    </head>
    <body class="font-sans antialiased h-full">
        @inertia
    </body>
</html>
