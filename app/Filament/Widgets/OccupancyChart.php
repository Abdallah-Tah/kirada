<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use Filament\Widgets\ChartWidget;

class OccupancyChart extends ChartWidget
{
    protected ?string $heading = 'Properties & Occupancy';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $properties = Property::withCount(['units as total_units'])
            ->with(['units' => fn ($q) => $q->where('status', 'occupied')])
            ->limit(10)
            ->get();

        $labels = $properties->pluck('name')->toArray();
        $occupied = $properties->map(fn ($p) => $p->units->count())->toArray();
        $vacant = $properties->map(fn ($p) => $p->total_units - $p->units->count())->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Occupied',
                    'data' => $occupied,
                    'backgroundColor' => '#10b981',
                ],
                [
                    'label' => 'Vacant',
                    'data' => $vacant,
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
