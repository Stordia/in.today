<?php

use App\Http\Controllers\AffiliateRedirectController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PublicCityController;
use App\Http\Controllers\PublicVenueController;
use App\Http\Controllers\PublicVenueBookingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Language selection page
Route::get('/language', function () {
    return view('language-select');
})->name('language.select');

// Root page - Public discovery home with city search
Route::get('/', [PublicCityController::class, 'home'])
    ->name('root');

// Affiliate redirect route - tracks clicks and redirects to clean landing page
Route::get('/go/{slug}', [AffiliateRedirectController::class, 'redirect'])
    ->name('affiliate.redirect');

// Legacy Greek locale redirect: /el → /gr
Route::get('/el/{path?}', function ($path = null) {
    if ($path) {
        return redirect('/gr/' . $path, 301);
    }
    return redirect('/gr', 301);
})->where('path', '.*');

// Localized landing pages (en, de, gr, it)
// Note: These are locale landing pages, NOT country routes.
// Single-segment country codes like /fr return 404.
// Two-segment paths like /fr/paris work as city routes.
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => 'en|de|gr|it'],
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

// TEMPORARY: Old booking routes for backward compatibility during transition
// These will be removed after full migration to new venue routing is complete
Route::match(['get', 'post'], '/book/{slug}', [App\Http\Controllers\PublicBookingController::class, 'show'])
    ->name('public.booking.show');

Route::post('/book/{slug}/request', [App\Http\Controllers\PublicBookingController::class, 'request'])
    ->name('public.booking.request');

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

/*
|--------------------------------------------------------------------------
| Public City Discovery Routes
|--------------------------------------------------------------------------
|
| City search and discovery pages. These must come after locale routes
| but before venue routes to avoid conflicts.
|
*/

// City search form submission
Route::get('/search', [PublicCityController::class, 'search'])
    ->name('public.city.search');

// City results page: /{country}/{city}
Route::get('/{country}/{city}', [PublicCityController::class, 'show'])
    ->name('public.city.show')
    ->where([
        'country' => '[a-z]{2}',
        'city' => '[a-z0-9\-]+',
    ]);

/*
|--------------------------------------------------------------------------
| Global Public Venue Routes
|--------------------------------------------------------------------------
|
| New URL structure for venues, bookings, and menus:
| /{country}/{city}/{venue}
| /{country}/{city}/{venue}/book
| /{country}/{city}/{venue}/menu
|
| These routes must be placed AFTER all admin/business/auth routes to avoid conflicts.
|
*/

Route::group([
    'where' => [
        'country' => '[a-z]{2}',
        'city' => '[a-z0-9\-]+',
        'venue' => '[a-z0-9\-\.]+',
    ],
], function () {
    // Venue public profile page
    Route::get('/{country}/{city}/{venue}', [PublicVenueController::class, 'show'])
        ->name('public.venue.show');

    // Venue booking page (GET = show form, POST = submit booking)
    Route::get('/{country}/{city}/{venue}/book', [PublicVenueBookingController::class, 'show'])
        ->name('public.venue.book.show');

    Route::post('/{country}/{city}/{venue}/book', [PublicVenueBookingController::class, 'request'])
        ->name('public.venue.book.request');

    // Venue menu page (skeleton for now)
    Route::get('/{country}/{city}/{venue}/menu', [PublicVenueController::class, 'showMenu'])
        ->name('public.venue.menu.show');
});
