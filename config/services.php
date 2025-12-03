<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Services
    |--------------------------------------------------------------------------
    |
    | Configuration for analytics and tracking services. Leave empty to disable.
    | Set the environment variables when ready to enable tracking.
    |
    */

    'analytics' => [
        'gtm_id' => env('ANALYTICS_GTM_ID'),
        'meta_pixel_id' => env('ANALYTICS_META_PIXEL_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking / Reservation Services
    |--------------------------------------------------------------------------
    |
    | Configuration for reservation emails and notifications.
    |
    */

    'bookings' => [
        'notification_email' => env('RESERVATIONS_NOTIFICATION_EMAIL', 'proposals@in.today'),
        'from_address' => env('RESERVATIONS_FROM_ADDRESS', 'noreply@in.today'),
        'from_name' => env('RESERVATIONS_FROM_NAME', 'in.today'),
    ],

];
