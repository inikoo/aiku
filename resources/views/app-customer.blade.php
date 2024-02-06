<!DOCTYPE html>
<html class="h-full"  lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @env(['staging', 'production'])
        @include('new-relic', ['appID' => Config::get('new-relic.application_id.customer')])
        @endenv
        <title inertia>{{ config('app.name', 'Aiku') }}</title>
        <link rel="icon" type="image/png" sizes="16x16" href="{{ url('favicons/aiku-favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ url('favicons/aiku-favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="48x48" href="{{ url('favicons/aiku-favicon-48x48.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ url('favicons/aiku-favicon-180x180.png') }}">


        <!-- Scripts -->
        @routes('customer')
        {{Vite::useHotFile('customer.hot')->useBuildDirectory('customer')->withEntryPoints(['resources/js/app-customer.js'])}}
        @inertiaHead
    </head>
    <body class="font-sans antialiased h-full">
        @inertia
    </body>
</html>
