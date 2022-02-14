@if(!empty($userProfile))
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/css/plugins/datatables.min.css')}}" />
@endsection
    <div class="card common-settings mb-4" bladefile="resources/views/admin_panel/staff/loadallstaffdata.blade.php">
        <h4 class="card-header d-flex justify-content-between align-items-center">{{ $userProfile->email }}
            <button class="btn btn-outline-primary btn-rounded text-nowrap" onclick="loginToUserAccount('{{ route('login/user', encodeDecodeId($userProfile->id, 'encode')) }}')">Entrar como usuario</button>
        </h4>
        <div class="card-body">
            <div class="row">
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal" href="javascript:;">
                        <i class="i-Administrator text-32 mr-3" height="40"></i>
                        <div class="mt-1">Tiene permisos para pagar?</div>
                        <div class="mt-1">{{ (in_array('manage_firm_and_billing_settings', $userPermissions))  ? 'Sí' : 'No' }}</div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal" href="javascript:;">
                        <i class="i-Administrator text-32 mr-3" height="40"></i>
                        <div class="mt-1">Activo?</div>
                        <div class="mt-1">{{ $userProfile->user_status == '1' ? 'Sí' : 'No' }}</div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal" href="javascript:;">
                        <i class="i-Administrator text-32 mr-3" height="40"></i>
                        <div class="mt-1">Registro Usuario:</div>
                        <div class="mt-1">{{ date('d-m-Y H:i', strtotime($userProfile->created_at)) }} </div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal" href="javascript:;">
                        <i class="i-Administrator text-32 mr-3" height="40"></i>
                        <div class="mt-1">Registro Firma:</div>
                        <div class="mt-1">{{ date('d-m-Y H:i', strtotime($userProfile->firmDetail->created_at)) }} </div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal" href="javascript:;">
                        <i class="i-Files text-32 mr-3" height="40"></i>
                        <div class="mt-1">Usuarios en la firma:</div>
                        <div class="mt-1">{{ $userData[0]->staffCount }}</div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal" href="javascript:;">
                        <i class="i-Files text-32 mr-3" height="40"></i>
                        <div class="mt-1">Casos del usuario:</div>
                        <div class="mt-1">{{ count($userProfile->caseStaff) }}</div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal" href="javascript:;">
                        <i class="i-Files text-32 mr-3" height="40"></i>
                        <div class="mt-1">Casos de la firma:</div>
                        <div class="mt-1">{{ $userData[0]->firmCaseCount }}</div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-md-12">
            <a href="{{ route('admin/stafflist/staff', $userProfile->decode_id) }}">Ver lista de usuarios de la Firma</a>
        </div>
    </div>
</div>
@section('page-js')
<script src="{{asset('assets/js/plugins/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/scripts/datatables.script.min.js')}}"></script>
<script>
    $(document).ready(function() {
        
    });
    
    /* function loginToUserAccount(url) {
            alert();
            // var redirectWindow = window.open('http://youtube.com', '_blank', 'width=400, height=400');
            // redirectWindow.location;
            var myWindow = window.open("", "", "width=400,height=400");
        } */
</script>
@endsection
@else
<div class="d-flex justify-content-center align-items-center">Ningún usuario encontrado...</div>
@endif