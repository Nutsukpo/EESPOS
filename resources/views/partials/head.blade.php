<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=4">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('eespos-favicon/favicon-32.png') }}?v=4">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('eespos-favicon/favicon-16.png') }}?v=4">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('eespos-favicon/apple-touch-icon.png') }}?v=4">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
