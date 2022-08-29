<?php

namespace App\Http\Controllers;

use App\Services\GoogleService;
use App\UserSyncSocialAccount;
use Exception;
use Illuminate\Http\Request;
use Microsoft\Graph\Graph;

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
        $client_id     = env('OUTLOOK_CLIENT_ID');
        $client_secret = env('OUTLOOK_CLIENT_SECRET_VALUE');
        // $client_scope = env('OUTLOOK_GRAPH_USER_SCOPES');
        $client_scope = "openid+https%3A%2F%2Foutlook.office.com%2Fuser.read+https%3A%2F%2Foutlook.office.com%2Fcalendars.readwrite+offline_access%20openid%20email%20profile";
        $redirect_uri  = route('outlook/oauth'); 
        
        $response   = "";
        $response   = "https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id=".$client_id."&scope=".$client_scope."&response_type=code&redirect_uri=".urlencode($redirect_uri)."&prompt=select_account";  //&prompt=consent
        if(!isset($_GET['code']))
        {
            return redirect()->to($response);
        }

        $output = "";
        //  Redeem the authorization code for tokens office 365 using PHP
        if(isset($_GET['code']))
        {
            $auth = $_GET['code'];
            $data = "client_id=".$client_id."&redirect_uri=".urlencode($redirect_uri)."&client_secret=".urlencode($client_secret)."&code=".$auth."&grant_type=authorization_code";
            try {		
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://login.microsoftonline.com/common/oauth2/v2.0/token");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/x-www-form-urlencoded',
                    ));
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $output = curl_exec($ch);

                $account = json_decode($output, true);
                $accessToken = $account['access_token'];
                /* $graph = new Graph();
                $graph->setBaseUrl("https://graph.microsoft.com/")->setAccessToken($accessToken);

                return $user = $graph->setApiVersion("2.0")
                            ->createRequest("GET", "/me")
                            ->addHeaders(array("Content-Type" => "application/json"))
                            ->setReturnType(\App\User::class)
                            ->execute(); */

                $syncAccount = UserSyncSocialAccount::updateOrCreate([
                    'user_id' => auth()->id(),
                    ], [
                    'social_id' => Null,
                    'social_type' => 'outlook',
                    'access_token' => $accessToken,
                    'refresh_token' => $account['refresh_token'],
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
                // Map the account's id to the `google_id`.
                'social_id' => $account->id,
            ], [
                'user_id' => auth()->id(),
                // Use the first email address as the Google account's name.
                'email' => $account->email ?? '',
                // Last but not least, save the access token for later use.
                'access_token' => $accessToken['access_token'],
                'refresh_token' => $google->getRefreshToken(),
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
    
        return redirect()->route('integration/apps');
    }


    public function createEvent(Request $request, GoogleService $google)
    {
        $googleAccount = UserSyncSocialAccount::where('user_id', auth()->id())->whereNotNull('access_token')->first();
        $google->setAccessToken($googleAccount->access_token);
        if ($google->isAccessTokenExpired()) {
            $accessToken = $google->fetchAccessTokenWithRefreshToken($googleAccount->refresh_token);
            $googleAccount->update([
                'access_token' => json_encode($accessToken),
            ]);
        }
        // $google->connectUsing($googleAccount->access_token)->service('Calendar');
        $service = $google->service('Calendar');
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
     * Uninstall synced calendar
     */
    public function uninstallSyncCalendar(Request $request, GoogleService $google)
    {
        $syncAccount = UserSyncSocialAccount::where('user_id', auth()->id())->first();
        if($syncAccount && $syncAccount->social_type == 'google') {

            $google->setAccessToken($syncAccount->access_token);
            if ($google->isAccessTokenExpired()) {
                $accessToken = $google->fetchAccessTokenWithRefreshToken($syncAccount->refresh_token);
                $syncAccount->update([
                    'access_token' => $accessToken['access_token'],
                ]);
            }
            $syncAccount->refresh();
            $token = $syncAccount->access_token;
            $google->revokeToken($token);
            $syncAccount->delete();
            session()->flash('success', 'Calendar Integration will be uninstalled, but it may take a few minutes.');
        }
        return redirect()->route('integration/apps');
    }
}