@extends('layouts.pdflayout') 
<h4>Saved Activities</h4>
<table class="display table table-striped table-bordered" id="timeEntryGrid" style="width:100%;" border="1">
    <thead>
        <tr>
            <th width="10%">Activity</th>
            <th width="15%">Default Description</th>
            <th width="5%">Time Entries</th>
            <th width="15%">Expenses</th>
            <th width="5%">Total Hours</th>
            <th width="5%">Flat Fees</th>
            <th width="5%">Created By</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($case as $k=>$v){ ?>
        <tr style="padding-left: 4px;">
            <td scope="col" style="width: 10%;text-align:left;    white-space: nowrap!important;">
                {{$v->title}}
            </td>

            <td scope="col" style="width: 15%;text-align:left;    white-space: nowrap!important;">
                {{$v->default_description}}
            </td>

            <td scope="col" style="width: 5%;text-align:left;    white-space: nowrap!important;">
                {{$v->time_entry}}
            </td>

            <td scope="col" style="width: 15%;text-align:left;">
                {{$v->expense_counter}}
            </td>

            <td scope="col" style="width: 5%;text-align:left;    white-space: nowrap!important;">
                {{$v->total_hours}}
            </td>

            <td scope="col" style="width: 5%;text-align:left;    white-space: nowrap!important;">
                {{$v->flat_fees}}
            </td>

            <td scope="col" style="width: 5%;text-align:left;    white-space: nowrap!important;">
                {{$v->contact_name}}
            </td>

        </tr>

        <?php }?>
    </tbody>
</table>
