<div class="row ">
    <div class="col-12 col-md-12 col-lg-8">
        <div class="d-flex align-items-center mb-2 mt-3">
            <h3 class="mb-0">Contacts</h3>
            <div class="ml-auto d-flex align-items-center d-print-none">
                <a data-toggle="modal" data-target="#typeSelect" data-placement="bottom" href="javascript:;"> <button
                        class="btn btn-primary btn-rounded m-1" type="button" onclick="typeSelection();">Add
                        Contact</button></a>

            </div>
        </div>
        
        <fieldset id="mc-table-fieldset">
            <div class="d-flex justify-content-end">
            </div>
            <div data-testid="mc-table-container" style="font-size: small;">
                <table style="table-layout: auto;" class="p-0 table table-md table-striped table-hover">
                    <colgroup>
                        <col style="width: 35px;">
                        <col style="width: 30%;">
                        <col style="width: 15%;">
                        <col style="width: 15%;">
                        <col style="width: 15%;">
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                        <col style="width: 10%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="user-avatar align-middle c-pointer YXd6tPOgoO-RylXVRzzZh"
                                style="cursor: initial;"></th>
                            <th class="user-name align-middle c-pointer YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">
                                Name</th>
                                <th class="user-name align-middle c-pointer YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">
                                    Group</th>
                                    <th class="user-name align-middle c-pointer YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">
                                        Role</th>
                            <th class="user-type align-middle pl-0 c-pointer YXd6tPOgoO-RylXVRzzZh"
                                style="cursor: initial;">Type</th>
                            <th class="user-phone align-middle YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">Phone
                            </th>
                            <th class="text-left user-email align-middle YXd6tPOgoO-RylXVRzzZh"
                                style="cursor: initial;">Email</th>
                            <th class="text-right user-edit d-print-none align-middle YXd6tPOgoO-RylXVRzzZh"
                                style="cursor: initial;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!$caseCllientSelection->isEmpty()){
                        // print_r($CompanyList);
                        foreach($caseCllientSelection as $clistKey=>$clistVal){?>
                        <tr class="case-contact-row">
                            <td class="user-avatar align-middle c-pointer">
                               
                                <?php if($clistVal->user_level==2){?>
                                    <?php if(file_exists(public_path().'/images/users/'.$clistVal->profile_image) && $clistVal->profile_image!="" && $clistVal->is_published=="yes"){
                                        ?><img class="rounded-circle" alt="" src="{{BASE_URL}}public/profile/{{$clistVal->profile_image}}" width="32" height="25"><?php
                                    }else{
                                        ?><i class="fas fa-2x fa-user-circle text-black-50"></i><?php
                                    }?>
                                <?php }else{ ?>
                                <i class="fas fa-building fa-2x text-black-50"></i>
                                <?php  }?>
                                
                            </td>
                            <td class="user-name align-middle c-pointer">
                            <?php if($clistVal->user_level==2){?>
                                {{-- <a href="{{BASE_URL}}contacts/clients/{{$clistVal->id}}"> --}}
                                <a href="{{ route('contacts/clients/view', $clistVal->id) }}">
                            <?php } else{ ?>
                            
                                <a href="{{ route('contacts/companies/view', $clistVal->id) }}">
                            <?php }?>
                                    <div>
                                        <div>{{$clistVal->first_name}} {{$clistVal->middle_name}}  {{$clistVal->last_name}}</div>
                                        @if($clistVal->is_billing_contact == "yes")
                                            <div><strong>Billing Contact</strong></div>
                                        @endif
                                    </div>
                                    <input type="hidden" name_id="multiple_compnay_id" value="{{$clistVal->multiple_compnay_id}}"/>
                                    <?php $allCompany=explode(",",$clistVal->multiple_compnay_id);
                                    foreach($allCompany as $kk=>$vv){
                                        ?><div class="font-italic text-secondary2">{{@$CompanyList[$vv]}}</div><?php
                                    }
                                    ?>
                                </a>
                            </td>
                            <td class="user-name align-middle c-pointer">
                                <div>
                                    <div>{{$clistVal->group_name_is}}</div>
                                </div>
                            </td>
                            <td class="user-name align-middle c-pointer">
                                <div class="d-flex align-items-center" style="white-space: nowrap;">
                                    @if($clistVal->role_name=="")
                                    <div class="d-flex align-items-center">
                                        <i class="table-cell-placeholder" data-testid="default-placeholder"></i>
                                        <a data-toggle="modal" data-target="#changeRole" data-placement="bottom" href="javascript:;" onclick="changeRole('{{$clistVal->uid}}','{{$clistVal->case_id}}');">
                                            <i class="fas fa-pen fa-sm text-black-50 c-pointer pl-1"></i>
                                        </a>
                                    </div>
                                    
                                    
                                    @else
                                    <div>{{$clistVal->role_name}} 
                                        <a data-toggle="modal" data-target="#changeRole" data-placement="bottom" href="javascript:;" onclick="changeRole('{{$clistVal->uid}}','{{$clistVal->case_id}}');"> 
                                            <i class="fas fa-pen fa-sm text-black-50 c-pointer pl-1"></i>

                                        </a>
                                    </div>
                                    @endif   
                                </div>
                            </td>
                            <td class="user-type align-middle pl-0 c-pointer">
                                <?php if($clistVal->user_level==2){?>
                                <div>Client</div>
                                <?php }else{ ?>
                                <div>Compnay</div>
                                <?php  }?>
                            </td>
                            <td class="user-phone align-middle">
                                <?php if($clistVal->mobile_number){ ?>
                                {{$clistVal->mobile_number}}
                                <?php }else{ ?>
                                <i class="table-cell-placeholder"></i>
                                <?php }?>
                            </td>
                            <td class="user-phone align-middle">
                                <?php  if($clistVal->email){ ?>
                                <a href="mailto:{{$clistVal->email}}">{{$clistVal->email}}</a>
                                <?php }else{ ?>
                                <i class="table-cell-placeholder"></i>
                                <?php }?>
                            </td>

                            <td class="text-right user-edit d-print-none align-middle"><span
                                    class="px-0 text-black-50 c-pointer"><a href="javascript:;" onclick="onClickDelete({{$clistVal->case_client_selection_id}},'{{$clistVal->first_name}} {{$clistVal->last_name}}');" ><i class="fas fa-times-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"  ></a></span></td>
                        </tr>
                        <?php } 
                      }else{
                          ?>
                            <tr class="case-contact-row">
                                <td class="user-avatar align-middle c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-name align-middle c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-group align-middle pl-0 c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-phone align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="text-left user-email align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="text-right user-edit d-print-none align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                            </tr>
                            <tr class="case-contact-row">
                                <td class="user-avatar align-middle c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-name align-middle c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-group align-middle pl-0 c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-phone align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="text-left user-email align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="text-right user-edit d-print-none align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                            </tr>
                            <tr class="case-contact-row">
                                <td class="user-avatar align-middle c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-name align-middle c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-group align-middle pl-0 c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-phone align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="text-left user-email align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="text-right user-edit d-print-none align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                            </tr>
                            <tr class="case-contact-row">
                                <td class="user-avatar align-middle c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-name align-middle c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-group align-middle pl-0 c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-phone align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="text-left user-email align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="text-right user-edit d-print-none align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                            </tr>
                            <tr class="case-contact-row">
                                <td class="user-avatar align-middle c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-name align-middle c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-group align-middle pl-0 c-pointer">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="user-phone align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="text-left user-email align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                                <td class="text-right user-edit d-print-none align-middle">
                                    <div><i class="table-cell-placeholder"></i></div>
                                </td>
                            </tr>
                          <?php }  ?>
                    </tbody>
                </table>
            </div>
        </fieldset>
    </div>
    <div class="pl-0 col-12 col-md-12 col-lg-4">
        <div class="p-3 card">
            <div class="d-flex align-items-center mb-2">
                <h3 class="mb-0">Staff</h3>
                <div class="ml-auto d-print-none">
                    <a data-toggle="modal" data-target="#staffSelect" data-placement="bottom" href="javascript:;"> <button
                        class="btn btn-primary btn-rounded m-1" type="button" >Add Staff</button></a>
                </div>
            </div>
            <fieldset id="mc-table-fieldset">
                
                <div data-testid="mc-table-container" style="font-size: small;">
                    <table style="table-layout: auto;" class="p-0 table table-md table-striped table-hover">
                        <colgroup>
                            <col style="width: 35px;">
                            <col style="width: 50%;">
                            <col style="width: 40%;">
                            <col style="width: 10%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="user-avatar align-middle c-pointer YXd6tPOgoO-RylXVRzzZh"
                                    style="cursor: initial;"></th>
                                <th class="user-name align-middle c-pointer YXd6tPOgoO-RylXVRzzZh"
                                    style="cursor: initial;">Name</th>
                                <th class="user-rate align-middle px-0 YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">
                                    Rate</th>
                                <th class="text-right user-edit d-print-none align-middle YXd6tPOgoO-RylXVRzzZh"
                                    style="cursor: initial;"></th>
                            </tr>
                        </thead>
                        <?php 
                        if(!$staffList->isEmpty()){?>
                        <tbody>
                            <?php 
                            foreach($staffList as $k=>$v){
                               
                                $pName=$v->first_name." " .$v->last_name;
                            ?>
                            <tr class="case-staff-row">
                                <td class="user-avatar align-middle c-pointer"> 
                                    <i class="fas fa-2x fa-user-circle text-black-50"></i>
                                <td class="user-name align-middle c-pointer">
                                    <div>
                                        <div class="staff_name lead-attorney-name">{{$v->first_name}} {{$v->last_name}}</div>
                                        <div><small>{{$v->user_title}}</small></div>
                                        <?php if($v->lead_attorney!=NULL && $v->id==$v->lead_attorney){?>
                                        <div data-testid="lead-lawyer-user"><small><strong>Lead Attorney</strong></small></div>
                                        <?php } ?>
                                        <?php if($v->originating_attorney!=NULL  && $v->id==$v->originating_attorney ){?>
                                            <div data-testid="lead-lawyer-user"><small><strong>Originating Attorney</strong></small></div>
                                            <?php } ?>
                                    </div>
                                </td>
                                <td class="user-rate align-middle px-0">
                                    <div>
                                    <a data-toggle="modal" data-target="#changeCaseRate" onclick="changeCaseRate('{{$v->id}}');" data-placement="bottom" href="javascript:;">
                                    <button type="button" class="p-0 text-left edit-user-case-rate btn btn-link">
                                    <span class="billing-rate-19798316 user-case-rate">
                                                <?php 
                                                $rate=$v->staff_rate_amount;
                                                $rateType = "Case";
                                                if($v->rate_type==0){
                                                    $rate=$v->user_default_rate;
                                                    $rateType = "Default";
                                                }
                                                // else{
                                                //     $rate=$v->staff_rate_amount;
                                                // }
                                                ?>${{($rate)??0}}/hr ({{ $rateType }})
                                                </span></button></a></div>
                                </td>
                                <td class="text-right user-edit d-print-none align-middle">
                                    <a href="javascript:;" onclick="onClickUnlinkStaff({{$v->case_staff_id}},'{{$pName}}');"><i class="fas fa-times-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"></i></a>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                        <?php } else{ ?>
                            <tbody>
                                <tr class="case-staff-row">
                                    <td class="user-avatar align-middle c-pointer">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="user-name align-middle c-pointer">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="user-rate align-middle px-0">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="text-right user-edit d-print-none align-middle">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                </tr>
                                <tr class="case-staff-row">
                                    <td class="user-avatar align-middle c-pointer">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="user-name align-middle c-pointer">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="user-rate align-middle px-0">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="text-right user-edit d-print-none align-middle">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                </tr>
                                <tr class="case-staff-row">
                                    <td class="user-avatar align-middle c-pointer">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="user-name align-middle c-pointer">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="user-rate align-middle px-0">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="text-right user-edit d-print-none align-middle">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                </tr>
                                <tr class="case-staff-row">
                                    <td class="user-avatar align-middle c-pointer">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="user-name align-middle c-pointer">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="user-rate align-middle px-0">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="text-right user-edit d-print-none align-middle">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                </tr>
                                <tr class="case-staff-row">
                                    <td class="user-avatar align-middle c-pointer">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="user-name align-middle c-pointer">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="user-rate align-middle px-0">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                    <td class="text-right user-edit d-print-none align-middle">
                                        <div><i class="table-cell-placeholder"></i></div>
                                    </td>
                                </tr>
                                </tbody>
                        <?php }?>
                    </table>
                </div>
            </fieldset>
            <div class="ml-auto">
                <a data-toggle="modal" data-target="#loadLeadAttorney" data-placement="bottom" href="javascript:;"> <button
                    class="btn btn-link" type="button" onclick="loadLeadAttorney();">
                    <i class="fas fa-pen fa-sm text-black-50 c-pointer pl-1"></i> Lead Attorney</button>
                </a>
                <a data-toggle="modal" data-target="#loadOriginatingAttorney" data-placement="bottom" href="javascript:;"> <button
                    class="btn btn-link" type="button" onclick="loadOriginatingAttorney();">
                    <i class="fas fa-pen fa-sm text-black-50 c-pointer pl-1"></i> Originating Attorney</button>
                </a>
              
            </div>
        </div>
    </div>
</div>


<div id="changeRole" class="modal fade bd-example-modal-lg show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Update Role</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="changeRoleArea">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="typeSelect" class="modal fade bd-example-modal-lg show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="ModelData">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addContact" class="modal fade bd-example-modal-lg show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="AddContact">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addCompany" class="modal fade bd-example-modal-lg show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Company</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="AddCompany">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="addExisting" class="modal fade bd-example-modal-lg show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Existing Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="AddExisting">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadLeadAttorney" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Lead Attorney</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="leadAttorney">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadOriginatingAttorney" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Originating Attorney</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="leadOriginating">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="staffSelect" class="modal fade bd-example-modal-lm show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Staff</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="staffSelection">
                        <h5 class="text-center my-4"> Would you like to add a new or existing staff? </h5>
                        <div class="row">
                                    
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <a data-toggle="modal" data-target="#addNewStaffMember" data-placement="bottom"  onclick="addStaff();" href="javascript:;" >
                        
                                    <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                                    <div class="card-body text-center"><i class="i-Add-User"></i>
                                        <div class="content">
                                            <p class="text-muted mt-2 mb-0">New Staff</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <a data-toggle="modal" data-target="#addExistingStaff" onclick="addExistingStaff();" data-placement="bottom" href="javascript:;" >
                        
                                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                                    <div class="card-body text-center"><i class="i-Find-User"></i>
                                        <div class="content">
                                            <p class="text-muted mt-2 mb-0">Existing Staff</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            </div>
                            
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addNewStaffMember" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add New User</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!--  SmartWizard html -->
                        <div id="smartwizard">
                            <ul>
                                <li class="text-center"><a href="#step-1">1<br /><small>Add New User</small></a></li>
                                <li class="text-center"><a href="#step-2">2<br /><small>Link to Cases </small></a></li>
                                <li class="text-center"><a href="#step-3">3<br /><small>Firm Level
                                            Permissions</small></a></li>
                                <li class="text-center"><a href="#step-4">4<br /><small>Access Permissions</small></a>
                                </li>
                            </ul>
                            <div>
                                <div id="step-1">
                                    
                                </div>
                                <div id="step-2">
                                
                                </div>
                                <div id="step-3">
                                

                                </div>
                                <div id="step-4">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
            {{-- <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary ml-2" id="next-btn" type="button">Create User</button>
            </div> --}}
        </div>
    </div>
</div>
<div id="addExistingStaff" class="modal fade bd-example-modal-lg show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Existing Staff</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="AddExistingStaff">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="changeCaseRate" class="modal fade bd-example-modal-lg show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Change Rate</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="changeCaseRateArea">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style> .modal { overflow: auto !important; }.text-secondary2 {
    color: #27bfad!important;
}</style>

@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        // / Toolbar extra buttons
        var btnFinish = $('<button></button>').text('Finish')
            .addClass('btn btn-info')
            .on('click', function () { alert('Finish Clicked'); });
        var btnCancel = $('<button></button>').text('Cancel')
            .addClass('btn btn-danger')
            .on('click', function () { $('#smartwizard').smartWizard("reset"); });
            

        // Smart Wizard
        $('#smartwizard').smartWizard({
            selected: 0,
            theme: 'default',
            transitionEffect: 'fade',
            showStepURLhash: false,
            enableURLhash: false,
            backButtonSupport: false, // Enable the back button support
            keyNavigation: false,
            toolbarSettings: {
                toolbarPosition: 'none',
                toolbarButtonPosition: 'end',
                toolbarExtraButtons: [btnFinish, btnCancel]
            },
            anchorSettings: {
                anchorClickable: false, // Enable/Disable anchor navigation
                enableAllAnchors: false, // Activates all anchors clickable all times
                markDoneStep: true, // Add done state on navigation
                markAllPreviousStepsAsDone: true, // When a step selected by url hash, all previous steps are marked done
                removeDoneStepOnNavigateBack: false, // While navigate back done step after active step will be cleared
                enableAnchorOnDoneStep: false // Enable/Disable the done steps navigation
            },
            
        });

    });
   
    function typeSelection() {  
        $("#preloader").show();
      
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadTypeSelection", // json datasource
                data: 'loadStep1',
                success: function (res) {
                     $("#ModelData").html('');
                    $("#ModelData").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function addContact() {  
        $("#preloader").show();
        
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/loadAddContact", // json datasource
                data: {'case_id':{{$CaseMaster->case_id}}
                },
                success: function (res) {
                    $("#AddContact").html('');
                    $('#typeSelect').modal('hide')
                    $("#AddContact").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function addCompany() {  
        $("#preloader").show();
       
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/loadAddCompany", // json datasource
                data: {'case_id':{{$CaseMaster->case_id}}
                },
                success: function (res) {
                    $("#AddCompany").html('');
                    $('#typeSelect').modal('hide')
                    $("#AddCompany").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } 
    
    function changeRole(user_id,case_id) {
        $('.showError').html('');
        $("#changeRoleArea").html('');
        $("#changeRoleArea").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/changeRolePopup",
            data: {
                "user_id": user_id,
                "case_id": case_id
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#changeRoleArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#changeRoleArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
              
            }
        })
    }

    function addExisting() {  
        $("#preloader").show();
     
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadExisting", // json datasource
                data: {'case_id':{{$CaseMaster->case_id}}
                },
                success: function (res) {
                    $("#AddExisting").html('');
                    $('#typeSelect').modal('hide')
                    $("#AddExisting").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } 

    function loadLeadAttorney() {  
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadLeadAttorney", // json datasource
                data: {'case_id':{{$CaseMaster->case_id}}},
                success: function (res) {
                    $("#leadAttorney").html('');
                    $("#leadAttorney").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function loadOriginatingAttorney() {  
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadOriginatingAttorney", // json datasource
                data: {'case_id':{{$CaseMaster->case_id}}},
                success: function (res) {
                    $("#leadOriginating").html('');
                    $("#leadOriginating").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function onClickUnlinkStaff(id,username){
        swal({
            title: 'Are you sure?',
            text: "Are you sure you want to unlink "+username+" from this court case?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0CC27E',
            cancelButtonColor: '#FF586B',
            confirmButtonText: 'Yes, Unlink',
            cancelButtonText: 'No, cancel!',
            confirmButtonClass: 'btn btn-success ml-5',
            cancelButtonClass: 'btn btn-danger',
            reverseButtons: true,
            buttonsStyling: false
            }).then(function () {
                $(function () {
                    $.ajax({
                        type: "POST",
                        url:  baseUrl +"/court_cases/UnlinkAttorney", 
                        data: {"id":id,"username":username},
                        success: function (res) {
                            $("#groupModel").html(res);
                            $("#preloader").hide();
                            window.location.reload();
                        }
                    });
                });

            }, function (dismiss) {
            
        });
    }
  
    function onClickDelete(id,username){
        swal({
            title: 'Are you sure?',
            text: "Are you sure you want to unlink "+username+" from this court case?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0CC27E',
            cancelButtonColor: '#FF586B',
            confirmButtonText: 'Yes, Unlink',
            cancelButtonText: 'No, cancel!',
            confirmButtonClass: 'btn btn-success ml-5',
            cancelButtonClass: 'btn btn-danger',
            buttonsStyling: false,
            reverseButtons: true
            }).then(function () {
                $(function () {
                    $.ajax({
                        type: "POST",
                        url:  baseUrl +"/court_cases/unlinkSelection", 
                        data: {"id":id,"username":username},
                        success: function (res) {
                            $("#groupModel").html(res);
                            $("#preloader").hide();
                            window.location.reload();
                        }
                    });
                });

            }, function (dismiss) {
            
        });
    }


    function addStaff() {  
        $("#preloader").show();
        
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/loadStep1", // json datasource
                data: {'case_id':{{$CaseMaster->case_id}}
                },
                success: function (res) {
                    $("#step-1").html('');
                    $('#staffSelect').modal('hide')
                    $("#step-1").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function addExistingStaff() {  
        $("#preloader").show();
     
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadExistingStaff", // json datasource
                data: {'case_id':{{$CaseMaster->case_id}}
                },
                success: function (res) {
                    $("#AddExistingStaff").html('');
                    $('#staffSelect').modal('hide')
                    $("#AddExistingStaff").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } 

    function changeCaseRate(staff_id) {  
        $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadRate", // json datasource
                data: {
                    'case_id':{{$CaseMaster->case_id}},
                    'staff_id':staff_id
                },
                success: function (res) {
                    $("#changeCaseRateArea").html(res);
                    $("#preloader").hide();
                }
            })  
    }
</script>
@stop
