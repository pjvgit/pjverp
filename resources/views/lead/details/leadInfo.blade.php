 <div class="lead-info-tab">
     <div class="row ">
         <div class="col">
             <h5 class="section-header"><b>Lead Information</b></h5>
         </div>
         <div class="col">
             <div class="float-right">
                <a data-toggle="modal" data-target="#editLead" data-placement="bottom" href="javascript:;">
                    <button class="btn btn-primary btn-rounded m-1 px-5" type="button" onclick="editLead({{$LeadData['user_id']}});">Edit Lead</button>
                </a>
                </div>
         </div>
     </div>
     <div class="row ">
         <div class="col-md-12 col-lg-6">
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Name</label></div>
                 <div class="col-9"><span class="field-value">{{substr($LeadData['leadname'],0,50)}}</span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Home</label></div>
                 <div class="col-9"><span class="field-value">{{$LeadData['home_phone']}}</span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Work</label></div>
                 <div class="col-9"><span class="field-value">{{$LeadData['work_phone']}}</span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Cell</label></div>
                 <div class="col-9"><span class="field-value">{{$LeadData['mobile_number']}}</span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Birthday</label></div>
                 <div class="col-9">
                     <?php
                     if($LeadData['dob']!=NULL){ 
                     ?>
                     <span class="field-value">{{date('m/d/Y',strtotime($LeadData['dob']))}}</span>
                     <?php } ?> 
                    </div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Driver License</label></div>
             <div class="col-9"><span class="field-value">{{($LeadData['driver_license'])??''}} 
                
                <?php
                if($LeadData['license_state']!=NULL){
                    echo "(".$LeadData['license_state'].")";
                } 
                ?> </span></div>
             </div>
         </div>
         
         <div class="col-md-12 col-lg-6">
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Address</label></div>
                 <div class="col-9"><span class="field-value">
                     {{$LeadData['full_address']}} 
                     <?php
                     if(isset($LeadData['country_name'])){
                        echo ",".$LeadData['country_name'];
                     }
                     ?></span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Email</label></div>
                 <div class="col-9"><span class="field-value"><a
                             href="mailto:{{$LeadData['email']}}">{{$LeadData['email']}}</a></span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Referred By</label></div>
                 <div class="col-9">
                     <span class="field-value">
                         <div class="d-flex flex-row">
                           <?php
                           if(!empty($referBy)){?>
                             <a href="{{BASE_URL}}/contacts/clients/{{base64_encode($referBy['id'])}}" class="d-flex align-items-center user-link" title="{{$referBy['user_title']}}">
                                {{substr($referBy['first_name'],0,50)}}  {{substr($referBy['last_name'],0,50)}}</a>
                           
                        <?php } ?>
                        </div>
                     </span></div>
             </div>
             <div class="row ">
                 <div class="col-3"><label class="mr-2 field-label">Lead Details</label></div>
                 <div class="col-9">
                     <div class="field-value">
                         <p>{{$LeadData['lead_detail']}}</p>
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