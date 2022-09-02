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
use Microsoft\Graph\Model\Calendar;

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
                /* curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/x-www-form-urlencoded',
                    )); */
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $output = curl_exec($ch);

                $account = json_decode($output);
                $accessToken = $account->access_token;
                
                $syncAccount = UserSyncSocialAccount::updateOrCreate([
                    'user_id' => auth()->id(),
                    ], [
                    'social_id' => Null,
                    'social_type' => 'outlook',
                    'access_token' => $accessToken,
                    'refresh_token' => $account->refresh_token,
                    'expires_in' => Carbon::now()->addSeconds($account->expires_in),
                ]);

                /* $guzzle = new \GuzzleHttp\Client(['base_uri' => 'https://graph.microsoft.com']);
                $newresponse = $guzzle->request('GET', '/v1.0/me',
                    ['headers' => [
                            'Authorization' => " Bearer EwBoA8l6BAAUkj1NuJYtTVha+Mogk+HEiPbQo04AAQXlPMFAC2NahrkRlygFCd4cVBtOF36WIz710GgIJ5j2Y7qxVeGi8IzVzyDczIxR8K6nrmP9ymvZVD5jJCm3bsnxmu6NMyEJCwa5nvqw0oYuv8F+tcAv0QqKGkcE2+WYpjFT/KaoTXaakWbzEfGnVx0G3nvhYCsYSlNAFthJ7A8IiWXsZNsG5wppmJj4E6L0EhMwHc9TevzWNDCF9a7zYYNwHaB2GXHLujATXn3uOyjnPLegORETzSi/Xdw8ClfbSCwjayANsMEsnoDHxX23zK3gkNBc7op/M59+yU/bKMEYWskVYp1EhJl3CZcjTBQkfFNSz135zi0W7xTyT1CClAQDZgAACK5QDo2NE/4kOALHEL/4Lzybm/W7YGNlTFIJlUvdwvzLlO6oSI1jI9QeSbmqvP3TAZhIlCwz+p/e5kDaX9W/naF6JYpYoeZZTGUnv2XTCfeE8WJhofxRFBdLZa/TQJ2H+uxw3O10qQ8p1egD+TosZj3LPI2ktn1SCBZs0pUkREa4HzluUn70r9y8qwcRh182fl9Xf/K3tJT6wnoTIO23uX/2/3Tqm9iIx7ZC1tk5tHoaGdtFA/TNUL+qskSeiWrql4rW9NagXAy5L9jOW0CGT+OpsHVw8r8LDCRB87txNb1GSWVjYHN0X19rXBzp+GgAY3/YnWuQIMglfQBnbsHL5VV12uZ9RxSYjoTLt289okPDgYIkf3RAeV6fhyQDl46bYzK1bN+8fPNoNK26Bc74wJ0jDKRzUaUmnHka0o01cy385UqYvdU2OL2qlSza8WMpNTLBu7hhC8yW0pWx+q96lXgK92UCbbT5dbrMjoypiSJIV2U4JCavdWk5S1owilrxLHHJkeuGgBvDvOeHvtUSwZCr5FHAsIDfJk4U/QDjarS6lZIOPfUkVX4KbvNgkCgwYEpM5pnwHBkkVBnDDJTytMBv4KMf7o4p9hohhFbnnLJC0RDpnI5aHET4PW05Sw5rMx6MsR/yySyryX7DpTbcU+fmFikqDi3ksjzTr4jcnJjUmNIuaMq7YkBGv9vtBFJH/WuZ0HYzgamgAZ9TzDcSiGedOf5NPHohPQbYGBRakRQSz/AOjqPWUr50RPNETyiOfQJngQI="
                        ]
                    ]
                )->getBody()->getContents();
                $user = json_decode($newresponse);
                $syncAccount->fill(['email' => $user->userPrincipalName ?? '', 'social_id' => $user->id])->save();

                $accessToken = env('OUTLOOK_ACCESS_TOKEN');
                $graph = new Graph();
                $graph->setAccessToken($accessToken);
                
                $response = $graph->createRequest('POST', '/me/calendars')
                            ->attachBody(['name' => 'LegalCase1'])
                            ->execute();
                $calendar = json_decode($response);
                $syncAccount->fill([
                    'calendar_id' => $calendar->id,
                    'calendar_name' => $calendar->name
                ])->save(); */
                
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

        // To get logged-in user detail
        /* $guzzle = new \GuzzleHttp\Client(['base_uri' => 'https://graph.microsoft.com']);
        $newresponse = $guzzle->request(
            'GET',
            '/v1.0/me',
            ['headers' => [
                    'Authorization' => " Bearer EwBoA8l6BAAUkj1NuJYtTVha+Mogk+HEiPbQo04AAQXlPMFAC2NahrkRlygFCd4cVBtOF36WIz710GgIJ5j2Y7qxVeGi8IzVzyDczIxR8K6nrmP9ymvZVD5jJCm3bsnxmu6NMyEJCwa5nvqw0oYuv8F+tcAv0QqKGkcE2+WYpjFT/KaoTXaakWbzEfGnVx0G3nvhYCsYSlNAFthJ7A8IiWXsZNsG5wppmJj4E6L0EhMwHc9TevzWNDCF9a7zYYNwHaB2GXHLujATXn3uOyjnPLegORETzSi/Xdw8ClfbSCwjayANsMEsnoDHxX23zK3gkNBc7op/M59+yU/bKMEYWskVYp1EhJl3CZcjTBQkfFNSz135zi0W7xTyT1CClAQDZgAACK5QDo2NE/4kOALHEL/4Lzybm/W7YGNlTFIJlUvdwvzLlO6oSI1jI9QeSbmqvP3TAZhIlCwz+p/e5kDaX9W/naF6JYpYoeZZTGUnv2XTCfeE8WJhofxRFBdLZa/TQJ2H+uxw3O10qQ8p1egD+TosZj3LPI2ktn1SCBZs0pUkREa4HzluUn70r9y8qwcRh182fl9Xf/K3tJT6wnoTIO23uX/2/3Tqm9iIx7ZC1tk5tHoaGdtFA/TNUL+qskSeiWrql4rW9NagXAy5L9jOW0CGT+OpsHVw8r8LDCRB87txNb1GSWVjYHN0X19rXBzp+GgAY3/YnWuQIMglfQBnbsHL5VV12uZ9RxSYjoTLt289okPDgYIkf3RAeV6fhyQDl46bYzK1bN+8fPNoNK26Bc74wJ0jDKRzUaUmnHka0o01cy385UqYvdU2OL2qlSza8WMpNTLBu7hhC8yW0pWx+q96lXgK92UCbbT5dbrMjoypiSJIV2U4JCavdWk5S1owilrxLHHJkeuGgBvDvOeHvtUSwZCr5FHAsIDfJk4U/QDjarS6lZIOPfUkVX4KbvNgkCgwYEpM5pnwHBkkVBnDDJTytMBv4KMf7o4p9hohhFbnnLJC0RDpnI5aHET4PW05Sw5rMx6MsR/yySyryX7DpTbcU+fmFikqDi3ksjzTr4jcnJjUmNIuaMq7YkBGv9vtBFJH/WuZ0HYzgamgAZ9TzDcSiGedOf5NPHohPQbYGBRakRQSz/AOjqPWUr50RPNETyiOfQJngQI="
                ]
            ]
        )->getBody()->getContents();
        $user = json_decode($newresponse); */
        $accessToken = env('OUTLOOK_ACCESS_TOKEN');
        $graph = new Graph();
        $graph->setAccessToken("EwBoA8l6BAAUkj1NuJYtTVha+Mogk+HEiPbQo04AAYgu6Ui5H51+kXNYFQX+zdXUogeo0MTvmjD5c5OMoSrIU1HZaG62ko6DCKwXqUcZvsXV3QMh6YUv+cgS5LOgdOy77ynIsZfdZLiHSnLGjZ3+Tk/jIFMRAvmJXTBdCQf9jtlRwL29cG0TyCJAGjvLoV3VAutV2gWTLSdNA9incOy12amVAL3WmPs2UQ3boB5xJgXjWzpy15tX4bGSsnR19jWRi2nlgp3Zli300+372dqxatrqxQW7r4LYsLmuqEtCiZuwvuNjscTutK5HEAN745DvoyDs+jthLS0B2S/MnRVt/MZ2f2R51TUOY45SJokvsndsX6lM6egKZKjt47CDAkUDZgAACCg8ILWRYH/6OAI8SWXRHnU5hsbZa1R24Tul23QUAebEi94GqRQbHbWnyjJm/tx3D4hsaaBe/StWug6MNqf9TPEJi8ERxKhFV7/u1TdGMx82tiFpjiN1sWEAaOAM0ExWfgzuZIHNkTdz8TDQyOioe64LrAvZlc5Y+xxsYBHX8nI9VMPVHEVETvn4haXN23hY01QentaYrOqE2O3mfMppnDMQoDpFVJsvKTSHki6Z8je1H9sm1mecHLp9e7b4+OD7M7hXW0UoQdiibv92/6HhDJ97C5FXlFRl5ByfCZgZJ9p0zp0yugz87Msl+heGeV3ezYyyjjDkXEEgITQq+EqqkwhZXizh+YpOtMR0MjqQihRyKeDmugnf9iRYtZU15q7Qe/6u3Q1G021nuKIvoSPPd1cQcD5pRuo9zjn+VZ8qBIaRCKEu5PZ9bg+wSqfuSypVCazQvG3qHYHh18gii9h/vxT4nzmMicyeW0oSiy79B6D2+sp7RJbg55spXBxvp9eHWkxVKYb+mGTGOitLjByVkO4czb2wc1mcAokepQTqtpbNzu15jHYLgPFKDMGXtwaivln83KALM/4RlI+Bu6uiwuOOhYpUHmYXUmJMJqGxHfyaNoqcxDlc7/2bitY75x6ejiaYqfdCZMwOdVebur1oyuMM3gTJ6OmWgtRavxmIuV5aDcq/is6d8/IpvdTfgWzKQF/tN/PeXRVU4wEcakFJ8EGr4DCVrifedck8+GaoCgJwTol5tSP8z7wDEOCn2x9qx3iHgQI=");
        
        $response = $graph->createRequest('POST', '/me/calendars')
                    ->attachBody(['name' => 'LegalCase5'])
                    ->execute();
        $calendar = $response;
        $syncAccount->fill([
            'calendar_id' => @$calendar->id,
            'calendar_name' => $calendar->name
        ])->save();
        return $syncAccount;
    }
}