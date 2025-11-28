<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\ContactLead;

class ContactLeadEmailTemplates
{
    /**
     * Available email template definitions.
     *
     * @return array<string, array{label: string, subject: string, body: string}>
     */
    public static function all(): array
    {
        return [
            'initial_reply' => [
                'label' => 'Initial Reply',
                'subject' => 'in.today – Thanks for your request for {restaurant}',
                'body' => <<<'BODY'
Hi {name},

Thanks a lot for your interest in in.today and for telling us about {restaurant} in {city}, {country}.

We'll review your details and come back to you with a concrete proposal and timeline within 24 hours.

Best regards,
The in.today team
BODY,
            ],
            'follow_up' => [
                'label' => 'Follow-up',
                'subject' => 'in.today – Quick follow-up on your website request',
                'body' => <<<'BODY'
Hi {name},

I hope this message finds you well! I wanted to follow up on our previous conversation about {restaurant}.

We're still very interested in helping you establish a great online presence for your business. If you have any questions or would like to discuss further, please don't hesitate to reach out.

Looking forward to hearing from you!

Best regards,
The in.today team
BODY,
            ],
            'proposal_sent' => [
                'label' => 'Proposal Sent',
                'subject' => 'in.today – Proposal for {restaurant}',
                'body' => <<<'BODY'
Hi {name},

Thank you for your patience! We've prepared a tailored proposal for {restaurant} based on our conversation.

Please find the proposal details attached/sent separately. It includes:
- Recommended features and services
- Timeline for development
- Investment overview

Feel free to review and let us know if you have any questions or would like to schedule a call to discuss.

Best regards,
The in.today team
BODY,
            ],
        ];
    }

    /**
     * Get template options for dropdown selection.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::all())
            ->mapWithKeys(fn (array $template, string $key) => [$key => $template['label']])
            ->toArray();
    }

    /**
     * Resolve a template for a specific lead.
     *
     * @return array{subject: string, body: string}
     */
    public static function for(ContactLead $lead, string $key): array
    {
        $templates = self::all();

        if (! isset($templates[$key])) {
            return [
                'subject' => '',
                'body' => '',
            ];
        }

        $template = $templates[$key];

        return [
            'subject' => self::replacePlaceholders($template['subject'], $lead),
            'body' => self::replacePlaceholders($template['body'], $lead),
        ];
    }

    /**
     * Replace placeholders in a string with lead data.
     */
    private static function replacePlaceholders(string $text, ContactLead $lead): string
    {
        $replacements = [
            '{name}' => $lead->name ?? 'there',
            '{restaurant}' => $lead->restaurant_name ?? 'your restaurant',
            '{city}' => $lead->city ?? 'your city',
            '{country}' => $lead->country ?? 'your country',
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $text
        );
    }
}
