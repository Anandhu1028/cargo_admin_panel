<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="dark" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>Login | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Admin Login Page" name="description" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="shortcut icon" href="{{ asset('assets/images/sidebar_logo.png') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
</head>

<body>
<div class="container-xxl">
    <div class="row vh-100 d-flex justify-content-center align-items-center">
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-lg border-0 rounded-3 overflow-hidden">

                <!-- Header -->
              
                <div class="card-body p-0 auth-header-box rounded-top"
                    style="
                        background: url('{{ asset('assets/images/courier.png') }}') center center / cover no-repeat;
                        height: 150px; /* adjust height as needed */
                        position: relative;
                    ">
                    
                </div>

                <!-- Form -->
                <div class="card-body pt-4 pb-3 px-4">

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-success mb-3">{{ session('status') }}</div>
                    @endif

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger small">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   required autofocus
                                   placeholder="Enter your email">
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password"
                                   type="password"
                                   name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required
                                   placeholder="Enter your password">
                        </div>

                        <!-- Remember Me + Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                                <label class="form-check-label" for="remember_me">Remember me</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-muted small">
                                    <i class="dripicons-lock"></i> Forgot password?
                                </a>
                            @endif
                        </div>

                        <!-- Submit -->
                        <div class="d-grid mt-4">
                            <button type="submit"
                                style="
                                    background: linear-gradient(90deg, #141824 0%, #1e2736 60%, #253043 100%);
                                    color: #fff;
                                    border: none;
                                    padding: 10px 26px;
                                    font-weight: 600;
                                    border-radius: 8px;
                                    letter-spacing: 0.5px;
                                    box-shadow: 0 4px 12px rgba(20, 24, 36, 0.4);
                                    transition: all 0.3s ease;
                                "
                                onmouseover="this.style.background='linear-gradient(90deg, #1e2736 0%, #2b374f 60%, #34445e 100%)'; this.style.transform='translateY(-2px)';"
                                onmouseout="this.style.background='linear-gradient(90deg, #141824 0%, #1e2736 60%, #253043 100%)'; this.style.transform='translateY(0)';"
                                onmousedown="this.style.transform='scale(0.97)';"
                                onmouseup="this.style.transform='translateY(-2px)';">
                                <i class="fas fa-sign-in-alt me-2 text-info"></i> Log In
                            </button>
                        </div>
                    </form>

                    <!-- Register -->
                    <div class="text-center mt-3">
                        @if (Route::has('register'))
                            <h5 class="text-muted mb-0">
                               Wlecome To  Admin                         
                            </h5>
                        @endif
                    </div>

                    <!-- Divider -->
                    <div class="text-center mt-4">
                        <h6 class="px-3 d-inline-block text-muted">Or Login With</h6>
                    </div>

                    <!-- Social Login -->
                    <div class="d-flex justify-content-center mt-2">
                        <a href="#" class="d-flex justify-content-center align-items-center thumb-md bg-primary-subtle text-primary rounded-circle me-2">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="d-flex justify-content-center align-items-center thumb-md bg-info-subtle text-info rounded-circle me-2">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="d-flex justify-content-center align-items-center thumb-md bg-danger-subtle text-danger rounded-circle">
                            <i class="fab fa-google"></i>
                        </a>
                    </div>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
    </div>
</div>
</body>
</html>
