@extends('layouts.pdflayout')

<body style="padding:25px;">
    <h4>Online Leads</h4>
    <table style="width:100%" border="1">
        <thead>
            <tr>
                <th width="60%" style="text-align: left;">Name</th>
                <th width="20%" style="text-align: left;">Date Added</th>
                <th width="20%" style="text-align: left;">Submitted Form</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach($OnlineLeadSubmit as $k=>$v){?>
            <tr>
                <td style="width: 60%;"><b>
                        {{ucfirst(substr($v->first_name,0,50))}}
                        {{ucfirst(substr($v->middle_name,0,50))}}
                        {{ucfirst(substr($v->last_name,0,50))}}</b>
                    <?php 
                        if($v->email!=NULL){?>
                    <div class="row ml-1"><span style="opacity: 0.6; width: 20px;"><i aria-hidden="true"
                                class="fa fa-envelope col- mt-1 pl-0"></i></span><a class="col-10 p-0"
                            href="mailto:{{$v->email}}">{{$v->email}}</a></div>
                    <?php } ?>
                </td>
                <td>
                    {{$v->added_date}}
                </td>
                <td>
                    Contact Us
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</body>

</html>
