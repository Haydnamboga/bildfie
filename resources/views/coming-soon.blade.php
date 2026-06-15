<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b3d2e">
    <title>bildfie</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f7f9f8; color: #14201b; min-height: 100vh;
            display: flex; align-items: center; justify-content: center; padding: 24px; text-align: center;
        }
        .card { max-width: 420px; }
        .logo { font-size: 24px; font-weight: 800; color: #0b3d2e; margin-bottom: 20px; }
        .logo span { color: #f6b73c; }
        h1 { font-size: 24px; color: #0b3d2e; margin-bottom: 12px; }
        p { color: #5b6b63; margin-bottom: 24px; }
        a { display: inline-block; padding: 14px 22px; background: #0b3d2e; color: #fff;
            border-radius: 12px; text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">bild<span>fie</span></div>
        <h1>We&rsquo;re building this next.</h1>
        <p>
            The &ldquo;{{ str_replace('-', ' ', $action) }}&rdquo; flow is part of Phase&nbsp;1 (Core Marketplace)
            and is on its way. The platform foundation is live and hosted.
        </p>
        <a href="{{ url('/') }}">Back to home</a>
    </div>
</body>
</html>
