<?php

namespace App\Filament\Resources\LandlordTeamMembershipResource\Pages;

use App\Filament\Resources\LandlordTeamMembershipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLandlordTeamMembership extends EditRecord
{
    protected static string $resource = LandlordTeamMembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
