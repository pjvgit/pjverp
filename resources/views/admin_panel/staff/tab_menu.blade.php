<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?php if(Route::currentRouteName()=="admin/stafflist/info"){ echo "active show"; } ?>" id="profile-basic-tab" 
            href="{{URL::to('admin/stafflist/info/'.$userProfile->decode_id)}}" aria-controls="profileBasic" aria-selected="true">Info</a>
    </li>
    <li class="nav-item">
        <a class="nav-link  <?php if(Route::currentRouteName()=="admin/stafflist/cases"){ echo "active show"; } ?>" id="contact-basic-tab"
        href="{{URL::to('admin/stafflist/info/'.$userProfile->decode_id.'/cases')}}" role="tab" aria-controls="contactBasic" aria-selected="false">Cases</a>
    </li>
</ul>