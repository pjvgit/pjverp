    <td style="text-align: center;padding:10px;">
        <div class="locked">
            <input type="checkbox" name="portalAccess" checked value="21672788" id="portalAccess_{{$UsersAdditionalInfo['user_id']}}"
                class="invoiceSharingBox invoice-sharing-box" uid="{{$UsersAdditionalInfo['user_id']}}"
                onclick="checkPortalAccess({{$UsersAdditionalInfo['user_id']}})" em="{{$UsersAdditionalInfo['email']}}" pe="{{$UsersAdditionalInfo['client_portal_enable']}}">
        </div>
    </td>
    <td class="invoice-sharing-name">
        <div class="locked pl-1">
            {{ucfirst($UsersAdditionalInfo['unm'])}}
            <?php 
            if($UsersAdditionalInfo['user_level']=="2"){
                echo "(Client)";
            }else{
                echo "(Company)";
            }?>
        </div>
    </td>
    <td class="invoice-sharing-last-login">
        <div class="locked last-login-at pl-1">
            <?php if($UsersAdditionalInfo['email']==""){
                echo "Disabled";
            }else{
                $CommonController= new App\Http\Controllers\CommonController();
                if($UsersAdditionalInfo['last_login']!=NULL){
                    $loginDate=$CommonController->convertUTCToUserTime($UsersAdditionalInfo['last_login'],Auth::User()->user_timezone);
                    echo date('F jS Y, h:i:s A',strtotime($loginDate));
                }else{
                    echo "Never";
                }
            }
            ?></div>
    </td>
