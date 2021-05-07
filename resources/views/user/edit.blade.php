@extends('layouts.afterlogin')

@section('content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 
$ip = $_SERVER['REMOTE_ADDR'];
$ipInfo = file_get_contents('http://ip-api.com/json/' . $ip);
$ipInfo = json_decode($ipInfo);
$userDefaultTimeZone = isset($ipInfo->timezone) ? $ipInfo->timezone : 'UTC' ;
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Update User</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('user.index') }}">User</a></li>
            <li class="breadcrumb-item active">Update User</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      @include('pages.messages')
      <div class="row">
        <!-- left column -->
        <div class="col-md-12">
          <!-- general form elements -->
          <div class="card card-secondary">
            <div class="card-header">
              <h3 class="card-title">Update User Details</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{ route('user.update',$user->id) }}" id="updateUser" method="POST"
              enctype="multipart/form-data">
              @csrf
              {{ method_field('PUT') }}
              <div class="row">
                <div class="col-md-6">
                  <div class="card-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">First Name :</label>
                      <input type="text" name="first_name" value="{{ $user->first_name }}" maxlength="50"
                        class="form-control" placeholder="First Name">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Last Name :</label>
                      <input type="text" name="last_name" value="{{ $user->last_name }}" maxlength="50"
                        class="form-control" placeholder="Last Name">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Select User Type :</label>
                      <select name="is_admin" class="form-control" placeholder="Select User Type">
                        <option value="0" <?php if($user->is_admin==0){ echo "selected='selected'"; } ?>>User</option>
                        <option value="1" <?php if($user->is_admin==1){ echo "selected='selected'"; } ?>>Admin</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputFile">Profile Image : </label>
                      <div class="input-group">
                        <div class="custom-file">
                          <input type="file" class="custom-file-input" name="profile_image" id="exampleInputFile">
                          <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                        </div>

                      </div>
                    </div>
                    <div class="form-group">
                      {{-- <label for="exampleInputFile">Section Image : </label> --}}
                      <div class="input-group">
                        <img width="30%" height="30%" src="{{$user->thumb_url}}">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card-body">
                    <div class="form-group">
											<label for="exampleInputEmail1">Select Timezone :</label>
											<select name="user_timezone" class="form-control select2" placeholder="Select Timezone">
											  <?php 
											  foreach(array_flip($timezoneData) as $key=>$val){?>
											  <option  <?php if($key == $user->user_timezone){ echo "selected='selected'"; } ?>   value="{{$key}}">{{$val}}</option>
											  <?php }?>
											</select>
                      </div>
                      
                    <div class="form-group">
                      <label for="exampleInputEmail1">Email address :</label>
                      <input type="text" name="email" value="{{ $user->email }}" class="form-control" maxlength="200"
                        placeholder="Email">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Password :</label>
                      <input type="text" name="password" class="form-control" value="" maxlength="50" id="password"
                        placeholder="Password">
                     
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Confirm Password :</label>
                      <input type="text" name="password_confirmation" class="form-control" maxlength="50" value=""
                        placeholder="Confirm Password">
                        <h6 class="text-warning">(Please leave as empty if dont want update password.)</h6>
                    </div>
                  </div>
                </div>
              </div>

              <!-- /.card-body -->

              <div class="card-footer">
                <a href="{{ route('user.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary float-right">Update User</button>
              </div>
            </form>
          </div>
          <!-- /.card -->
        </div>
        <!-- /.card -->
      </div>
      <!--/.col (right) -->
    </div>
    <!-- /.row -->
</div><!-- /.container-fluid -->
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

@section('page-js-script')
<script type="text/javascript">
  $(document).ready(function () {
    $("#updateUser").validate({
      rules: {
        first_name: {
          required: true,
          minlength: 2
        },
        last_name: {
          required: true,
          minlength: 2
        },
        password: {
          minlength: 5
        },
        password_confirmation: {
          minlength: 5,
          equalTo: "#password"
        },
        email: {
          required: true,
          email: true
        }
      },
      messages: {
        first_name: {
          required: "Please enter a first name",
          minlength: "Your username must consist of at least 2 characters"
        },
        last_name: {
          required: "Please enter a last name",
          minlength: "Your username must consist of at least 2 characters"
        },
        password: {
          required: "Please provide a password",
          minlength: "Your password must be at least 5 characters long"
        },
        password_confirmation: {
          required: "Please provide a confirm password",
          minlength: "Your password must be at least 5 characters long",
          equalTo: "Please enter the same password as above"
        },
        email: "Please enter a valid email address"

      }
    });
  });
</script>
@stop
@endsection