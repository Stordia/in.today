<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.reservation.customer_cancelled_subject', ['restaurant' => $restaurant->name], $locale) }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #ef4444;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #ef4444;
            margin: 0;
            font-size: 24px;
        }
        .details {
            background-color: #fef2f2;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #fecaca;
        }
        .details h2 {
            margin-top: 0;
            color: #991b1b;
            font-size: 18px;
        }
        .details ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .details li {
            padding: 8px 0;
            border-bottom: 1px solid #fee2e2;
        }
        .details li:last-child {
            border-bottom: none;
        }
        .details strong {
            color: #b91c1c;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $restaurant->name }}</h1>
    </div>

    <p>{{ __('emails.reservation.customer_greeting', ['name' => $reservation->customer_name], $locale) }}</p>

    <p>{{ __('emails.reservation.customer_cancelled_intro', [], $locale) }}</p>

    <div class="details">
        <h2>{{ __('emails.reservation.customer_cancelled_details_title', [], $locale) }}</h2>
        <ul>
            <li><strong>{{ __('emails.reservation.customer_date', [], $locale) }}:</strong> {{ $formattedDate }}</li>
            <li><strong>{{ __('emails.reservation.customer_time', [], $locale) }}:</strong> {{ $formattedTime }}</li>
            <li><strong>{{ __('emails.reservation.customer_guests', [], $locale) }}:</strong> {{ $reservation->guests }}</li>
            @if(!empty($reservation->customer_notes))
                <li><strong>{{ __('emails.reservation.customer_notes', [], $locale) }}:</strong> {{ $reservation->customer_notes }}</li>
            @endif
        </ul>
    </div>

    <p>{{ __('emails.reservation.customer_cancelled_outro', [], $locale) }}</p>

    <div class="footer">
        <p>{{ __('emails.reservation.customer_cancelled_signature', ['restaurant' => $restaurant->name], $locale) }}</p>
    </div>
</body>
</html>
