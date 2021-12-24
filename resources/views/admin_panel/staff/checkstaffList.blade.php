<div class="card common-settings mb-4" bladefile="resources/views/admin_panel/staff/checkstaffList.blade.php">
    <h4 class="card-header d-flex justify-content-center align-items-center">{{ $userProfiles[0]->fullName }}
    </h4>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <p class="d-flex justify-content-center align-items-center"> Which account would you like to check? </p>
                @foreach($userProfiles as $k => $v)                        
                    <div class="card-body align-self-center btn btn-outline-primary d-flex flex-column justify-content-between align-items-lg-center flex-lg-row searchStaff" staff_id="{{$v->id}}">
                        {{ $v->firmDetail->firm_name}}    
                        <span class="right">{{$v->user_title}}
                            <i class="fa fa-lg fa-arrow-circle-right ml-2" aria-hidden="true"></i>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>