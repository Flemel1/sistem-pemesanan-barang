<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>{{ config('app.name') }}</title>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
    @vite('resources/css/app.css')
</head>

<body class="antialiased">
    <livewire:components.top-bar />
    <main @class([
        'fi-main py-4 mx-auto h-full w-full px-4 md:px-6 lg:px-8',
        'max-w-7xl',
    ])>
        {{ $slot }}
    </main>

    @filamentScripts
    @vite('resources/js/app.js')

    @livewire('notifications')
</body>

</html>
