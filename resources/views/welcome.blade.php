<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Letters & words') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">
        @vite(['resources/js/app.js'])
        <script>
            window.__WORD_MAX_LENGTH__ = {{ (int) config('words.max_length') }};
        </script>
    </head>
    <body class="min-h-screen bg-[#FDFDFC] font-sans text-[#1b1b18] antialiased">
        <div id="app"></div>
    </body>
</html>
