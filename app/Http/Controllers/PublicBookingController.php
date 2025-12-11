<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
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

        // Get current time in restaurant timezone
        $now = Carbon::now($timezone);
        $today = $now->copy()->startOfDay();

        // Calculate minimum date considering lead time
        $minLeadTimeMinutes = $restaurant->booking_min_lead_time_minutes ?? 60;
        $earliestBookableTime = $now->copy()->addMinutes($minLeadTimeMinutes);
        $minDate = $earliestBookableTime->startOfDay();

        // If the earliest bookable time is tomorrow or later, adjust minDate
        if ($earliestBookableTime->isAfter($today->copy()->endOfDay())) {
            $minDate = $earliestBookableTime->copy()->startOfDay();
        }

        // Parse and validate date from request (works for both GET and POST)
        $dateInput = $request->input('date');
        if ($dateInput) {
            try {
                $date = Carbon::parse($dateInput, $timezone)->startOfDay();

                // Ensure date is not before minimum date
                if ($date->lt($minDate)) {
                    $date = $minDate;
                }

                // Ensure date is not beyond max lead time
                $maxDate = $today->copy()->addDays($restaurant->booking_max_lead_time_days ?? 30);
                if ($date->gt($maxDate)) {
                    $date = $maxDate;
                }
            } catch (\Exception) {
                $date = $minDate;
            }
        } else {
            $date = $minDate;
        }

        // Parse and validate party size from request (works for both GET and POST)
        $partySizeInput = $request->input('party_size');
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

        // Determine if deposit is required for this party size
        $requiresDeposit = $restaurant->requiresDeposit($partySize);
        $depositAmount = $requiresDeposit ? $restaurant->calculateDepositAmount($partySize) : 0;
        $formattedDepositAmount = $requiresDeposit ? $restaurant->getFormattedDepositAmount($partySize) : null;

        return view('public.booking', [
            'restaurant' => $restaurant,
            'date' => $date->toDateString(),
            'partySize' => $partySize,
            'availability' => $availability,
            'minDate' => $minDate->toDateString(),
            'maxDate' => $today->copy()->addDays($restaurant->booking_max_lead_time_days ?? 30)->toDateString(),
            'minPartySize' => $minPartySize,
            'maxPartySize' => $maxPartySize,
            // Deposit info
            'requiresDeposit' => $requiresDeposit,
            'depositAmount' => $depositAmount,
            'formattedDepositAmount' => $formattedDepositAmount,
            'depositThreshold' => $restaurant->booking_deposit_threshold_party_size ?? 4,
            'depositType' => $restaurant->booking_deposit_type ?? 'fixed_per_person',
            'depositPolicy' => $restaurant->booking_deposit_policy,
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
        $now = Carbon::now($timezone);
        $today = $now->copy()->startOfDay();

        // Calculate minimum date considering lead time
        $minLeadTimeMinutes = $restaurant->booking_min_lead_time_minutes ?? 60;
        $earliestBookableTime = $now->copy()->addMinutes($minLeadTimeMinutes);
        $minDate = $earliestBookableTime->startOfDay();

        // If the earliest bookable time is tomorrow or later, use tomorrow
        if ($earliestBookableTime->isAfter($today->copy()->endOfDay())) {
            $minDate = $earliestBookableTime->copy()->startOfDay();
        }

        $maxDate = $today->copy()->addDays($restaurant->booking_max_lead_time_days ?? 30);
        $minPartySize = $restaurant->booking_min_party_size ?? 1;
        $maxPartySize = $restaurant->booking_max_party_size ?? 20;

        // Build validation rules
        $rules = [
            'date' => [
                'required',
                'date',
                'after_or_equal:' . $minDate->toDateString(),
                'before_or_equal:' . $maxDate->toDateString(),
            ],
            'time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'party_size' => ['required', 'integer', 'min:' . $minPartySize, 'max:' . $maxPartySize],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^[+]?[0-9\s\-\(\)]+$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'accepted_terms' => ['required', 'accepted'],
            'hp_website' => ['nullable', 'string', 'max:255'],
        ];

        // Custom error messages
        $messages = [
            'date.after_or_equal' => __('booking.validation.date_too_early'),
            'date.before_or_equal' => __('booking.validation.date_too_late'),
            'party_size.min' => __('booking.validation.party_size_min', ['min' => $minPartySize]),
            'party_size.max' => __('booking.validation.party_size_max', ['max' => $maxPartySize]),
            'name.min' => __('booking.validation.name_too_short'),
            'accepted_terms.required' => __('booking.validation.terms_required'),
            'accepted_terms.accepted' => __('booking.validation.terms_required'),
            'phone.regex' => __('booking.validation.phone_invalid'),
        ];

        // Check if deposit is required - add deposit consent checkbox validation
        $partySize = (int) $request->input('party_size', 0);
        $requiresDeposit = $restaurant->requiresDeposit($partySize);

        if ($requiresDeposit) {
            $rules['accepted_deposit'] = ['required', 'accepted'];
            $messages['accepted_deposit.required'] = __('booking.validation.deposit_consent_required');
            $messages['accepted_deposit.accepted'] = __('booking.validation.deposit_consent_required');
        }

        // Validate input
        $validated = $request->validate($rules, $messages);

        // Honeypot spam protection
        if (! empty($validated['hp_website'] ?? null)) {
            // Treat as spam: pretend success to not help spammers
            Log::info('Honeypot triggered - blocking spam booking attempt', [
                'ip' => $request->ip(),
                'restaurant' => $slug,
            ]);

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
                ->withErrors(['time' => __('booking.validation.slot_unavailable')]);
        }

        // Check if the selected slot is not in the past (considering lead time)
        $slotDateTime = $selectedDate->copy()->setTimeFromTimeString($selectedTime);
        $earliestAllowed = $now->copy()->addMinutes($minLeadTimeMinutes);

        if ($slotDateTime->isBefore($earliestAllowed)) {
            return redirect()
                ->route('public.booking.show', [
                    'slug' => $slug,
                    'date' => $validated['date'],
                    'party_size' => $partySize,
                ])
                ->withInput()
                ->withErrors(['time' => __('booking.validation.slot_too_soon')]);
        }

        // Calculate deposit if required
        $depositRequired = $restaurant->requiresDeposit($partySize);
        $depositAmount = $depositRequired ? $restaurant->calculateDepositAmount($partySize) : null;
        $depositCurrency = $depositRequired ? ($restaurant->booking_deposit_currency ?? 'EUR') : null;
        $depositStatus = $depositRequired ? DepositStatus::Pending : DepositStatus::None;

        // Create reservation
        $reservation = Reservation::create([
            'restaurant_id' => $restaurant->id,
            'date' => $slotDateTime->toDateString(),
            'time' => $slotDateTime->format('H:i:s'),
            'guests' => $partySize,
            'duration_minutes' => $restaurant->booking_default_duration_minutes ?? 90,
            'customer_name' => trim($validated['name']),
            'customer_email' => $validated['email'],
            'customer_phone' => $validated['phone'] ?? null,
            'customer_notes' => $validated['notes'] ?? null,
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'language' => app()->getLocale(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            // Deposit fields
            'deposit_required' => $depositRequired,
            'deposit_amount' => $depositAmount,
            'deposit_currency' => $depositCurrency,
            'deposit_status' => $depositStatus,
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

        // Build success session data
        $successData = [
            'booking_status' => 'success',
            'reservation_uuid' => $reservation->uuid,
            'deposit_required' => $depositRequired,
        ];

        if ($depositRequired) {
            $successData['deposit_amount'] = $depositAmount;
            $successData['deposit_currency'] = $depositCurrency;
            $successData['formatted_deposit_amount'] = $reservation->getFormattedDepositAmount();
        }

        // Redirect back with success message (no query parameters)
        return redirect()
            ->route('public.booking.show', ['slug' => $slug])
            ->with($successData);
    }
}
