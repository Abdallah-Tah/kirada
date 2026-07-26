<?php

namespace App\Livewire\MaintenanceProfiles;

use App\Models\MaintenanceProfile;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public MaintenanceProfile $profile;

    public function mount(MaintenanceProfile $profile): void
    {
        $this->authorize('view', $profile);
        abort_unless($profile->is_published || $profile->user_id === auth()->id() || auth()->user()->isAdmin(), 404);

        $this->profile = $profile->load([
            'user' => fn ($query) => $query->withCount([
                'assignedMaintenanceRequests as completed_jobs_count' => fn ($query) => $query
                    ->whereIn('status', ['resolved', 'closed']),
            ]),
            'currency',
        ])->loadAvg('reviews', 'rating')->loadCount('reviews');
    }

    public function render()
    {
        $reviews = $this->profile->reviews()
            ->with('landlord:id,name')
            ->paginate(10);

        return view('livewire.maintenance-profiles.show', compact('reviews'))
            ->layout('layouts.app')
            ->title($this->profile->business_name);
    }
}
