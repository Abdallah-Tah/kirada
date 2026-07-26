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
            <h2 style="margin:0; font-size:24px; color:#0f172a;">{{ __('New work invitation') }}</h2>
            <p style="margin:8px 0 0; font-size:14px; color:#64748b;">
                {{ __(':landlord would like to add you to their approved maintenance team.', ['landlord' => $landlord->name]) }}
            </p>
        </td>
    </tr>

    {{-- Details Card --}}
    <tr>
        <td style="padding:16px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb; border-radius:14px; overflow:hidden;">
                <tr>
                    <td colspan="2" style="background:#f8fafc; padding:14px 16px; font-weight:bold; font-size:14px; color:#475569;">{{ __('Landlord') }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 16px; color:#94a3b8; font-size:13px; width:120px;">{{ __('Name') }}</td>
                    <td style="padding:12px 16px; text-align:right; font-size:14px; color:#0f172a;">{{ $landlord->name }}</td>
                </tr>
                @if($landlord->city)
                <tr>
                    <td style="padding:12px 16px; color:#94a3b8; font-size:13px;">{{ __('City') }}</td>
                    <td style="padding:12px 16px; text-align:right; font-size:14px; color:#0f172a;">{{ $landlord->city }}</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>

    @if($note)
    {{-- Their message --}}
    <tr>
        <td style="padding:16px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border-left:4px solid #0EA5E9; border-radius:0 12px 12px 0;">
                <tr>
                    <td style="padding:16px; font-size:14px; line-height:1.6; color:#334155;">
                        {{ $note }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    @endif

    <tr>
        <td style="padding:8px 32px 0; font-size:13px; line-height:1.6; color:#64748b;">
            {{ __('They can only assign work orders to you once you accept.') }}
        </td>
    </tr>
@endsection
