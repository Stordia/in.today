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

class ReservationCustomerConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $emailLocale;

    public string $formattedDate;

    public string $formattedTime;

    public function __construct(
        public Reservation $reservation,
        public Restaurant $restaurant,
    ) {
        $this->emailLocale = $reservation->language ?? app()->getLocale();
        $this->locale($this->emailLocale);
        $this->computeFormattedDateTime();
    }

    public function envelope(): Envelope
    {
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
            subject: __('emails.reservation.customer_subject', ['restaurant' => $this->restaurant->name], $this->emailLocale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation_customer_confirmation',
            with: [
                'reservation' => $this->reservation,
                'restaurant' => $this->restaurant,
                'locale' => $this->emailLocale,
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
