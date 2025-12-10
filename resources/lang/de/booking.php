<?php

return [
    'page_title' => 'Tisch reservieren',
    'page_subtitle' => 'Verfügbarkeit prüfen und eine Reservierung für Ihren Besuch anfragen.',

    'header' => [
        'online_booking' => 'Online-Reservierung',
    ],

    'step_1' => [
        'title' => 'Datum & Personenzahl wählen',
        'date_label' => 'Datum',
        'party_size_label' => 'Personenzahl',
        'party_size_hint' => ':min–:max Gäste',
        'check_availability' => 'Verfügbarkeit prüfen',
    ],

    'step_2' => [
        'title' => 'Uhrzeit wählen',
        'for_date' => 'Für :date',
        'party_of' => ':count Personen',
        'available' => 'Verfügbar',
        'not_available' => 'Nicht verfügbar',
        'no_slots_title' => 'Keine Verfügbarkeit für dieses Datum',
        'no_slots_hint' => 'Bitte versuchen Sie einen anderen Tag oder passen Sie die Personenzahl an.',
        'slots_summary' => ':available von :total Zeitfenstern verfügbar',
        'legend_available' => 'Verfügbar',
        'legend_unavailable' => 'Nicht verfügbar',
    ],

    'step_3' => [
        'title' => 'Ihre Daten',
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
        'terms_consent_label' => 'Ich akzeptiere die Buchungsbedingungen und Datenschutzrichtlinie.',
    ],

    'summary' => [
        'title' => 'Reservierungsübersicht',
        'date' => 'Datum',
        'time' => 'Uhrzeit',
        'guests' => 'Gäste',
        'guests_count' => '{1} :count Gast|[2,*] :count Gäste',
        'deposit' => 'Anzahlung',
    ],

    'info' => [
        'title' => 'Buchungsinformationen',
        'party_size' => 'Wir akzeptieren Online-Reservierungen für :min–:max Gäste.',
        'lead_time_days' => 'Sie können bis zu :days Tage im Voraus reservieren.',
        'lead_time_hours' => 'Reservierungen müssen mindestens :hours Stunde(n) im Voraus erfolgen.',
        'lead_time_minutes' => 'Reservierungen müssen mindestens :minutes Minuten im Voraus erfolgen.',
        'deposit_threshold' => 'Anzahlung erforderlich für Gruppen ab :threshold Personen.',
    ],

    'success' => [
        'title' => 'Vielen Dank!',
        'message' => 'Ihre Reservierungsanfrage wurde erhalten. Wir werden Ihre Reservierung so schnell wie möglich bestätigen.',
        'deposit_title' => 'Anzahlung erforderlich',
        'deposit_message' => 'Für diese Reservierung ist eine Anzahlung von :amount erforderlich.',
        'deposit_instructions' => 'Sie erhalten die Zahlungsanweisungen in Kürze per E-Mail. Ihre Reservierung ist bedingt, bis die Anzahlung eingegangen ist.',
    ],

    'error' => [
        'title' => 'Bitte korrigieren Sie folgende Fehler',
    ],

    'restaurant_info' => [
        'title' => 'Restaurant',
    ],

    'deposit' => [
        'title' => 'Anzahlung erforderlich',
        'info_title' => 'Anzahlung erforderlich',
        'info_message' => 'Für diese Reservierung ist eine Anzahlung von :amount erforderlich.',
        'payment_instructions' => 'Sie erhalten die Zahlungsanweisungen in der Bestätigungs-E-Mail des Restaurants.',
        'message' => 'Für Reservierungen ab :threshold Gästen ist eine Anzahlung von :amount :type erforderlich.',
        'per_person' => 'pro Person',
        'per_reservation' => 'pro Reservierung',
        'payment_note' => 'Sie erhalten die Zahlungsanweisungen nach dem Absenden Ihrer Anfrage.',
        'consent_label' => 'Ich verstehe, dass eine Anzahlung erforderlich ist und gemäß der Anzahlungsrichtlinie gezahlt werden muss.',
        'consent_text' => 'Ich verstehe, dass eine Anzahlung von :amount zur Bestätigung dieser Reservierung erforderlich sein kann.',
        'not_required' => 'Für diese Reservierung ist keine Anzahlung erforderlich.',
    ],

    'footer' => [
        'powered_by' => 'Powered by',
    ],

    'validation' => [
        'date_too_early' => 'Dieses Datum ist für Online-Buchungen nicht verfügbar.',
        'date_too_late' => 'Dieses Datum liegt zu weit in der Zukunft für Online-Buchungen.',
        'party_size_min' => 'Mindestpersonenzahl ist :min.',
        'party_size_max' => 'Online-Buchungen sind für maximal :max Gäste möglich. Für größere Gruppen kontaktieren Sie bitte direkt das Restaurant.',
        'name_too_short' => 'Bitte geben Sie Ihren vollständigen Namen ein.',
        'terms_required' => 'Bitte bestätigen Sie, dass Sie die Buchungsbedingungen und Datenschutzrichtlinie akzeptieren.',
        'deposit_consent_required' => 'Bitte bestätigen Sie, dass Sie die Anzahlungsanforderung verstanden haben.',
        'phone_invalid' => 'Bitte geben Sie eine gültige Telefonnummer ein.',
        'slot_unavailable' => 'Leider ist dieses Zeitfenster gerade nicht mehr verfügbar. Bitte wählen Sie ein anderes.',
        'slot_too_soon' => 'Dieses Zeitfenster ist aufgrund der Mindestvorlaufzeit nicht mehr verfügbar.',
    ],
];
