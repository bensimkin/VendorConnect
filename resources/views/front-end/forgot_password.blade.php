@include('layout.header')
    <!-- Layout wrapper -->
    <!-- Content -->
    <div class="container-fluid">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <!-- Forgot Password -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="{{ route('login.view') }}" class="app-brand-link">
                                <span class="app-brand-logo demo">
                                    <img src="{{ asset('front-end/assets/image/vendorconnect.png') }}" width="300px" />
                                </span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-2">Forgot Password?</h4>
                        {{-- <p class="mb-4">Enter your email and we&#039;ll send you password reset link</p> --}}
                        <form id="formAuthentication" class="mb-3 form-submit-event"
                            action="{{ route('forgot-password-mail') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail <span class="asterisk">*</span></label>
                                <input type="text" class="form-control" id="email" name="email"
                                    placeholder="Please Enter Email" value="" autofocus />
                            </div>
                            <button type="submit" id="submit_btn" class="btn btn-primary d-grid w-100">Submit</button>
                        </form>
                        <div class="text-center">
                            <a href="{{ route('login.view') }}"
                                class="d-flex align-items-center justify-content-center">
                                <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                                Back to login
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Forgot Password -->
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
