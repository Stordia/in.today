<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.reservation.restaurant_subject', ['restaurant' => $restaurant->name], $locale) }}</title>
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
            background-color: #0ea5e9;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
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
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details td {
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .details tr:last-child td {
            border-bottom: none;
        }
        .details td:first-child {
            color: #64748b;
            width: 140px;
            font-weight: 500;
        }
        .details td:last-child {
            color: #1e293b;
        }
        .footer {
            margin-top: 30px;
            padding: 20px;
            background-color: #f1f5f9;
            border-radius: 8px;
            font-size: 14px;
            color: #475569;
        }
        .cta-link {
            color: #0ea5e9;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('emails.reservation.restaurant_subject', ['restaurant' => $restaurant->name], $locale) }}</h1>
    </div>

    <p>{{ __('emails.reservation.restaurant_greeting', [], $locale) }}</p>

    <p>{{ __('emails.reservation.restaurant_intro', [], $locale) }}</p>

    <div class="details">
        <h2>{{ __('emails.reservation.restaurant_details_title', [], $locale) }}</h2>
        <table>
            <tr>
                <td>{{ __('emails.reservation.restaurant_customer_name', [], $locale) }}</td>
                <td><strong>{{ $reservation->customer_name }}</strong></td>
            </tr>
            <tr>
                <td>{{ __('emails.reservation.restaurant_customer_email', [], $locale) }}</td>
                <td><a href="mailto:{{ $reservation->customer_email }}">{{ $reservation->customer_email }}</a></td>
            </tr>
            @if(!empty($reservation->customer_phone))
                <tr>
                    <td>{{ __('emails.reservation.restaurant_customer_phone', [], $locale) }}</td>
                    <td><a href="tel:{{ $reservation->customer_phone }}">{{ $reservation->customer_phone }}</a></td>
                </tr>
            @endif
            <tr>
                <td>{{ __('emails.reservation.restaurant_date', [], $locale) }}</td>
                <td><strong>{{ $formattedDate }}</strong></td>
            </tr>
            <tr>
                <td>{{ __('emails.reservation.restaurant_time', [], $locale) }}</td>
                <td><strong>{{ $formattedTime }}</strong></td>
            </tr>
            <tr>
                <td>{{ __('emails.reservation.restaurant_guests', [], $locale) }}</td>
                <td><strong>{{ $reservation->guests }}</strong></td>
            </tr>
            @if(!empty($reservation->customer_notes))
                <tr>
                    <td>{{ __('emails.reservation.restaurant_notes', [], $locale) }}</td>
                    <td>{{ $reservation->customer_notes }}</td>
                </tr>
            @endif
            <tr>
                <td>{{ __('emails.reservation.restaurant_source', [], $locale) }}</td>
                <td><span class="badge badge-success">{{ $reservation->source->label() }}</span></td>
            </tr>
            <tr>
                <td>{{ __('emails.reservation.restaurant_status', [], $locale) }}</td>
                <td><span class="badge badge-warning">{{ $reservation->status->label() }}</span></td>
            </tr>
            @if($reservation->deposit_required)
                <tr>
                    <td>{{ __('emails.reservation.restaurant_deposit', [], $locale) }}</td>
                    <td>
                        <span class="badge" style="background-color: #fef3c7; color: #92400e;">
                            {{ __('emails.reservation.restaurant_deposit_required', [], $locale) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('emails.reservation.restaurant_deposit_amount', [], $locale) }}</td>
                    <td><strong>{{ $reservation->getFormattedDepositAmount() }}</strong></td>
                </tr>
                <tr>
                    <td>{{ __('emails.reservation.restaurant_deposit_status', [], $locale) }}</td>
                    <td><span class="badge badge-warning">{{ $reservation->deposit_status->label() }}</span></td>
                </tr>
            @endif
        </table>
    </div>

    <div class="footer">
        <p>{{ __('emails.reservation.restaurant_signature', [], $locale) }}</p>
        <p><a href="{{ url('/business') }}" class="cta-link">{{ url('/business') }}</a></p>
    </div>
</body>
</html>
