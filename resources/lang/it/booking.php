<?php

return [
    'page_title' => 'Prenota un tavolo',
    'page_subtitle' => 'Verifica la disponibilità e richiedi una prenotazione per la tua visita.',

    'step_1' => [
        'title' => '1. Scegli data e numero di ospiti',
        'date_label' => 'Data',
        'party_size_label' => 'Numero di ospiti',
        'party_size_hint' => ':min–:max ospiti',
        'check_availability' => 'Verifica disponibilità',
    ],

    'step_2' => [
        'title' => '2. Scegli l\'orario',
        'for_date' => 'Per il :date',
        'party_of' => ':count persone',
        'available' => 'Disponibile',
        'not_available' => 'Non disponibile',
        'no_slots_title' => 'Nessuna disponibilità per questa data',
        'no_slots_hint' => 'Prova un altro giorno o modifica il numero di ospiti.',
        'slots_summary' => ':available di :total fasce orarie disponibili',
    ],

    'step_3' => [
        'title' => '3. I tuoi dati',
        'summary_title' => 'La tua prenotazione',
        'name_label' => 'Nome',
        'name_placeholder' => 'Il tuo nome completo',
        'email_label' => 'Email',
        'email_placeholder' => 'tua@email.it',
        'phone_label' => 'Telefono',
        'phone_placeholder' => '+39 02 12345678',
        'phone_optional' => 'opzionale',
        'notes_label' => 'Richieste speciali',
        'notes_placeholder' => 'Esigenze alimentari, occasioni speciali, preferenze di posto...',
        'notes_optional' => 'opzionale',
        'submit' => 'Richiedi prenotazione',
        'terms_note' => 'Inviando, accetti i nostri termini di prenotazione. La tua richiesta sarà esaminata e confermata dal ristorante.',
    ],

    'info' => [
        'title' => 'Informazioni sulla prenotazione',
        'party_size' => 'Accettiamo prenotazioni online per :min–:max ospiti.',
        'lead_time_days' => 'Puoi prenotare fino a :days giorni in anticipo.',
        'lead_time_hours' => 'Le prenotazioni devono essere effettuate con almeno :hours ora/e di anticipo.',
        'lead_time_minutes' => 'Le prenotazioni devono essere effettuate con almeno :minutes minuti di anticipo.',
    ],

    'success' => [
        'title' => 'Grazie!',
        'message' => 'La tua richiesta di prenotazione è stata ricevuta. Confermeremo la tua prenotazione il prima possibile.',
    ],

    'error' => [
        'title' => 'Correggi i seguenti errori',
    ],

    'restaurant_info' => [
        'title' => 'Ristorante',
    ],
];
