@extends('layouts.master')
@section('title', 'My Settings')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA);
?>
<div class="breadcrumb">
    <h3>Settings & Preferences</h1>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')
    </div>
    <div class="col-md-10">
        <div class="card mb-4 o-hidden">
            <div class="card-body">
            <div class="settings-content ml-4 w-100">
      <div class="row">
        <div class="col-8">
          <h3>
             My Notifications
          </h3>
        </div>
        <div class="col-4"></div>
      </div>
    
  <ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link active" href="">Recent Activity</a>
  </li>
  <!-- <li class="nav-item">
    <a class="nav-link " href="">Individual Notifications</a>
  </li> -->
</ul>

  <div class="row tabbed-card mb-4">
    <div class="card-body">
      <form id="notifications_form" action="{{ route('account/update_notifications') }}" accept-charset="UTF-8" method="post">
      @csrf
        <div class="clearfix form-group">
          <label style="float: left; font-weight:bold; padding-top: 8px;">
            Recent Activity Email Frequency
          </label>
          <div style="float: left; padding-top: 8px; margin-left: 4px;">
            <a class="help_tip" href="#" onclick="; return false;"><img src="https://assets.mycase.com/packs/svg/table_icons/question_mark_icon-abef3e2eca.svg"></a>
          </div>
          <div style="float: left; margin-left: 20px;">
            <select name="notification_email_interval" id="notification_email_interval" class="custom-select">
                <option value="0" <?php echo ( isset($UsersAdditionalInfo->notification_email_interval) && $UsersAdditionalInfo->notification_email_interval == 0) ? 'selected' : ''; ?> >Turn off</option>
                <option value="5" <?php echo ( isset($UsersAdditionalInfo->notification_email_interval) && $UsersAdditionalInfo->notification_email_interval == 5) ? 'selected' : ''; ?> >Send every 5 minutes</option>
                <option value="15" <?php echo ( isset($UsersAdditionalInfo->notification_email_interval) && $UsersAdditionalInfo->notification_email_interval == 15) ? 'selected' : ''; ?> >Send every 15 minutes</option>
                <option value="30" <?php echo ( isset($UsersAdditionalInfo->notification_email_interval) && $UsersAdditionalInfo->notification_email_interval == 30) ? 'selected' : ''; ?> >Send every 30 minutes</option>
                <option value="60" <?php echo ( isset($UsersAdditionalInfo->notification_email_interval) && $UsersAdditionalInfo->notification_email_interval == 60) ? 'selected' : ''; ?> >Send every hour</option>
                <option value="1440" <?php echo ( isset($UsersAdditionalInfo->notification_email_interval) && $UsersAdditionalInfo->notification_email_interval == 1440) ? 'selected' : ((!isset($UsersAdditionalInfo->notification_email_interval)) ? 'selected' : ''); ?> >Send once a day</option>
            </select>
          </div>
        </div>

        <table id="notifications" class="table">
                <thead class="bg-light" id="notifications-section-3">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Cases
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-3-1" id="section-method-all-3-1" value="1" class="all-notifications-toggle email-toggle case-email" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-3-2" id="section-method-all-3-2" value="1" class="all-notifications-toggle case-feed" data-method="2">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                    </th>
                  </tr>
                </thead>

                <tbody>
                @foreach($notificationSetting as $k => $v)   
                @if($v->type == 'cases')             
                <tr>
                  <td>{{$v->topic}}</td>
                    <td class="text-center">
                        <input type="checkbox" name="email[{{$v->id}}]" id="email_{{$v->id}}" value="1" class="email-toggle all-case-email" data-method="1" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_email == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="feed[{{$v->id}}]" id="feed_{{$v->id}}" value="1" class="feed-toggle all-case-feed" data-method="2" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_feed == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                @endif
                @endforeach
                </tbody>
                <thead class="bg-light" id="notifications-section-1">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Calendar
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-1-1" id="section-method-all-1-1" value="1" class="all-notifications-toggle email-toggle calendar-email" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-1-2" id="section-method-all-1-2" value="1" class="all-notifications-toggle calendar-feed" data-method="2">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                        <div class="text-center">
                          In-App Notification
                          <a class="help_tip" href="#" onclick="; return false;"><img src="https://assets.mycase.com/packs/svg/table_icons/question_mark_icon-abef3e2eca.svg"></a>
                        </div>
                        <div class="text-center mt-2">
                          <input type="checkbox" name="section-method-all-1-3" id="section-method-all-1-3" value="1" class="all-notifications-toggle" data-method="3">
                        </div>
                    </th>
                  </tr>
                </thead>

                <tbody>
                @foreach($notificationSetting as $k => $v)   
                @if($v->type == 'calendars')             
                <tr>
                  <td>{{$v->topic}}</td>
                    <td class="text-center">
                        <input type="checkbox" name="email[{{$v->id}}]" id="email_{{$v->id}}" value="1" class="email-toggle all-calendar-email" data-method="1" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_email == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="feed[{{$v->id}}]" id="feed_{{$v->id}}" value="1" class="feed-toggle all-calendar-feed" data-method="2" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_feed == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                @endif
                @endforeach
                </tbody>
                <thead class="bg-light" id="notifications-section-12">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Documents
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-12-1" id="section-method-all-12-1" value="1" class="all-notifications-toggle email-toggle documents-email" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-12-2" id="section-method-all-12-2" value="1" class="all-notifications-toggle documents-feed" data-method="2">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                        <div class="text-center">
                          In-App Notification
                          <a class="help_tip" href="#" onclick="; return false;"><img src="https://assets.mycase.com/packs/svg/table_icons/question_mark_icon-abef3e2eca.svg"></a>
                        </div>
                        <div class="text-center mt-2">
                          <input type="checkbox" name="section-method-all-12-3" id="section-method-all-12-3" value="1" class="all-notifications-toggle" data-method="3">
                        </div>
                    </th>
                  </tr>
                </thead>

                <tbody>
                @foreach($notificationSetting as $k => $v)   
                @if($v->type == 'documents')             
                <tr>
                  <td>{{$v->topic}}</td>
                    <td class="text-center">
                        <input type="checkbox" name="email[{{$v->id}}]" id="email_{{$v->id}}" value="1" class="email-toggle all-documents-email" data-method="1" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_email == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="feed[{{$v->id}}]" id="feed_{{$v->id}}" value="1" class="feed-toggle all-documents-feed" data-method="2" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_feed == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                @endif
                @endforeach                
                </tbody>
                <thead class="bg-light" id="notifications-section-11">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Tasks
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-11-1" id="section-method-all-11-1" value="1" class="all-notifications-toggle email-toggle task-email" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-11-2" id="section-method-all-11-2" value="1" class="all-notifications-toggle task-feed" data-method="2">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                        <div class="text-center">
                          In-App Notification
                          <a class="help_tip" href="#" onclick="; return false;"><img src="https://assets.mycase.com/packs/svg/table_icons/question_mark_icon-abef3e2eca.svg"></a>
                        </div>
                        <div class="text-center mt-2">
                          <input type="checkbox" name="section-method-all-11-3" id="section-method-all-11-3" value="1" class="all-notifications-toggle" data-method="3">
                        </div>
                    </th>
                  </tr>
                </thead>

                <tbody>
                @foreach($notificationSetting as $k => $v)   
                @if($v->type == 'tasks')             
                <tr>
                  <td>{{$v->topic}}</td>
                    <td class="text-center">
                        <input type="checkbox" name="email[{{$v->id}}]" id="email_{{$v->id}}" value="1" class="email-toggle all-task-email" data-method="1" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_email == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="feed[{{$v->id}}]" id="feed_{{$v->id}}" value="1" class="feed-toggle all-task-feed" data-method="2" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_feed == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                @endif
                @endforeach
                </tbody>
                <thead class="bg-light" id="notifications-section-10">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Time &amp; Billing
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-10-1" id="section-method-all-10-1" value="1" class="all-notifications-toggle email-toggle time-billing-email" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-10-2" id="section-method-all-10-2" value="1" class="all-notifications-toggle time-billing-feed" data-method="2">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                    </th>
                  </tr>
                </thead>

                <tbody>
                @foreach($notificationSetting as $k => $v)   
                @if($v->type == 'tasks')             
                <tr>
                  <td>{{$v->topic}}</td>
                    <td class="text-center">
                        <input type="checkbox" name="email[{{$v->id}}]" id="email_{{$v->id}}" value="1" class="email-toggle all-time-billing-email" data-method="1" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_email == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="feed[{{$v->id}}]" id="feed_{{$v->id}}" value="1" class="feed-toggle all-time-billing-feed" data-method="2" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_feed == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                @endif
                @endforeach
                </tbody>
                <thead class="bg-light" id="notifications-section-7">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Contacts &amp; Companies
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-7-1" id="section-method-all-7-1" value="1" class="all-notifications-toggle email-toggle contact-email" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-7-2" id="section-method-all-7-2" value="1" class="all-notifications-toggle contact-feed" data-method="2">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                    </th>
                  </tr>
                </thead>
                <tbody>
                @foreach($notificationSetting as $k => $v)   
                @if($v->type == 'contacts')             
                <tr>
                  <td>{{$v->topic}}</td>
                    <td class="text-center">
                        <input type="checkbox" name="email[{{$v->id}}]" id="email_{{$v->id}}" value="1" class="email-toggle all-contact-email" data-method="1" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_email == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="feed[{{$v->id}}]" id="feed_{{$v->id}}" value="1" class="feed-toggle all-contact-feed" data-method="2" <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_feed == 'yes')  ? 'checked="checked"' :''; ?>>
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                @endif
                @endforeach
                </tbody>
                <thead class="bg-light" id="notifications-section-8">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Firm Administration
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-8-1" id="section-method-all-8-1" value="1" class="all-notifications-toggle email-toggle firm-email" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-8-2" id="section-method-all-8-2" value="1" class="all-notifications-toggle firm-feed" data-method="2">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                    </th>
                  </tr>
                </thead>

                <tbody>
                @foreach($notificationSetting as $k => $v)   
                @if($v->type == 'firms')             
                <tr>
                  <td>{{$v->topic}}</td>
                    <td class="text-center">
                        <input type="checkbox" name="email[{{$v->id}}]" id="email_{{$v->id}}" value="1" class="email-toggle all-firm-email" data-method="1" 
                        <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_email == 'yes')  ? 'checked="checked"' :''; ?> >
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="feed[{{$v->id}}]" id="feed_{{$v->id}}" value="1" class="feed-toggle all-firm-feed" data-method="2"
                        <?php echo (isset($userNotificationSetting[$k]) && $userNotificationSetting[$k]->for_feed == 'yes')  ? 'checked="checked"' :''; ?> >
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                @endif
                @endforeach
                
              </tbody>
            </table>
        <div class="d-flex flex-row justify-content-end">
            <button type="submit" class="btn btn-outline-secondary btn-rounded m-1">Save Preferences</button>
        </div>
        </form>    
      </div>

        </div>

        </div>
            </div>
        </div>
    </div>
</div>
@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {

      //Case
      //select all case email checkboxes
        $(".case-email").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-case-email').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-case-email').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".case-email")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-case-email:checked').length == $('.all-case-email').length ){ 
            $(".case-email")[0].checked = true; //change "select all" checked status to true
          }
        });

        //select all case feed checkboxes
        $(".case-feed").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-case-feed').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-case-feed').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".case-feed")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-case-feed:checked').length == $('.all-case-feed').length ){ 
            $(".case-feed")[0].checked = true; //change "select all" checked status to true
          }
        });

        if ($('.all-case-email:checked').length == $('.all-case-email').length ){ 
          $(".case-email")[0].checked = true; //change "select all" checked status to true
        }
        if ($('.all-case-feed:checked').length == $('.all-case-feed').length ){ 
          $(".case-feed")[0].checked = true; //change "select all" checked status to true
        }

        //Calendar
        //select all Calendar email checkboxes
        $(".calendar-email").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-calendar-email').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-calendar-email').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".calendar-email")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-calendar-email:checked').length == $('.all-calendar-email').length ){ 
            $(".calendar-email")[0].checked = true; //change "select all" checked status to true
          }
        });

        //select all Calendar feed checkboxes
        $(".calendar-feed").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-calendar-feed').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-calendar-feed').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".calendar-feed")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-calendar-feed:checked').length == $('.all-calendar-feed').length ){ 
            $(".calendar-feed")[0].checked = true; //change "select all" checked status to true
          }
        });

        if ($('.all-calendar-email:checked').length == $('.all-calendar-email').length ){ 
          $(".calendar-email")[0].checked = true; //change "select all" checked status to true
        }
        if ($('.all-calendar-feed:checked').length == $('.all-calendar-feed').length ){ 
          $(".calendar-feed")[0].checked = true; //change "select all" checked status to true
        }
        //Documents
        //select all Documents email checkboxes
        $(".documents-email").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-documents-email').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-documents-email').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".documents-email")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-documents-email:checked').length == $('.all-documents-email').length ){ 
            $(".documents-email")[0].checked = true; //change "select all" checked status to true
          }
        });

        //select all Documents feed checkboxes
        $(".documents-feed").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-documents-feed').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-documents-feed').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".documents-feed")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-documents-feed:checked').length == $('.all-documents-feed').length ){ 
            $(".documents-feed")[0].checked = true; //change "select all" checked status to true
          }
        });
        if ($('.all-documents-email:checked').length == $('.all-documents-email').length ){ 
          $(".documents-email")[0].checked = true; //change "select all" checked status to true
        }
        if ($('.all-documents-feed:checked').length == $('.all-documents-feed').length ){ 
          $(".documents-feed")[0].checked = true; //change "select all" checked status to true
        }

        //Tasks
        //select all Tasks email checkboxes
        $(".task-email").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-task-email').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-task-email').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".task-email")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-task-email:checked').length == $('.all-task-email').length ){ 
            $(".task-email")[0].checked = true; //change "select all" checked status to true
          }
        });

        //select all tasks feed checkboxes
        $(".task-feed").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-task-feed').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-task-feed').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".task-feed")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-task-feed:checked').length == $('.all-task-feed').length ){ 
            $(".task-feed")[0].checked = true; //change "select all" checked status to true
          }
        });
        if ($('.all-task-email:checked').length == $('.all-task-email').length ){ 
          $(".task-email")[0].checked = true; //change "select all" checked status to true
        }
        if ($('.all-task-feed:checked').length == $('.all-task-feed').length ){ 
          $(".task-feed")[0].checked = true; //change "select all" checked status to true
        }

        //Time & Billing
        //select all Time and billing email checkboxes
        $(".time-billing-email").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-time-billing-email').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-time-billing-email').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".time-billing-email")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-time-billing-email:checked').length == $('.all-time-billing-email').length ){ 
            $(".time-billing-email")[0].checked = true; //change "select all" checked status to true
          }
        });

        //select all Time and billing  feed checkboxes
        $(".time-billing-feed").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-time-billing-feed').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-time-billing-feed').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".time-billing-feed")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-time-billing-feed:checked').length == $('.all-time-billing-feed').length ){ 
            $(".time-billing-feed")[0].checked = true; //change "select all" checked status to true
          }
        });
        if ($('.all-time-billing-email:checked').length == $('.all-time-billing-email').length ){ 
          $(".time-billing-email")[0].checked = true; //change "select all" checked status to true
        }
        if ($('.all-time-billing-feed:checked').length == $('.all-time-billing-feed').length ){ 
          $(".time-billing-feed")[0].checked = true; //change "select all" checked status to true
        }

        //Contact
        //select all contact email checkboxes
        $(".contact-email").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-contact-email').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-contact-email').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".contact-email")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-contact-email:checked').length == $('.all-contact-email').length ){ 
            $(".contact-email")[0].checked = true; //change "select all" checked status to true
          }
        });

        //select all contact feed checkboxes
        $(".contact-feed").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-contact-feed').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-contact-feed').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".contact-feed")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-contact-feed:checked').length == $('.all-contact-feed').length ){ 
            $(".contact-feed")[0].checked = true; //change "select all" checked status to true
          }
        });
        if ($('.all-contact-email:checked').length == $('.all-contact-email').length ){ 
          $(".contact-email")[0].checked = true; //change "select all" checked status to true
        }
        if ($('.all-contact-feed:checked').length == $('.all-contact-feed').length ){ 
          $(".contact-feed")[0].checked = true; //change "select all" checked status to true
        }

        //Firm
        //select all firm email checkboxes
        $(".firm-email").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-firm-email').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-firm-email').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".firm-email")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-firm-email:checked').length == $('.all-firm-email').length ){ 
            $(".firm-email")[0].checked = true; //change "select all" checked status to true
          }
        });

        //select all contact feed checkboxes
        $(".firm-feed").change(function(){  //"select all" change 
          var status = this.checked; // "select all" checked status
          $('.all-firm-feed').each(function(){ //iterate all listed checkbox items
            this.checked = status; //change ".checkbox" checked status
          });
        });

        $('.all-firm-feed').change(function(){ //".checkbox" change 
          //uncheck "select all", if one of the listed checkbox item is unchecked
          if(this.checked == false){ //if this item is unchecked
            $(".firm-feed")[0].checked = false; //change "select all" checked status to false
          }
          
          //check "select all" if all checkbox items are checked
          if ($('.all-firm-feed:checked').length == $('.all-firm-feed').length ){ 
            $(".firm-feed")[0].checked = true; //change "select all" checked status to true
          }
        });

        if ($('.all-firm-email:checked').length == $('.all-firm-email').length ){ 
          $(".firm-email")[0].checked = true; //change "select all" checked status to true
        }
        if ($('.all-firm-feed:checked').length == $('.all-firm-feed').length ){ 
          $(".firm-feed")[0].checked = true; //change "select all" checked status to true
        }



});
</script>
@stop
@endsection
