<!DOCTYPE html>
<html>
<head>
    <title>Legalcase : Event Reminder Email</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700" rel="stylesheet">
</head>
<body style="font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;" data-gr-c-s-loaded="true">
    <table style="border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;">
        <tr style="background: #DCDCDC;">
			<td style="text-align:center;height: auto;font-size: 24px; font-weight: 600;color:#ffffff;">
				<a href="{{ url('/') }}" style="text-decoration: none;" target="_blank">
					<img src="{{ @$firm->firm_logo_url }}"  style="display: inline-block;vertical-align: middle;width: 100px;height: 100px;">
				</a>
			</td>
        </tr>
        <tr>
            <td colspan="2" style="color: #1c2224;padding: 15px 25px 15px;font-size: 16px;">Hi {{ @$user->first_name }}, </td>
        </tr>
        <tr>
            <td colspan="2" style="color: #1c2224;padding: 15px 25px 15px;font-size: 16px;">This is a reminder that you have an upcoming event. </td>
        </tr>
		
		<tr>
            <td colspan="2" style="padding: 20px 25px;font-size: 16px;">
                <strong>Event Name:</strong> {{ @$event->event_title }}
            </td>
        </tr>
		<tr>
            <td colspan="2" style="padding: 20px 25px;font-size: 16px;">
                <strong>Case/Lead Name:</strong> {{ @$event->case->case_title }}
            </td>
        </tr>
		<tr>
            <td colspan="2" style="padding: 20px 25px;font-size: 16px;">
                <strong>Date/Time:</strong> {{ date('d-m-Y h:i', strtotime(convertUTCToUserTime(@$event->start_date." ".@$event->start_time, @$user->user_timezone))) }}
            </td>
        </tr>
		<tr>
            <td colspan="2" style="padding: 20px 25px;font-size: 16px;">
                <strong>Attendance Required:</strong> {{ @$attendEvent }}
            </td>
        </tr>
		<tr>
            <td colspan="2" style="padding: 20px 25px;font-size: 16px;">
                <strong>Location:</strong> {{ @$event->eventLocation->full_address }}
            </td>
        </tr>
		<tr>
            <td colspan="2" style="padding: 0px 25px;font-size: 16px;">
                <a href="#" style="width: auto;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;padding: 0 10px 0 10px">View Event</a>
            </td>
        </tr>
		
		<tr>
            <td colspan="2" style="padding: 0px 25px;font-size: 16px;"><b>For additional details about the event, please log in to your <a href="{{ url('/') }}">Account</a>.</b> </td>
        </tr>
		
		
		<tr>
            <td colspan="2" style="padding: 0px 25px;font-size: 16px;">
               &nbsp;<br>
            </td>
        </tr>
		
	
		<tr>
            <td colspan="2" style="padding: 0px 25px;font-size: 16px;">
               Thanks,
			   <br>{{ @$firm->firm_name }}.
            </td>
        </tr>
		
		<tr>
            <td colspan="2" style="padding: 0px 25px;font-size: 16px;">
               &nbsp;
            </td>
        </tr>
		  
		<tr>
			<td style="color: #7a7a7a;background: #DCDCDC;">
				<div style="padding: 5px;">
					<div style="max-width: 100%;font-size: 14px;color: #7a7a7a;">
						<p style="margin: 25px;text-align:center;">
						 This is an automated notification. To protect the confidentiality of these communications.<br><b> PLEASE DO NOT REPLY TO THIS EMAIL. </b></p>
						 </p>
						<p style="margin: 25px;text-align:center;">© {{ date('Y') }} {{ @$firm->firm_name }}</p>
					</div>
				</div>
			</td>
		</tr>
        </tbody>
    </table>
</body>
</html>