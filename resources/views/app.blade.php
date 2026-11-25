<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Life Log') }}</title>

    <script>
        (() => {
            let theme = 'auto';

            try {
                theme = localStorage.getItem('life-log-theme') ?? 'auto';
            } catch {
                theme = 'auto';
            }

            const resolvedTheme = theme === 'auto'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : theme;

            document.documentElement.setAttribute('data-coreui-theme', resolvedTheme);
        })();
    </script>
    @vite('resources/js/app.js')
    <x-inertia::head />
</head>

<body>
    <x-inertia::app />
</body>

</html>
