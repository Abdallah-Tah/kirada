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
        <p style="font-size: 14px; line-height: 1.6; color: #475569;">
            {{ __('Your maintenance request is now being worked on. You will be notified when it is resolved.') }}
        </p>
    @elseif($newStatus === 'resolved')
        <p style="font-size: 14px; line-height: 1.6; color: #475569;">
            {{ __('The maintenance request has been marked as resolved. Please review and close it if you are satisfied.') }}
        </p>
    @elseif($newStatus === 'closed')
        <p style="font-size: 14px; line-height: 1.6; color: #475569;">
            {{ __('This maintenance request has been closed. No further action is required.') }}
        </p>
    @elseif($newStatus === 'cancelled')
        <p style="font-size: 14px; line-height: 1.6; color: #475569;">
            {{ __('This maintenance request has been cancelled.') }}
        </p>
    @endif
@endsection