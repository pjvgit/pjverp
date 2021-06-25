<!DOCTYPE html>
<html>
<head>
    <title>Legalcase : Task Reminder Email</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700" rel="stylesheet">
</head>
<body style="font-family: Source Sans Pro, sans-serif;color: #1c2224;margin: 0;padding: 0;box-sizing: border-box;" data-gr-c-s-loaded="true">
    <table style="border-collapse:collapse;margin:auto;max-width: 600px;background: #fcfcfc;font-family: Source Sans Pro, sans-serif;color: #1c2224;">
        <tr colspan="2" style="background: #DCDCDC;">
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
            <th style="padding: 20px 25px;font-size: 16px;">Task Name</th>
            <th style="padding: 20px 25px;font-size: 16px;">Case/Lead</th>
            <th style="padding: 20px 25px;font-size: 16px;">Due Date</th>
            <th style="padding: 20px 25px;font-size: 16px;">Priority</th>
        </tr>
		
		<tr>
            <td style="padding: 20px 25px;font-size: 16px;"><a href="{{ route("tasks", ['id' => $task->id]) }}">{{ @$task->task_title }}</a></td>
            <td style="padding: 20px 25px;font-size: 16px;">
                @if($task->case_id)
                    {{ @$task->case->case_title }}
                @elseif($task->lead_id)
                    {{ @$task->lead->potential_case_title }}
                @else
                @endif
            </td>
            <td style="padding: 20px 25px;font-size: 16px;">{{ date('d-m-Y', strtotime(convertUTCToUserDate(@$task->task_due_on, @$user->user_timezone))) }}</td>
            <td style="padding: 20px 25px;font-size: 16px;">{{ ucfirst(@$task->task_priority) }}</td>
        </tr>
		
		<tr>
            <td colspan="2" style="padding: 0px 25px;font-size: 16px;">For additional details about these tasks, please sign in to your LegalCase account.</td>
        </tr>		
		<tr>
            <td colspan="2" style="padding: 0px 25px;font-size: 16px;">
               &nbsp;
            </td>
        </tr>
		<tr>
            <td colspan="2" style="padding: 0px 25px;font-size: 16px;">
                <a href="#" style="width: auto;height: 55px;background-color: #663399;font-size: 18px;color: #ffffff;line-height: 54px;display: inline-block;font-weight: 600;text-align: center;text-decoration: none;border-radius: 6px !important;padding: 0 10px 0 10px">View Task</a>
            </td>
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
			<td colspan="2" style="color: #7a7a7a;background: #DCDCDC;">
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
    </table>
</body>
</html>