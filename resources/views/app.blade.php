<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@800&display=swap" rel="stylesheet">


    <link rel="icon" type="image/png" sizes="16x16" href="{{ url('favicons/aiku-favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('favicons/aiku-favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ url('favicons/aiku-favicon-48x48.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('favicons/aiku-favicon-180x180.png') }}">


    @routes
    @vite('resources/js/app.js')
    @inertiaHead
</head>
<body class="font-sans antialiased">
@inertia
</body>
</html>
