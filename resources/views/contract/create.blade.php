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
					<h1>Create User</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ route('user.index') }}">User</a></li>
						<li class="breadcrumb-item active">Create User</li>
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
							<h3 class="card-title">Add User Details</h3>
						</div>
						<!-- /.card-header -->
						<!-- form start -->
						<form action="{{ route('user.store') }}" id="createUser" method="POST"
							enctype="multipart/form-data">
							@csrf
							<div class="row">
								<div class="col-md-6">
									<div class="card-body">
										<div class="form-group">
											<label for="exampleInputEmail1">First Name :</label>
											<input type="text" name="first_name" value="{{old('first_name')}}"
												class="form-control" placeholder="First Name">
										</div>
										<div class="form-group">
											<label for="exampleInputEmail1">Last Name :</label>
											<input type="text" name="last_name" value="{{old('last_name')}}"
												maxlength="50" class="form-control" placeholder="Last Name">
										</div>



										<div class="form-group">
											<label for="exampleInputEmail1">Select User Type :</label>
											<select name="is_admin" class="form-control" placeholder="Select User Type">
												<option value="0">User</option>
												<option value="1">Admin</option>
											</select>
										</div>
										<div class="form-group">
											<label for="exampleInputFile">Profile Image : </label>
											<div class="input-group">
												<div class="custom-file">
													<input type="file" class="custom-file-input" name="profile_image"
														id="exampleInputFile">
													<label class="custom-file-label" for="exampleInputFile">Choose
														file</label>
												</div>

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
											  <option  <?php if($key == $userDefaultTimeZone){ echo "selected='selected'"; } ?>   value="{{$key}}">{{$val}}</option>
											  <?php }?>
											</select>
										  </div>
										<div class="form-group">
											<label for="exampleInputEmail1">Email address :</label>
											<input type="text" name="email" value="{{old('email')}}"
												class="form-control" placeholder="Email">
										</div>

										<div class="form-group">
											<label for="exampleInputEmail1">Password :</label>
											<input id="password" type="password" class="form-control" name="password">
										</div>

										<div class="form-group">
											<label for="exampleInputEmail1">Confirm Password :</label>
											<input id="password-confirm" type="password" class="form-control"
												name="password_confirmation">
												<h6 class="text-warning">(Please leave as empty if dont want update password.)</h6>
										</div>


									</div>
								</div>
							</div>
							<!-- /.card-body -->

							<div class="card-footer">
								<a href="{{ route('user.index') }}" class="btn btn-secondary">Cancel</a>
								<button type="submit" class="btn btn-primary float-right">Create User</button>
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
@section('page-js-script')
<script type="text/javascript">
	$(document).ready(function () {
		$("#createUser").validate({
			rules: {
				first_name: {
					required: true,
					minlength: 2
				},
				last_name: {
					required: true,
					minlength: 2
				},
				// password: {
				// 	required: true,
				// 	minlength: 5
				// },
				// password_confirmation: {
				// 	required: true,
				// 	minlength: 5,
				// 	equalTo: "#password"
				// },
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
				// password: {
				// 	required: "Please provide a password",
				// 	minlength: "Your password must be at least 5 characters long"
				// },
				// password_confirmation: {
				// 	required: "Please provide a confirm password",
				// 	minlength: "Your password must be at least 5 characters long",
				// 	equalTo: "Please enter the same password as above"
				// },
				email: "Please enter a valid email address"

			}
		});
	});
</script>
@stop