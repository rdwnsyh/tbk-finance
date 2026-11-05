<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'TBK Finance') }}</title>

        {{-- Memuat preload font Bunny (Instrument Sans) yang dihasilkan plugin Vite. --}}
        @fonts

        {{-- @vite membaca manifest hasil `npm run build`, atau dev server saat `npm run dev`. --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100">
        {{-- Elemen mount point yang dipakai resources/js/app.js untuk menjalankan Vue. --}}
        <div id="app"></div>
    </body>
</html>
