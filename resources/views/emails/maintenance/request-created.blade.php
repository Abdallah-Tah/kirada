@extends('emails.maintenance.layout')

@section('email-content')
    <h2 class="subject">{{ __('New maintenance request') }}</h2>
    <p class="meta">
        {{ __('Reference: :ref', ['ref' => $maintenanceRequest->reference]) }}
        · {{ $maintenanceRequest->created_at->format('d/m/Y H:i') }}
    </p>

    <table class="field-table">
        <tr>
            <td class="label">{{ __('Title') }}</td>
            <td class="value">{{ $maintenanceRequest->title }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Property') }}</td>
            <td class="value">{{ $maintenanceRequest->property?->name ?? '—' }}
                @if($maintenanceRequest->unit) · {{ __('Unit') }} {{ $maintenanceRequest->unit->unit_number }}@endif
            </td>
        </tr>
        <tr>
            <td class="label">{{ __('Category') }}</td>
            <td class="value">{{ $maintenanceRequest->category_label }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Priority') }}</td>
            <td class="value">
                <span class="priority-badge priority-{{ $maintenanceRequest->priority }}">
                    {{ __(ucfirst($maintenanceRequest->priority)) }}
                </span>
            </td>
        </tr>
        @if($maintenanceRequest->location)
        <tr>
            <td class="label">{{ __('Location') }}</td>
            <td class="value">{{ $maintenanceRequest->location }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">{{ __('Reported by') }}</td>
            <td class="value">{{ $maintenanceRequest->reporter?->name ?? '—' }}</td>
        </tr>
    </table>

    <div class="description">
        {{ $maintenanceRequest->description }}
    </div>
@endsection