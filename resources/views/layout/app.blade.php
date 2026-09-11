<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'TM Service — Transport Management System')</title>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,wght@0,400;0,600;0,700;0,800;1,600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="{{ asset('css/tms.css') }}?v={{ filemtime(public_path('css/tms.css')) }}" rel="stylesheet">
@stack('styles')
</head>
<body>

@yield('content')

<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
@stack('scripts')
</body>
</html>