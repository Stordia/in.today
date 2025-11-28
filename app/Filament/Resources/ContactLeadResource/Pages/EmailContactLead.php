<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactLeadResource\Pages;

use App\Filament\Resources\ContactLeadResource;
use App\Mail\ContactLeadReply;
use App\Models\ContactLead;
use App\Models\ContactLeadEmail;
use App\Support\ContactLeadEmailTemplates;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailContactLead extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ContactLeadResource::class;

    protected static string $view = 'filament.resources.contact-lead-resource.pages.email-contact-lead';

    protected static ?string $title = 'Email Lead';

    public ?array $data = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->form->fill([
            'template' => 'initial_reply',
            'to_email' => $this->record->email,
            'subject' => ContactLeadEmailTemplates::for($this->record, 'initial_reply')['subject'],
            'body' => ContactLeadEmailTemplates::for($this->record, 'initial_reply')['body'],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('template')
                    ->label('Template')
                    ->options(ContactLeadEmailTemplates::options())
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
                    ->maxLength(255),

                Forms\Components\TextInput::make('subject')
                    ->label('Subject')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('body')
                    ->label('Body')
                    ->required()
                    ->rows(14),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $status = 'sent';
        $sentAt = now();

        try {
            Mail::to($data['to_email'])
                ->send(new ContactLeadReply(
                    lead: $this->record,
                    emailSubject: $data['subject'],
                    emailBody: $data['body'],
                ));
        } catch (\Throwable $e) {
            $status = 'failed';
            $sentAt = null;

            Log::error('Failed to send ContactLead email', [
                'contact_lead_id' => $this->record->id,
                'to_email' => $data['to_email'],
                'error' => $e->getMessage(),
            ]);

            ContactLeadEmail::create([
                'contact_lead_id' => $this->record->id,
                'sent_by_user_id' => Auth::id(),
                'to_email' => $data['to_email'],
                'subject' => $data['subject'],
                'body' => $data['body'],
                'status' => $status,
                'sent_at' => $sentAt,
            ]);

            Notification::make()
                ->title('Email failed to send')
                ->body('The email could not be sent. Please try again later.')
                ->danger()
                ->send();

            return;
        }

        // Log successful email
        ContactLeadEmail::create([
            'contact_lead_id' => $this->record->id,
            'sent_by_user_id' => Auth::id(),
            'to_email' => $data['to_email'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'status' => $status,
            'sent_at' => $sentAt,
        ]);

        // Auto-update status from "new" to "contacted"
        if ($this->record->status->value === 'new') {
            $this->record->update(['status' => 'contacted']);
        }

        Notification::make()
            ->title('Email sent successfully')
            ->body("Email sent to {$data['to_email']}")
            ->success()
            ->send();

        $this->redirect(ContactLeadResource::getUrl('view', ['record' => $this->record]));
    }

    public function getBreadcrumbs(): array
    {
        return [
            ContactLeadResource::getUrl() => 'Contact Leads',
            ContactLeadResource::getUrl('view', ['record' => $this->record]) => $this->record->name,
            '' => 'Email',
        ];
    }
}
