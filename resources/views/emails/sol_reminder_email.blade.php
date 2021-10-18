<html>
<head>
    <title>LegalCase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

</head>
<body style="background-color: #ffff;margin: 0px;padding: 20px;border: 0px;">
    <table style="padding:30px;margin:0 auto;max-width:600px;background-color:#ffff;box-sizing:border-box;border-collapse:collapse">
        <tr style="margin: 0; padding: 0; border: 0">
            <td align="center" style="margin: 0; padding: 0; border: 0">
                <table cellspacing="0" border="0" style="padding: 0; border: 0; margin: 0" width="601" cellpadding="0">
                    <tbody>
                        <tr height="50" style="margin: 0; padding: 0; border: 0">
                            <td style="padding: 0; border: 0; margin: 0">
                                &nbsp;
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table cellspacing="0" border="0" style="padding: 0; border: 0; margin: 0; background-image: url(&quot;{{ asset('images/mail/table_header.jpg') }}&quot;); background-size: initial; background-repeat: no-repeat; background-attachment: initial; background-origin: initial; background-clip: initial; background-color: initial" bgcolor="#ffffff" width="601" cellpadding="0">
                    <tbody>
                        <tr height="83" style="margin: 0; padding: 0; border: 0">
                            <td style="padding: 0; border: 0; margin: 0" cellpadding="0">
                                <div style="margin-top: 2px; margin-left: 15px; font-size: 18px; width: 100%; text-align: center;">
                                    <img src="{{@$firm->firm_logo_url}}" style="max-width: 110px; margin: 0px auto;">
                                </div>
                            </td>
                            <td style="padding: 0; border: 0; margin: 0; text-align: right" cellpadding="0" align="right">
                                <div style="margin-top: 20px; margin-right: 15px; font-size: 13px">
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table cellspacing="0" border="0" style="padding: 0; border: 0; margin: 0" width="619" cellpadding="0">
                    <tbody>
                        <tr height="75" style="margin: 0; padding: 0; border: 0">
                            <td style="padding: 0; border: 0; margin: 0; color: rgba(255, 255, 255, 1); font-size: 18px; background-image:url(&quot;{{ asset('images/mail/table_banner.jpg')}}&quot;); background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; background-color: rgba(0, 68, 117, 1); font-family: Helvetica, Arial, sans-serif" bgcolor="#004475" align="center">
                            Upcoming Statute of Limitations Due Date
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table cellspacing="0" border="0" style="padding:0;border:0;margin:0;" bgcolor="#ffffff" width="601" cellpadding="0">
                <tbody>
                    <tr style="margin:0;padding:0;border:0">
                    <td style="padding:0;border:0;margin:0" cellpadding="0" align="center">

                    <table cellspacing="0" border="0" style="padding:0;border:0;margin:0" bgcolor="#ffffff" width="561" cellpadding="0">
                    <tbody><tr style="margin:0;padding:0;border:0">
                        <td style="padding:0;border:0;margin:0" cellpadding="0">

                        <p style="margin-top:15px;margin-bottom:10px;padding:0;font-size:13px;line-height:18px;font-family:Helvetica,Arial,sans-serif;color:#000000">

                        Dear {{ @$user->first_name }},

                        </p>

                        <p style="margin-top:15px;margin-bottom:10px;padding:0;font-size:13px;line-height:18px;font-family:Helvetica,Arial,sans-serif;color:#000000">

                        This is a reminder that you have an upcoming statute of limitations due date.

                    </p>

                    <table>

                    <tbody><tr>

                    <td style="padding:5px;font-weight:bold;vertical-align:top">Case Name:</td>

                    <td style="padding:5px;vertical-align:top"><a href="{{ route('info', @$case->case_unique_number) }}" style="color:#0069be;text-decoration:none" target="_blank">{{@$case->case_title}}</a></td>

                </tr>

                <tr>

                <td style="padding:5px;font-weight:bold;vertical-align:top">Statute of Limitations:</td>

                <td style="padding:5px;vertical-align:top">{{ date('M d, Y', strtotime(convertUTCToUserTime(@$case->case_statute_date." 00:00:00", @$user->user_timezone))) }}

                </td>

                </tr>

                </tbody></table>

                <p style="margin-top:15px;margin-bottom:10px;padding:0;font-size:13px;line-height:18px;font-family:Helvetica,Arial,sans-serif;color:#000000">

                Thanks,<br>

                The {{ config('app.name') }} Team @ {{ @$firm->firm_name }}

                </p>
                        </td>

                        </tr>

                    </tbody></table>

                    </td>

                </tr>

                </tbody></table>
                <table cellspacing="0" border="0" style="padding: 0; border: 0; margin: 0" width="601" cellpadding="0">
                    <tbody>
                        <tr style="margin: 0; padding: 0; border: 0">
                            <td style="padding: 0; border: 0; margin: 0" align="center">
                                <p style="text-align: center; margin: 0; padding: 10px 0; font-size: 12px; line-height: 22px; color: rgba(102, 102, 102, 1); border: 0; font-family: Helvetica, Arial, sans-serif">
                                    This email is sent to <a href="mailto:{{$user->email}}">{{ $user->email }}</a>. To ensure that you continue receiving our emails, please add us to your address book or safe list.
                                </p>
                                <p style="text-align: center; margin: 0; padding: 10px 0; font-size: 12px; line-height: 22px; color: rgba(136, 136, 136, 1); border: 0; font-family: Helvetica, Arial, sans-serif; font-style: normal">
                                    © {{ date('Y') }} {{ config('app.name') }}, Inc.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>