<?php
http_response_code(404);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>404 Not Found</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #0b0b12; color: #ececf1; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.14); border-radius: 16px; padding: 40px; text-align: center; box-shadow: 0 8px 28px rgba(0,0,0,.35); max-width: 420px; }
        a { color: #f03a3a; text-decoration: none; font-weight: 600; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Page not found</h1>
        <p>The page you are looking for does not exist or has been moved.</p>
        <p><a href="/">Return to homepage</a></p>
    </div>
</body>
</html>
