<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build assets required</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #060e1e; color: #f0f6ff; max-width: 640px; margin: 4rem auto; padding: 2rem; line-height: 1.6; }
        code { background: #0d1f3c; padding: 2px 8px; border-radius: 4px; color: #00c9a7; }
        pre { background: #0d1f3c; padding: 1rem; border-radius: 8px; overflow-x: auto; border: 1px solid #132848; }
    </style>
</head>
<body>
    <h1>Frontend build missing</h1>
    <p>Login and dashboard need compiled assets in <code>public/build</code>. The landing page works because it does not use Vite.</p>
    <p>On the server, run:</p>
    <pre>npm install
npm run build</pre>
    <p>Or build on your PC and upload the <code>public/build</code> folder to the server.</p>
</body>
</html>
