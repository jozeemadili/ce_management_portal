@extends('admin.authentication.master')

@section('title')login
 {{ $title }}
@endsection

@push('css')
@endpush

@section('content')
    <section>
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12">
                <div class="login-card">
                    <form class="theme-form login-form" method="post">
                        <h4>Login</h4>
                        <h6>Welcome back! Log in to your account.</h6>
                        <div class="form-group">
                            <label>Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="icon-user"></i></span>
                                <input class="form-control" type="text" required placeholder="Enter your username here ..." />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="icon-lock"></i></span>
                                <input class="form-control" type="password"  required placeholder="Enter your password here ..." />
                                <div class="show-hide"><span class="show"> </span></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="checkbox">
                                <input id="checkbox1" type="checkbox" />
                                <label for="checkbox1">Remember password</label>
                            </div>
                            <a class="link" href="{{ route('forget-password') }}">Forgot password?</a>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary btn-block" type="submit">Sign in</button>
                        </div>

                        <br /> <br /> <br />

                        <div class="login-social-title">
                            <h5>&copy; {{Config('custom.constants.solution.name')}} {{Config('custom.constants.solution.version')}}</h5>
                        </div>
                        <div class="form-group">
                            <ul class="login-social">
                                <li>
                                    <a href="https://www.linkedin.com/login" target="_blank"><i data-feather="linkedin"></i></a>
                                </li>
                                <li>
                                    <a href="https://www.linkedin.com/login" target="_blank"><i data-feather="twitter"></i></a>
                                </li>
                                <li>
                                    <a href="https://www.linkedin.com/login" target="_blank"><i data-feather="facebook"></i></a>
                                </li>
                                <li>
                                    <a href="https://www.instagram.com/login" target="_blank"><i data-feather="instagram"> </i></a>
                                </li>
                            </ul>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>


    @push('scripts')
    @endpush

@endsection
