
<?php if($isExists<=0){?>
<table class="form">
    <tbody>
    <?php
    if(isset($clientList) && !$clientList->isEmpty()){?>
      <tr>
          <th>Company<br>Contact Link</th>
          <td style="padding-top: 12px !important;">
            Select the contacts at
            <span style="font-weight: bold;">{{$getUserInfo['sel_name']}}</span>
            that should be linked to this case:
            <br>
            <table class="no_padding">
                <tbody>
                <?php 
                foreach($clientList as $k=>$v){?>
                <tr>
                  <td style="padding: 0 !important; padding-top: 3px !important;">
                    <input type="checkbox" name="client_links[]" id="client_links_" value="{{$v->id}}" checked="checked">
                  </td>
                  <td style="padding: 0 !important; padding-left: 5px !important; padding-top: 3px !important; ">
                    {{$v->name}} (Client)
                  </td>
                </tr>
                <?php } ?>
                
            </tbody></table>
          </td>
        </tr>
        <?php }?>
        <?php if($getUserInfo['client_portal_enable']==0){
          ?>
          <tr>
            <th style="border-top: 1px solid #cccccc;" class="pr-3">Case Sharing</th>
            <td style=" border-top: 1px solid #cccccc;">
              <div id="share_message" style="font-style: italic; ">
                Sharing is disabled since this contact is not allowed to login.
              </div>
            </td>
          </tr>
        <?php
        }else{?>
        <tr>
          <th style="border-top: 1px solid #cccccc;">Case Sharing</th>
          <td style=" border-top: 1px solid #cccccc;padding-top: 12px !important;">
            <div id="share_link">

              <label id="court_case_user_link_share_label">
                <input type="checkbox" name="user_link_share" id="court_case_user_link_share">
                Share all existing case events and documents with selected contacts
              </label>
              <br>
              <label id="court_case_user_link_share_read_label" style="color: gray;">
                <input type="checkbox" name="user_link_share_read" id="court_case_user_link_share_read" disabled="">
                Automatically mark all items as read
              </label>
            </div>
          </td>
        </tr>
        <?php } ?>
  </tbody>
</table>
<?php } ?>
<?php if($isExists>0){?>
  <table class="form">
      <tbody><tr>
        <th></th>
        <td style="padding-top: 12px !important; color: red;">
          <img align="absmiddle" src="{{BASE_URL}}public/images/alert_icon.png">
          This contact is already linked to this case.
        </td>
      </tr>
  </tbody>
</table>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function () {
      
  <?php if($isExists>0){?>
      $("#submit_with_user").attr("disabled", true);
    <?php }else{ ?>
      $('#submit_with_user').removeAttr("disabled");

    <?php } ?>  
    });
 </script>