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

<h2>Contacts Import Report</h2>

<p>
    Imported: {{date('F d Y, H:i A',strtotime($ClientCompanyImport['created_at']))}}<br />
    Filename: {{$ClientCompanyImport['file_name']}}
    <?php 
    if($ClientCompanyImport['file_type']==1){
?> <span style='font-style: italic;'>vCard (.vcf)</span>

    <?php
    }else{
?>
    <span style='font-style: italic;'>Outlook (.csv)</span>
    <?php
    }
    ?>
</p>

<table class='import_results'>
    <tr>
        <th>Status</th>
        <th>Name</th>
        <th>Company</th>
        <th>Email</th>
        <th>Group</th>
        <th>Outstanding Trust Balance</th>
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
        <td style='white-space:nowrap;'>
            <?php echo $val['full_name'];?>
        </td>
        <td style='white-space:nowrap;'> <?php echo $val['company_name']; ?> </td>
        <td style='white-space:nowrap;'> <?php echo $val['email']; ?></td>
        <td style='white-space:nowrap;'> <?php echo $val['contact_group'];?> </td>
        <td style='white-space:nowrap;'> <?php echo $val['outstanding_amount'];?> </td>

        <td style='white-space:nowrap;'>
            <?php echo $val['warning_list'];?>
        </td>
    </tr>
    <?php
if($val['status']=="1"){
    $counter++;
} } ?>
</table>

{{-- <h4>General CSV Errors/Warnings:</h4>
<ul class="custom-header-errors">
    <li> No matching custom field for column Login Enabled. </li>
    <li> No matching custom field for column Welcome Message. </li>
    <li> No matching custom field for column zczxc. </li>
    <li> No matching custom field for column xzx. </li>
</ul> --}}
<p>
    <b>Imported: {{count($ClientCompanyImportHistory)}}/{{$counter}}</b>
</p>
