<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationDeliveryResource\Pages;
use App\Models\NotificationDelivery;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationDeliveryResource extends Resource
{
    protected static ?string $model = NotificationDelivery::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell';

    protected static string|\UnitEnum|null $navigationGroup = 'Communications';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('event')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('channel')->searchable()->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('recipient_masked')->label('Recipient')->toggleable(),
                Tables\Columns\TextColumn::make('attempts')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        NotificationDelivery::STATUS_QUEUED => 'Queued',
                        NotificationDelivery::STATUS_PROCESSING => 'Processing',
                        NotificationDelivery::STATUS_RETRYING => 'Retrying',
                        NotificationDelivery::STATUS_SENT => 'Sent',
                        NotificationDelivery::STATUS_DELIVERED => 'Delivered',
                        NotificationDelivery::STATUS_READ => 'Read',
                        NotificationDelivery::STATUS_SKIPPED => 'Skipped',
                        NotificationDelivery::STATUS_FAILED => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('channel')
                    ->options(['email' => 'Email', 'whatsapp' => 'WhatsApp']),
            ])
            ->actions([Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Delivery Details')
                    ->schema([
                        TextEntry::make('event'),
                        TextEntry::make('channel')->badge(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('recipient_masked')->label('Recipient'),
                        TextEntry::make('attempts'),
                        TextEntry::make('error_code'),
                        TextEntry::make('error_message'),
                    ])
                    ->columns(2),
                Section::make('Timeline')
                    ->schema([
                        TextEntry::make('queued_at')->dateTime(),
                        TextEntry::make('sent_at')->dateTime(),
                        TextEntry::make('delivered_at')->dateTime(),
                        TextEntry::make('read_at')->dateTime(),
                        TextEntry::make('failed_at')->dateTime(),
                        TextEntry::make('created_at')->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationDeliveries::route('/'),
            'view' => Pages\ViewNotificationDelivery::route('/{record}'),
        ];
    }
}
