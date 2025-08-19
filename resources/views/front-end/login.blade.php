@include('layout.header')
    <!-- Layout wrapper -->
    <!-- Content -->
    <div class="container-fluid">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Register -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="{{  route('login.view')}}" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <img src="{{ asset('front-end/assets/image/vendorconnect.png') }}"
                                        width="300px" alt="" />
                                </span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <p class="mb-4">Sign into your account</p>
                        <form id="formAuthentication" class="mb-3 form-submit-event"action="{{route('users.authenticate')}}" method="POST">
                          @csrf
                          <input type="hidden" name="redirect_url" value="{{ route('dashboard.view') }}">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail <span class="asterisk">*</span></label>
                                <input type="text" class="form-control" id="email" name="email"
                                    placeholder="Please Enter Email" autofocus />
                            </div>
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">Password <span
                                            class="asterisk">*</span></label>
                                    <a href="{{route('forgot.pass.view')}}">
                                        <small>Forgot Password?</small>
                                    </a>
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="Please Enter Password"  aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <button class="btn btn-primary d-grid w-100" id="submit_btn"
                                    type="submit">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /Register -->
            </div>
        </div>
    </div>
    <!-- / Content -->
    @include('layout.labels')
    </div>
    <!-- Content wrapper -->
    </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- / Layout page -->
    @include('layout.footer_links')

    </script>
</body>

</html>
