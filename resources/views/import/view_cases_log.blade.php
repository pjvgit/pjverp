<style type='text/css'>
    body,
    td,
    th {
        font-family: arial, helvetica, sans-serif;
        font-size: 13px;
        line-height: 17px;
    }

    table {
        border-collapse: collapse;
        border: 1px solid #dbdbdb;
    }

    td,
    th {
        padding: 8px 4px;
        border-bottom: 1px solid #dbdbdb;
    }

    td {
        white-space: nowrap;
        vertical-align: top;
    }

    th {
        text-align: left;
        background-color: #f6f6f6;
        border-left: 1px solid #dbdbdb;
        border-right: 1px solid #dbdbdb;
    }

    ul {
        margin: 0px;
        padding: 0px;
        padding-left: 15px;
    }

    .import_results tbody tr:nth-child(odd) td {
        background-color: #ffffff;
    }

    .import_results tbody tr:nth-child(even) td {
        background-color: #f6fcff;
    }

</style>

<h2>Cases Import Report</h2>

<p>
    Imported: {{date('F d Y, H:i A',strtotime($ClientCompanyImport['created_at']))}}<br />
    Filename: {{$ClientCompanyImport['file_name']}}
    <span style='font-style: italic;'> {{config('app.name')}} CSV</span>
    
</p>

<table class='import_results'>
    <tr>
        <th>Status</th>
        <th>case_title</th>
        <th>case_number</th>
        <th>case_open_date</th>
        <th>practice_area</th>
        <th>case_description</th>
        <th>case_close</th>
        <th>case_close_date</th>
        <th>lead_attorney</th>
        <th>originating_attorney</th>
        <th>sol_date</th>
        <th>outstanding_balance</th>
        <th>case_stage</th>
        <th>conflict_check</th>
        <th>conflict_check_notes</th>
        <th>Errors / Warnings</th>
    </tr>
    <?php 
    $counter=0;
    // print_r($ClientCompanyImportHistory);
    foreach($ClientCompanyImportHistory as $key=>$val){?>
    <tr>
        <td style='color: green; white-space: nowrap;'>
            Success
            <br />
            <?php if($val['warning_list']!=NULL && $val['warning_list'] != '<ul></ul>'){?>
            <span style='color:orange'>(With warnings)</span>
            <?php } ?>
        </td>
        <td style='white-space:nowrap;'><?php echo $val['case_title'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['case_number'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['case_open_date'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['practice_area'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['case_description'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['case_close'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['case_close_date'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['lead_attorney'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['originating_attorney'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['sol_date'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['outstanding_balance'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['case_stage'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['conflict_check'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['conflict_check_notes'];?></td>
        <td style='white-space:nowrap;'><?php echo $val['warning_list'];?></td>
    </tr>
    <?php
if($val['status']=="1"){
    $counter++;
} } ?>
</table>
<p>
    <b>Imported: {{count($ClientCompanyImportHistory)}}/{{$counter}}</b>
</p>
