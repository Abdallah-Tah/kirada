<?php

namespace App\Filament\Resources\LandlordTeamMembershipResource\Pages;

use App\Filament\Resources\LandlordTeamMembershipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLandlordTeamMemberships extends ListRecords
{
    protected static string $resource = LandlordTeamMembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
