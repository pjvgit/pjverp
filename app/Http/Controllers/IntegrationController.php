<?php

namespace App\Http\Controllers;

use App\Services\GoogleServices;
use App\UserSyncSocialAccount;
use Carbon\Carbon;
use Exception;
use Google\Client;
use Google_Client;
use Illuminate\Http\Request;

class IntegrationController extends Controller {

    public function index()
    {
        return view("integration.index");
    }

    /**
     * Outlook oAuth
     */
    public function getOutlookAccessToken(Request $request)
    {
        return $request->all();
        // try {
            /* $guzzle = new \GuzzleHttp\Client();
            $url = 'https://login.microsoftonline.com/'.env('OUTLOOK_TENANT_ID').'/oauth2/v2.0/authorize?';
            $token = json_decode($guzzle->get($url, [
                'client_id' => env('OUTLOOK_CLIENT_ID'),
                'response_type' => 'code',
                'redirect_uri' => route('integration/apps'),
                'response_mode' => 'query',
                'scope' => 'offline_access,user.read,mail.read',
                'state' => 12345,
            ])->getBody()->getContents()); */

            $guzzle = new \GuzzleHttp\Client();
            $url = 'https://login.microsoftonline.com/' . env('OUTLOOK_TENANT_ID') . '/oauth2/v2.0/authorize';
            $token = json_decode($guzzle->post($url, [
                'form_params' => [
                    'client_id' => env('OUTLOOK_CLIENT_ID'),
                    // 'client_secret' => env('OUTLOOK_CLIENT_SECRET'),
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                    'response_mode' => 'query',
                    'response_type' => 'code',
                    'state' => 12345,
                    'redirect_uri' => route('integration/apps'),
                ],
            ])->getBody()->getContents());
            // $accessToken = $token->access_token;
        
            return $token;
        /* } catch (Exception $e) {
            return redirect('/')->with('error', 'Error requesting access token')->with('errorDetail', json_encode($e->getResponseBody()));
        } */
    }

    /**
     * Google oAuth 
     */
    public function getGoogleAuth(Request $request, GoogleServices $google)
    {
        if (! $request->has('code')) {
            return redirect($google->createAuthUrl());
        }

        // Use the given code to authenticate the user.
        $google->authenticate($request->get('code'));
        $accessToken = $google->getAccessToken();

        // Make a call to the Google+ API to get more information on the account.
        $account = $google->service('Oauth2')->userinfo->get();
    
        UserSyncSocialAccount::updateOrCreate(
            [
                // Map the account's id to the `google_id`.
                'social_id' => $account->id,
            ],
            [
                'user_id' => auth()->id(),
                // Use the first email address as the Google account's name.
                'email' => $account->email ?? '',
                
                // Last but not least, save the access token for later use.
                'access_token' => $accessToken['access_token'],
                'refresh_token' => $google->getRefreshToken(),
            ]
        );
    
        return redirect()->route('integration/apps');
    }


    public function createEvent(Request $request, GoogleServices $google)
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
}