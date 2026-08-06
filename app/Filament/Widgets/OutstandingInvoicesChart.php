<?php

namespace App\Filament\Widgets;

use App\Models\RentInvoice;
use Filament\Widgets\ChartWidget;

class OutstandingInvoicesChart extends ChartWidget
{
    protected ?string $heading = 'Outstanding Invoices by Status';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $statuses = ['unpaid', 'partially_paid', 'overdue'];
        $data = [];
        $labels = [];
        $colors = ['#f59e0b', '#3b82f6', '#ef4444'];

        foreach ($statuses as $index => $status) {
            $count = RentInvoice::where('status', $status)->count();
            $data[] = $count;
            $labels[] = ucfirst(str_replace('_', ' ', $status));
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
