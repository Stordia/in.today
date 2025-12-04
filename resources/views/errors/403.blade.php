<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access denied - in.today</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #020617;
            color: #e5e7eb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            line-height: 1.5;
        }

        .card {
            background-color: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 16px;
            padding: 40px 32px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .brand {
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 24px;
        }

        .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            background-color: #1e293b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon svg {
            width: 32px;
            height: 32px;
            color: #f59e0b;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 12px;
        }

        .subtitle {
            font-size: 16px;
            color: #94a3b8;
            margin-bottom: 16px;
        }

        .hint {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 32px;
            padding: 16px;
            background-color: #1e293b;
            border-radius: 8px;
        }

        .buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 24px;
            background-color: #f59e0b;
            color: #0f172a;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #d97706;
        }

        .btn-primary svg {
            width: 20px;
            height: 20px;
        }

        .btn-secondary {
            display: inline-block;
            padding: 12px 24px;
            color: #94a3b8;
            font-size: 14px;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .btn-secondary:hover {
            color: #e5e7eb;
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="brand">in.today</div>

        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>

        <h1>Access denied</h1>

        <p class="subtitle">You don't have permission to access this section.</p>

        <p class="hint">If you are a restaurant owner, please use the Business Panel instead of the Admin Panel.</p>

        <div class="buttons">
            <a href="/business" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                </svg>
                Go to Business Panel
            </a>
            <a href="/" class="btn-secondary">Back to homepage</a>
        </div>
    </main>
</body>
</html>
