<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Support\Money;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|\UnitEnum|null $navigationGroup = 'Rent & Payments';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Expense details')
                ->schema([
                    Forms\Components\Select::make('landlord_id')
                        ->relationship('landlord', 'name', modifyQueryUsing: fn (Builder $query) => $query->role('landlord'))
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\DatePicker::make('expense_date')->required()->default(now()),
                    Forms\Components\Select::make('category')
                        ->options(Expense::CATEGORIES)
                        ->default('miscellaneous')
                        ->required(),
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->minValue(0.01)
                        ->required(),
                    Forms\Components\Select::make('currency_id')
                        ->relationship('currency', 'code')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('property_id')
                        ->relationship('property', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('address')
                        ->label('Address / expense location')
                        ->maxLength(500),
                    Forms\Components\Select::make('payment_method')
                        ->options(Expense::PAYMENT_METHODS)
                        ->default('cash'),
                    Forms\Components\TextInput::make('vendor')->maxLength(255),
                    Forms\Components\TextInput::make('description')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                    Forms\Components\FileUpload::make('receipt_path')
                        ->label('Receipt or supporting file')
                        ->disk('private')
                        ->directory('expenses/admin')
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'image/heic',
                            'image/heif',
                        ])
                        ->maxSize(10240)
                        ->downloadable()
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['landlord', 'property', 'currency']))
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('landlord.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('category')->badge()->formatStateUsing(fn (string $state) => Expense::CATEGORIES[$state] ?? $state),
                Tables\Columns\TextColumn::make('property.name')->placeholder('General')->sortable(),
                Tables\Columns\TextColumn::make('address')->label('Address')->searchable()->limit(45)->placeholder('—'),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn ($state, Expense $record) => Money::format((float) $state, $record->currency))
                    ->sortable(),
                Tables\Columns\IconColumn::make('receipt_path')->label('Receipt')->boolean(),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options(Expense::CATEGORIES),
                Tables\Filters\SelectFilter::make('landlord')->relationship('landlord', 'name')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('property')->relationship('property', 'name')->searchable()->preload(),
            ])
            ->actions([
                Actions\Action::make('receipt')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Expense $record) => route('expenses.receipt', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Expense $record) => $record->hasReceipt()),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
