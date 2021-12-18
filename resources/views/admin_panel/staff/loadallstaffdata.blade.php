@if(!empty($userProfile))
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/css/plugins/datatables.min.css')}}" />
@endsection
    <div class="card common-settings mb-4">
        <h4 class="card-header d-flex justify-content-between align-items-center">{{ $userProfile->email }}
            <button class="btn btn-outline-primary btn-rounded text-nowrap">Entrar como usuario</button>
        </h4>
        <div class="card-body">
            <div class="row">
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal"  data-target="#DeleteModal" data-placement="bottom" href="javascript:;"  onclick="loadStep1();">
                        <i class="i-Administrator text-32 mr-3" height="40"></i>
                        <div class="mt-1">Tiene permisos para pagar?</div>
                        <div class="mt-1">No</div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal"  data-target="#DeleteModal" data-placement="bottom" href="javascript:;"  onclick="loadStep1();">
                        <i class="i-Administrator text-32 mr-3" height="40"></i>
                        <div class="mt-1">Activo?</div>
                        <div class="mt-1">{{ $userProfile->user_status == '1' ? 'Sí' : 'No' }}</div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal"  data-target="#DeleteModal" data-placement="bottom" href="javascript:;"  onclick="loadStep1();">
                        <i class="i-Administrator text-32 mr-3" height="40"></i>
                        <div class="mt-1">Registro Usuario:</div>
                        <div class="mt-1">{{ date('d-m-Y H:i', strtotime($userProfile->created_at)) }} </div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal"  data-target="#DeleteModal" data-placement="bottom" href="javascript:;"  onclick="loadStep1();">
                        <i class="i-Administrator text-32 mr-3" height="40"></i>
                        <div class="mt-1">Registro Firma:</div>
                        <div class="mt-1">{{ date('d-m-Y H:i', strtotime($userProfile->firmDetail->created_at)) }} </div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal"  data-target="#DeleteModal" data-placement="bottom" href="javascript:;"  onclick="loadStep1();">
                        <i class="i-Files text-32 mr-3" height="40"></i>
                        <div class="mt-1">Usuarios en la firma:</div>
                        <div class="mt-1">{{ $userData[0]->staffCount }}</div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal"  data-target="#DeleteModal" data-placement="bottom" href="javascript:;"  onclick="loadStep1();">
                        <i class="i-Files text-32 mr-3" height="40"></i>
                        <div class="mt-1">Casos del usuario:</div>
                        <div class="mt-1">{{ $userProfile->active_case_counter }}</div>
                    </a>
                </div>
                <div class="col-3 text-center common-shortcut p-2">
                    <a data-toggle="modal"  data-target="#DeleteModal" data-placement="bottom" href="javascript:;"  onclick="loadStep1();">
                        <i class="i-Files text-32 mr-3" height="40"></i>
                        <div class="mt-1">Casos de la firma:</div>
                        <div class="mt-1">{{ $case }}</div>
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
</script>
@endsection
@else
<div class="d-flex justify-content-center align-items-center">Ningún usuario encontrado...</div>
@endif