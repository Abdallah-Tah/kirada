<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditEventResource\Pages;
use App\Models\AuditEvent;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AuditEventResource extends Resource
{
    protected static ?string $model = AuditEvent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('actor.name')->label('Actor')->sortable(),
                Tables\Columns\TextColumn::make('auditable_type')->label('Type')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('event')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('route_name')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Audit Details')
                    ->schema([
                        TextEntry::make('actor.name')->label('Actor'),
                        TextEntry::make('auditable_type')->label('Auditable Type'),
                        TextEntry::make('auditable_id')->label('Auditable ID'),
                        TextEntry::make('event'),
                        TextEntry::make('route_name')->label('Route'),
                        TextEntry::make('ip_address')->label('IP Address'),
                        TextEntry::make('request_id')->label('Request ID'),
                        TextEntry::make('user_agent')->label('User Agent')->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Values')
                    ->schema([
                        TextEntry::make('old_values')
                            ->label('Old Values')
                            ->formatStateUsing(fn ($state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '')
                            ->columnSpanFull(),
                        TextEntry::make('new_values')
                            ->label('New Values')
                            ->formatStateUsing(fn ($state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '')
                            ->columnSpanFull(),
                    ]),
                Section::make('Timestamp')
                    ->schema([
                        TextEntry::make('created_at')->dateTime(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditEvents::route('/'),
            'view' => Pages\ViewAuditEvent::route('/{record}'),
        ];
    }
}
