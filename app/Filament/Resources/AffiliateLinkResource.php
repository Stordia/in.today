<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateLinkResource\Pages;
use App\Models\AffiliateLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateLinkResource extends Resource
{
    protected static ?string $model = AffiliateLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Partners';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Affiliate Links';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Link Details')
                    ->schema([
                        Forms\Components\Select::make('affiliate_id')
                            ->relationship('affiliate', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Short identifier, e.g. newsletter-q1, berlin-event'),
                        Forms\Components\TextInput::make('target_url')
                            ->required()
                            ->url()
                            ->maxLength(2048)
                            ->columnSpanFull()
                            ->helperText('Full URL where the link redirects, e.g. https://in.today/en?utm_source=...'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('clicks_count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('conversions_count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2)
                    ->hiddenOn('create'),

                Forms\Components\Section::make('Additional')
                    ->schema([
                        Forms\Components\Textarea::make('metadata')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('JSON format for custom tracking parameters'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('affiliate.name')
                    ->label('Affiliate')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('share_url')
                    ->label('Share URL')
                    ->getStateUsing(fn (AffiliateLink $record): string => url('/go/' . $record->slug))
                    ->copyable()
                    ->copyMessage('Share URL copied!')
                    ->icon('heroicon-o-clipboard-document')
                    ->iconPosition('after'),
                Tables\Columns\TextColumn::make('target_url')
                    ->label('Target URL')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->target_url)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('Clicks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('conversions_count')
                    ->label('Conversions')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('affiliate_id')
                    ->label('Affiliate')
                    ->relationship('affiliate', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAffiliateLinks::route('/'),
            'create' => Pages\CreateAffiliateLink::route('/create'),
            'view' => Pages\ViewAffiliateLink::route('/{record}'),
            'edit' => Pages\EditAffiliateLink::route('/{record}/edit'),
        ];
    }
}
