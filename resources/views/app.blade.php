<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Scripts de SEO Globais (Injetados via Middleware ou Compartilhados) --}}
        @if(isset($seo_global))
            {!! $seo_global->google_tag_manager !!}
            {!! $seo_global->schema_markup !!}
        @endif

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        @routes
        @vite([
            app()->environment('local') ? 'resources/js/app.ts' : 'resources/js/app.js', 
            "resources/js/Pages/{$page['component']}.vue"
        ])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>