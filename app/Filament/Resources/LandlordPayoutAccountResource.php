<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LandlordPayoutAccountResource\Pages;
use App\Models\LandlordPayoutAccount;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LandlordPayoutAccountResource extends Resource
{
    protected static ?string $model = LandlordPayoutAccount::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|\UnitEnum|null $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('landlord_id')
                            ->label('Landlord')
                            ->relationship('landlord', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('label')->required()->maxLength(255),
                        Forms\Components\Select::make('method')
                            ->options(LandlordPayoutAccount::METHODS)
                            ->required(),
                        Forms\Components\TextInput::make('account_number')->required()->maxLength(255),
                        Forms\Components\TextInput::make('account_name')->maxLength(255),
                        Forms\Components\Textarea::make('instructions')->rows(3),
                        Forms\Components\Toggle::make('is_primary')->default(false),
                        Forms\Components\Toggle::make('is_active')->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('label')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('landlord.name')->label('Landlord')->sortable(),
                Tables\Columns\TextColumn::make('method')->badge(),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('Account')
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('•', max(0, strlen($state) - 4)).substr($state, -4) : '')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_primary')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([Actions\EditAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLandlordPayoutAccounts::route('/'),
            'create' => Pages\CreateLandlordPayoutAccount::route('/create'),
            'edit' => Pages\EditLandlordPayoutAccount::route('/{record}/edit'),
        ];
    }
}
