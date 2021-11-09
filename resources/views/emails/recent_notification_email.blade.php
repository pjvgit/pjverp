<html>
<head>
    <title>LegalCase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

</head>
<body style="background-color: #ffff;margin: 0px;padding: 20px;border: 0px;">
    <table style="padding:30px;margin:0 auto;max-width:600px;background-color:#ffff;box-sizing:border-box;border-collapse:collapse">
        <tr style="margin: 0; padding: 0; border: 0">
            <td align="center" style="margin:0;padding:0;border:0">

            <table cellspacing="0" border="0" style="padding:0;border:0;margin:0" width="601" cellpadding="0">

                <tbody>
                    <tr height="50" style="margin:0;padding:0;border:0">
                    
                    <td style="padding:0;border:0;margin:0">
                    &nbsp;
                    </td>

                    </tr>
            
                </tbody>
            </table>
            <table cellspacing="0" border="0" style="padding:0;border:0;margin:0;background:url(&quot;{{ asset('images/mail/table_header.jpg') }}&quot;);" bgcolor="#ffffff" background="{{ asset('images/mail/table_header.jpg') }}" width="601" cellpadding="0">

                <tbody>
                    <tr height="83" style="margin:0;padding:0;border:0">

                    <td style="padding:0;border:0;margin:0" cellpadding="0">

                    <div style="margin-top:20px;margin-left:15px;font-size:18px">

                        <img src="{{@$firm}}"  class="CToWUd">

                    </div>

                    </td>

                    <td style="padding:0;border:0;margin:0;text-align:right" cellpadding="0" align="right">

                    <div style="margin-top:20px;margin-right:15px;font-size:13px">

                        <img src="{{ asset('images/mail/email_header_notification.png') }}" width="313" height="24" ><br>

                        <span style="font-weight:bold"><b>Prepared for {{$preparedFor}}</b></span>

                    </div>

                    </td>
                </tr>

                </tbody>
                </table>



                <table cellspacing="0" border="0" style="padding:0;border:0;margin:0" width="619" cellpadding="0">

                <tbody>
                    <tr height="75" style="margin:0;padding:0;border:0">

                <td style="padding:0;border:0;margin:0;color:#ffffff;font-size:18px;background:#004475 url(&quot;{{ asset('images/mail/table_banner.jpg')}}&quot;);font-family:Helvetica,Arial,sans-serif" bgcolor="#004475" background="{{ asset('images/mail/table_banner.jpg')}}" align="left">        
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        {{date('M d, Y')}} 
                        &nbsp;
                        <span style="color:#aaaaaa"> {{date('H:ia')}}</span>
                        </td>
                </tr>
                </tbody>
                </table>

                <table cellspacing="0" border="0" style="padding:0;border:0;margin:0;background:url(&quot;{{ asset('images/mail/table_bg.jpg')}}&quot;);" bgcolor="#ffffff" background="{{ asset('images/mail/table_bg.jpg')}}" width="601" cellpadding="0">

                    <tbody><tr style="margin:0;padding:0;border:0">

                        <td style="padding:0;border:0;margin:0" cellpadding="0" align="center">

                    <table cellspacing="0" border="0" style="padding:0;border:0;margin:0" bgcolor="#ffffff" width="599" cellpadding="0">

                        <tbody>
                            <tr style="margin:0;padding:0;border:0">

                            <td style="padding:0;border:0;margin:0" cellpadding="0">
                <br>

                            
                        @foreach($cases as $k => $case)
                        <table width="100%" bgcolor="#e0e0e0" style="padding:0;border:0;margin:0;margin-bottom:5px;background-color:#e0e0e0">

                        <tbody>
                            
                        <tr>

                        <td width="40px" style="width:40px;text-align:center" align="center">

                        <img src="{{ asset('icon/briefcase_email.png') }}" class="CToWUd">

                        </td>

                        <td>

                        <p style="margin-top:15px;margin-bottom:10px;padding:0;font-size:13px;line-height:18px;font-family:Helvetica,Arial,sans-serif;color:#000000;font-size:17px;color:#666666;font-weight:bold;margin:0;padding:8px 0px">

                        {{$k}}

                        </p>

                        </td>

                        <td width="110px" align="right" style="text-align:right;width:110px;vertical-align:middle" valign="middle">

                        <div style="padding-top:3px">

                        </div>

                        </td>
                        <td width="110px" align="right" style="text-align:right;width:110px;vertical-align:middle" valign="middle">

                        <div style="padding-top:3px">

                            <img alt="New Case" src="{{ asset('icon/case_status_new.png') }}" class="CToWUd">

                        </div>

                        </td>
                        <td width="5px;" style="width:5px">&nbsp;</td>

                        </tr>
                        </tbody>
                        </table>
                        @foreach($case as $i => $v)
                            @if($v->type == 'case')
                            <?php 
                            $ImageArray=[];
                            $ImageArray['add']="activity_client_added.png";
                            $ImageArray['update']="activity_client_updated.png";
                            $ImageArray['link']="activity_client_linked.png";
                            $ImageArray['unlink']="activity_client_unlinked.png";
                            $ImageArray["pay"]="activity_ledger_deposited.png";
                            $image=$ImageArray[$v->action];
                            ?>
                            
                            <table cellspacing="0" border="0" style="padding:0;border:0;margin:0" bgcolor="#ffffff" width="100%" cellpadding="0">

                            <tbody>
                                <tr style="margin:0;padding:0;border:0">

                            <td style="padding:0;border:0;margin:0" cellpadding="0" align="center">

                                <table width="580" style="padding:0;border:0;margin:0;background-color:#ffffff;width:580px" bgcolor="#ffffff">



                                <tbody>
                                    <tr>

                                <td style="width:25px" width="25px">

                                <img src="{{ asset('images/'.$image) }}" width="27" height="21" class="CToWUd">

                                </td>

                                <td style="font-size:12px">

                                <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">
                                {{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})
                                </a> {{$v->activity}} 
                                <?php if($v->case_title!=""){?>
                                <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>                    
                                <?php } ?>

                                </td>

                                </tr>



                                </tbody>
                                </table>

                                </td>

                                </tr>

                                </tbody>
                                </table>
                            @endif
                            @if($v->type == 'contact')
                            <?php 
                                $ImageArray=[];
                                $ImageArray['add']="activity_client_added.png";
                                $ImageArray['update']="activity_client_updated.png";
                                $ImageArray['link']="activity_client_linked.png";
                                $ImageArray['unlink']="activity_client_unlinked.png";
                                $ImageArray["pay"]="activity_ledger_deposited.png";
                                $ImageArray["change"]="activity_attorney_permissions.png";
                                $ImageArray["archive"]="activity_attorney_archived.png";
                                $image=$ImageArray[$v->action];
                            ?>
                            
                            <table cellspacing="0" border="0" style="padding:0;border:0;margin:0" bgcolor="#ffffff" width="100%" cellpadding="0">

                            <tbody>
                                <tr style="margin:0;padding:0;border:0">

                            <td style="padding:0;border:0;margin:0" cellpadding="0" align="center">

                                <table width="580" style="padding:0;border:0;margin:0;background-color:#ffffff;width:580px" bgcolor="#ffffff">
                                <tbody>
                                    <tr>

                                <td style="width:25px" width="25px">

                                <img src="{{ asset('images/'.$image) }}" width="27" height="21" class="CToWUd">

                                </td>

                                <td style="font-size:12px">

                                <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">
                                {{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})
                                </a> {{$v->activity}} 
                                
                                <?php if($v->ulevel=="2"){?> <a class="name" href="{{ route('contacts/clients/view', $v->client_id) }}">{{$v->fullname}} (Client)</a>
                                <?php } ?>
                                
                                <?php if($v->ulevel=="4"){?> <a class="name" href="{{route('contacts/companies/view',$v->client_id) }}">{{$v->fullname}} (Company)</a>
                                <?php } ?>

                                <?php if($v->ulevel=="3"){?> <a class="name" href="{{route('contacts/attorneys/info',base64_encode($v->client_id)) }}">{{$v->fullname}} ({{$v->user_title}})</a>
                                <?php } ?>
                                
                                <?php if($v->action=="link"){ ?> to case <?php
                                    if($v->case_title!=""){?>
                                    <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>                    
                                    <?php } ?>
                                <?php } ?>
                                <?php if($v->action=="unlink"){ ?> from case  <?php
                                    if($v->case_title!=""){?>
                                    <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>                    
                                    <?php } ?>
                                <?php } ?> 
                                </td>
                                </tr>
                                </tbody>
                                </table>
                               </td>
                                </tr>
                                </tbody>
                                </table>
                            @endif
                            @if($v->type == 'invoices')
                            <?php 
                                $imageLink=[];
                                $imageLink["add"]="activity_bill_added.png";
                                $imageLink["update"]="activity_bill_updated.png";
                                $imageLink["delete"]="activity_bill_deleted.png";
                                $imageLink["pay"]="activity_bill_paid.png";
                                $imageLink["refund"]="activity_bill_refunded.png";
                                $imageLink["share"]="activity_bill_shared.png";
                                $imageLink["unshare"]="activity_bill_unshared.png";
                                $imageLink["email"]="activity_bill_email_shared.png";
                                $image=$imageLink[$v->action];
                            ?>
                            
                            <table cellspacing="0" border="0" style="padding:0;border:0;margin:0" bgcolor="#ffffff" width="100%" cellpadding="0">

                            <tbody>
                                <tr style="margin:0;padding:0;border:0">

                            <td style="padding:0;border:0;margin:0" cellpadding="0" align="center">

                                <table width="580" style="padding:0;border:0;margin:0;background-color:#ffffff;width:580px" bgcolor="#ffffff">
                                <tbody>
                                    <tr>

                                <td style="width:25px" width="25px">

                                <img src="{{ asset('icon/'.$image) }}" width="27" height="21" class="CToWUd">

                                </td>

                                <td style="font-size:12px">
                                @if(in_array($v->action,["add","update","delete","pay","refund"]))
                                    <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} 
                                    @if($v->action == "pay") for invoice  @endif
                                    @if ($v->deleteInvoice == NULL)
                                        @if($v->type == 'lead_invoice')
                                        <a href="{{ route('bills/invoices/potentialview',base64_encode($v->activity_for)) }}"> #{{sprintf('%06d', $v->activity_for)}} </a> 
                                        @else
                                        <a href="{{ route('bills/invoices/view',base64_encode($v->activity_for)) }}"> #{{sprintf('%06d', $v->activity_for)}} </a> 
                                        @endif
                                    @else
                                        #{{sprintf('%06d', $v->activity_for)}}
                                    @endif 
                                @elseif(in_array($v->action,["share","unshare","email"]))
                                    <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> 
                                    {{$v->activity}} 
                                    
                                    @if ($v->deleteInvoice == NULL)
                                        @if($v->type == 'lead_invoice')
                                        <a href="{{ route('bills/invoices/potentialview',base64_encode($v->activity_for)) }}"> #{{sprintf('%06d', $v->activity_for)}} </a> 
                                        @else
                                        <a href="{{ route('bills/invoices/view',base64_encode($v->activity_for)) }}"> #{{sprintf('%06d', $v->activity_for)}} </a> 
                                        @endif
                                    @else
                                        #{{sprintf('%06d', $v->activity_for)}}
                                    @endif 
                                    {{ ($v->action == "unshare") ? "from the portal with" : (($v->action == "share") ? "in the portal with" : "") }}
                                    @if($v->action == "email") to @endif
                                    <a class="name" href="{{ route('contacts/clients/view', $v->client_id) }}">{{ $v->fullname }} ({{$v->user_title}})</a>
                                @else
                                    <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                                    <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> 
                                    {{$v->activity}} for {{$v->title}} 
                                @endif
                                </td>
                                </tr>
                                </tbody>
                                </table>
                                </td>
                                </tr>
                                </tbody>
                                </table>
                            @endif

                        @endforeach
                        @endforeach

                        <br><br>


                        <table width="100%" bgcolor="#e0e0e0" style="padding:0;border:0;margin:0;margin-bottom:5px;background-color:#e0e0e0">

                        <tbody><tr>

                        <td width="40px" style="width:40px;text-align:center" align="center">

                        <img src="{{ asset('icon/firm_email.png') }}" class="CToWUd">

                        </td>

                        <td>

                        <p style="margin-top:15px;margin-bottom:10px;padding:0;font-size:13px;line-height:18px;font-family:Helvetica,Arial,sans-serif;color:#000000;font-size:17px;color:#666666;font-weight:bold;margin:0;padding:8px 0px">

                        Firm Activity

                        </p>

                        </td>

                        <td width="110px" align="right" style="text-align:right;width:110px;vertical-align:middle" valign="middle">

                        <div style="padding-top:3px">

                        </div>

                        </td>

                        <td width="5px;" style="width:5px">&nbsp;</td>

                        </tr>

                        </tbody>
                        </table>

                <table cellspacing="0" border="0" style="padding:0;border:0;margin:0" bgcolor="#ffffff" width="100%" cellpadding="0">

                <tbody>
                    @foreach($history as $k => $v)
                        @if($v->type == 'user')
                        @php
                            $imageLink=[];
                            $imageLink["login"]="activity_client_login.png";
                            $image=$imageLink[$v->action];
                        @endphp  
                        <tr style="margin:0;padding:0;border:0">          
                        <td style="padding:0;border:0;margin:0" cellpadding="0" align="center">

                            <table width="580" style="padding:0;border:0;margin:0;background-color:#ffffff;width:580px" bgcolor="#ffffff">
                                <tbody>
                                    <tr>

                                    <td style="width:25px" width="25px">

                                    <img src="{{ asset('icon/'.$image) }}" width="27" height="21" class="CToWUd">

                                    </td>

                                    <td style="font-size:12px">

                                    <a class="name" href="{{ route('contacts/clients/view', $v->client_id) }}">{{ $v->fullname }} (Client)</a>
                                    {{$v->activity}} 
                                    </td>

                                    </tr>
                                </tbody>
                            </table>

                        </td>
                        </tr>
                        @endif
                        @if($v->type == 'contact')
                        <?php 
                        $ImageArray=[];
                        $ImageArray['add']="activity_client_added.png";
                        $ImageArray['update']="activity_client_updated.png";
                        $ImageArray['link']="activity_client_linked.png";
                        $ImageArray['unlink']="activity_client_unlinked.png";
                        $ImageArray["pay"]="activity_ledger_deposited.png";
                        $ImageArray["change"]="activity_attorney_permissions.png";
                        $ImageArray["archive"]="activity_attorney_archived.png";
                        $image=$ImageArray[$v->action];
                        ?>
                        <tr style="margin:0;padding:0;border:0">          
                        <td style="padding:0;border:0;margin:0" cellpadding="0" align="center">

                            <table width="580" style="padding:0;border:0;margin:0;background-color:#ffffff;width:580px" bgcolor="#ffffff">
                                <tbody>
                                    <tr>

                                    <td style="width:25px" width="25px">

                                    <img src="{{ asset('images/'.$image) }}" width="27" height="21" class="CToWUd">
                                    </td>
                                    <td style="font-size:12px">
                                            <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">
                                            {{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})
                                            </a> {{$v->activity}} 
                                            
                                            <?php if($v->ulevel=="2"){?> <a class="name" href="{{ route('contacts/clients/view', $v->client_id) }}">{{$v->fullname}} (Client)</a>
                                            <?php } if($v->ulevel=="4"){?> <a class="name" href="{{route('contacts/companies/view',$v->client_id) }}">{{$v->fullname}} (Company)</a>
                                            <?php } if($v->ulevel=="3"){?> <a class="name" href="{{route('contacts/attorneys/info',base64_encode($v->client_id)) }}">{{$v->fullname}} ({{$v->user_title}})</a>
                                            <?php } if($v->case_title!=""){?>|       <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>                    
                                            <?php } ?>
                                    </td>

                                    </tr>
                                </tbody>
                            </table>

                        </td>
                        </tr>
                        @endif
                        
                    @endforeach
                    

                </tbody>
                </table>

                <br><br>

                <table cellspacing="0" border="0" style="padding:0;border:0;margin:0" bgcolor="#ffffff" width="100%" cellpadding="0">

                <tbody><tr style="margin:0;padding:0;border:0">

                <td style="padding:0;border:0;margin:0;text-align:left;width:8px" cellpadding="0" align="left" width="8px">

                &nbsp;

                </td>

                <td style="width:32px;text-align:center" width="32px" align="center">

                <img src="https://assets.mycase.com/packs/notifications/gear-ca1074872e.png" width="16" height="16" class="CToWUd">

                </td>

                <td>

                <p style="margin-top:15px;margin-bottom:10px;padding:0;font-size:13px;line-height:18px;font-family:Helvetica,Arial,sans-serif;color:#000000;margin:0px;padding:0px;font-size:12px">

                Change the content and/or frequency of these emails -

                <a href="{{route('account/notifications')}}" style="color:#0069be;text-decoration:none" target="_blank" data-saferedirecturl="{{route('account/notifications')}}">Edit your email settings</a>

                </p>

                </td>

                </tr>

                </tbody>
                </table>
            </td>

            </tr>

          </tbody>
        </table>

                </td>

        </tr>

        </tbody>
        </table>

        
                <table cellspacing="0" border="0" style="padding: 0; border: 0; margin: 0; background-image: url(&quot;{{ asset('images/mail/table_footer.jpg') }}&quot;); background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; background-color: initial"
                    bgcolor="#ffffff" width="601" cellpadding="0">

                    <tbody>
                        <tr height="17" style="margin: 0; padding: 0; border: 0">

                            <td style="padding: 0; border: 0; margin: 0" cellpadding="0">

                                &nbsp;

                            </td>

                        </tr>

                    </tbody>
                </table>
                <table cellspacing="0" border="0" style="padding: 0; border: 0; margin: 0" width="601" cellpadding="0">
                    <tbody>
                        <tr style="margin: 0; padding: 0; border: 0">
                            <td style="padding: 0; border: 0; margin: 0" align="center">
                                <p style="text-align: center; margin: 0; padding: 10px 0; font-size: 12px; line-height: 22px; color: rgba(102, 102, 102, 1); border: 0; font-family: Helvetica, Arial, sans-serif">
                                    This email is sent to <a href="mailto:{{$preparedEmail}}">{{ $preparedEmail }}</a>. To ensure that you continue receiving our emails, please add us to your address book or safe list.
                                </p>
                                <p style="text-align: center; margin: 0; padding: 10px 0; font-size: 12px; line-height: 22px; color: rgba(136, 136, 136, 1); border: 0; font-family: Helvetica, Arial, sans-serif; font-style: normal">
                                    © {{ date('Y') }} {{ config('app.name') }}, Inc.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>