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
                <table cellspacing="0" border="0" style="padding: 0; border: 0; margin: 0; background-image: url(&quot;{{ asset('images/mail/table_header.jpg') }}&quot;); background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; background-color: initial"
                    bgcolor="#ffffff" width="601" cellpadding="0">

                    <tbody>
                        <tr height="83" style="margin: 0; padding: 0; border: 0">

                            <td style="padding: 0; border: 0; margin: 0" cellpadding="0">

                                <div style="margin-top: 20px; margin-left: 15px; font-size: 18px">

                                    <img src="{{ @$firm->firm_logo_url }}" alt="LegalCase - Simplify Your Law Practice">

                                </div>

                            </td>
                            <td style="padding: 0; border: 0; margin: 0; text-align: right" cellpadding="0" align="right">

                                <div style="margin-top: 20px; margin-right: 15px; font-size: 13px">



                                </div>

                            </td>

                        </tr>

                    </tbody>
                </table>
                    @php
                        $content = str_replace('[USER_NAME]', $user->full_name, $template->content);
                        $content = str_replace('[CREATED_BY_USER_NAME]', @$commentAddedUser->full_name, $content);
                        $content = str_replace('[EVENT_NAME]', $event->event_title, $content);
                        $content = str_replace('[CREATED_BY_USER_LEVEL]', @$commentAddedUser->user_level, $content);
                        $content = str_replace('[CLIENT_PORTAL_URL]', config('app.url'), $content);
                        $content = str_replace('[TABLE_BG_IMAGE_URL]', asset('images/mail/table_bg.jpg'), $content);
                        $content = str_replace('[TABLE_BANNER_IMAGE_URL]', asset('images/mail/table_banner.jpg'), $content);
                    @endphp
                    {!! $content !!}

                <table cellspacing="0" border="0" style="padding: 0; border: 0; margin: 0; background-image: url(&quot;{{ asset('images/mail/table_footer.jpg') }}&quot;); background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; background-color: initial"
                    bgcolor="#ffffff" width="601" cellpadding="0">

                    <tbody>
                        <tr height="17" style="margin: 0; padding: 0; border: 0">

                            <td style="padding: 0; border: 0; margin: 0" cellpadding="0">

                                &nbsp;

                            </td>

                        </tr>

                    </tbody>
                </table>
                <table cellspacing="0" border="0" style="padding: 0; border: 0; margin: 0" width="601" cellpadding="0">
                    <tbody>
                        <tr style="margin: 0; padding: 0; border: 0">
                            <td style="padding: 0; border: 0; margin: 0" align="center">
                                <p style="text-align: center; margin: 0; padding: 10px 0; font-size: 12px; line-height: 22px; color: rgba(102, 102, 102, 1); border: 0; font-family: Helvetica, Arial, sans-serif">
                                    To ensure that you continue receiving our emails, please add us to your address book or safe list.
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