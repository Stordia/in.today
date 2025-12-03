<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Services\AppSettings;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCustomerStatusUpdate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $locale;

    public string $formattedDate;

    public string $formattedTime;

    public function __construct(
        public Reservation $reservation,
        public Restaurant $restaurant,
        public string $type, // 'confirmed' or 'cancelled'
    ) {
        $this->locale = $reservation->language ?? app()->getLocale();
        $this->computeFormattedDateTime();
    }

    public function envelope(): Envelope
    {
        $subjectKey = $this->type === 'confirmed'
            ? 'emails.reservation.customer_confirmed_subject'
            : 'emails.reservation.customer_cancelled_subject';

        $fromAddress = AppSettings::get(
            'email.from_address',
            config('mail.from.address', 'noreply@in.today')
        );
        $fromName = AppSettings::get(
            'email.from_name',
            config('mail.from.name', 'in.today')
        );

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: __($subjectKey, ['restaurant' => $this->restaurant->name], $this->locale),
        );
    }

    public function content(): Content
    {
        $view = $this->type === 'confirmed'
            ? 'emails.reservation_customer_confirmed'
            : 'emails.reservation_customer_cancelled';

        return new Content(
            view: $view,
            with: [
                'reservation' => $this->reservation,
                'restaurant' => $this->restaurant,
                'locale' => $this->locale,
                'formattedDate' => $this->formattedDate,
                'formattedTime' => $this->formattedTime,
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function computeFormattedDateTime(): void
    {
        $tz = $this->restaurant->timezone ?: config('app.timezone', 'UTC');

        // Parse the reservation time - date is a Carbon date, time is cast to datetime:H:i
        $timeString = $this->reservation->time instanceof \DateTimeInterface
            ? $this->reservation->time->format('H:i:s')
            : (string) $this->reservation->time;

        $dateString = $this->reservation->date instanceof \DateTimeInterface
            ? $this->reservation->date->format('Y-m-d')
            : (string) $this->reservation->date;

        $dateTime = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $dateString.' '.$timeString,
            config('app.timezone', 'UTC')
        )->setTimezone($tz);

        // Format with locale awareness
        $this->formattedDate = $dateTime->translatedFormat('l, j F Y');
        $this->formattedTime = $dateTime->format('H:i');
    }
}
