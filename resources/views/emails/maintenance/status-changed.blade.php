@extends('emails.maintenance.layout')

@section('email-content')
    <h2 class="subject">{{ __('Status update: :title', ['title' => $maintenanceRequest->title]) }}</h2>
    <p class="meta">
        {{ __('Reference: :ref', ['ref' => $maintenanceRequest->reference]) }}
        · {{ now()->format('d/m/Y H:i') }}
    </p>

    <table class="field-table">
        <tr>
            <td class="label">{{ __('Request') }}</td>
            <td class="value">{{ $maintenanceRequest->title }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Property') }}</td>
            <td class="value">{{ $maintenanceRequest->property?->name ?? '—' }}
                @if($maintenanceRequest->unit) · {{ __('Unit') }} {{ $maintenanceRequest->unit->unit_number }}@endif
            </td>
        </tr>
        @if($previousStatus && $previousStatus !== $newStatus)
        <tr>
            <td class="label">{{ __('Previous') }}</td>
            <td class="value">
                <span class="status-badge status-{{ $previousStatus }}">
                    {{ __(ucfirst(str_replace('_', ' ', $previousStatus))) }}
                </span>
            </td>
        </tr>
        @endif
        <tr>
            <td class="label">{{ __('New status') }}</td>
            <td class="value">
                <span class="status-badge status-{{ $newStatus }}">
                    {{ __(ucfirst(str_replace('_', ' ', $newStatus))) }}
                </span>
            </td>
        </tr>
    </table>

    @if($newStatus === 'in_progress')
        <div class="context-msg">{{ __('Your maintenance request is now being worked on. You will be notified when it is resolved.') }}</div>
    @elseif($newStatus === 'resolved')
        <div class="context-msg">{{ __('The maintenance request has been marked as resolved. Please review and close it if you are satisfied.') }}</div>
    @elseif($newStatus === 'closed')
        <div class="context-msg">{{ __('This maintenance request has been closed. No further action is required.') }}</div>
    @elseif($newStatus === 'cancelled')
        <div class="context-msg">{{ __('This maintenance request has been cancelled.') }}</div>
    @endif
@endsection