<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentPaymentResource\Pages;
use App\Filament\Resources\RentPaymentResource\RelationManagers;
use App\Models\RentPayment;
use App\Services\BrandedPdfService;
use App\Services\ReceiptDeliveryService;
use App\Services\RentPaymentService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RentPaymentResource extends Resource
{
    protected static ?string $model = RentPayment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Rent & Payments';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('payment_number')->required()->maxLength(100),
                        Forms\Components\TextInput::make('amount')->numeric()->required(),
                        Forms\Components\DatePicker::make('payment_date')->required(),
                        Forms\Components\Select::make('method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'mobile_money' => 'Mobile Money',
                                'check' => 'Check',
                                'other' => 'Other',
                            ])
                            ->default('cash')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected'])
                            ->default('pending')
                            ->required(),
                        Forms\Components\TextInput::make('reference_number')->maxLength(100),
                        Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['tenant', 'rentInvoice', 'currency']))
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('payment_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tenant.full_name')->label('Tenant')->sortable(),
                Tables\Columns\TextColumn::make('rentInvoice.invoice_number')->label('Invoice')->sortable(),
                Tables\Columns\TextColumn::make('amount')->sortable(),
                Tables\Columns\TextColumn::make('method')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('payment_date')->date()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected']),
            ])
            ->actions([
                Actions\Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (RentPayment $record) => $record->isPending())
                    ->action(function (RentPayment $record, RentPaymentService $service) {
                        $service->confirmPayment($record, Auth::user()?->id);
                        Notification::make()->title('Payment confirmed.')->success()->send();
                    }),
                Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (RentPayment $record) => $record->isPending())
                    ->action(function (RentPayment $record, RentPaymentService $service) {
                        $service->rejectPayment($record);
                        Notification::make()->title('Payment rejected.')->success()->send();
                    }),
                Actions\Action::make('download-receipt')
                    ->label('Receipt')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (RentPayment $record) => $record->isConfirmed())
                    ->action(fn (RentPayment $record, BrandedPdfService $pdf) => response()->download($pdf->render('receipts.payment', ['payment' => $record], $record->payment_number, $record->payment_date)))
                    ->openUrlInNewTab(),
                Actions\Action::make('email-receipt')
                    ->label('Email Receipt')
                    ->icon('heroicon-o-envelope')
                    ->requiresConfirmation()
                    ->visible(fn (RentPayment $record) => $record->isConfirmed())
                    ->action(function (RentPayment $record, ReceiptDeliveryService $service) {
                        $service->dispatch($record, 'receipt_sent');
                        Notification::make()->title('Receipt emailed.')->success()->send();
                    }),
                Actions\Action::make('whatsapp-receipt')
                    ->label('WhatsApp Receipt')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->requiresConfirmation()
                    ->visible(fn (RentPayment $record) => $record->isConfirmed() && $record->tenant?->hasWhatsAppConsent())
                    ->action(function (RentPayment $record, ReceiptDeliveryService $service) {
                        $service->dispatch($record, 'receipt_sent', null, ['whatsapp']);
                        Notification::make()->title('Receipt sent via WhatsApp.')->success()->send();
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
        return [RelationManagers\DeliveriesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRentPayments::route('/'),
            'create' => Pages\CreateRentPayment::route('/create'),
            'view' => Pages\ViewRentPayment::route('/{record}'),
            'edit' => Pages\EditRentPayment::route('/{record}/edit'),
        ];
    }
}
