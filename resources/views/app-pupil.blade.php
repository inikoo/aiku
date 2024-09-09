<!DOCTYPE html>
<html class="h-full"  lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <!-- <meta name="csrf-token" content="{{ csrf_token() }}"> -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @env(['staging', 'production'])
        @include('new-relic', ['appID' => Config::get('new-relic.application_id.pupil')])
        @endenv
        <title inertia>{{ config('app.name', 'Pupil') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fira-sans:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="16x16" href="{{ url('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ url('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="48x48" href="{{ url('favicons/favicon-48x48.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ url('favicons/favicon-180x180.png') }}">

        <!-- Scripts -->
        @routes('shopify')
        {{Vite::useHotFile('pupil.hot')->useBuildDirectory('pupil')->withEntryPoints(['resources/js/app-pupil.js'])}}
        @inertiaHead
    </head>
    <body class="font-sans antialiased h-full text-slate-700">
        @if(\Osiset\ShopifyApp\Util::getShopifyConfig('appbridge_enabled'))
            <script src="https://unpkg.com/@shopify/app-bridge{{ \Osiset\ShopifyApp\Util::getShopifyConfig('appbridge_version') ? '@'.config('shopify-app.appbridge_version') : '' }}"></script>
            <script src="https://unpkg.com/@shopify/app-bridge-utils{{ \Osiset\ShopifyApp\Util::getShopifyConfig('appbridge_version') ? '@'.config('shopify-app.appbridge_version') : '' }}"></script>
            <script
                @if(\Osiset\ShopifyApp\Util::getShopifyConfig('turbo_enabled'))
                    data-turbolinks-eval="false"
                @endif
            >
                var AppBridge = window['app-bridge'];
                var actions = AppBridge.actions;
                var utils = window['app-bridge-utils'];
                var createApp = AppBridge.default;
                var app = createApp({
                    apiKey: "{{ \Osiset\ShopifyApp\Util::getShopifyConfig('api_key', $shopDomain ?? Auth::user()->name ) }}",
                    shopOrigin: "{{ $shopDomain ?? Auth::user()->name }}",
                    host: "{{ \Request::get('host') }}",
                    forceRedirect: true,
                });
            </script>

            @include('shopify-app::partials.token_handler')
            @include('shopify-app::partials.flash_messages')
        @endif

        @inertia
    </body>
</html>
