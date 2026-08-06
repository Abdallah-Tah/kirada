<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentInvoiceResource\Pages;
use App\Filament\Resources\RentInvoiceResource\RelationManagers;
use App\Models\RentInvoice;
use App\Services\InvoiceDeliveryService;
use App\Services\InvoicePdfFactory;
use App\Services\RentInvoiceService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RentInvoiceResource extends Resource
{
    protected static ?string $model = RentInvoice::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static string|\UnitEnum|null $navigationGroup = 'Rent & Payments';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')->required()->maxLength(100),
                        Forms\Components\TextInput::make('payment_reference')->maxLength(100),
                        Forms\Components\TextInput::make('amount')->numeric()->required(),
                        Forms\Components\DatePicker::make('due_date')->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'scheduled' => 'Scheduled',
                                'sent' => 'Sent',
                                'unpaid' => 'Unpaid',
                                'partially_paid' => 'Partially Paid',
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['tenant', 'property', 'currency']))
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('invoice_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('payment_reference')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('tenant.full_name')->label('Tenant')->sortable(),
                Tables\Columns\TextColumn::make('property.name')->label('Property')->sortable(),
                Tables\Columns\TextColumn::make('amount')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'unpaid' => 'Unpaid',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Actions\Action::make('download-pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (RentInvoice $record, InvoicePdfFactory $factory) => response()->download($factory->make($record)))
                    ->openUrlInNewTab(),
                Actions\Action::make('send-email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->requiresConfirmation()
                    ->action(function (RentInvoice $record, InvoiceDeliveryService $service) {
                        $service->dispatch($record, 'invoice_sent');
                        Notification::make()->title('Invoice sent via email.')->success()->send();
                    }),
                Actions\Action::make('send-whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->requiresConfirmation()
                    ->visible(fn (RentInvoice $record) => $record->tenant?->hasWhatsAppConsent())
                    ->action(function (RentInvoice $record, InvoiceDeliveryService $service) {
                        $service->dispatch($record, 'invoice_sent', null, ['whatsapp']);
                        Notification::make()->title('Invoice sent via WhatsApp.')->success()->send();
                    }),
                Actions\Action::make('mark-overdue')
                    ->label('Mark Overdue')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (RentInvoice $record, RentInvoiceService $service) {
                        $service->markOverdue();
                        Notification::make()->title('Invoices marked overdue.')->success()->send();
                    }),
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LineItemsRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
            RelationManagers\DeliveriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRentInvoices::route('/'),
            'create' => Pages\CreateRentInvoice::route('/create'),
            'view' => Pages\ViewRentInvoice::route('/{record}'),
            'edit' => Pages\EditRentInvoice::route('/{record}/edit'),
        ];
    }
}
