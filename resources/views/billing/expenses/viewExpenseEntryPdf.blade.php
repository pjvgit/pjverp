@extends('layouts.pdflayout')

<h4>Expenses</h4>
<table class="display table table-striped table-bordered" id="timeEntryGrid" style="width:100%;" border="1">
    <thead>
        <tr>
            <th width="10%">Date</th>
            <th width="15%">Activity</th>
            <th width="5%">Quantity</th>
            <th width="5%">Cost</th>
            <th width="15%">Description</th>
            <th width="5%">Total</th>
            <th width="5%">Status</th>
            <th width="15%">User</th>
            <th width="15%">Case</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($case as $k=>$v){ ?>
        <tr style="padding-left: 4px;">
            <td scope="col" style="width: 10%;text-align:left;    white-space: nowrap!important;">
                {{$v->date_format_new}}
            </td>

            <td scope="col" style="width: 15%;text-align:left;    white-space: nowrap!important;">
                {{$v->activity_title}}
            </td>

            <td scope="col" style="width: 5%;text-align:left;    white-space: nowrap!important;">
                {{$v->duration}}
            </td>
            <td scope="col" style="width: 5%;text-align:left;    white-space: nowrap!important;">
                {{$v->cost}}
            </td>

            <td scope="col" style="width: 15%;text-align:left;">
                {{$v->description}}
            </td>
            <td scope="col" style="width: 5%;text-align:left;    white-space: nowrap!important;">
                <?php echo "$".number_format($v->duration * $v->cost,2); ?>
            </td>
            <td scope="col" style="width: 5%;text-align:left;    white-space: nowrap!important;">
                Open
            </td>
            <td scope="col" style="width: 15%;text-align:left;    white-space: nowrap!important;">
                {{$v->user_name}}
            </td>
            <td scope="col" style="width: 15%;text-align:left;    white-space: nowrap!important;">
                {{$v->ctitle}}
            </td>
        </tr>

        <?php }?>
    </tbody>
</table>
