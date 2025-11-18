<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @routes
    @vite(['resources/js/app.ts'])
    @inertiaHead

    {{-- Always force light mode - ignore system preferences --}}
    <script>
        (function() {
            // Always remove dark class to ensure light mode
            document.documentElement.classList.remove('dark');

            // Override any saved appearance setting
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem('appearance', 'light');
            }
        })();
    </script>

    {{-- Inline style to set the HTML background color based on our theme in app.css --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
