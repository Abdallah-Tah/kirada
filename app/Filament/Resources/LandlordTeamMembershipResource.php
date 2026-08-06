<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LandlordTeamMembershipResource\Pages;
use App\Models\LandlordTeamMembership;
use App\Services\LandlordTeamService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LandlordTeamMembershipResource extends Resource
{
    protected static ?string $model = LandlordTeamMembership::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static string|\UnitEnum|null $navigationGroup = 'People';

    protected static ?int $navigationSort = 3;

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
                        Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
                        Forms\Components\Select::make('role')
                            ->options(array_combine(LandlordTeamMembership::ROLES, array_map('ucfirst', array_map(function ($r) {
                                return str_replace('-', ' ', $r);
                            }, LandlordTeamMembership::ROLES))))
                            ->required(),
                        Forms\Components\TextInput::make('phone')->tel(),
                        Forms\Components\CheckboxList::make('delivery_channels')
                            ->options(['email' => 'Email', 'whatsapp' => 'WhatsApp'])
                            ->default(['email']),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('landlord.name')->label('Landlord')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->placeholder(fn ($record) => $record->email),
                Tables\Columns\TextColumn::make('role')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('whatsapp_status')->label('WhatsApp')->badge()->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Actions\Action::make('resend-invitation')
                    ->label('Resend Invite')
                    ->icon('heroicon-o-paper-airplane')
                    ->requiresConfirmation()
                    ->action(function (LandlordTeamMembership $record, LandlordTeamService $service) {
                        $service->invite($record->landlord, $record->email, $record->role, $record->phone, $record->delivery_channels ?? ['email']);
                        Notification::make()->title('Invitation resent.')->success()->send();
                    }),
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
            'index' => Pages\ListLandlordTeamMemberships::route('/'),
            'create' => Pages\CreateLandlordTeamMembership::route('/create'),
            'edit' => Pages\EditLandlordTeamMembership::route('/{record}/edit'),
        ];
    }
}
