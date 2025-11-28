<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactLeadResource\Pages;

use App\Filament\Resources\ContactLeadResource;
use App\Mail\ContactLeadReply;
use App\Models\ContactLead;
use App\Models\ContactLeadEmail;
use App\Support\ContactLeadEmailTemplates;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ViewContactLead extends ViewRecord
{
    protected static string $resource = ContactLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeSendEmailAction(),
            Actions\EditAction::make(),
        ];
    }

    /**
     * Create the Send Email action for the View page header.
     *
     * Uses Filament\Actions\Action (page action) - not Tables\Actions\Action.
     */
    protected function makeSendEmailAction(): Actions\Action
    {
        return Actions\Action::make('send_email_view')
            ->label('Send Email')
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->modalHeading('Send Email to Lead')
            ->modalDescription(fn (): string => "Sending email to {$this->record->name} ({$this->record->email})")
            ->modalSubmitActionLabel('Send Email')
            ->form(fn (): array => [
                Forms\Components\Select::make('template')
                    ->label('Template')
                    ->options(ContactLeadEmailTemplates::options())
                    ->default('initial_reply')
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                        if ($state && $this->record) {
                            $resolved = ContactLeadEmailTemplates::for($this->record, $state);
                            $set('subject', $resolved['subject']);
                            $set('body', $resolved['body']);
                        }
                    }),
                Forms\Components\TextInput::make('to_email')
                    ->label('To')
                    ->email()
                    ->required()
                    ->default($this->record->email),
                Forms\Components\TextInput::make('subject')
                    ->label('Subject')
                    ->required()
                    ->maxLength(255)
                    ->default(ContactLeadEmailTemplates::for($this->record, 'initial_reply')['subject']),
                Forms\Components\Textarea::make('body')
                    ->label('Body')
                    ->required()
                    ->rows(12)
                    ->default(ContactLeadEmailTemplates::for($this->record, 'initial_reply')['body']),
            ])
            ->action(function (array $data): void {
                $this->sendEmailToLead($this->record, $data);
            });
    }

    /**
     * Shared email sending logic.
     */
    protected function sendEmailToLead(ContactLead $record, array $data): void
    {
        $status = 'sent';
        $sentAt = now();

        try {
            Mail::to($data['to_email'])
                ->send(new ContactLeadReply(
                    lead: $record,
                    emailSubject: $data['subject'],
                    emailBody: $data['body'],
                ));
        } catch (\Throwable $e) {
            $status = 'failed';
            $sentAt = null;

            Log::error('Failed to send ContactLead email', [
                'contact_lead_id' => $record->id,
                'to_email' => $data['to_email'],
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Email failed to send')
                ->body('The email could not be sent. Please try again later.')
                ->danger()
                ->send();

            // Still log the failed attempt
            ContactLeadEmail::create([
                'contact_lead_id' => $record->id,
                'sent_by_user_id' => Auth::id(),
                'to_email' => $data['to_email'],
                'subject' => $data['subject'],
                'body' => $data['body'],
                'status' => $status,
                'sent_at' => $sentAt,
            ]);

            return;
        }

        // Log successful email
        ContactLeadEmail::create([
            'contact_lead_id' => $record->id,
            'sent_by_user_id' => Auth::id(),
            'to_email' => $data['to_email'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'status' => $status,
            'sent_at' => $sentAt,
        ]);

        Notification::make()
            ->title('Email sent successfully')
            ->body("Email sent to {$data['to_email']}")
            ->success()
            ->send();
    }

    public function infolist(Infolist $infolist): Infolist
    {
        // Get parent infolist schema
        $parentInfolist = parent::infolist($infolist);

        // Add email history section
        return $parentInfolist->schema([
            ...$parentInfolist->getComponents(),

            Section::make('Email History')
                ->icon('heroicon-o-envelope')
                ->collapsed(fn (): bool => $this->record->emails()->count() === 0)
                ->description(fn (): string => $this->record->emails()->count() . ' email(s) sent')
                ->schema([
                    ViewEntry::make('emails_list')
                        ->view('filament.infolists.components.email-history', [
                            'emails' => $this->record->emails()
                                ->with('sentBy')
                                ->orderByDesc('created_at')
                                ->limit(10)
                                ->get(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
