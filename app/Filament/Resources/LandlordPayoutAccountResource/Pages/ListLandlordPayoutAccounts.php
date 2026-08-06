<?php

namespace App\Filament\Resources\LandlordPayoutAccountResource\Pages;

use App\Filament\Resources\LandlordPayoutAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLandlordPayoutAccounts extends ListRecords
{
    protected static string $resource = LandlordPayoutAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
