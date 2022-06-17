<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.head')
    <title>Wrong data</title>
</head>

<body>
    <pre>{{ $text }}</pre>
</body>

</html>
