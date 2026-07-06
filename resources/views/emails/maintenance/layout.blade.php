<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? '' }}</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family:Arial, 'Helvetica Neue', sans-serif; color:#0f172a;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:32px 16px;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 10px 30px rgba(15,23,42,0.08);">

    @yield('email-content')

    {{-- CTA Button --}}
    <tr>
        <td style="padding:24px 32px 36px; text-align:center;">
            <a href="{{ $actionUrl }}" style="display:inline-block; background:#0EA5E9; color:#ffffff !important; text-decoration:none; padding:14px 32px; border-radius:12px; font-weight:bold; font-size:15px;">
                {{ $actionText }}
            </a>
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td style="background:#f8fafc; padding:24px 32px; text-align:center; color:#94a3b8; font-size:13px; line-height:1.6;">
            <strong style="color:#475569; font-size:14px;">Kirada</strong><br>
            {{ __('Smart rent management platform') }}<br>
            <a href="{{ config('app.url') }}" style="color:#64748b; text-decoration:none;">{{ config('app.url') }}</a>
        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>