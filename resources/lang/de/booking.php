<?php

return [
    'page_title' => 'Tisch reservieren',
    'page_subtitle' => 'Verfügbarkeit prüfen und eine Reservierung für Ihren Besuch anfragen.',

    'step_1' => [
        'title' => '1. Datum & Personenzahl wählen',
        'date_label' => 'Datum',
        'party_size_label' => 'Personenzahl',
        'party_size_hint' => ':min–:max Gäste',
        'check_availability' => 'Verfügbarkeit prüfen',
    ],

    'step_2' => [
        'title' => '2. Uhrzeit wählen',
        'for_date' => 'Für :date',
        'party_of' => ':count Personen',
        'available' => 'Verfügbar',
        'not_available' => 'Nicht verfügbar',
        'no_slots_title' => 'Keine Verfügbarkeit für dieses Datum',
        'no_slots_hint' => 'Bitte versuchen Sie einen anderen Tag oder passen Sie die Personenzahl an.',
        'slots_summary' => ':available von :total Zeitfenstern verfügbar',
    ],

    'step_3' => [
        'title' => '3. Ihre Daten',
        'summary_title' => 'Ihre Reservierung',
        'name_label' => 'Name',
        'name_placeholder' => 'Ihr vollständiger Name',
        'email_label' => 'E-Mail',
        'email_placeholder' => 'ihre@email.de',
        'phone_label' => 'Telefon',
        'phone_placeholder' => '+49 123 456789',
        'phone_optional' => 'optional',
        'notes_label' => 'Besondere Wünsche',
        'notes_placeholder' => 'Ernährungsanforderungen, besondere Anlässe, Sitzplatzpräferenzen...',
        'notes_optional' => 'optional',
        'submit' => 'Reservierung anfragen',
        'terms_note' => 'Mit dem Absenden stimmen Sie unseren Buchungsbedingungen zu. Ihre Reservierungsanfrage wird vom Restaurant geprüft und bestätigt.',
    ],

    'info' => [
        'title' => 'Buchungsinformationen',
        'party_size' => 'Wir akzeptieren Online-Reservierungen für :min–:max Gäste.',
        'lead_time_days' => 'Sie können bis zu :days Tage im Voraus reservieren.',
        'lead_time_hours' => 'Reservierungen müssen mindestens :hours Stunde(n) im Voraus erfolgen.',
        'lead_time_minutes' => 'Reservierungen müssen mindestens :minutes Minuten im Voraus erfolgen.',
    ],

    'success' => [
        'title' => 'Vielen Dank!',
        'message' => 'Ihre Reservierungsanfrage wurde erhalten. Wir werden Ihre Reservierung so schnell wie möglich bestätigen.',
    ],

    'error' => [
        'title' => 'Bitte korrigieren Sie folgende Fehler',
    ],

    'restaurant_info' => [
        'title' => 'Restaurant',
    ],
];
