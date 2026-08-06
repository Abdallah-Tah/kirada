<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document';

    protected static string|\UnitEnum|null $navigationGroup = 'Communications';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\Select::make('landlord_id')
                            ->label('Landlord')
                            ->relationship('landlord', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('tenant_id')
                            ->label('Tenant')
                            ->relationship('tenant', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('lease_id')
                            ->label('Lease')
                            ->relationship('lease', 'id')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('title')->required()->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options(['lease_agreement' => 'Lease Agreement', 'payment_receipt' => 'Payment Receipt', 'payment_proof' => 'Payment Proof', 'id_document' => 'ID Document', 'other' => 'Other'])
                            ->default('other')
                            ->required(),
                        Forms\Components\FileUpload::make('file_path')->label('File')->required()->directory('documents'),
                        Forms\Components\Select::make('visibility')
                            ->options(['landlord_only' => 'Landlord Only', 'tenant_visible' => 'Tenant Visible', 'admin_only' => 'Admin Only'])
                            ->default('landlord_only')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('landlord.name')->label('Landlord')->sortable(),
                Tables\Columns\TextColumn::make('tenant.full_name')->label('Tenant')->sortable(),
                Tables\Columns\TextColumn::make('size')->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 1).' KB' : ''),
                Tables\Columns\TextColumn::make('visibility')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(['lease_agreement' => 'Lease Agreement', 'payment_receipt' => 'Payment Receipt', 'payment_proof' => 'Payment Proof', 'id_document' => 'ID Document', 'other' => 'Other']),
            ])
            ->actions([
                Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Document $record) => Storage::download($record->file_path, $record->original_filename)),
                Actions\EditAction::make(),
            ])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
