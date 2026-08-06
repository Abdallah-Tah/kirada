<?php

namespace App\Filament\Resources\MaintenanceProfileResource\Pages;

use App\Filament\Resources\MaintenanceProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaintenanceProfile extends EditRecord
{
    protected static string $resource = MaintenanceProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
