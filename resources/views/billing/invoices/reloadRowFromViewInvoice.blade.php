
 <?php
 $CommonController= new App\Http\Controllers\CommonController();
 ?>
 <td scope="row" class="text-center">
    <input type="checkbox" name="invoice_shared[{{$getAllClientForSharing['user_id']}}]" value="{{$getAllClientForSharing['user_id']}}"
    id="portalAccess_{{$getAllClientForSharing['user_id']}}" class="invoiceSharingBox invoice-sharing-box"
    uid="{{$getAllClientForSharing['user_id']}}" em="{{$getAllClientForSharing['email']}}" pe="{{$getAllClientForSharing['client_portal_enable']}}"
    onclick="checkPortalAccess({{$getAllClientForSharing['user_id']}})" checked="checked">
</td>
<td class="client-name"> {{ucfirst($getAllClientForSharing['unm'])}}
    <?php 
    if($getAllClientForSharing['user_level']=="2"){
        echo "(Client)";
    }else{
        echo "(Company)";
    }?></td>
<td class="last-login-date">
    <?php if($getAllClientForSharing['email']==""){
        echo "Disabled";
    }else{
        
        if($getAllClientForSharing['last_login']!=NULL){
            $loginDate=$CommonController->convertUTCToUserTime($getAllClientForSharing['last_login'],Auth::User()->user_timezone);
            echo date('F jS Y, h:i:s A',strtotime($loginDate));
        }else{
            echo "Never";
        }
    }
?></td>
<td class="shared-on-date">
    <?php if($getAllClientForSharing['sharedDate']!=NULL){
        $sharedDate=$CommonController->convertUTCToUserTime($getAllClientForSharing['sharedDate'],Auth::User()->user_timezone);
        echo date('F jS Y, h:i a',strtotime($sharedDate));
    }else{
        echo "Not Shared";
    }
    ?>
</td>
<td class="viewed-on-date">
    <?php if($getAllClientForSharing['isViewd']=="yes"){
    echo "Yes";
}else{
    echo "Never";
}
?></td>