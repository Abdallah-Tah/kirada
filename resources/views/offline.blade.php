<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirada — Offline</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 400px;
        }
        .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            background: #000;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
            color: white;
        }
        h1 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        p { color: #64748b; font-size: 0.875rem; line-height: 1.5; margin-bottom: 1.5rem; }
        button {
            background: #0ea5e9;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: #0284c7; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">K</div>
        <h1>You're Offline</h1>
        <p>
            Kirada can't reach the server right now. Check your internet connection
            and try again. Cached pages may still be available.
        </p>
        <button onclick="window.location.reload()">Try Again</button>
    </div>
</body>
</html>
