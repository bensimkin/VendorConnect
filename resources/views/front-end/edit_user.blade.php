@include('layout.header')
<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('layout.sidebar')
        <!-- Layout container -->
        <div class="layout-page">
            @include('layout.navbar')
            <!-- Navbar -->
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('dashboard.view') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="#">{{$title}}</a>
                                    </li>
                                    <li class="breadcrumb-item active">
                                        Create </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    {{-- <div class="alert alert-primary" role="alert">As Account Creation Email Status is Active, Please
                        Ensure Email Settings Are Configured and Operational. </div> --}}
                        @if (session('message'))
                                <div class="alert add_client_message">
                                 {{ session('message') }}
                                 </div>
                            @endif
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('users.update_user',$user->id) }}" method="POST"
                                class="form-submit-event" enctype="multipart/form-data">
                            @csrf
                                 @method('PUT')
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label for="firstName" class="form-label">First name <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="text" id="first_name" name="first_name"
                                            placeholder="Please enter first name" value="{{ $user->first_name }}">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="lastName" class="form-label">Last name <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="text" name="last_name" id="last_name"
                                            placeholder="Please enter last name" value="{{ $user->last_name }}">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="email" class="form-label">E-mail <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="text" id="email" name="email"
                                            placeholder="Please enter email" value="{{ $user->email }}">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Country code and phone number</label>
                                        <div class="input-group">
                                            <!-- Country Code Input -->
                                            <input type="text" name="country_code"
                                                class="form-control country-code-input" placeholder="+1"
                                                value="{{ $user->country_code }}">
                                            <!-- Mobile Number Input -->
                                            <input type="text" name="phone" class="form-control"
                                                placeholder="1234567890" value="{{ $user->phone }}">
                                        </div>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="password" class="form-label">Password <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="password" id="password" name="password"
                                            placeholder="Please enter password" autocomplete="off">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="password_confirmation" class="form-label">Confirm password <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="password" id="password_confirmation"
                                            name="password_confirmation" placeholder="Please re enter password"
                                            autocomplete="off">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="dob" class="form-label">Date of birth</label>
                                        <input class="form-control" type="text" id="dob" name="dob"
                                            placeholder="Please select" value="{{ \Carbon\Carbon::parse($user->dob)->format('d-m-Y') }}" autocomplete="off">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="doj" class="form-label">Date of joining</label>
                                        <input class="form-control" type="text" id="doj" name="doj"
                                            placeholder="Please select" value="{{ \Carbon\Carbon::parse($user->doj)->format('d-m-Y') }}" autocomplete="off">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="role">Role <span
                                                class="asterisk">*</span></label>
                                        <!-- <div class="input-group"> -->
                                            <select class="form-select text-capitalize js-example-basic-multiple" id="role" name="role">
                                                <option value="">Please select</option>
                                                @foreach ($UserRoles as $roles)
                                                    <option value="{{ $roles->id }}"
                                                        @if ($user->roles->contains($roles->id)) selected @endif>
                                                        {{ $roles->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        <!-- </div> -->
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="address" class="form-label">Address</label>
                                        <input class="form-control" type="text" id="address" name="address"
                                            placeholder="Please enter address" value="{{ $user->address  }}">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="city" class="form-label">City</label>
                                        <input class="form-control" type="text" id="city" name="city"
                                            placeholder="Please enter city" value="{{ $user->city }}">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="state" class="form-label">State</label>
                                        <input class="form-control" type="text" id="state" name="state"
                                            placeholder="Please enter state" value="{{ $user->state }}">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="country" class="form-label">Country</label>
                                        <input class="form-control" type="text" id="country" name="country"
                                            placeholder="Please enter country" value="{{ $user->country }}">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="zip" class="form-label">Zip code</label>
                                        <input class="form-control" type="text" id="zip" name="zip"
                                            placeholder="Please enter ZIP code" value="{{ $user->zip }}">
                                    </div>
                                    <div class="mb-3 col-md-12">
                                        <label for="photo" class="form-label"><?= get_label('profile_picture', 'Profile picture') ?></label>
                                        <div class="d-flex align-items-start align-items-sm-center gap-4 my-3">
                                            <img src="{{$user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')}}" alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
                                            <div class="button-wrapper">
                                                <div class="input-group d-flex">
                                                    <input type="file" class="form-control" id="inputGroupFile02" name="upload">
                                                </div>
                                                <p class="text-muted mt-2"><?= get_label('allowed_jpg_png', 'Allowed JPG or PNG.') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="">Status (<small
                                                class="text-muted mt-2">If
                                                Deactivated, the User Won't Be Able to Log In to Their
                                                Account</small>)</label>
                                                <div class="">
                                                    <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                        <input type="radio" class="btn-check" id="user_active" name="status" value="1"
                                                            @if ($user->status == 1) checked @endif>
                                                        <label class="btn btn-outline-primary" for="user_active">Active</label>

                                                        <input type="radio" class="btn-check" id="user_deactive" name="status" value="0"
                                                            @if ($user->status == 0) checked @endif>
                                                        <label class="btn btn-outline-primary" for="user_deactive">Deactive</label>
                                                    </div>
                                                </div>
                                    </div>
                                    {{-- <div class="mb-3 col-md-6">
                                        <label class="form-label" for="">
                                            Require email verification? <i class='bx bx-info-circle text-primary'
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="If Yes is selected, user will receive a verification link via email. Please ensure that email settings are configured and operational."></i>
                                        </label>
                                        <div class="">
                                            <div class="btn-group btn-group d-flex justify-content-center"
                                                role="group" aria-label="Basic radio toggle button group">
                                                <input type="radio" class="btn-check" id="require_ev_yes"
                                                    name="require_ev" value="1" checked>
                                                <label class="btn btn-outline-primary"
                                                    for="require_ev_yes">Yes</label>
                                                <input type="radio" class="btn-check" id="require_ev_no"
                                                    name="require_ev" value="0">
                                                <label class="btn btn-outline-primary" for="require_ev_no">No</label>
                                            </div>
                                        </div>
                                    </div> --}}

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary me-2"
                                            id="submit_btn">Update</button>
                                        <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                                    </div>
                                </div>
                        </div>
                        </form>
                    </div>

                </div>

                @include('layout.labels')
            </div>
            <!-- Footer -->
            @include('layout.footer_bottom')
        </div>
        <!-- / footer -->
    </div>
    <!-- / Layout page -->
</div>
<!-- Overlay -->
<div class="layout-overlay layout-menu-toggle"></div>
</div>
<!-- / Layout page -->
@include('layout.footer_links')

</script>

</body>

</html>
