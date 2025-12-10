<?php

return [
    'page_title' => 'Prenota un tavolo',
    'page_subtitle' => 'Verifica la disponibilità e richiedi una prenotazione per la tua visita.',

    'header' => [
        'online_booking' => 'Prenotazione Online',
    ],

    'step_1' => [
        'title' => 'Scegli data e numero di ospiti',
        'date_label' => 'Data',
        'party_size_label' => 'Numero di ospiti',
        'party_size_hint' => ':min–:max ospiti',
        'check_availability' => 'Verifica disponibilità',
    ],

    'step_2' => [
        'title' => 'Scegli l\'orario',
        'for_date' => 'Per il :date',
        'party_of' => ':count persone',
        'available' => 'Disponibile',
        'not_available' => 'Non disponibile',
        'no_slots_title' => 'Nessuna disponibilità per questa data',
        'no_slots_hint' => 'Prova un altro giorno o modifica il numero di ospiti.',
        'slots_summary' => ':available di :total fasce orarie disponibili',
        'legend_available' => 'Disponibile',
        'legend_unavailable' => 'Non disponibile',
    ],

    'step_3' => [
        'title' => 'I tuoi dati',
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
        'terms_consent_label' => 'Accetto i termini di prenotazione e la privacy policy.',
    ],

    'summary' => [
        'title' => 'Riepilogo prenotazione',
        'date' => 'Data',
        'time' => 'Orario',
        'guests' => 'Ospiti',
        'guests_count' => '{1} :count ospite|[2,*] :count ospiti',
        'deposit' => 'Caparra',
    ],

    'info' => [
        'title' => 'Informazioni sulla prenotazione',
        'party_size' => 'Accettiamo prenotazioni online per :min–:max ospiti.',
        'lead_time_days' => 'Puoi prenotare fino a :days giorni in anticipo.',
        'lead_time_hours' => 'Le prenotazioni devono essere effettuate con almeno :hours ora/e di anticipo.',
        'lead_time_minutes' => 'Le prenotazioni devono essere effettuate con almeno :minutes minuti di anticipo.',
        'deposit_threshold' => 'Caparra richiesta per gruppi di :threshold o più persone.',
    ],

    'success' => [
        'title' => 'Grazie!',
        'message' => 'La tua richiesta di prenotazione è stata ricevuta. Confermeremo la tua prenotazione il prima possibile.',
        'deposit_title' => 'Caparra richiesta',
        'deposit_message' => 'Per questa prenotazione è richiesta una caparra di :amount.',
        'deposit_instructions' => 'Riceverai le istruzioni di pagamento via email a breve. La tua prenotazione è condizionata al ricevimento della caparra.',
    ],

    'error' => [
        'title' => 'Correggi i seguenti errori',
    ],

    'restaurant_info' => [
        'title' => 'Ristorante',
    ],

    'deposit' => [
        'title' => 'Caparra richiesta',
        'info_title' => 'Caparra richiesta',
        'info_message' => 'Per questa prenotazione è richiesta una caparra di :amount.',
        'payment_instructions' => 'Riceverai le istruzioni di pagamento nell\'email di conferma del ristorante.',
        'message' => 'Per prenotazioni di :threshold o più ospiti, è richiesta una caparra di :amount :type.',
        'per_person' => 'a persona',
        'per_reservation' => 'per prenotazione',
        'payment_note' => 'Riceverai le istruzioni di pagamento dopo aver inviato la richiesta.',
        'consent_label' => 'Comprendo che è richiesta una caparra e che deve essere pagata secondo la policy.',
        'consent_text' => 'Comprendo che una caparra di :amount potrebbe essere richiesta per confermare questa prenotazione.',
        'not_required' => 'Nessuna caparra richiesta per questa prenotazione.',
    ],

    'footer' => [
        'powered_by' => 'Powered by',
    ],

    'validation' => [
        'date_too_early' => 'Questa data non è disponibile per le prenotazioni online.',
        'date_too_late' => 'Questa data è troppo lontana per le prenotazioni online.',
        'party_size_min' => 'Il numero minimo di ospiti è :min.',
        'party_size_max' => 'Le prenotazioni online sono disponibili per massimo :max ospiti. Per gruppi più numerosi, contatta direttamente il ristorante.',
        'name_too_short' => 'Inserisci il tuo nome completo.',
        'terms_required' => 'Conferma di accettare i termini di prenotazione e la privacy policy.',
        'deposit_consent_required' => 'Conferma di aver compreso il requisito della caparra.',
        'phone_invalid' => 'Inserisci un numero di telefono valido.',
        'slot_unavailable' => 'Purtroppo questa fascia oraria non è più disponibile. Scegline un\'altra.',
        'slot_too_soon' => 'Questa fascia oraria non è più disponibile per i requisiti di preavviso minimo.',
    ],
];
