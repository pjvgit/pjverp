@extends('layouts.afterlogin')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>View User</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('user.index') }}">User</a></li>
            <li class="breadcrumb-item active">View User</li>
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
              <h3 class="card-title">View User Details</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{ route('user.store') }}" id="signupForm" method="POST">
              @csrf
              <div class="row">
                <div class="col-md-6">
                  <div class="card-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">First Name :</label>
                      <input type="text" readonly name="first_name" value=" {{ $user->first_name }}"
                        class="form-control" placeholder="First Name">
                    </div>

                    <div class="form-group">
                      <label for="exampleInputEmail1">Last Name :</label>
                      <input type="text" readonly name="name" value=" {{ $user->last_name }}" class="form-control"
                        placeholder="Last Name">
                    </div>


                    <div class="form-group">
                      <label for="exampleInputFile">Profile Image : </label>
                      <div class="input-group">
                        <img width="30%" height="30%" src="{{$user->thumb_url}}">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Email address :</label>
                      <input type="text" readonly name="email" value="{{ $user->email }}" class="form-control"
                        placeholder="Email">
                    </div>

                    <div class="form-group">
                      <label for="exampleInputEmail1">Select User Type :</label>
                      <select name="is_admin" disabled class="form-control" placeholder="Select User Type">
                        <option value="0" <?php if($user->is_admin==0){ echo "selected='selected'"; } ?>>User</option>
                        <option value="1" <?php if($user->is_admin==1){ echo "selected='selected'"; } ?>>Admin</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Status :</label>
                      <?php  if ($user->status == 1) {
                  ?>
                      <span class="btn btn-success btn-sm btn-padding ">Active</span><?php
                  } else {
                  ?>
                      <span class="btn btn-danger btn-sm btn-padding">Inactive</span>
                      <?php 
                  }
                  ?>

                    </div>
                    
                  </div>
                </div>
              </div>
              <!-- /.card-body -->

              <div class="card-footer">
                {{-- <a href="{{ route('user.index') }}" class="btn btn-secondary">Cancel</a> --}}

                <a href="{{ route('user.index') }}" class="btn btn-primary float-right">Back</a>
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


@endsection