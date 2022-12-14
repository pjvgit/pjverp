@extends('layouts.pdflayout')

<h4>Account Activity</h4>
<table style="width: 100%;">
    <tr>
        <td style="width: 50%;float:left;"><b>Bank Account:</b> <?php if($requestData['account']==''){ echo "All"; } else { echo "Trust Account(trust)"; }?> </td>
        <td style="width: 50%;float: right;text-align:right;"><b>Date Range:</b> {{$requestData['range']}} </td>
    </tr>
</table>
<table class="display table table-striped table-bordered" id="timeEntryGrid" style="width:100%;" border="1">
    <thead>
        <tr>
            <th width="10%">Date</th>
            <th width="15%">Related To</th>
            <th width="15%">Contact</th>
            <th width="20%">Case</th>
            <th width="16%">Entered By</th>
            <th width="7%">Credit</th>
            <th width="7%">Debit</th>
            <th width="7%">Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($FetchQuery as $k=>$v){ ?>
        <tr style="padding-left: 4px;">
            <td scope="col" style="width: 10%;text-align:left;    white-space: nowrap!important;">
                {{$v->added_date}}
            </td>

            <td scope="col" style="width: 15%;text-align:left;    white-space: nowrap!important;">
                <?php   if($v->section=="invoice"){
                    echo "#".$v->related;
                }else if($v->section=="request"){
                    echo "#R-".$v->related;
                }else{
                    ?><i class="table-cell-placeholder"></i><?php
                }?>
            </td>

            <td scope="col" style="width: 5%;text-align:left;    white-space: nowrap!important;">
                <?php
                    $cData=json_decode($v->contact);
                    if(!empty($cData)){
                        echo $cData->name;
                    }else{
                        ?><i class="table-cell-placeholder"></i><?php
                    }
                    ?>
            </td>

            <td scope="col" style="width: 15%;text-align:left;">
                <?php
                    $caseData=json_decode($v->case);
                    if(!empty($caseData)){
                        echo $caseData->case_title;
                    }else
                    {
                        ?><i class="table-cell-placeholder"></i><?php
                    }
                    ?>
            </td>
            <td scope="col" style="width: 15%;text-align:left;    white-space: nowrap!important;">
                {{$v->entered_by}}

            </td>

            <td scope="col" style="width: 10%;text-align:left;    white-space: nowrap!important;">
                <?php
                if($v->c_amt=="0.00"){
                    ?><i class="table-cell-placeholder"></i><?php
                }else{?>
                ${{number_format($v->c_amt,2)}}
                <?php } ?>

            </td>

            <td scope="col" style="width: 10%;text-align:left;">
                <?php
                if($v->d_amt=="0.00"){
                    ?><i class="table-cell-placeholder"></i><?php
                }else{?>
                ${{number_format($v->d_amt,2)}}
                <?php } ?>

            </td>
            <td scope="col" style="width: 15%;text-align:left;">
                ${{number_format($v->t_amt,2)}}
            </td>
        </tr>
        <?php }?>
    </tbody>
</table>
<style>


</style>
