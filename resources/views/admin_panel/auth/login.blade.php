<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>{{ config('app.name') }} - Admin Login</title>
        <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
        <link rel="stylesheet" href="{{asset('assets/styles/css/themes/lite-purple.min.css')}}">
    </head>

    <body>
        <div class="auth-layout-wrap" style="background: #e9ecef;">
            <div class="auth-content">
                <div class="card o-hidden">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="p-4">
                                <div class="auth-logo text-center mb-4">
                                    <img src="{{asset('assets/images/logo.png')}}" alt="">
                                </div>
                                <h1 class="mb-3 text-18">Sign In As Administrator</h1>
                                <form method="POST" action="{{ route('admin/login/post') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="email">Email address</label>
                                        <input id="email" class="form-control form-control-rounded @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" id="password" class="form-control form-control-rounded @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <button class="btn btn-rounded btn-primary btn-block mt-2">Sign In</button>
                                </form>
                                <div class="mt-3 text-center">
                                    <a href="{{ route('password.request') }}" class="text-muted"><u>Forgot Password?</u></a>
                                </div>
                                <div class="mt-3 text-center">
                                    <a href="{{ route('login') }}" class="text-muted"><u>Login As Normal User</u></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{asset('assets/js/common-bundle-script.js')}}"></script>

        <script src="{{asset('assets/js/script.js')}}"></script>
    </body>

</html>