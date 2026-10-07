<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unable to Prepare Labels</title>
    <style>
        * { box-sizing: border-box; }
        body { display: grid; min-height: 100vh; margin: 0; place-items: center; padding: 24px; background: #f8fafc; color: #0f172a; font-family: Arial, sans-serif; }
        main { width: min(480px, 100%); padding: 28px; border: 1px solid #fecaca; border-radius: 14px; background: #fff; box-shadow: 0 14px 35px rgba(15, 23, 42, .08); text-align: center; }
        h1 { margin: 0 0 10px; font-size: 21px; } p { margin: 0 0 20px; color: #64748b; line-height: 1.6; }
        button { padding: 10px 16px; border: 0; border-radius: 8px; background: #2563eb; color: #fff; cursor: pointer; font-weight: 700; }
    </style>
</head>
<body>
    <main role="alert">
        <h1>Unable to prepare labels</h1>
        <p>{{ $message }}</p>
        <button type="button" onclick="window.close()">Close this window</button>
    </main>
</body>
</html>
