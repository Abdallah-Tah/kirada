<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class RentInvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'rentInvoices';

    protected static ?string $title = 'Rent Invoices';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number'),
                Tables\Columns\TextColumn::make('tenant.full_name')->label('Tenant'),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('due_date')->date(),
            ])
            ->headerActions([])
            ->actions([ViewAction::make()]);
    }
}
