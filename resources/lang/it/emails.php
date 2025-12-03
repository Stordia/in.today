<?php

return [
    'reservation' => [
        // Customer confirmation email (initial request received)
        'customer_subject' => 'La tua richiesta di prenotazione presso :restaurant',
        'customer_greeting' => 'Ciao :name,',
        'customer_intro' => 'Grazie per la tua richiesta di prenotazione presso :restaurant.',
        'customer_details_title' => 'Ecco i dettagli della tua prenotazione:',
        'customer_date' => 'Data',
        'customer_time' => 'Ora',
        'customer_guests' => 'Numero di ospiti',
        'customer_notes' => 'Note',
        'customer_outro' => 'Confermeremo la tua prenotazione il prima possibile.',
        'customer_signature' => 'Cordiali saluti, :restaurant',

        // Customer confirmed email (reservation confirmed by restaurant)
        'customer_confirmed_subject' => 'La tua prenotazione presso :restaurant è confermata',
        'customer_confirmed_intro' => 'Ottime notizie! La tua prenotazione è stata confermata.',
        'customer_confirmed_details_title' => 'La tua prenotazione confermata:',
        'customer_confirmed_outro' => 'Non vediamo l\'ora di accoglierti.',
        'customer_confirmed_signature' => 'Cordiali saluti, :restaurant',

        // Customer cancelled email (reservation cancelled by restaurant)
        'customer_cancelled_subject' => 'La tua prenotazione presso :restaurant è stata cancellata',
        'customer_cancelled_intro' => 'Ci dispiace informarti che la tua prenotazione è stata cancellata dal ristorante.',
        'customer_cancelled_details_title' => 'Dettagli della prenotazione cancellata:',
        'customer_cancelled_outro' => 'Per qualsiasi domanda, non esitare a contattare direttamente il ristorante.',
        'customer_cancelled_signature' => 'Cordiali saluti, :restaurant',

        // Restaurant notification email
        'restaurant_subject' => 'Nuova richiesta di prenotazione tramite in.today per :restaurant',
        'restaurant_greeting' => 'Ciao,',
        'restaurant_intro' => 'È stata ricevuta una nuova richiesta di prenotazione tramite il widget di prenotazione in.today.',
        'restaurant_details_title' => 'Dettagli della prenotazione:',
        'restaurant_customer_name' => 'Nome cliente',
        'restaurant_customer_email' => 'Email cliente',
        'restaurant_customer_phone' => 'Telefono cliente',
        'restaurant_date' => 'Data',
        'restaurant_time' => 'Ora',
        'restaurant_guests' => 'Numero di ospiti',
        'restaurant_notes' => 'Note',
        'restaurant_source' => 'Fonte',
        'restaurant_status' => 'Stato',
        'restaurant_signature' => 'Accedi alla tua dashboard business in.today per confermare o gestire questa prenotazione.',
    ],
];
