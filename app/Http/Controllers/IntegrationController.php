<?php

namespace App\Http\Controllers;

use App\Jobs\SyncAllEventToSocialAccountJob;
use App\Services\GoogleService;
use App\UserSyncSocialAccount;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class IntegrationController extends Controller {

    public function index()
    {
        $syncAccount = UserSyncSocialAccount::where('user_id', auth()->id())->first();
        return view("integration.index", compact('syncAccount'));
    }

    /**
     * Outlook oAuth
     */
    public function getOutlookAccessToken()
    {
        header('Content-Type: text/html; charset=utf-8');
        $tenant     = 'common';
        $client_id     = config('services.outlook.client_id');
        $client_secret = config('services.outlook.client_secret_value');
        $client_scope = config('services.outlook.scopes');
        $redirect_uri  = config('services.outlook.redirect_uri'); 
        $approval_prompt  = config('services.outlook.approval_prompt'); 
        // $client_scope = "https%3A%2F%2Fgraph.microsoft.com%2F.default";

        $response   = "https://login.microsoftonline.com/".$tenant."/oauth2/v2.0/authorize?client_id=".$client_id."&scope=".$client_scope."&response_type=code&redirect_uri=".urlencode($redirect_uri)."&prompt=".$approval_prompt;  //&prompt=consent
        if(!isset($_GET['code'])) {
            return redirect()->to($response);
        }

        $output = "";
        //  Redeem the authorization code for tokens office 365 using PHP
        if(isset($_GET['code']))
        {
            $auth = $_GET['code'];
            try {		
                // $data = "client_id=".$client_id."&redirect_uri=".urlencode($redirect_uri)."&client_secret=".urlencode($client_secret)."&code=".$auth."&grant_type=authorization_code";
                $data = "client_id=".$client_id."&redirect_uri=".$redirect_uri."&client_secret=".$client_secret."&code=".$auth."&grant_type=authorization_code";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://login.microsoftonline.com/".$tenant."/oauth2/v2.0/token");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/x-www-form-urlencoded',
                    ));
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $output = curl_exec($ch);

                $account = json_decode($output);
                $accessToken = $account->access_token;
                session()->put('outlook_access_token', $account->access_token);
                $syncAccount = UserSyncSocialAccount::updateOrCreate([
                    'user_id' => auth()->id(),
                    ], [
                    'social_id' => Null,
                    'social_type' => 'outlook',
                    'access_token' => $accessToken,
                    'refresh_token' => $account->refresh_token,
                    'expires_in' => Carbon::now()->addSeconds($account->expires_in),
                ]);
                
                session()->flash('show_success_modal', 'yes');
                return redirect()->route('integration/apps');
            } catch (Exception $exception) {
                return redirect()->back()->with('error', $exception->getMessage());
            }            
        }
    }    

    /**
     * Google oAuth 
     */
    public function getGoogleAuth(Request $request, GoogleService $google)
    {
        if (! $request->has('code')) {
            return redirect($google->createAuthUrl());
        }
        $authUser = auth()->user();
        // Use the given code to authenticate the user.
        $google->authenticate($request->get('code'));
        $accessToken = $google->getAccessToken();

        // Make a call to the Google+ API to get more information on the account.
        $account = $google->service('Oauth2')->userinfo->get();
    
        $syncAccount = UserSyncSocialAccount::updateOrCreate([
                'social_id' => $account->id, // Map the account's id to the `google_id`.
            ], [
                'user_id' => auth()->id(),
                'social_type' => 'google',
                'email' => $account->email ?? '',
                'access_token' => $accessToken['access_token'],
                'refresh_token' => $google->getRefreshToken(),
                'created_by' => auth()->id(),
            ]
        );
        
        $service = $google->service('Calendar');
        if(empty($syncAccount->calendar_id)) {
            $calendar = new \Google\Service\Calendar\Calendar();
            $calendar->setSummary('LegalCase');
            $calendar->setTimeZone($authUser->user_timezone);

            $createdCalendar = $service->calendars->insert($calendar);

            $syncAccount->update([
                'calendar_id' => $createdCalendar->getId(), 
                'calendar_name' => $calendar->getSummary(), 
                'calendar_timezone' => $authUser->user_timezone
            ]);
        }

        // Sync events to social account calendar
        $this->dispatch(new SyncAllEventToSocialAccountJob($authUser));

        session()->flash('show_success_modal', 'yes');
        return redirect()->route('integration/apps');
    }


    public function createEvent(Request $request, GoogleService $google)
    {
        $googleAccount = UserSyncSocialAccount::where('user_id', auth()->id())->whereNotNull('access_token')->first();
        /* $google->setAccessToken($googleAccount->access_token);
        if ($google->isAccessTokenExpired()) {
            $accessToken = $google->fetchAccessTokenWithRefreshToken($googleAccount->refresh_token);
            $googleAccount->update([
                'access_token' => json_encode($accessToken),
            ]);
        } */
        $google->connectUsing($googleAccount);
        $service = $google->service('Calendar');
        /* $calendar = $service->calendars->get($googleAccount->calendar_id);
        $calendar->setSummary($googleAccount->calendar_name.' (Un-synced)');
        $updatedCalendar = $service->calendars->update($googleAccount->calendar_id, $calendar);
        return $updatedCalendar;
        exit; */
        if(empty($googleAccount->calendar_id)) {
            $calendar = new \Google\Service\Calendar\Calendar();
            $calendar->setSummary('LegalCase');
            $calendar->setTimeZone(auth()->user()->user_timezone);

            $createdCalendar = $service->calendars->insert($calendar);

            $googleAccount->update(['calendar_id' => $createdCalendar->getId()]);
        }
        $googleAccount->refresh();
        $event = new \Google\Service\Calendar\Event(array(
            'summary' => 'aug event - daily',
            'location' => '800 Howard St., San Francisco, CA 94103',
            'description' => 'A chance to hear more about Google\'s developer products.',
            'start' => array(
              'dateTime' => '2022-08-28T09:00:00-05:00',
              'timeZone' => 'America/Mexico_City',
            ),
            'end' => array(
              'dateTime' => '2022-08-28T17:00:00-05:00',
              'timeZone' => 'America/Mexico_City',
            ),
            'recurrence' => array(
              'RRULE:FREQ=DAILY;COUNT=2'
            ),
            'attendees' => array(
              array('email' => 'lpage@example.com'),
              array('email' => 'sbrin@example.com'),
            ),
            'reminders' => array(
              'useDefault' => FALSE,
              'overrides' => array(
                array('method' => 'email', 'minutes' => 24 * 60),
                array('method' => 'popup', 'minutes' => 10),
              ),
            ),
        ));
        // $cal = new \Google\Service\Calendar($google);
        $calendarId = $googleAccount->calendar_id;
        $event = $service->events->insert($calendarId, $event);
        printf('Event id: %s\n', $event->id);
        printf('Event created: %s\n', $event->htmlLink);
          

        return "success";
    }

    /**
     * Load synced calendar detail/settings
     */
    public function loadSyncCalendarSetting(Request $request)
    {
        $syncAccount = UserSyncSocialAccount::whereId($request->sync_id)->where('user_id', auth()->id())->first();
        return view("integration.partial.load_sync_calendar_setting", compact('syncAccount'));
    }

    /**
     * Sync events to social calendar
     */
    public function eventSyncCalendar()
    {
        $authUser = auth()->user();
        $this->dispatch(new SyncAllEventToSocialAccountJob($authUser));

        return 'success';
    }

    /**
     * Uninstall synced calendar
     */
    public function uninstallSyncCalendar(Request $request, GoogleService $google)
    {
        // return $request->all();
        $syncAccount = UserSyncSocialAccount::where('user_id', auth()->id())->first();
        if($syncAccount && $syncAccount->social_type == 'google') {
            $google->connectUsing($syncAccount);
            $service = $google->service('Calendar');
            if(isset($request->is_delete_event)) {
                $service->calendars->delete($syncAccount->calendar_id);
                $syncAccount->fill(['is_event_deleted' => 'yes'])->save();
            } else {
                $calendar = $service->calendars->get($syncAccount->calendar_id);
                $calendar->setSummary($syncAccount->calendar_name.' (Un-synced)');
                $updatedCalendar = $service->calendars->update($syncAccount->calendar_id, $calendar);
                $syncAccount->fill(['calendar_name' => $calendar->getSummary()])->save();
            }
            $syncAccount->refresh();
            $google->revokeToken($syncAccount->access_token);
            $syncAccount->delete();
            session()->flash('success', 'Calendar Integration will be uninstalled, but it may take a few minutes.');
        } else if($syncAccount && $syncAccount->social_type == 'outlook') {

            /* $google->setAccessToken($syncAccount->access_token);
            if ($google->isAccessTokenExpired()) {
                $accessToken = $google->fetchAccessTokenWithRefreshToken($syncAccount->refresh_token);
                $syncAccount->update([
                    'access_token' => $accessToken['access_token'],
                ]);
            } */
            $syncAccount->refresh();
            $token = $syncAccount->access_token;
            // $google->revokeToken($token);
            $syncAccount->delete();
            session()->flash('success', 'Calendar Integration will be uninstalled, but it may take a few minutes.');
        }
        return redirect()->route('integration/apps');
    }

    public function createOutlookEvent()
    {
        $syncAccount = UserSyncSocialAccount::where('user_id', auth()->id())->first();
        $accessToken = $syncAccount->access_token;
        $redirect_uri  = config('services.outlook.redirect_uri');
        $client_id     = config('services.outlook.client_id');
        $client_secret = config('services.outlook.client_secret_value');

        if(Carbon::now()->gt(Carbon::parse($syncAccount->expires_in))) {
            $data = "client_id=".$client_id."&redirect_uri=".urlencode($redirect_uri)."&client_secret=".urlencode($client_secret)."&refresh_token=".$syncAccount->refresh_token."&grant_type=refresh_token";	
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://login.microsoftonline.com/common/oauth2/v2.0/token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded',));
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $output = curl_exec($ch);

            $account = json_decode($output);
            $accessToken = $account->access_token;

            $syncAccount->fill([
                'access_token' => $accessToken, 
                'refresh_token' => $account->refresh_token,
                'expires_in' => Carbon::now()->addSeconds($account->expires_in),
            ])->save();
        }

        /* $credentials = base64_encode($client_id .':' . $client_secret ) ;
        $guzzle = new \GuzzleHttp\Client(['base_uri' => 'https://graph.microsoft.com']);
        return $newresponse = $guzzle->request(
            'GET',
            '/v1.0/me',
            ['headers' => 
                [
                    'Authorization' => " Bearer EwBgA8l6BAAUkj1NuJYtTVha+Mogk+HEiPbQo04AAfRMbXGLJHRGx9TMXfR+28BMOssassxCbbd1hkZKWh8G+DgcVi2WX0lmqhUZpwV6txahOszHgoxtmhPKosYHKMANCxkKc0v/BdazHXISPpMHxVIeB4/tSkdgZH5MABldQxi2vn7cINjSZDoHR8sOU2TqVuknyxjhoqlFg3hvZkNgAtSyR/yrVjHEf28AtsjvwJGAItUXYmT4dWbfkcUXR+So6ixK5+nWjjpv3Ff+0JTiHXUP3Y/Cw55mSJFv8C70Gi57tF20EzYHfv7WxDDYnSBADB6FVDEzEk9I+TsSPlzyzUKktRQ98UQY+9TlcMP4akxp+aXreYp9NPASrQIdXD8DZgAACLQGq7IMHUrQMAL/G3aTjGMhOH9hj9A7jDJs28B+fA33hAkwd/yJWcl8tR0RZGnFcyssEMmxYCVKewzWCQvxF9rwmslNxxCX6CFtwL9v+JfTOIFVQ4t6LZ5U5sqqtgArOQrU7slxVNAmI0QhiMHf3GGt9QJNZgJezemNhB7BdkXaH+0m2SsS5YSN49QR0mqlMU77J//E/1ul2OIMduJokA6gtBMbUi9EymXac5eJV7f4V5xkYvvpyZENQecyLhgMXW19JM+U6QnVbyMffm22caNZjktPJCdEHj0Sv/5xYHyv3d5+tyaWZrTFn7q+FLLYaKHr0lBdgBOmPzmcpfl/Hyi3kXYoimKZbU1UOKuCVSjJduJ4tZXBFbG6U0Lcn7YJ8zYfExIst1nmDZZVKiWwJuZsW/8oVG9+SvEKclpJ6rBMW+paGLiH5GS1XEWTU5pE+GrwMtvFpB4k751o7NAGgUl3KNzf5VnLVqWWmK7SJitJ52VkukubRiLfOrqSA+aujFHb30OYlW5WQSo7Ks1DRmMc3vPoQ1ZpKxhKF9DENTa48AFwyXBsirim6WsC6qp/yMMofG1GYdNm5zl1TAyfpdxZPo6QfJvCcVLlqqqX9IfDau47kEa31mt8F6PeANRODJRKVFGi7r1L0r24C0yHDcjzLOsW/M77UpFuvEAVTAwfEAH+XAM3+nmOosMvHYaXnIyl3FFqzoF9oPIq1Bs6JhkrYPhwCuoWKnkrWZ+jZlin0YEyvRhwwA01+HkC"
                ]
            ]
        )->getBody()->getContents(); */

        $graph = new Graph();
        $graph->setBaseUrl("https://graph.microsoft.com/")
               ->setApiVersion("v1.0")
               ->setAccessToken(session()->get('outlook_access_token')); 
                //   ->setAccessToken('EwBoA8l6BAAUkj1NuJYtTVha+Mogk+HEiPbQo04AAbzDI5KOz4rc5BuLpNDnDezWi7Nz8zm+vrc1+p6acVctZEsYcWRTyjkHsdwXSvl97EW5Ii+LG7kpfq5Xxpf24/HJ8tYQXLXo2L2Mb9YOty/ZWAXzw3gWeVRzB6yjTwCawXwo765QegfBv0UDOgYVYk27ZWI6QIs/9Z07HeI1X+zlHVskecn8z7KYQ+nbIGdjlX3tHr/4Mql7/FNaVAW2jLJMNiscqwdlTjbV2gFJGclKanwBQgn6DqKIwQEri04ea/tLAdgMBOzVVAiBj63McxGOLswGC9+kHw19bth+Dw2QCUdKR+hxLTTFULLqnU9or3WdFS994RpUxck+d7pJBxwDZgAACD5oWKwPClO1OAKG6gSwIuKJPQ/I4fid5ixnt88ztGsxs7yHYrE2LpqHVinIMPqeWnYA1QdPdZMQunqDT3DbNXiDdn9S9Yt891hQ3c7meyTaX7EnzQaw9XdDFtUzIHujX+8vfcbwLxVsqm9kruW7wY4hMD/IfiQXFO8Ly8VrGKqZXetR9chxedbQ4tJkuf3lvUwIEiKqkpyrwhiojBU/9v+esKVzDvjCH/dqdu4WYluv0TJ8ruwJdOtSLeGknLCQDK+XLeM1i28md9tEuCMLpFpHt+MdEEEaMHIJ78+8OlE1+mKhfAgEX3ILG/eEu77cTP8OB/nh3AzrAZvnJTCTW+CcPZD0LlSVHs2X5D2H6iqVibMpbAjkw/rUVy+jRWfEO4cQVmSlEgfiGt6wOjExhBLdQBFyIxj9rNFUHT7I5XdjeU2KQNN0Lf0F9PV69YZUiVUQMgPBuzpENbzMFZxvYaP4x//LoLhtPn1y5ylOVI9A/cgPSnumvxAqjOvGEMQP5G9xddEtr++d6d+lzLGIfPQRSav2AMkcQRLKlkiEbvhxjrAsMJWVK/3EpEofKG4VPFzVLEaEk1J1T/+Tx9eiHjQedQ84nsdLsGIqZpyHAbXcZhpRLExdlmR+bYK0GDwB7TMnS/FWA6bkzulgNV9I+Hskp8YOYmLCPLquUn6dnyl6Lnc8Zqe5Vj5mYpPf4IZfi6/8GZekCsH92aEjf37WvG2Tx2VR0WjOLXDXDVfnQwunbDWVL2Hj3ieJpCFvg6uOZqWegQI='); 
        // dd($graph);
        $user = $graph->createRequest("GET","/me")
                    ->addHeaders(array("Content-Type" => "application/json"))
                    // ->setReturnType(Model\User::class)
                    ->setTimeout("1000")
                    ->execute();   
        dd($user);
        echo "Hello, I am $user->userPrincipalName ";
    }
}