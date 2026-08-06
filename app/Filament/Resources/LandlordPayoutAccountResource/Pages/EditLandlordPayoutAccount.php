<?php

namespace App\Filament\Resources\LandlordPayoutAccountResource\Pages;

use App\Filament\Resources\LandlordPayoutAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLandlordPayoutAccount extends EditRecord
{
    protected static string $resource = LandlordPayoutAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
