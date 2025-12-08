<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.reservation.customer_subject', ['restaurant' => $restaurant->name], $locale) }}</title>
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
            border-bottom: 2px solid #0ea5e9;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #0ea5e9;
            margin: 0;
            font-size: 24px;
        }
        .details {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .details h2 {
            margin-top: 0;
            color: #1e293b;
            font-size: 18px;
        }
        .details ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .details li {
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .details li:last-child {
            border-bottom: none;
        }
        .details strong {
            color: #475569;
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

    <p>{{ __('emails.reservation.customer_intro', ['restaurant' => $restaurant->name], $locale) }}</p>

    <div class="details">
        <h2>{{ __('emails.reservation.customer_details_title', [], $locale) }}</h2>
        <ul>
            <li><strong>{{ __('emails.reservation.customer_date', [], $locale) }}:</strong> {{ $formattedDate }}</li>
            <li><strong>{{ __('emails.reservation.customer_time', [], $locale) }}:</strong> {{ $formattedTime }}</li>
            <li><strong>{{ __('emails.reservation.customer_guests', [], $locale) }}:</strong> {{ $reservation->guests }}</li>
            @if(!empty($reservation->customer_notes))
                <li><strong>{{ __('emails.reservation.customer_notes', [], $locale) }}:</strong> {{ $reservation->customer_notes }}</li>
            @endif
        </ul>
    </div>

    @if($reservation->deposit_required)
        <div class="details" style="background-color: #fef3c7; border-left: 4px solid #f59e0b;">
            <h2 style="color: #92400e; margin-top: 0;">{{ __('emails.reservation.deposit_required_title', [], $locale) }}</h2>
            <p style="color: #78350f; margin-bottom: 10px;">
                {{ __('emails.reservation.deposit_amount', ['amount' => $reservation->getFormattedDepositAmount()], $locale) }}
            </p>
            <p style="color: #92400e; font-size: 14px;">
                {{ __('emails.reservation.deposit_instructions', [], $locale) }}
            </p>
            @if(!empty($restaurant->booking_deposit_policy))
                <p style="color: #a16207; font-size: 13px; margin-top: 10px; padding-top: 10px; border-top: 1px solid #fde68a;">
                    <strong>{{ __('emails.reservation.deposit_policy_title', [], $locale) }}:</strong><br>
                    {{ $restaurant->booking_deposit_policy }}
                </p>
            @endif
        </div>
    @endif

    <p>{{ __('emails.reservation.customer_outro', [], $locale) }}</p>

    <div class="footer">
        <p>{{ __('emails.reservation.customer_signature', ['restaurant' => $restaurant->name], $locale) }}</p>
    </div>
</body>
</html>
