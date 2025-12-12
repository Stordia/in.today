<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | List of all locales supported by the application.
    | Used for route constraints, language switchers, and validation.
    |
    | Note: Canonical Greek locale is 'gr'. Legacy 'el' redirects to 'gr'.
    |
    */

    'supported' => ['en', 'de', 'gr', 'it'],

    /*
    |--------------------------------------------------------------------------
    | Locale Names
    |--------------------------------------------------------------------------
    |
    | Display names for each locale (in their native language).
    | Used in language selection UI.
    |
    */

    'names' => [
        'en' => 'English',
        'de' => 'Deutsch',
        'gr' => 'Ελληνικά',
        'it' => 'Italiano',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The default locale to use when none is specified or detected.
    |
    */

    'default' => 'en',
];
