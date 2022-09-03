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

    protected $outlookAccessToken;
    function __construct()
    {
        $this->outlookAccessToken = "EwBoA8l6BAAUkj1NuJYtTVha+Mogk+HEiPbQo04AAUZFmAJnbYLWsygFovtDkiyPyjBD2ZNLeIGuthhz+yBUR3ABycrQllvXmxOfRKR6m8MiYVmZNbZvkk3HG0Ky9s1FcC/xG4SyVMtydXR18QwhSXKgki7NfZY1rCCBWiVdYLmOw/VqFzeENE7PribT9AXMM43VnJ0vBdBvyNug7yCawdsLo8H3z1MWzy/dT6YHhF9Jw4QdCeq32B8+cOyu06v/HnAKUqhS+sCyFzPqOyUc4dPwE21vRm1nt+jsLufigX9W0v0AeZgjCt1SuoC7s11031dFde+41p00xrKWg90qcBCKu+5BJEl8nsM85zgMylfA1XDYlmcUkifZ9VzCAD4DZgAACIKIUbeqZGbCOAKM+wuCmHjyk32WYNwcfY60iju/5BbZHmV3UFbfdaFpVYvItiyU1HC671BdGotMiStky/wei+KUyX6sXaOx21cWf2wLp2OHX+0N67maTTInk1JX6krcViN0m/b7CLfLxt4cCDMvoxupLeMFVWSn9OraC8A5Nm6/HGDiaFDXGq/UvRCoM/3GQeeiDvxUpjaWxpm+R7sKwZt7qFaABB5cHHVLWsmPCJ6XO0AtL8QNKbh52MZqJ97LKIuqqiuVr+Xip++bUElAl/rUkb8C7lnsqi0SM/lMhsNbEHtyBGnqp4JB92l0DNp27sRsfa1a2LYIv2akKW0vNmDKFawiDwao3VKQGniggJr/aCuM/iFOMXsOlx/IuOmtDkR+rfygSJQMap0xTPERHBMajbYLpNIiTB0zLZVAY+A/CLDqKFFcrdK1REOTywCgt9KqEfWpX0btrmhDl4Tar0MJqhIhmMbDiIKHmRM6oj4ghsjvJTbh/PNDJpKJzLIBAtWK9nSdVBGAOifMaqoCb0QyA/bkqWwaEsnkXktTtr5ofNs8wqdxLcRnzN+j1RijoBL7mcyPQqmnYCTZR+PIVyfbP81AnuUCTO5lDXtrXj7xeBjZSvG/FyUbfCZMgB78/yPBns8yQBl7GZPKhcsXnmfP97SI1G/1m0aUoBdeWNlr6ZN1/FJm8tG8EL0oyxb4ANIv6KKtMip48vhZ4TuTwoPD1nyiAyXx4wCTHwTKkic7lh0ZeHeiRze/cJafbIw/Zw5qgQI=";
    }

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
                $newresponse = $guzzle->request('GET', '/v1.0/me', [
                        'headers' => [
                            'Authorization' => " Bearer EwBoA8l6BAAUkj1NuJYtTVha+Mogk+HEiPbQo04AAQXlPMFAC2NahrkRlygFCd4cVBtOF36WIz710GgIJ5j2Y7qxVeGi8IzVzyDczIxR8K6nrmP9ymvZVD5jJCm3bsnxmu6NMyEJCwa5nvqw0oYuv8F+tcAv0QqKGkcE2+WYpjFT/KaoTXaakWbzEfGnVx0G3nvhYCsYSlNAFthJ7A8IiWXsZNsG5wppmJj4E6L0EhMwHc9TevzWNDCF9a7zYYNwHaB2GXHLujATXn3uOyjnPLegORETzSi/Xdw8ClfbSCwjayANsMEsnoDHxX23zK3gkNBc7op/M59+yU/bKMEYWskVYp1EhJl3CZcjTBQkfFNSz135zi0W7xTyT1CClAQDZgAACK5QDo2NE/4kOALHEL/4Lzybm/W7YGNlTFIJlUvdwvzLlO6oSI1jI9QeSbmqvP3TAZhIlCwz+p/e5kDaX9W/naF6JYpYoeZZTGUnv2XTCfeE8WJhofxRFBdLZa/TQJ2H+uxw3O10qQ8p1egD+TosZj3LPI2ktn1SCBZs0pUkREa4HzluUn70r9y8qwcRh182fl9Xf/K3tJT6wnoTIO23uX/2/3Tqm9iIx7ZC1tk5tHoaGdtFA/TNUL+qskSeiWrql4rW9NagXAy5L9jOW0CGT+OpsHVw8r8LDCRB87txNb1GSWVjYHN0X19rXBzp+GgAY3/YnWuQIMglfQBnbsHL5VV12uZ9RxSYjoTLt289okPDgYIkf3RAeV6fhyQDl46bYzK1bN+8fPNoNK26Bc74wJ0jDKRzUaUmnHka0o01cy385UqYvdU2OL2qlSza8WMpNTLBu7hhC8yW0pWx+q96lXgK92UCbbT5dbrMjoypiSJIV2U4JCavdWk5S1owilrxLHHJkeuGgBvDvOeHvtUSwZCr5FHAsIDfJk4U/QDjarS6lZIOPfUkVX4KbvNgkCgwYEpM5pnwHBkkVBnDDJTytMBv4KMf7o4p9hohhFbnnLJC0RDpnI5aHET4PW05Sw5rMx6MsR/yySyryX7DpTbcU+fmFikqDi3ksjzTr4jcnJjUmNIuaMq7YkBGv9vtBFJH/WuZ0HYzgamgAZ9TzDcSiGedOf5NPHohPQbYGBRakRQSz/AOjqPWUr50RPNETyiOfQJngQI="
                        ]
                    ]
                )->getBody()->getContents();
                $user = json_decode($newresponse);
                $syncAccount->fill(['email' => $user->userPrincipalName ?? '', 'social_id' => $user->id])->save();

                $guzzle = new \GuzzleHttp\Client(['base_uri' => 'https://graph.microsoft.com']);
                $newresponse = $guzzle->request('POST', '/v1.0/me/calendars', [
                    'headers' => [
                        'Authorization' => "Bearer EwBoA8l6BAAUkj1NuJYtTVha+Mogk+HEiPbQo04AAaS0+fv3NGstH7Nn7vykGPjNqgk+jzH3BzWn86B/kVvgs9i77DIF+ie8It4xO2kvx3VhiBl4Y8y5PgeN8D/1LrPkIblsn9Tanr/g9zMcrIKm3lgvPWTGjNQFzfGtGddVIWYELTJMn01AwnBWnTv+ehytUgeGjr0Bc86Ipkte0YDh74BVdWKwmCHsyC9hoFIU5Bk5UdLCbrGu3hPNZoSPZmVb40w49KdiqpO783aZev+ixstXMhIbwk2K2lh6YOLw2y3Z7CrTH7pgwLp32KCpmtw1LN8+JXbmo3PpLiVL89YvdH8H4EoZRj85ox5JvwWVJ0U/U6SMBMdv+S1wm2CXDicDZgAACJ1M3iG7oFKdOALB9SlZll59B9wgxyIdzLkr5EtLQ0sK8AxTiO2f8Df/VsThbPOxjQjtoi+5pecDDejO86lx2w4TRCHSZsQlryFIkww0M37GA+Hu5FQ74mtRYn1ZU2u/hWsGOj1FCzWMHjlXWB4Q4ge+c/3oveDKKzfsarSTjQSxVfoieqEwnWz33bSngNxsJ4Dj5dwaQRWIaw/YbGlqL+7t2s2d+TVI9m5PS0gbDD73ozFYUKfwpwxJAnD4hv7b7709SYgpobEJUoAvGuaghuSuhDiKgjMzHF22YSLZSLhDzBMBdX2Um76eOS/lb/z7xcoRUtExz6M+V8cgqxCkMB06HmRB3a/bMS9/MfUmrrdiblrlmE2xlUZmi5dDtDvxYLkYhCjzl+GdyNr4Fpjt22tN3yS97GfLpJ0GU5oATQzjyzqjRKwX4h9uJYTL/sgpHR+hSvOkMkGbNHTkcSiIjwoN5X3ws5SfF7Bov5aFYuTn270iR1lg4BIbDlMxKeT9h19u8hLs3b0LdEhtrx5k/z2GTgtTY3YsbK+pzKWxl87xR65j5ECTxtyVi2AfEMcMwqSn4dTA9K6XUEpD2QomyrZlpRCmKxWuE4k+px21mju1r9qocrp5CVG+h1Voo6e9JCYzjvwjd59pqVzS9j4Hy0PFqXGvv8kIF5cn0rC8HbPMJCDLDK95xBu1M4UPRt99W/stUA+/svhFmby7uOdTFRK/C3RiExrCeblug0RYxr3yaFxOeOGi2KU89onrsGzbPki9gQI=",
                        'content-type' => "application/json",
                    ], 'json' => ['name' => 'LegalCase10']
                ])->getBody()->getContents();
                $calendar = json_decode($newresponse);
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

        /* if(Carbon::now()->gt(Carbon::parse($syncAccount->expires_in))) {
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
        } */

        /* $guzzle = new \GuzzleHttp\Client(['base_uri' => 'https://graph.microsoft.com']);
        $newresponse = $guzzle->request('POST', '/v1.0/me/calendars', [
            'headers' => [
                'Authorization' => "Bearer EwBoA8l6BAAUkj1NuJYtTVha+Mogk+HEiPbQo04AAQCZa7MgQVM5lYL2rmgejCVdLWWaXIybrGG/JbaEOKy9qZ6/VDWsXf2ZXU6jqjQ984CHUcYK68cnA+UTDSu6giFyys9WJOkzuVhkD4CHI1eDf0lmYZfAZ4cAQ+t8PMtu6XefcDM4z+T9M2AylxLQkQTTpbDgyBa4DibHU3nN/wDwxxsRMF52t09NbOq3WW+SKBXLwHILwVc9P6eAOkZ/2xNhJfzoJjbdtzAxDR0idou+/yt0qoCVQQuSiBqIKlMVxIlH2mKqxFfvrk9NqnaNF5BcKNULBmdtCZw+pKCbdtQAp2+mNBaQst652G48jpJKxQ6GL3J7MrkzqLVozUbb2XADZgAACAB4aKfkxh32OAKEUuw462NpEnZhJQwDQufhNBx37/D4b2ZsGkRlgU5cJB43t1xN6ddZJHoEtJxev8GcSb0OjrreSoEyEr2cjSrl2h2oLGYFfpl3Arvlgsgxao0tSBKPQgVEm0i5stnwfrHuYeCTY8YPtRhdLslW5ZU96XbAzfkU0BuFVMpsTGZ429Iqs2o4M34/21SowAzNrmfXkFIHXksOSxxzQMVsVIc9tng6iZQp4yJOX36UNRuS0CYI21jY/IyrvMjBST8Ddf8SjtwQNriNCxhSmtBz2nwa++4COl4RZbLvkOX1gCu9HdYSBGf3KADggfrMTMXWWSoI+aKTy7OVfafJcCUjIe5DO8xNt2sHZjQl0pKy9RkA5LA8AcgXgrT9wzd2GpPU3RIBiEoBlBkKUOQTP8qBcvKm6BHIjmkDUaArKFbV4zeg4IURiWFHRaENhtD55cJop8iMjA7C0EELxEcpRUxDaD43nhsQnuniTca3THEVULMCAAGwny2O23kxvDr7vuoIL9wO6eKYfKY8Vf8hPr1WjZp9ys/BsYV5OIxmXX+5OOnj0e1yUs+mWyzhJMhxjal7wgM8Uv0tWgV4WbOB86aWoIZZjBYE0iGOrcXP+JV4vEESnpMYhyzHK0l3N1s8/K/r4N1Uc6h2yA7HJg/c8eAPXQC1JbSN5drj7SA8+TzQ8gNZfSyKIN74IIjvM4NIRci8jQCNVN2qkg9DnmSFZJ2gboGUSg5tc+wxOGH3RX1p9zXZz5x2B6JQY16OgQI=",
                'content-type' => "application/json",
            ], 'json' => ['name' => 'LegalCase1']
        ])->getBody()->getContents();
        $calendar = json_decode($newresponse);
        dd($calendar); */

        $guzzle = new \GuzzleHttp\Client(['base_uri' => 'https://graph.microsoft.com']);
        $newresponse = $guzzle->request('POST', '/v1.0/me/calendars/'.$syncAccount->calendar_id.'/events', [
            'headers' => [
                'Authorization' => "Bearer EwBoA8l6BAAUkj1NuJYtTVha+Mogk+HEiPbQo04AAeYbCiJBvTYW+oxwG+S8GMPERArD/eVpz/hQZCRi12w+UaY4MNCtDHN9EeHZOSxgWQrjEExE+5tf3beEpFJp8E5EkWB/ee7hp8uJzHAuTPxr5dJJKr1eRA3miy6YDpaOVhoJJ5JujtAv0891lCSgnkpvHR9g41htNWA0Pl9cxFXgqsYTo95cSxEKOvTuJ/S7SSXUg0l5MqlD3Gn9gEPPZiy1nIApBWr/2Qy9YOfpyrFZ6iNvGeyfrVAKLwJJq6nt41gw5678KvDybAcJa82wTdKxwZ6hadX/6wnldf2MX+2q+N2fLSAVUjFq/MFTp12zlcieQVgLrciv8TOs86mSOZoDZgAACKPVnFvo0wpSOAI1d1ccEynl+NdWikY8W43gSRTJAzlsFC/qtzXRMVciT4u7r1QlRk9MU6ALbuEup1lv2KpTCFngyLBsk+H9bozYQkaY2mjKqR7mHzsCVrQ/SZHFt8JrtiaoJcmg4+9QFMVA9ZAmv+CVrok64wQ1sO0ZiXQSb6vkBNWx96JPF9AUsIj7Qq9O9fm8TIdcqQskSZ9WRyEWW9oO9dqS5Tfc6YeO6YDDJGur60BoZtcKw1aLS9A1WDWBPocwCPV8cIUxqKBD5w9OkL3ecD1wgJ7zvCzizzpTR7/bu4FaRues/X61Hr3TkYKasw4coWM5RRj7ipu/CvLyxYshEBGPJ3dZoZT0BMlQwobRjb5ysm4Ej48WfKnkZPXpTZlQ2Xfeq5P53oooXAVNDdR9yrBn9MfggLg+I5foOALMCnPXDpwPTch+1AMOsW9Ua4GbLqAm2orc2HnEGVf349IHNZKgFvSRGTezi/L3LkTn/wZuY7XTKIxmcwu3NgrTaOSRK6xrrzblCUpDOJ+ieWUHVYRIrS2VIV01wBAKwzaRQJ32DuzloZFF2BktQJ90Jx7gyqBa/Nf3QbPq0fcuYFWOdTbLRoFLtW9NkcHDq6yiQeVySy7s0er24xSlG3GoYVVsyUvQbaMENc4RvCurbsZVL3waeJskGYgVqUWOAfBXMoU1ELXrVNrY0pN7RR1QoLO8xE4OHnPropseJQG0JLHYON1TBd+s241jMgjIJOwokMIKwhj8LNGQCfeFlUD3TODTgQI=",
                'content-type' => "application/json",
            ],
            'json' => [
                "subject"=> "daily event",
                "body"=> [
                    "contentType"=> "HTML",
                    "content"=> "Does noon work for you?"
                ],
                "start"=> [
                    "dateTime"=> "2022-09-08T12:00:00",
                    "timeZone"=> "America/Mexico_City"
                ],
                "end"=> [
                    "dateTime"=> "2022-09-08T13:00:00",
                    "timeZone"=> "America/Mexico_City"
                ],
                // "isAllDay"=> true,
                "Recurrence" => [
                    "pattern" => [
                        "type" => "daily",
                        "interval" => 1
                    ],
                    "Range" => [
                        "Type" => "EndDate",
                        "EndDate" => "2022-09-30",
                        "StartDate" => "2022-09-08",
                        "RecurrenceTimeZone" => "America/Mexico_City"
                    ]
                ],
                "location"=>[
                    "displayName"=>"Harry's Bar"
                ],
                "attendees"=> [
                    [
                    "emailAddress"=> [
                        "address"=>"samanthab@contoso.onmicrosoft.com",
                        "name"=> "Samantha Booth"
                    ],
                    "type"=> "required"
                    ]
                ],
                "allowNewTimeProposals"=> true,
            ]
        ])->getBody()->getContents();
        $calendar = json_decode($newresponse);
        dd($calendar);
    }
}