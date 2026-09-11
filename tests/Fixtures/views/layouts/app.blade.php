<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('template_title')</title>
    @stack('template_linked_css')
</head>
<body>
    @yield('content')
</body>
</html>
