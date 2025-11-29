<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use App\Filament\Resources\AffiliateLinkResource;
use App\Models\AffiliateLink;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    protected static string $relationship = 'links';

    protected static ?string $title = 'Links';

    protected static ?string $icon = 'heroicon-o-link';

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
