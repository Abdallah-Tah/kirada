@extends('emails.maintenance.layout')

@section('email-content')
    {{-- Header with white logo card on blue gradient --}}
    <tr>
        <td style="background:linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%); padding:28px; text-align:center;">
            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                <tr>
                    <td style="background:#ffffff; border-radius:12px; padding:6px 12px; box-shadow:0 1px 2px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
                        <picture><source srcset="{{ asset('brand/kirada-logo-transparent.webp') }}" type="image/webp"><img src="{{ asset('brand/kirada-logo-transparent.png') }}" alt="Kirada" height="28" style="display:block; border-radius:8px;"></picture>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Hero --}}
    <tr>
        <td style="padding:32px 32px 16px;">
            <h2 style="margin:0; font-size:24px; color:#0f172a;">{{ __('New maintenance request') }}</h2>
            <p style="margin:8px 0 0; font-size:14px; color:#64748b;">
                {{ __('Reference: :ref', ['ref' => $maintenanceRequest->reference]) }}
                · {{ $maintenanceRequest->created_at->format('d/m/Y H:i') }}
            </p>
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
                    <td style="padding:12px 16px; color:#94a3b8; font-size:13px; width:120px;">{{ __('Title') }}</td>
                    <td style="padding:12px 16px; text-align:right; font-size:14px; color:#0f172a;">{{ $maintenanceRequest->title }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 16px; color:#94a3b8; font-size:13px;">{{ __('Property') }}</td>
                    <td style="padding:12px 16px; text-align:right; font-size:14px; color:#0f172a;">
                        {{ $maintenanceRequest->property?->name ?? '—' }}
                        @if($maintenanceRequest->unit) · {{ __('Unit') }} {{ $maintenanceRequest->unit->unit_number }}@endif
                    </td>
                </tr>
                <tr>
                    <td style="padding:12px 16px; color:#94a3b8; font-size:13px;">{{ __('Category') }}</td>
                    <td style="padding:12px 16px; text-align:right; font-size:14px; color:#0f172a;">{{ $maintenanceRequest->category_label }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 16px; color:#94a3b8; font-size:13px;">{{ __('Priority') }}</td>
                    <td style="padding:12px 16px; text-align:right;">
                        @php $prioColors = ['low' => '#16a34a', 'medium' => '#d97706', 'high' => '#dc2626', 'urgent' => '#dc2626']; @endphp
                        @php $prioBg = ['low' => '#f0fdf4', 'medium' => '#fffbeb', 'high' => '#fef2f2', 'urgent' => '#fef2f2']; @endphp
                        <span style="background:{{ $prioBg[$maintenanceRequest->priority] ?? '#f1f5f9' }}; color:{{ $prioColors[$maintenanceRequest->priority] ?? '#475569' }}; padding:5px 12px; border-radius:999px; font-size:12px; font-weight:bold;">
                            {{ __(ucfirst($maintenanceRequest->priority)) }}
                        </span>
                    </td>
                </tr>
                @if($maintenanceRequest->location)
                <tr>
                    <td style="padding:12px 16px; color:#94a3b8; font-size:13px;">{{ __('Location') }}</td>
                    <td style="padding:12px 16px; text-align:right; font-size:14px; color:#0f172a;">{{ $maintenanceRequest->location }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:12px 16px; color:#94a3b8; font-size:13px;">{{ __('Reported by') }}</td>
                    <td style="padding:12px 16px; text-align:right; font-size:14px; color:#0f172a;">{{ $maintenanceRequest->reporter?->name ?? '—' }}</td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Description Card --}}
    <tr>
        <td style="padding:16px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border-left:4px solid #0EA5E9; border-radius:0 12px 12px 0;">
                <tr>
                    <td style="padding:16px; font-size:14px; line-height:1.6; color:#334155;">
                        {{ $maintenanceRequest->description }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
@endsection