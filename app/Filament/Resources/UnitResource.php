<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Models\Building;
use App\Models\Unit;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home-modern';

    protected static string|\UnitEnum|null $navigationGroup = 'Portfolio';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\Select::make('property_id')
                            ->label('Property')
                            ->relationship('property', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('building_id', null)),
                        Forms\Components\Select::make('building_id')
                            ->label('Building')
                            ->options(fn (Get $get) => Building::query()
                                ->where('property_id', $get('property_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('unit_number')->required()->maxLength(50),
                        Forms\Components\TextInput::make('floor')->numeric()->default(0),
                        Forms\Components\Select::make('type')
                            ->options([
                                'apartment' => 'Apartment',
                                'office' => 'Office',
                                'shop' => 'Shop',
                                'warehouse' => 'Warehouse',
                                'other' => 'Other',
                            ])
                            ->default('apartment')
                            ->required(),
                        Forms\Components\TextInput::make('area_sqm')->label('Area (m²)')->numeric(),
                        Forms\Components\TextInput::make('bedrooms')->numeric()->default(0),
                        Forms\Components\TextInput::make('bathrooms')->numeric()->default(0),
                        Forms\Components\TextInput::make('monthly_rent')->numeric(),
                        Forms\Components\TextInput::make('security_deposit')->numeric(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'vacant' => 'Vacant',
                                'occupied' => 'Occupied',
                                'maintenance' => 'Maintenance',
                            ])
                            ->default('vacant')
                            ->required(),
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
                Tables\Columns\TextColumn::make('unit_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('property.name')->label('Property')->sortable(),
                Tables\Columns\TextColumn::make('building.name')->label('Building')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('bedrooms')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('monthly_rent')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['vacant' => 'Vacant', 'occupied' => 'Occupied', 'maintenance' => 'Maintenance']),
            ])
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
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
