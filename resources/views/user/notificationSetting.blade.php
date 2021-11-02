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
  <li class="nav-item">
    <a class="nav-link " href="">Individual Notifications</a>
  </li>
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
            <select name="notification_seconds" id="notification_seconds" class="custom-select">
                <option value="">Turn off</option>
                <option value="300">Send every 5 minutes</option>
                <option value="900">Send every 15 minutes</option>
                <option value="1800">Send every 30 minutes</option>
                <option value="3600">Send every hour</option>
                <option selected="selected" value="86400">Send once a day</option>
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
                        <input type="checkbox" name="section-method-all-3-1" id="section-method-all-3-1" value="1" class="all-notifications-toggle email-toggle" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-3-2" id="section-method-all-3-2" value="1" class="all-notifications-toggle" data-method="2">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                    </th>
                  </tr>
                </thead>

                <tbody><tr>
                  <td>A new case is added to the system</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[3-1-1]" id="notifications_3-1-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[3-1-2]" id="notifications_3-1-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>An existing case is updated</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[3-2-1]" id="notifications_3-2-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[3-2-2]" id="notifications_3-2-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>An open case is closed</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[3-13-1]" id="notifications_3-13-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[3-13-2]" id="notifications_3-13-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>A closed case is reopened</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[3-14-1]" id="notifications_3-14-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[3-14-2]" id="notifications_3-14-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>A closed case is deleted</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[3-3-1]" id="notifications_3-3-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[3-3-2]" id="notifications_3-3-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>

                <tr>
                  <td>A new note is added, edited, or deleted on a case you're linked to</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[1002-1-1]" id="notifications_1002-1-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[1002-1-2]" id="notifications_1002-1-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>

                <tr>
                  <td>You are added or removed from a case</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[19-7-1]" id="notifications_19-7-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[19-7-2]" id="notifications_19-7-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>

                <tr>
                  <td>A contact / company is added or removed from a case you're linked to</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-7-1]" id="notifications_7-7-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-7-2]" id="notifications_7-7-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>

                <tr>
                  <td>A firm user is added or removed from a case you're linked to</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[8-7-1]" id="notifications_8-7-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[8-7-2]" id="notifications_8-7-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                </tbody><thead class="bg-light" id="notifications-section-1">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Calendar
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-1-1" id="section-method-all-1-1" value="1" class="all-notifications-toggle email-toggle" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-1-2" id="section-method-all-1-2" value="1" class="all-notifications-toggle" data-method="2">
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

                <tbody><tr>
                  <td>A new event is added to the system</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-1-1]" id="notifications_1-1-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-1-2]" id="notifications_1-1-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-1-3]" id="notifications_1-1-3" value="1" class="in_app-toggle" data-method="3" checked="checked">
                    </td>
                </tr>
                <tr>
                  <td>An existing event is updated</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-2-1]" id="notifications_1-2-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-2-2]" id="notifications_1-2-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>Someone deletes an event</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-3-1]" id="notifications_1-3-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-3-2]" id="notifications_1-3-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>Someone comments on an event</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-9-1]" id="notifications_1-9-1" value="1" disabled="disabled" title="Email notifications are always sent for this activity." data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-9-2]" id="notifications_1-9-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-9-3]" id="notifications_1-9-3" value="1" disabled="disabled" title="In-App notifications are always sent for this activity." data-method="3" checked="checked">
                    </td>
                </tr>
                <tr>
                  <td>A contact views an event</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-23-1]" id="notifications_1-23-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[1-23-2]" id="notifications_1-23-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                </tbody><thead class="bg-light" id="notifications-section-12">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Documents
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-12-1" id="section-method-all-12-1" value="1" class="all-notifications-toggle email-toggle" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-12-2" id="section-method-all-12-2" value="1" class="all-notifications-toggle" data-method="2">
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

                <tbody><tr>
                  <td>A new document is uploaded in the system</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-1-1]" id="notifications_12-1-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-1-2]" id="notifications_12-1-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-1-3]" id="notifications_12-1-3" value="1" class="in_app-toggle" data-method="3" checked="checked">
                    </td>
                </tr>
                <tr>
                  <td>An existing document is updated</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-2-1]" id="notifications_12-2-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-2-2]" id="notifications_12-2-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>Someone deletes a document</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-3-1]" id="notifications_12-3-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-3-2]" id="notifications_12-3-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>Someone comments on a document</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-9-1]" id="notifications_12-9-1" value="1" disabled="disabled" title="Email notifications are always sent for this activity." data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-9-2]" id="notifications_12-9-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-9-3]" id="notifications_12-9-3" value="1" disabled="disabled" title="In-App notifications are always sent for this activity." data-method="3" checked="checked">
                    </td>
                </tr>
                <tr>
                  <td>A contact views a document</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-23-1]" id="notifications_12-23-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[12-23-2]" id="notifications_12-23-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                </tbody><thead class="bg-light" id="notifications-section-11">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Tasks
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-11-1" id="section-method-all-11-1" value="1" class="all-notifications-toggle email-toggle" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-11-2" id="section-method-all-11-2" value="1" class="all-notifications-toggle" data-method="2">
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

                <tbody><tr>
                  <td>A new task is added</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-1-1]" id="notifications_11-1-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-1-2]" id="notifications_11-1-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-1-3]" id="notifications_11-1-3" value="1" class="in_app-toggle" data-method="3" checked="checked">
                    </td>
                </tr>
                <tr>
                  <td>An existing task is updated</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-2-1]" id="notifications_11-2-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-2-2]" id="notifications_11-2-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-2-3]" id="notifications_11-2-3" value="1" class="in_app-toggle" data-method="3" checked="checked">
                    </td>
                </tr>
                <tr>
                  <td>Someone deletes a task</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-3-1]" id="notifications_11-3-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-3-2]" id="notifications_11-3-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>A task is completed</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-10-1]" id="notifications_11-10-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-10-2]" id="notifications_11-10-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>A completed task is marked incomplete</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-19-1]" id="notifications_11-19-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[11-19-2]" id="notifications_11-19-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                </tbody><thead class="bg-light" id="notifications-section-10">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Time &amp; Billing
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-10-1" id="section-method-all-10-1" value="1" class="all-notifications-toggle email-toggle" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-10-2" id="section-method-all-10-2" value="1" class="all-notifications-toggle" data-method="2">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                    </th>
                  </tr>
                </thead>

                <tbody><tr>
                  <td>A new time entry / expense is added</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[10-1-1]" id="notifications_10-1-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[10-1-2]" id="notifications_10-1-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>An existing time entry / expense is updated</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[10-2-1]" id="notifications_10-2-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[10-2-2]" id="notifications_10-2-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>Someone deletes a time entry / expense</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[10-3-1]" id="notifications_10-3-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[10-3-2]" id="notifications_10-3-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>

                <tr>
                  <td>A new invoice is added to a case you're linked to</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-1-1]" id="notifications_15-1-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-1-2]" id="notifications_15-1-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>An existing invoice is updated on a case you're linked to</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-2-1]" id="notifications_15-2-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-2-2]" id="notifications_15-2-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>A contact views an invoice</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-23-1]" id="notifications_15-23-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-23-2]" id="notifications_15-23-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>Someone deletes an invoice on a case you're linked to</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-3-1]" id="notifications_15-3-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-3-2]" id="notifications_15-3-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>A payment is made on an invoice on a case you're linked to</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-11-1]" id="notifications_15-11-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-11-2]" id="notifications_15-11-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>A payment is refunded on an invoice on a case you're linked to</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-12-1]" id="notifications_15-12-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-12-2]" id="notifications_15-12-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>Someone shares an invoice on a case you're linked to</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-17-1]" id="notifications_15-17-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-17-2]" id="notifications_15-17-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>Someone sends a reminder on a case you're linked to</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-24-1]" id="notifications_15-24-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[15-24-2]" id="notifications_15-24-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                </tbody><thead class="bg-light" id="notifications-section-7">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Contacts &amp; Companies
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-7-1" id="section-method-all-7-1" value="1" class="all-notifications-toggle email-toggle" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-7-2" id="section-method-all-7-2" value="1" class="all-notifications-toggle" data-method="2">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                    </th>
                  </tr>
                </thead>

                <tbody><tr>
                  <td>A new contact/company is added to the system</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-1-1]" id="notifications_7-1-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-1-2]" id="notifications_7-1-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>An existing contact/company is updated</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-2-1]" id="notifications_7-2-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-2-2]" id="notifications_7-2-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>Someone archives a contact/company</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-13-1]" id="notifications_7-13-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-13-2]" id="notifications_7-13-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>Someone unarchives a contact/company</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-14-1]" id="notifications_7-14-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-14-2]" id="notifications_7-14-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>Someone deletes a company</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-3-1]" id="notifications_7-3-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-3-2]" id="notifications_7-3-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>A contact logs in to MyCase</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-4-1]" id="notifications_7-4-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[7-4-2]" id="notifications_7-4-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>

                <tr>
                  <td>A new note is added, edited, or deleted on a contact</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[1001-1-1]" id="notifications_1001-1-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[1001-1-2]" id="notifications_1001-1-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                </tbody><thead class="bg-light" id="notifications-section-8">
                  <tr>
                    <th class="font-weight-bold" style="vertical-align: middle">
                      Firm Administration
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Email</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-8-1" id="section-method-all-8-1" value="1" class="all-notifications-toggle email-toggle" data-method="1">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                      <div class="text-center">In Activity Feed</div>
                      <div class="text-center mt-2">
                        <input type="checkbox" name="section-method-all-8-2" id="section-method-all-8-2" value="1" class="all-notifications-toggle" data-method="2">
                      </div>
                    </th>
                    <th style="vertical-align: middle">
                    </th>
                  </tr>
                </thead>

                <tbody><tr>
                  <td>A new firm user is added</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[8-1-1]" id="notifications_8-1-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[8-1-2]" id="notifications_8-1-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>

                <tr>
                  <td>Firm user contact information is updated</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[8-2-1]" id="notifications_8-2-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[8-2-2]" id="notifications_8-2-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
                <tr>
                  <td>A firm user is deactivated or reactivated</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[8-13-1]" id="notifications_8-13-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[8-13-2]" id="notifications_8-13-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>

                <tr>
                  <td>Firm user permissions are changed</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[8-6-1]" id="notifications_8-6-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[8-6-2]" id="notifications_8-6-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>

                <tr>
                  <td>Items are imported into MyCase</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[18-15-1]" id="notifications_18-15-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[18-15-2]" id="notifications_18-15-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>

                <tr>
                  <td>Firm information is updated</td>

                    <td class="text-center">
                        <input type="checkbox" name="notifications[4-2-1]" id="notifications_4-2-1" value="1" class="email-toggle" data-method="1" checked="checked">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="notifications[4-2-2]" id="notifications_4-2-2" value="1" class="feed-toggle" data-method="2" checked="checked">
                    </td>
                    <td class="text-center">
                    </td>
                </tr>
        </tbody></table>
        <div class="d-flex flex-row justify-content-end">
            <button type="submit" class="btn btn-outline-secondary btn-rounded m-1">Save Preferences</button>
        </div>
</form>    </div>

  </div>

  </div>
            </div>
        </div>
    </div>
</div>
@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        
    });
</script>
@stop
@endsection
