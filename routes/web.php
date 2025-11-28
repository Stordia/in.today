<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

    Route::get('/imprint', function () {
        return view('legal.imprint');
    })->name('imprint');

    Route::get('/privacy', function () {
        return view('legal.privacy');
    })->name('privacy');
});

// Admin attachment download (protected by auth middleware)
// Attachments are stored on the public disk
Route::get('/admin/attachments/{path}', function (string $path) {
    $path = urldecode($path);

    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->download($path);
})
    ->where('path', '.*')
    ->middleware(['auth', 'verified'])
    ->name('admin.attachment.download');
