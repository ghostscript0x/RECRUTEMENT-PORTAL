<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $academyName }} application update</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        An update from {{ $academyName }} about your application.
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background-color:#f1f5f9;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;border-collapse:separate;border-spacing:0;background-color:#ffffff;border:1px solid #dce3ed;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="height:5px;background-color:#ed6a24;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding:26px 32px;background-color:#102c55;">
                            <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <td width="64" height="48" align="center" valign="middle" style="width:64px;height:48px;border-radius:9px;background-color:#ffffff;">
                                        <img src="{{ asset('images/fintec-logo.png') }}" width="56" height="38" alt="{{ $academyName }} logo" style="display:block;width:56px;height:38px;object-fit:contain;border:0;">
                                    </td>
                                    <td style="padding-left:12px;color:#ffffff;font-size:16px;font-weight:bold;line-height:1.35;">{{ $academyName }}<br><span style="color:#cbd5e1;font-size:11px;font-weight:normal;letter-spacing:1.2px;text-transform:uppercase;">Recruitment team</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:34px 32px 30px;font-size:15px;line-height:1.75;color:#334155;overflow-wrap:anywhere;">
                            <p style="margin:0 0 20px;color:#ed6a24;font-size:11px;font-weight:bold;letter-spacing:1.4px;text-transform:uppercase;">Application update</p>
                            <div style="font-size:15px;line-height:1.8;color:#334155;">
                                {!! $body !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;background-color:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;color:#475569;font-size:12px;line-height:1.6;">This message was sent by {{ $academyName }} regarding your recruitment application.</p>
                            @if($supportEmail || $supportPhone)
                                <p style="margin:8px 0 0;font-size:12px;line-height:1.6;">
                                    @if($supportEmail)<a href="mailto:{{ $supportEmail }}" style="color:#173b73;text-decoration:underline;">{{ $supportEmail }}</a>@endif
                                    @if($supportEmail && $supportPhone)<span style="color:#94a3b8;">&nbsp;·&nbsp;</span>@endif
                                    @if($supportPhone)<a href="tel:{{ $supportPhone }}" style="color:#173b73;text-decoration:underline;">{{ $supportPhone }}</a>@endif
                                </p>
                            @endif
                            <p style="margin:12px 0 0;color:#94a3b8;font-size:11px;line-height:1.5;">Please do not share your application reference with anyone you do not trust.</p>
                        </td>
                    </tr>
                </table>
                <p style="margin:18px 0 0;color:#94a3b8;font-size:11px;text-align:center;">{{ $academyName }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
