<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceProfileResource\Pages;
use App\Models\MaintenanceProfile;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MaintenanceProfileResource extends Resource
{
    protected static ?string $model = MaintenanceProfile::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('business_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('headline')->maxLength(255),
                        Forms\Components\Textarea::make('bio')->rows(3),
                        Forms\Components\CheckboxList::make('trades')
                            ->options(array_combine(MaintenanceProfile::TRADES, array_map('ucfirst', array_map(function ($t) {
                                return str_replace('_', ' ', $t);
                            }, MaintenanceProfile::TRADES))))
                            ->columns(2),
                        Forms\Components\TagsInput::make('service_areas'),
                        Forms\Components\TagsInput::make('languages'),
                        Forms\Components\TextInput::make('hourly_rate')->numeric(),
                        Forms\Components\TextInput::make('callout_fee')->numeric(),
                        Forms\Components\TextInput::make('phone')->tel(),
                        Forms\Components\TextInput::make('whatsapp')->tel(),
                        Forms\Components\TextInput::make('website')->url(),
                        Forms\Components\TextInput::make('years_experience')->numeric(),
                        Forms\Components\TextInput::make('availability_status')->maxLength(50),
                        Forms\Components\Toggle::make('is_published')->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('business_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('User')->sortable(),
                Tables\Columns\TextColumn::make('trades')->badge()->separator(',')->limitList(3),
                Tables\Columns\TextColumn::make('service_areas')->badge()->separator(',')->limitList(2)->toggleable(),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('verified_at')->dateTime()->placeholder('Not verified'),
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
            'index' => Pages\ListMaintenanceProfiles::route('/'),
            'create' => Pages\CreateMaintenanceProfile::route('/create'),
            'edit' => Pages\EditMaintenanceProfile::route('/{record}/edit'),
        ];
    }
}
