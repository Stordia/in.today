<?php

return [
    'reservation' => [
        // Customer confirmation email (initial request received)
        'customer_subject' => 'Your reservation request at :restaurant',
        'customer_greeting' => 'Hi :name,',
        'customer_intro' => 'Thank you for your reservation request at :restaurant.',
        'customer_details_title' => 'Here are your reservation details:',
        'customer_date' => 'Date',
        'customer_time' => 'Time',
        'customer_guests' => 'Number of guests',
        'customer_notes' => 'Notes',
        'customer_outro' => 'We will confirm your reservation as soon as possible.',
        'customer_signature' => 'Best regards, :restaurant',

        // Customer confirmed email (reservation confirmed by restaurant)
        'customer_confirmed_subject' => 'Your reservation at :restaurant is confirmed',
        'customer_confirmed_intro' => 'Good news! Your reservation has been confirmed.',
        'customer_confirmed_details_title' => 'Your confirmed reservation:',
        'customer_confirmed_outro' => 'We are looking forward to welcoming you.',
        'customer_confirmed_signature' => 'Best regards, :restaurant',

        // Customer cancelled email (reservation cancelled by restaurant)
        'customer_cancelled_subject' => 'Your reservation at :restaurant has been cancelled',
        'customer_cancelled_intro' => 'We\'re sorry to inform you that your reservation has been cancelled by the restaurant.',
        'customer_cancelled_details_title' => 'Cancelled reservation details:',
        'customer_cancelled_outro' => 'If you have any questions, feel free to contact the restaurant directly.',
        'customer_cancelled_signature' => 'Best regards, :restaurant',

        // Restaurant notification email
        'restaurant_subject' => 'New reservation request via in.today for :restaurant',
        'restaurant_greeting' => 'Hello,',
        'restaurant_intro' => 'A new reservation request has been received via the in.today booking widget.',
        'restaurant_details_title' => 'Reservation details:',
        'restaurant_customer_name' => 'Customer name',
        'restaurant_customer_email' => 'Customer email',
        'restaurant_customer_phone' => 'Customer phone',
        'restaurant_date' => 'Date',
        'restaurant_time' => 'Time',
        'restaurant_guests' => 'Number of guests',
        'restaurant_notes' => 'Notes',
        'restaurant_source' => 'Source',
        'restaurant_status' => 'Status',
        'restaurant_deposit' => 'Deposit required',
        'restaurant_deposit_yes' => 'Yes',
        'restaurant_deposit_no' => 'No',
        'restaurant_deposit_amount' => 'Deposit amount',
        'restaurant_deposit_status' => 'Deposit status',
        'restaurant_signature' => 'Please log in to your in.today business dashboard to confirm or manage this reservation.',

        // Customer email deposit section
        'deposit_required_title' => 'Deposit Required',
        'deposit_amount' => 'A deposit of :amount is required for your reservation.',
        'deposit_instructions' => 'Please contact the restaurant directly to arrange payment. Your reservation will be confirmed once the deposit has been received.',
        'deposit_policy_title' => 'Deposit Policy',
    ],
];
