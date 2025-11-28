<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailBody }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-body {
            margin-bottom: 32px;
            white-space: pre-line;
        }
        .email-footer {
            border-top: 1px solid #e5e5e5;
            padding-top: 24px;
            margin-top: 24px;
            font-size: 14px;
            color: #666;
        }
        .brand {
            font-weight: 600;
            color: #333;
        }
        .brand-link {
            color: #2563eb;
            text-decoration: none;
        }
        .brand-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-body">
            {!! nl2br(e($emailBody)) !!}
        </div>

        <div class="email-footer">
            <p class="brand">in.today</p>
            <p>
                Websites & booking systems for restaurants<br>
                <a href="https://in.today" class="brand-link">in.today</a>
            </p>
        </div>
    </div>
</body>
</html>
