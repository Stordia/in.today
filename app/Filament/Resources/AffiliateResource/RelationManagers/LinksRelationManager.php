<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use App\Filament\Resources\AffiliateLinkResource;
use App\Models\AffiliateLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class LinksRelationManager extends RelationManager
{
    protected static string $relationship = 'links';

    protected static ?string $title = 'Links';

    protected static ?string $icon = 'heroicon-o-link';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(191)
                    ->unique(table: 'affiliate_links', column: 'slug', ignoreRecord: true)
                    ->helperText('Used in /go/{slug} redirect URL. Use lowercase letters, numbers, and hyphens.')
                    ->rules(['regex:/^[a-z0-9\-]+$/'])
                    ->validationMessages([
                        'regex' => 'Slug must contain only lowercase letters, numbers, and hyphens.',
                    ]),

                Forms\Components\TextInput::make('target_url')
                    ->label('Target URL')
                    ->required()
                    ->url()
                    ->maxLength(2048)
                    ->columnSpanFull()
                    ->helperText('Full URL where visitors will be redirected, e.g. https://example.com/landing'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive links will not redirect visitors.'),

                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(2)
                    ->maxLength(1000)
                    ->columnSpanFull()
                    ->helperText('Internal notes about this link (optional).'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('slug')
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_url')
                    ->label('Target')
                    ->limit(40)
                    ->tooltip(fn (AffiliateLink $record): string => $record->target_url ?? '')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('share_url')
                    ->label('Share URL')
                    ->getStateUsing(fn (AffiliateLink $record): string => url('/go/' . $record->slug))
                    ->copyable()
                    ->copyMessage('Share URL copied!')
                    ->icon('heroicon-o-clipboard-document')
                    ->iconPosition('after'),
                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('Clicks')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('conversions_count')
                    ->label('Conversions')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (AffiliateLink $record): string => AffiliateLinkResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
