<?php

namespace App\Filament\Widgets;

use App\Models\RentPayment;
use Filament\Widgets\ChartWidget;

class PaymentsByMethodChart extends ChartWidget
{
    protected ?string $heading = 'Payments by Method';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $payments = RentPayment::where('status', 'confirmed')
            ->where('payment_date', '>=', now()->subMonths(3))
            ->selectRaw('method, COUNT(*) as count')
            ->groupBy('method')
            ->pluck('count', 'method')
            ->toArray();

        $labels = array_keys($payments);
        $data = array_values($payments);
        $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6'];

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                ],
            ],
            'labels' => array_map(fn ($m) => ucfirst(str_replace('_', ' ', $m)), $labels),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
