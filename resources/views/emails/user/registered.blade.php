<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to {{ config('app.name') }}</title>
</head>
<body>
<h1>Hello, {{ $userName }}!</h1>
<p>Thank you for registering on site {{ config('app.name') }}!</p>
<p>We are glad to see you among us.</p>
</body>
</html>
