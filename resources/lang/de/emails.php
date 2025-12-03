<?php

return [
    'reservation' => [
        // Customer confirmation email (initial request received)
        'customer_subject' => 'Ihre Reservierungsanfrage bei :restaurant',
        'customer_greeting' => 'Hallo :name,',
        'customer_intro' => 'Vielen Dank für Ihre Reservierungsanfrage bei :restaurant.',
        'customer_details_title' => 'Hier sind Ihre Reservierungsdetails:',
        'customer_date' => 'Datum',
        'customer_time' => 'Uhrzeit',
        'customer_guests' => 'Anzahl der Gäste',
        'customer_notes' => 'Hinweise',
        'customer_outro' => 'Wir werden Ihre Reservierung so schnell wie möglich bestätigen.',
        'customer_signature' => 'Mit freundlichen Grüßen, :restaurant',

        // Customer confirmed email (reservation confirmed by restaurant)
        'customer_confirmed_subject' => 'Ihre Reservierung bei :restaurant ist bestätigt',
        'customer_confirmed_intro' => 'Gute Neuigkeiten! Ihre Reservierung wurde bestätigt.',
        'customer_confirmed_details_title' => 'Ihre bestätigte Reservierung:',
        'customer_confirmed_outro' => 'Wir freuen uns darauf, Sie begrüßen zu dürfen.',
        'customer_confirmed_signature' => 'Mit freundlichen Grüßen, :restaurant',

        // Customer cancelled email (reservation cancelled by restaurant)
        'customer_cancelled_subject' => 'Ihre Reservierung bei :restaurant wurde storniert',
        'customer_cancelled_intro' => 'Es tut uns leid, Ihnen mitteilen zu müssen, dass Ihre Reservierung vom Restaurant storniert wurde.',
        'customer_cancelled_details_title' => 'Stornierte Reservierungsdetails:',
        'customer_cancelled_outro' => 'Bei Fragen wenden Sie sich bitte direkt an das Restaurant.',
        'customer_cancelled_signature' => 'Mit freundlichen Grüßen, :restaurant',

        // Restaurant notification email
        'restaurant_subject' => 'Neue Reservierungsanfrage über in.today für :restaurant',
        'restaurant_greeting' => 'Hallo,',
        'restaurant_intro' => 'Eine neue Reservierungsanfrage wurde über das in.today Buchungs-Widget empfangen.',
        'restaurant_details_title' => 'Reservierungsdetails:',
        'restaurant_customer_name' => 'Kundenname',
        'restaurant_customer_email' => 'Kunden-E-Mail',
        'restaurant_customer_phone' => 'Kundentelefon',
        'restaurant_date' => 'Datum',
        'restaurant_time' => 'Uhrzeit',
        'restaurant_guests' => 'Anzahl der Gäste',
        'restaurant_notes' => 'Hinweise',
        'restaurant_source' => 'Quelle',
        'restaurant_status' => 'Status',
        'restaurant_signature' => 'Bitte melden Sie sich in Ihrem in.today Business-Dashboard an, um diese Reservierung zu bestätigen oder zu verwalten.',
    ],
];
