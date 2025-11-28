<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ContactLeadStatus;
use App\Enums\GlobalRole;
use App\Filament\Resources\ContactLeadResource\Pages;
use App\Models\ContactLead;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ContactLeadResource extends Resource
{
    protected static ?string $model = ContactLead::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Contact Lead';

    protected static ?string $pluralModelLabel = 'Contact Leads';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Left column - Contact & Business + Request Details
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('Contact & Business')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(50),
                                        Forms\Components\TextInput::make('restaurant_name')
                                            ->label('Restaurant/Business Name')
                                            ->maxLength(255),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('city')
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('country')
                                                    ->maxLength(255),
                                            ]),
                                        Forms\Components\TextInput::make('type')
                                            ->label('Business Type')
                                            ->maxLength(100),
                                    ]),

                                Forms\Components\Section::make('Request Details')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->schema([
                                        Forms\Components\Placeholder::make('services_display')
                                            ->label('Services Requested')
                                            ->content(function (?ContactLead $record): HtmlString {
                                                if (! $record || empty($record->services)) {
                                                    return new HtmlString('<span class="text-gray-400">None specified</span>');
                                                }

                                                $badges = collect($record->services)
                                                    ->map(fn ($service) => '<span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 bg-primary-50 text-primary-600 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">' . e($service) . '</span>')
                                                    ->join(' ');

                                                return new HtmlString('<div class="flex flex-wrap gap-1">' . $badges . '</div>');
                                            })
                                            ->visible(fn (?ContactLead $record): bool => $record !== null),
                                        Forms\Components\TextInput::make('budget')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('website_url')
                                            ->label('Website')
                                            ->url()
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('visit_website')
                                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                                    ->url(fn (?ContactLead $record): ?string => $record?->website_url)
                                                    ->openUrlInNewTab()
                                                    ->visible(fn (?ContactLead $record): bool => ! empty($record?->website_url))
                                            )
                                            ->maxLength(500),
                                        Forms\Components\Textarea::make('message')
                                            ->rows(5)
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(2),

                        // Right column - Internal CRM + Meta
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('Internal')
                                    ->icon('heroicon-o-briefcase')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->options(ContactLeadStatus::class)
                                            ->required()
                                            ->default(ContactLeadStatus::New)
                                            ->native(false),
                                        Forms\Components\Select::make('assigned_to_user_id')
                                            ->label('Assigned To')
                                            ->options(
                                                User::query()
                                                    ->where('global_role', GlobalRole::PlatformAdmin)
                                                    ->pluck('name', 'id')
                                            )
                                            ->searchable()
                                            ->placeholder('Unassigned'),
                                        Forms\Components\Textarea::make('internal_notes')
                                            ->label('Internal Notes')
                                            ->rows(6)
                                            ->helperText('Private notes (not visible to customer)'),
                                        Forms\Components\Placeholder::make('restaurant_link')
                                            ->label('Converted Restaurant')
                                            ->content(fn (ContactLead $record): string => $record->restaurant
                                                ? $record->restaurant->name
                                                : 'Not converted yet'
                                            )
                                            ->visible(fn (?ContactLead $record): bool => $record !== null),
                                    ]),

                                Forms\Components\Section::make('Meta')
                                    ->icon('heroicon-o-information-circle')
                                    ->collapsed()
                                    ->schema([
                                        Forms\Components\Placeholder::make('created_at_display')
                                            ->label('Submitted')
                                            ->content(fn (?ContactLead $record): string => $record?->created_at?->format('M j, Y \a\t H:i') ?? '—'),
                                        Forms\Components\TextInput::make('locale')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\Placeholder::make('source_url_display')
                                            ->label('Source URL')
                                            ->content(function (?ContactLead $record): HtmlString {
                                                if (! $record || empty($record->source_url)) {
                                                    return new HtmlString('<span class="text-gray-400">—</span>');
                                                }

                                                return new HtmlString(
                                                    '<a href="' . e($record->source_url) . '" target="_blank" class="text-primary-600 hover:underline truncate block max-w-full">' . e($record->source_url) . '</a>'
                                                );
                                            }),
                                        Forms\Components\TextInput::make('ip_address')
                                            ->label('IP Address')
                                            ->disabled()
                                            ->dehydrated(false),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3)
                    ->schema([
                        // Left column
                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make('Contact & Business')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->weight(FontWeight::SemiBold),
                                        Infolists\Components\TextEntry::make('email')
                                            ->copyable()
                                            ->icon('heroicon-o-envelope'),
                                        Infolists\Components\TextEntry::make('phone')
                                            ->icon('heroicon-o-phone')
                                            ->placeholder('—'),
                                        Infolists\Components\TextEntry::make('restaurant_name')
                                            ->label('Restaurant/Business')
                                            ->weight(FontWeight::SemiBold)
                                            ->placeholder('—'),
                                        Infolists\Components\TextEntry::make('location')
                                            ->icon('heroicon-o-map-pin')
                                            ->placeholder('—'),
                                        Infolists\Components\TextEntry::make('type')
                                            ->label('Business Type')
                                            ->badge()
                                            ->color('gray')
                                            ->placeholder('—'),
                                    ])
                                    ->columns(2),

                                Infolists\Components\Section::make('Request Details')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('services')
                                            ->label('Services Requested')
                                            ->badge()
                                            ->color('primary')
                                            ->separator(', ')
                                            ->placeholder('None specified'),
                                        Infolists\Components\TextEntry::make('budget')
                                            ->badge()
                                            ->color('success')
                                            ->placeholder('—'),
                                        Infolists\Components\TextEntry::make('website_url')
                                            ->label('Website')
                                            ->url(fn (ContactLead $record): ?string => $record->website_url)
                                            ->openUrlInNewTab()
                                            ->color('primary')
                                            ->placeholder('—'),
                                        Infolists\Components\TextEntry::make('message')
                                            ->columnSpanFull()
                                            ->markdown()
                                            ->placeholder('No message'),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(2),

                        // Right column
                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make('Internal')
                                    ->icon('heroicon-o-briefcase')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (ContactLeadStatus $state): string => $state->color()),
                                        Infolists\Components\TextEntry::make('assignedTo.name')
                                            ->label('Assigned To')
                                            ->placeholder('Unassigned')
                                            ->icon('heroicon-o-user'),
                                        Infolists\Components\TextEntry::make('internal_notes')
                                            ->label('Internal Notes')
                                            ->markdown()
                                            ->placeholder('No notes yet'),
                                        Infolists\Components\TextEntry::make('restaurant.name')
                                            ->label('Converted Restaurant')
                                            ->visible(fn (ContactLead $record): bool => $record->restaurant_id !== null)
                                            ->icon('heroicon-o-building-storefront')
                                            ->color('success'),
                                    ]),

                                Infolists\Components\Section::make('Meta')
                                    ->icon('heroicon-o-information-circle')
                                    ->collapsed()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('created_at')
                                            ->label('Submitted')
                                            ->dateTime('M j, Y \a\t H:i'),
                                        Infolists\Components\TextEntry::make('locale')
                                            ->label('Language')
                                            ->badge()
                                            ->color('gray'),
                                        Infolists\Components\TextEntry::make('source_url')
                                            ->label('Source URL')
                                            ->url(fn (ContactLead $record): ?string => $record->source_url)
                                            ->openUrlInNewTab()
                                            ->limit(40)
                                            ->placeholder('—'),
                                        Infolists\Components\TextEntry::make('ip_address')
                                            ->label('IP Address')
                                            ->placeholder('—'),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('restaurant_name')
                    ->label('Business')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable(['city', 'country'])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('services_summary')
                    ->label('Services')
                    ->badge()
                    ->color('primary')
                    ->getStateUsing(function (ContactLead $record): ?string {
                        $services = $record->services ?? [];
                        if (empty($services)) {
                            return null;
                        }
                        $count = \count($services);
                        if ($count <= 2) {
                            return implode(', ', $services);
                        }
                        $first = \array_slice($services, 0, 2);
                        $remaining = $count - 2;

                        return implode(', ', $first) . " +{$remaining} more";
                    })
                    ->tooltip(function (ContactLead $record): ?string {
                        $services = $record->services ?? [];

                        return empty($services) ? null : implode(', ', $services);
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (ContactLeadStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('restaurant_id')
                    ->label('Converted')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ContactLeadStatus::class)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('type')
                    ->options(fn () => ContactLead::query()
                        ->whereNotNull('type')
                        ->distinct()
                        ->pluck('type', 'type')
                        ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('assigned_to_user_id')
                    ->label('Assigned To')
                    ->options(
                        User::query()
                            ->where('global_role', GlobalRole::PlatformAdmin)
                            ->pluck('name', 'id')
                    ),
                Tables\Filters\TernaryFilter::make('converted')
                    ->label('Converted')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('restaurant_id'),
                        false: fn ($query) => $query->whereNull('restaurant_id'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('reply_email')
                    ->label('Reply')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->url(fn (ContactLead $record): string => self::buildMailtoUrl($record))
                    ->openUrlInNewTab(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('mark_contacted')
                        ->label('Mark as Contacted')
                        ->icon('heroicon-o-phone')
                        ->color('info')
                        ->visible(fn (ContactLead $record): bool => $record->status === ContactLeadStatus::New)
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::Contacted])),
                    Tables\Actions\Action::make('mark_qualified')
                        ->label('Mark as Qualified')
                        ->icon('heroicon-o-star')
                        ->color('purple')
                        ->visible(fn (ContactLead $record): bool => in_array($record->status, [ContactLeadStatus::New, ContactLeadStatus::Contacted]))
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::Qualified])),
                    Tables\Actions\Action::make('mark_proposal_sent')
                        ->label('Mark as Proposal Sent')
                        ->icon('heroicon-o-document-text')
                        ->color('warning')
                        ->visible(fn (ContactLead $record): bool => $record->status === ContactLeadStatus::Qualified)
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::ProposalSent])),
                    Tables\Actions\Action::make('mark_won')
                        ->label('Mark as Won')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (ContactLead $record): bool => $record->status->isOpen())
                        ->requiresConfirmation()
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::Won])),
                    Tables\Actions\Action::make('mark_lost')
                        ->label('Mark as Lost')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (ContactLead $record): bool => $record->status->isOpen())
                        ->requiresConfirmation()
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::Lost])),
                    Tables\Actions\Action::make('mark_spam')
                        ->label('Mark as Spam')
                        ->icon('heroicon-o-trash')
                        ->color('gray')
                        ->visible(fn (ContactLead $record): bool => $record->status !== ContactLeadStatus::Spam)
                        ->requiresConfirmation()
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::Spam])),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_spam_bulk')
                        ->label('Mark as Spam')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => ContactLeadStatus::Spam])),
                    Tables\Actions\BulkAction::make('assign_to_me')
                        ->label('Assign to Me')
                        ->icon('heroicon-o-user-plus')
                        ->action(fn ($records) => $records->each->update(['assigned_to_user_id' => Auth::id()])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactLeads::route('/'),
            'create' => Pages\CreateContactLead::route('/create'),
            'view' => Pages\ViewContactLead::route('/{record}'),
            'edit' => Pages\EditContactLead::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) ContactLead::query()
            ->where('status', ContactLeadStatus::New)
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    private static function buildMailtoUrl(ContactLead $record): string
    {
        $subject = 'in.today – Your website & booking request';

        $restaurantName = $record->restaurant_name ?? 'your restaurant';
        $body = "Hi {$record->name},\n\nThank you for your interest in in.today for {$restaurantName}.\n\n";

        return 'mailto:' . rawurlencode($record->email)
            . '?subject=' . rawurlencode($subject)
            . '&body=' . rawurlencode($body);
    }
}
