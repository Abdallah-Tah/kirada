@extends('emails.maintenance.layout')

@section('email-content')
    <h2 class="subject">{{ __('New comment on: :title', ['title' => $maintenanceRequest->title]) }}</h2>
    <p class="meta">
        {{ __('Reference: :ref', ['ref' => $maintenanceRequest->reference]) }}
        · {{ $comment->created_at->format('d/m/Y H:i') }}
    </p>

    <table class="field-table">
        <tr>
            <td class="label">{{ __('Request') }}</td>
            <td class="value">{{ $maintenanceRequest->title }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Property') }}</td>
            <td class="value">{{ $maintenanceRequest->property?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Status') }}</td>
            <td class="value">
                <span class="status-badge status-{{ $maintenanceRequest->status }}">
                    {{ __(ucfirst(str_replace('_', ' ', $maintenanceRequest->status))) }}
                </span>
            </td>
        </tr>
    </table>

    <div class="comment-box">
        <div class="comment-author">{{ $comment->user?->name ?? '—' }}</div>
        {{ $comment->comment }}
    </div>
@endsection