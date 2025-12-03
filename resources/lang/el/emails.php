<?php

return [
    'reservation' => [
        // Customer confirmation email (initial request received)
        'customer_subject' => 'Το αίτημα κράτησής σας στο :restaurant',
        'customer_greeting' => 'Γεια σας :name,',
        'customer_intro' => 'Ευχαριστούμε για το αίτημα κράτησής σας στο :restaurant.',
        'customer_details_title' => 'Ακολουθούν τα στοιχεία της κράτησής σας:',
        'customer_date' => 'Ημερομηνία',
        'customer_time' => 'Ώρα',
        'customer_guests' => 'Αριθμός ατόμων',
        'customer_notes' => 'Σημειώσεις',
        'customer_outro' => 'Θα επιβεβαιώσουμε την κράτησή σας το συντομότερο δυνατό.',
        'customer_signature' => 'Με εκτίμηση, :restaurant',

        // Customer confirmed email (reservation confirmed by restaurant)
        'customer_confirmed_subject' => 'Η κράτησή σας στο :restaurant επιβεβαιώθηκε',
        'customer_confirmed_intro' => 'Καλά νέα! Η κράτησή σας έχει επιβεβαιωθεί.',
        'customer_confirmed_details_title' => 'Η επιβεβαιωμένη κράτησή σας:',
        'customer_confirmed_outro' => 'Ανυπομονούμε να σας υποδεχτούμε.',
        'customer_confirmed_signature' => 'Με εκτίμηση, :restaurant',

        // Customer cancelled email (reservation cancelled by restaurant)
        'customer_cancelled_subject' => 'Η κράτησή σας στο :restaurant ακυρώθηκε',
        'customer_cancelled_intro' => 'Λυπούμαστε που σας ενημερώνουμε ότι η κράτησή σας ακυρώθηκε από το εστιατόριο.',
        'customer_cancelled_details_title' => 'Στοιχεία ακυρωμένης κράτησης:',
        'customer_cancelled_outro' => 'Εάν έχετε οποιεσδήποτε ερωτήσεις, μη διστάσετε να επικοινωνήσετε απευθείας με το εστιατόριο.',
        'customer_cancelled_signature' => 'Με εκτίμηση, :restaurant',

        // Restaurant notification email
        'restaurant_subject' => 'Νέο αίτημα κράτησης μέσω in.today για :restaurant',
        'restaurant_greeting' => 'Γεια σας,',
        'restaurant_intro' => 'Ελήφθη νέο αίτημα κράτησης μέσω του widget κρατήσεων in.today.',
        'restaurant_details_title' => 'Στοιχεία κράτησης:',
        'restaurant_customer_name' => 'Όνομα πελάτη',
        'restaurant_customer_email' => 'Email πελάτη',
        'restaurant_customer_phone' => 'Τηλέφωνο πελάτη',
        'restaurant_date' => 'Ημερομηνία',
        'restaurant_time' => 'Ώρα',
        'restaurant_guests' => 'Αριθμός ατόμων',
        'restaurant_notes' => 'Σημειώσεις',
        'restaurant_source' => 'Πηγή',
        'restaurant_status' => 'Κατάσταση',
        'restaurant_signature' => 'Παρακαλούμε συνδεθείτε στον πίνακα ελέγχου in.today business για να επιβεβαιώσετε ή να διαχειριστείτε αυτήν την κράτηση.',
    ],
];
