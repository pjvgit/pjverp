<?php  if(!$loadFirmUser->isEmpty()){?>
<li class="ui-widget-header ui-corner-all  ui-menu-item ui-autocomplete-category"><span>Contacts</span></li>
<?php 
foreach($loadFirmUser as $k=>$staffval){?>
<li class="ui-autocomplete-category-item autocomplete_Company ui-menu-item-click " role="menuitem" type="staff"
    id="{{$staffval->id}}">
    <a class="ui-corner-all" tabindex="-1">
        <span class="autocomplete_Company"> {{substr($staffval->first_name,0,100)}}
            {{substr($staffval->last_name,0,100)}}</span>
    </a>
</li>
<?php } } ?>
<?php 
if(!$CaseMasterCompany->isEmpty()){?>
<li class="ui-widget-header ui-corner-all ui-menu-item ui-autocomplete-category"> <span>Companies</span></li>
<?php 
foreach($CaseMasterCompany as $k=>$v){?>
<li class="ui-autocomplete-category-item autocomplete_Company ui-menu-item-click" role="menuitem" type="company"
    id="{{$v->id}}">
    <a class="ui-corner-all" tabindex="-1">
        <span class="autocomplete_Company"> {{substr($v->first_name,0,100)}}</span>
    </a>
</li>
<?php } }
?>

<?php 
if(!$CaseMasterData->isEmpty()){?>
<li class="ui-widget-header ui-corner-all ui-menu-item ui-autocomplete-category"> <span>Cases</span></li>
<?php 
foreach($CaseMasterData as $k=>$Caseval){?>
<li class="ui-autocomplete-category-item autocomplete_Company ui-menu-item-click" role="menuitem" type="case"
    id="{{$Caseval->id}}">
    <a class="ui-corner-all" tabindex="-1">
        <span class="autocomplete_Company"> {{substr($Caseval->case_title,0,100)}}</span>
    </a>
</li>
<?php } } ?>
