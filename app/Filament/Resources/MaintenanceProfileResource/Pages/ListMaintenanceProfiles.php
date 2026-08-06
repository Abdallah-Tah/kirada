<?php

namespace App\Filament\Resources\MaintenanceProfileResource\Pages;

use App\Filament\Resources\MaintenanceProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceProfiles extends ListRecords
{
    protected static string $resource = MaintenanceProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
