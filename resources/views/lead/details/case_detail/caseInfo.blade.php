 <?php
 $CommonController= new App\Http\Controllers\CommonController();
?>
<div class="lead-info-tab">
     <div class="row ">
         <div class="col">
             <h5 class="section-header"><b>Potential Case Information</b></h5>
         </div>
         <div class="col">
             <div class="float-right">
                <a data-toggle="modal" data-target="#editPotentialCase" data-placement="bottom" href="javascript:;">
                    <button class="btn btn-primary btn-rounded m-1 px-5" type="button" onclick="editPotentialCase({{$LeadData['user_id']}});">Edit Potential Case</button>
                </a>
                </div>
         </div>
     </div>
     <div class="row ">
         <div class="col-md-12 col-lg-6">
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Name</label></div>
                 <div class="col-9"><span class="field-value">{{substr($LeadData['potential_case_title'],0,50)}}</span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Status</label></div>
                 <div class="col-9"><span class="field-value">{{$LeadData['lead_status_title']}}</span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Practice Area</label></div>
                 <div class="col-9"><span class="field-value">{{$LeadData['case_practice_area_title']}}</span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Date Added</label></div>
                 <div class="col-9"><span class="field-value">{{date('m/d/Y',strtotime($LeadData['date_added']))}}</span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Conflict Check
                </label></div>
                 <div class="col-9">
                     <?php
                     if($LeadData['conflict_check']=='yes' && $LeadData['conflict_check_at']!=NULL){
                     $currentConvertedDate= $CommonController->convertUTCToUserTime($LeadData['conflict_check_at'],Auth::User()->user_timezone);
                     ?>
                     <span class="field-value">Marked complete {{date('m/d/Y h:i a',strtotime($currentConvertedDate))}}</span>
                     <?php } ?> 
                    </div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Conflict Check Note</label></div>
             <div class="col-9"><span class="field-value">{{($LeadData['conflict_check_description'])??''}} 
                 </span></div>
             </div>
         </div>
         
         <div class="col-md-12 col-lg-6">
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Value</label></div>
                 <div class="col-9"><span class="field-value">
                     <?php
                     if($LeadData['potential_case_value']!=NULL){?>
                    ${{number_format($LeadData['potential_case_value'], 2, '.', ',')}} 
                     <?php } ?>
                   </span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Assign To</label></div>
                 <div class="col-9"><span class="field-value">
                     <?php 
                     if(isset($assignedToData) && !empty($assignedToData)){?>
                    <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($assignedToData['id'])}}" class="d-flex align-items-center user-link" title="{{$assignedToData['user_title']}}">
                        {{substr($assignedToData['assigned_to_name'],0,50)}} </a>
                    <?php } ?>
                  </span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Office Location
                </label></div>
                 <div class="col-9">
                     <span class="field-value">
                         <?php
                         if($LeadData['office']=="1"){
                             echo "Primary";
                         }?>
                     </span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Case Description
                </label></div>
                 <div class="col-9">
                     <div class="field-value">
                         <p>{{$LeadData['potential_case_description']}}</p>
                     </div>
                 </div>
             </div>
         </div>
     </div>
     <div class="row ">
         <div class="col-md-12 col-lg-6"></div>
         <div class="col-md-12 col-lg-6"></div>
     </div>
 </div>