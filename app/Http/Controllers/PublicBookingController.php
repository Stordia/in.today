<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Mail\ReservationCustomerConfirmation;
use App\Mail\ReservationRestaurantNotification;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Services\AppSettings;
use App\Services\Reservations\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PublicBookingController extends Controller
{
    public function show(Request $request, string $slug, AvailabilityService $availabilityService): View
    {
        // Find restaurant by slug with booking enabled
        $restaurant = Restaurant::query()
            ->where('booking_enabled', true)
            ->where('booking_public_slug', $slug)
            ->first();

        if (! $restaurant) {
            abort(404);
        }

        // Determine timezone
        $timezone = $restaurant->timezone ?? config('app.timezone', 'UTC');

        // Get today's date in restaurant timezone
        $today = Carbon::now($timezone)->startOfDay();

        // Parse and validate date from request
        $dateInput = $request->query('date');
        if ($dateInput) {
            try {
                $date = Carbon::parse($dateInput, $timezone)->startOfDay();

                // Ensure date is not in the past
                if ($date->lt($today)) {
                    $date = $today;
                }

                // Ensure date is not beyond max lead time
                $maxDate = $today->copy()->addDays($restaurant->booking_max_lead_time_days ?? 30);
                if ($date->gt($maxDate)) {
                    $date = $maxDate;
                }
            } catch (\Exception) {
                $date = $today;
            }
        } else {
            $date = $today;
        }

        // Parse and validate party size from request
        $partySizeInput = $request->query('party_size');
        $minPartySize = $restaurant->booking_min_party_size ?? 1;
        $maxPartySize = $restaurant->booking_max_party_size ?? 20;

        if ($partySizeInput !== null && is_numeric($partySizeInput)) {
            $partySize = (int) $partySizeInput;
            // Clamp to valid range
            $partySize = max($minPartySize, min($maxPartySize, $partySize));
        } else {
            // Default to 2, but ensure it's within bounds
            $partySize = max($minPartySize, min($maxPartySize, 2));
        }

        // Get availability
        $availability = $availabilityService->getAvailableTimeSlots(
            restaurant: $restaurant,
            date: $date,
            partySize: $partySize,
        );

        return view('public.booking', [
            'restaurant' => $restaurant,
            'date' => $date->toDateString(),
            'partySize' => $partySize,
            'availability' => $availability,
            'minDate' => $today->toDateString(),
            'maxDate' => $today->copy()->addDays($restaurant->booking_max_lead_time_days ?? 30)->toDateString(),
            'minPartySize' => $minPartySize,
            'maxPartySize' => $maxPartySize,
        ]);
    }

    public function request(Request $request, string $slug, AvailabilityService $availabilityService): RedirectResponse
    {
        // Find restaurant by slug with booking enabled
        $restaurant = Restaurant::query()
            ->where('booking_enabled', true)
            ->where('booking_public_slug', $slug)
            ->first();

        if (! $restaurant) {
            abort(404);
        }

        // Determine timezone and date boundaries
        $timezone = $restaurant->timezone ?? config('app.timezone', 'UTC');
        $today = Carbon::now($timezone)->startOfDay();
        $maxDate = $today->copy()->addDays($restaurant->booking_max_lead_time_days ?? 30);
        $minPartySize = $restaurant->booking_min_party_size ?? 1;
        $maxPartySize = $restaurant->booking_max_party_size ?? 20;

        // Validate input
        $validated = $request->validate([
            'date' => [
                'required',
                'date',
                'after_or_equal:'.$today->toDateString(),
                'before_or_equal:'.$maxDate->toDateString(),
            ],
            'time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'party_size' => ['required', 'integer', 'min:'.$minPartySize, 'max:'.$maxPartySize],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'hp_website' => ['nullable', 'string', 'max:255'],
        ]);

        // Honeypot spam protection
        if (! empty($validated['hp_website'] ?? null)) {
            // Treat as spam: pretend success to not help spammers
            return redirect()
                ->route('public.booking.show', [
                    'slug' => $slug,
                    'date' => $validated['date'],
                    'party_size' => $validated['party_size'],
                ])
                ->with('booking_status', 'success');
        }

        // Parse date and party size
        $selectedDate = Carbon::parse($validated['date'], $timezone)->startOfDay();
        $partySize = (int) $validated['party_size'];

        // Re-compute availability and validate the chosen time slot
        $availability = $availabilityService->getAvailableTimeSlots(
            restaurant: $restaurant,
            date: $selectedDate,
            partySize: $partySize,
        );

        // Find the matching slot
        $selectedTime = $validated['time'];
        $matchingSlot = null;

        foreach ($availability->slots as $slot) {
            if ($slot->getStartTime() === $selectedTime && $slot->isBookable) {
                $matchingSlot = $slot;
                break;
            }
        }

        // If no matching bookable slot, redirect back with error
        if (! $matchingSlot) {
            return redirect()
                ->route('public.booking.show', [
                    'slug' => $slug,
                    'date' => $validated['date'],
                    'party_size' => $partySize,
                ])
                ->withInput()
                ->withErrors(['time' => 'Selected time is no longer available. Please choose another slot.']);
        }

        // Create reservation
        $slotStart = $selectedDate->copy()->setTimeFromTimeString($selectedTime);

        $reservation = Reservation::create([
            'restaurant_id' => $restaurant->id,
            'date' => $slotStart->toDateString(),
            'time' => $slotStart->format('H:i:s'),
            'guests' => $partySize,
            'duration_minutes' => $restaurant->booking_default_duration_minutes ?? 90,
            'customer_name' => $validated['name'],
            'customer_email' => $validated['email'],
            'customer_phone' => $validated['phone'] ?? null,
            'customer_notes' => $validated['notes'] ?? null,
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'language' => app()->getLocale(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        // Send confirmation emails (wrapped in try/catch to not break booking flow)
        try {
            $sendCustomerConfirmation = AppSettings::get('booking.send_customer_confirmation', true);
            $sendRestaurantNotification = AppSettings::get('booking.send_restaurant_notification', true);

            // Determine restaurant notification email
            $restaurantNotificationEmail = AppSettings::get(
                'booking.default_notification_email',
                config('services.bookings.notification_email')
            );

            // Send confirmation to customer
            if ($sendCustomerConfirmation && ! empty($reservation->customer_email)) {
                Mail::to($reservation->customer_email)
                    ->send(new ReservationCustomerConfirmation($reservation, $restaurant));
            }

            // Send notification to restaurant
            if ($sendRestaurantNotification && ! empty($restaurantNotificationEmail)) {
                Mail::to($restaurantNotificationEmail)
                    ->send(new ReservationRestaurantNotification($reservation, $restaurant));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send reservation emails', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Redirect back with success message
        return redirect()
            ->route('public.booking.show', [
                'slug' => $slug,
                'date' => $validated['date'],
                'party_size' => $partySize,
            ])
            ->with('booking_status', 'success');
    }
}
