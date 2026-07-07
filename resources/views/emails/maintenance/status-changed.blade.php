@extends('emails.maintenance.layout')

@section('email-content')
    {{-- Header with white logo card on blue gradient --}}
    <tr>
        <td style="background:linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%); padding:28px; text-align:center;">
            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                <tr>
                    <td style="background:#ffffff; border-radius:12px; padding:6px 12px; box-shadow:0 1px 2px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
                        <picture><source srcset="{{ asset('brand/kirada-logo.webp') }}" type="image/webp"><img src="{{ asset('brand/kirada-logo.jpg') }}" alt="Kirada" height="28" style="display:block; border-radius:8px;"></picture>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Hero --}}
    <tr>
        <td style="padding:32px 32px 16px;">
            <h2 style="margin:0; font-size:24px; color:#0f172a;">{{ __('Status update: :title', ['title' => $maintenanceRequest->title]) }}</h2>
            <p style="margin:8px 0 0; font-size:14px; color:#64748b;">
                {{ __('Reference: :ref', ['ref' => $maintenanceRequest->reference]) }}
                · {{ now()->format('d/m/Y H:i') }}
            </p>
        </td>
    </tr>

    {{-- Status Change Card --}}
    <tr>
        <td style="padding:16px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:14px; padding:24px; text-align:center;">
                <tr>
                    <td style="padding:24px;">
                        @if($previousStatus && $previousStatus !== $newStatus)
                            @php $statusColors = ['open' => '#2563eb', 'in_progress' => '#d97706', 'resolved' => '#16a34a', 'closed' => '#475569', 'cancelled' => '#dc2626']; @endphp
                            @php $statusBg = ['open' => '#eff6ff', 'in_progress' => '#fffbeb', 'resolved' => '#f0fdf4', 'closed' => '#f1f5f9', 'cancelled' => '#fef2f2']; @endphp
                            <span style="background:{{ $statusBg[$previousStatus] ?? '#f1f5f9' }}; color:{{ $statusColors[$previousStatus] ?? '#475569' }}; padding:6px 14px; border-radius:999px; font-size:13px; font-weight:bold;">
                                {{ __(ucfirst(str_replace('_', ' ', $previousStatus))) }}
                            </span>
                            <span style="display:inline-block; margin:0 12px; color:#94a3b8; font-size:18px; font-weight:bold;">→</span>
                            <span style="background:{{ $statusBg[$newStatus] ?? '#f1f5f9' }}; color:{{ $statusColors[$newStatus] ?? '#475569' }}; padding:6px 14px; border-radius:999px; font-size:13px; font-weight:bold;">
                                {{ __(ucfirst(str_replace('_', ' ', $newStatus))) }}
                            </span>
                        @else
                            @php $statusColors = ['open' => '#2563eb', 'in_progress' => '#d97706', 'resolved' => '#16a34a', 'closed' => '#475569', 'cancelled' => '#dc2626']; @endphp
                            @php $statusBg = ['open' => '#eff6ff', 'in_progress' => '#fffbeb', 'resolved' => '#f0fdf4', 'closed' => '#f1f5f9', 'cancelled' => '#fef2f2']; @endphp
                            <span style="background:{{ $statusBg[$newStatus] ?? '#f1f5f9' }}; color:{{ $statusColors[$newStatus] ?? '#475569' }}; padding:6px 14px; border-radius:999px; font-size:13px; font-weight:bold;">
                                {{ __(ucfirst(str_replace('_', ' ', $newStatus))) }}
                            </span>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Details Card --}}
    <tr>
        <td style="padding:16px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb; border-radius:14px; overflow:hidden;">
                <tr>
                    <td colspan="2" style="background:#f8fafc; padding:14px 16px; font-weight:bold; font-size:14px; color:#475569;">{{ __('Request details') }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 16px; color:#94a3b8; font-size:13px; width:120px;">{{ __('Request') }}</td>
                    <td style="padding:12px 16px; text-align:right; font-size:14px; color:#0f172a;">{{ $maintenanceRequest->title }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 16px; color:#94a3b8; font-size:13px;">{{ __('Property') }}</td>
                    <td style="padding:12px 16px; text-align:right; font-size:14px; color:#0f172a;">
                        {{ $maintenanceRequest->property?->name ?? '—' }}
                        @if($maintenanceRequest->unit) · {{ __('Unit') }} {{ $maintenanceRequest->unit->unit_number }}@endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Context Message --}}
    @if($newStatus === 'in_progress')
        <tr><td style="padding:8px 32px 16px; font-size:14px; line-height:1.6; color:#475569;">
            {{ __('Your maintenance request is now being worked on. You will be notified when it is resolved.') }}
        </td></tr>
    @elseif($newStatus === 'resolved')
        <tr><td style="padding:8px 32px 16px; font-size:14px; line-height:1.6; color:#475569;">
            {{ __('The maintenance request has been marked as resolved. Please review and close it if you are satisfied.') }}
        </td></tr>
    @elseif($newStatus === 'closed')
        <tr><td style="padding:8px 32px 16px; font-size:14px; line-height:1.6; color:#475569;">
            {{ __('This maintenance request has been closed. No further action is required.') }}
        </td></tr>
    @elseif($newStatus === 'cancelled')
        <tr><td style="padding:8px 32px 16px; font-size:14px; line-height:1.6; color:#475569;">
            {{ __('This maintenance request has been cancelled.') }}
        </td></tr>
    @endif
@endsection