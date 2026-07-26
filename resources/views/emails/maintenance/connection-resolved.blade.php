@extends('emails.maintenance.layout')

@section('email-content')
    {{-- Header with white logo card on blue gradient --}}
    <tr>
        <td class="header-bg" style="background:linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%); padding:28px; text-align:center;">
            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                <tr>
                    <td class="logo-card" style="background:#ffffff; border-radius:12px; padding:6px 12px; box-shadow:0 1px 2px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
                        <picture><source srcset="{{ asset('brand/kirada-logo-transparent.webp') }}" type="image/webp"><img src="{{ asset('brand/kirada-logo-transparent.png') }}" alt="Kirada" height="28" style="display:block; border-radius:8px;"></picture>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Hero --}}
    <tr>
        <td style="padding:32px 32px 16px;">
            <h2 style="margin:0; font-size:24px; color:#0f172a;">
                {{ $approved ? __('Invitation accepted') : __('Invitation declined') }}
            </h2>
            <p style="margin:8px 0 0; font-size:14px; color:#64748b;">
                {{ $approved
                    ? __(':provider joined your approved maintenance team.', ['provider' => $provider->name])
                    : __(':provider is not available for your properties right now.', ['provider' => $provider->name]) }}
            </p>
        </td>
    </tr>

    <tr>
        <td style="padding:16px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:{{ $approved ? '#f0fdf4' : '#f8fafc' }}; border-left:4px solid {{ $approved ? '#16a34a' : '#94a3b8' }}; border-radius:0 12px 12px 0;">
                <tr>
                    <td style="padding:16px; font-size:14px; line-height:1.6; color:#334155;">
                        {{ $approved
                            ? __('You can now assign work orders to them from any maintenance request.')
                            : __('You can browse the directory to find another provider in your area.') }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
@endsection
