<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $page['props']['auth']['lang'] ?? substr(app()->getLocale(), 0, 2)) }}" dir="{{ $page['props']['auth']['layout_direction'] ?? 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Mutamark ERP') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <!-- Scripts -->
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        @routes
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
        <!-- Favicons & App Icons -->
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
                <!-- Progressive Web App (PWA) -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#4f46e5">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Mutamark ERP">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        <!-- Primary Meta Tags -->
        <meta name="description" content="Mutamark ERP is an enterprise management system offering seamless accounting, 3-way matching procurement, inventory management, and integrated Tanzanian payment gateways.">
        <meta name="keywords" content="mutamark, mutamark erp, enterprise resource planning, tanzania, procurement, 3-way matching, accounting, payment gateways, selcom, pesapal, lipa namba">
        
        <!-- Open Graph / Facebook / WhatsApp -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ config('app.name', 'Mutamark ERP') }}">
        <meta property="og:description" content="Mutamark ERP - Next-Generation Enterprise Resource Planning Suite">
        <meta property="og:image" content="{{ asset('meta_image.png') }}">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ config('app.name', 'Mutamark ERP') }}">
        <meta name="twitter:description" content="Mutamark ERP - Next-Generation Enterprise Resource Planning Suite">
        <meta name="twitter:image" content="{{ asset('meta_image.png') }}">

        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
        <style>
            #app-loader{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;background:#fff}
            #app-loader>div{display:flex;flex-direction:column;align-items:center;gap:1rem}
            #app-loader .spinner{position:relative;width:3rem;height:3rem}
            #app-loader .spinner>div:first-child{width:3rem;height:3rem;border:4px solid #e5e7eb;border-radius:50%;animation:spin 1s linear infinite;border-top-color:#2563eb}
            #app-loader .spinner>div:last-child{position:absolute;inset:0;width:3rem;height:3rem;border:4px solid transparent;border-radius:50%;animation:ping 1s cubic-bezier(0,0,.2,1) infinite;border-top-color:#60a5fa;opacity:.2}
            @keyframes spin{to{transform:rotate(360deg)}}
            @keyframes ping{75%,100%{transform:scale(2);opacity:0}}
        </style>
        <div id="app-loader">
            <div>
                <div class="spinner"><div></div><div></div></div>
                <div style="text-align:center">
                    <h3 style="font-size:1.125rem;font-weight:600;color:#374151">{{ __('Loading...') }}</h3>
                    <p style="font-size:0.875rem;color:#6b7280">{{ __('Please wait while we prepare your webapp...') }}</p>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const loader = document.getElementById('app-loader');
                let removed = false;
                const hideLoader = () => {
                    if (loader && !removed) {
                        removed = true;
                        loader.style.opacity = '0';
                        loader.style.transition = 'opacity 0.25s ease-out';
                        setTimeout(() => loader.remove(), 250);
                    }
                };
                const checkApp = () => {
                    const appEl = document.getElementById('app');
                    if (appEl && appEl.children.length > 0) {
                        hideLoader();
                    } else {
                        setTimeout(checkApp, 40);
                    }
                };
                checkApp();
                // Safety timeout: remove loader after at most 2 seconds regardless of state
                setTimeout(hideLoader, 2000);
            });
        </script>
            <!-- Unregister old service workers & purge CacheStorage -->
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function(registrations) {
                    for (var i = 0; i < registrations.length; i++) {
                        registrations[i].unregister();
                    }
                });
            }
            if ('caches' in window) {
                caches.keys().then(function(keys) {
                    for (var j = 0; j < keys.length; j++) {
                        caches.delete(keys[j]);
                    }
                });
            }
        </script>
    </body>
</html>
