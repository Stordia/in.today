<?php

return [
    'page_title' => 'Book a table',
    'page_subtitle' => 'Check availability and request a booking for your visit.',

    'step_1' => [
        'title' => '1. Choose date & party size',
        'date_label' => 'Date',
        'party_size_label' => 'Party size',
        'party_size_hint' => ':min–:max guests',
        'check_availability' => 'Check availability',
    ],

    'step_2' => [
        'title' => '2. Choose your time',
        'for_date' => 'For :date',
        'party_of' => 'Party of :count',
        'available' => 'Available',
        'not_available' => 'Not available',
        'no_slots_title' => 'No availability for this date',
        'no_slots_hint' => 'Please try another day or adjust your party size.',
        'slots_summary' => ':available of :total time slots available',
    ],

    'step_3' => [
        'title' => '3. Your details',
        'summary_title' => 'Your booking',
        'name_label' => 'Name',
        'name_placeholder' => 'Your full name',
        'email_label' => 'Email',
        'email_placeholder' => 'your@email.com',
        'phone_label' => 'Phone',
        'phone_placeholder' => '+49 123 456789',
        'phone_optional' => 'optional',
        'notes_label' => 'Special requests',
        'notes_placeholder' => 'Dietary requirements, special occasions, seating preferences...',
        'notes_optional' => 'optional',
        'submit' => 'Request booking',
        'terms_note' => 'By submitting, you agree to our booking terms. Your reservation request will be reviewed and confirmed by the restaurant.',
    ],

    'info' => [
        'title' => 'Booking information',
        'party_size' => 'We accept online booking requests for :min–:max guests.',
        'lead_time_days' => 'You can book up to :days days in advance.',
        'lead_time_hours' => 'Reservations must be made at least :hours hour(s) in advance.',
        'lead_time_minutes' => 'Reservations must be made at least :minutes minutes in advance.',
    ],

    'success' => [
        'title' => 'Thank you!',
        'message' => 'Your booking request has been received. We will confirm your reservation as soon as possible.',
    ],

    'error' => [
        'title' => 'Please correct the following errors',
    ],

    'restaurant_info' => [
        'title' => 'Restaurant',
    ],
];
