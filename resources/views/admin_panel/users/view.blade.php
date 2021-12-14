@extends('admin_panel.layouts.master')
@section('page-title', 'User Info')
@section('page-css')
@endsection
@section('main-content')
<div class="breadcrumb">
    <i class="fas fa-user-circle fa-2x"></i>
    <h2 class="mx-2 mb-0 text-nowrap">
        <?php echo ucfirst($userProfile->first_name) .' '.ucfirst($userProfile->last_name);?> {{ "(".$userProfile->user_title.")"}}
        <?php if($userProfile->user_status=="3"){?>
        <span class="text-danger">[ Inactive ]</span>
        <?php } 
            if($userProfile->user_status=="4"){?>
        <span class="text-danger">[ Archived ]</span>
        <?php } ?>
    </h2> 
    <ul class="m2">
        <li><a href="">Dashboard</a></li>
        <li>Version 2</li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">    
    <div class="col-lg-12 col-md-12">
        <div class="card mb-4">
            <div class="card-body p-0">
                      
            </div>
        </div>
    </div>
</div>
@endsection
@section('page-js')
<script>
</script>
@endsection