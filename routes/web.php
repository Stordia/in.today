<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

// Language selection page
Route::get('/language', function () {
    return view('language-select');
})->name('language.select');

// Root redirect (language detection)
Route::get('/', function () {
    return view('lang-redirect');
})->name('root');

// Localized landing pages
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => implode('|', config('locales.supported', ['en']))],
    'middleware' => 'set.locale',
], function () {
    Route::get('/', function () {
        return view('landing');
    })->name('landing');

    Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
});
