@extends('layouts.pdflayout')

<h4>Requested Funds</h4>
<table class="display table table-striped table-bordered" id="timeEntryGrid" style="width:100%;" border="1">
    <thead>
        <tr>
            <th>Number</th>
            <th class="col-md-auto"> Contact </th>
            <th>Account</th>
            <th> Amount </th>
            <th> Paid </th>
            <th> Amount Due </th>
            <th>Due </th>
            <th> Date Sent</th>
            <th> Viewed </th>
            <th class="status-col-header">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($case as $k=>$v){ ?>
        <tr style="padding-left: 4px;">
            <td scope="col" style="width: 10%;text-align:left;    white-space: nowrap!important;">
                {{$v->padding_id}}
            </td>

            <td scope="col" style="width: 15%;text-align:left;    white-space: nowrap!important;">
                {{$v->contact_name}}
            </td>

            <td scope="col" style="width: 25%;text-align:left;">
                {{$v->trust_account}} <?php if($v->trust_account) echo  "(Trust Account)" ;?>
            </td>
            <td scope="col" style="width: 6%;text-align:left;">
                ${{number_format($v->amount_requested,2)}}
            </td>
            <td scope="col" style="width: 6%;text-align:left;    white-space: nowrap!important;">
                ${{number_format($v->amount_paid,2)}}
            </td>
            <td scope="col" style="width: 6%;text-align:left;    white-space: nowrap!important;">
                ${{number_format($v->amount_due,2)}}
            </td>
            <td scope="col" style="width: 10%;text-align:left;    white-space: nowrap!important;">
                {{$v->due_date_format}}
            </td>
            <td scope="col" style="width: 10%;text-align:left;    white-space: nowrap!important;">
                {{$v->last_send}}
            </td>
            <td scope="col" style="width: 6%;text-align:left;    white-space: nowrap!important;">
                <?php
                         if($v->is_viewed=="no"){
                            echo "Never";
                         }else{
                             echo "Yes";
                         }?>
            </td>
            <td scope="col" style="width: 6%;text-align:left;    white-space: nowrap!important;">
                {{$v->current_status}}
            </td>

        </tr>

        <?php }?>
    </tbody>
</table>
