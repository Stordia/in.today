<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactLead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ContactLeadReply extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $attachmentPaths  Array of storage paths for attachments
     */
    public function __construct(
        public ContactLead $lead,
        public string $emailSubject,
        public string $emailBody,
        public array $attachmentPaths = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_lead_reply',
            with: [
                'lead' => $this->lead,
                'emailBody' => $this->emailBody,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->attachmentPaths as $path) {
            if (Storage::exists($path)) {
                $attachments[] = Attachment::fromStorage($path)
                    ->as(basename($path));
            }
        }

        return $attachments;
    }
}
