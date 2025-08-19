@include('layout.header')

        @include('layout.sidebar')
        <!-- Layout container -->
        <div class="layout-page">
            @include('layout.navbar')
            <!-- / Navbar -->
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <div class="container-fluid">
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb breadcrumb-style1">
                                        <li class="breadcrumb-item">
                                            <a href="#">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">
                                            Profile </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <h5 class="card-header">Profile details</h5>
                                    <!-- Account -->
                                    <div class="card-body">
                                        <form action="{{route('profile.update_photo', Auth::user()->id)}}"
                                            class="form-submit-event" method="POST" enctype="multipart/form-data">
                                          @csrf
                                          <input type="hidden" name="redirect_url" value="{{ route('user.profile.view', Auth::user()->id) }}">
                                          <input type="hidden" name="_method" value="PUT">

                                          <div class="d-flex align-items-start align-items-sm-center gap-4">
                                              <img src="{{ asset('storage/'.Auth::user()->photo) }}"
                                                   alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />

                                              <div class="button-wrapper">
                                                  <div class="input-group">
                                                      <input type="file" class="form-control" id="inputGroupFile02" name="upload">
                                                      <button class="btn btn-outline-primary" type="submit" id="submit_btn">Update profile photo</button>
                                                  </div>
                                                  <p class="text-muted mt-2">Allowed JPG or PNG.</p>
                                              </div>
                                          </div>
                                      </form>
                                    </div>
                                    <hr class="my-0" />
                                    <div class="card-body">
                                        <form id="formAccountSettings" method="POST" class="form-submit-event"
                                            action="{{route('profile.update',Auth::user()->id)}}">

                                            <input type="hidden" name="redirect_url"
                                                value="{{ route('user.profile.view', Auth::user()->id) }}">
                                          @csrf
                                            <input type="hidden" name="_method" value="PUT">
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label for="firstName" class="form-label">First name <span
                                                            class="asterisk">*</span></label>
                                                    <input class="form-control" type="text" id="first_name"
                                                        name="first_name" placeholder="Enter first name"
                                                        autofocus value="{{ Auth::user()->first_name }}" />
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label for="lastName" class="form-label">Last name <span
                                                            class="asterisk">*</span></label>
                                                    <input class="form-control" type="text" name="last_name"
                                                        placeholder="Enter last name" id="last_name"
                                                        value="{{ Auth::user()->last_name }}"/>
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="phone">Phone number <span
                                                            class="asterisk">*</span></label>
                                                    <input type="text" value="{{ Auth::user()->phone }}" id="phone" name="phone"
                                                        placeholder="Enter phone number" class="form-control"/>
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="email">E-mail</label>
                                                    <input class="form-control" type="text" name="email"
                                                        placeholder="Enter email" value="{{ Auth::user()->email }}">
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label for="password" class="form-label">Password <small
                                                            class="text-muted"> (Leave it blank if no
                                                            change)</small></label>
                                                    <input class="form-control" type="password" id="password"
                                                        name="password" placeholder="Enter Password">

                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="password_confirmation" class="form-label">Confirm
                                                        password</label>
                                                    <input class="form-control" type="password"
                                                        id="password_confirmation" name="password_confirmation"
                                                        placeholder="Re Enter Password">
                                                </div>
                                                @if ($activeUser->hasRole('Admin'))
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="role">Role <span
                                                            class="asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <select class="form-select text-capitalize js-example-basic-multiple" id="role" name="role">
                                                            <option value="">Please select</option>
                                                            @foreach ($UserRoles as $roles)
                                                                <option value="{{ $roles->id }}"
                                                                    @if (Auth::user()->roles->contains($roles->id)) selected @endif>
                                                                    {{ $roles->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                @else
                                                <input type="hidden" name="role" value="{{ Auth::user()->roles()->first()->id }}">
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="role">Role <span
                                                            class="asterisk">*</span></label>
                                                    <div class="input-group">
                                                        @php
                                                            $role = Auth::user()->getRoleNames()->first();
                                                        @endphp
                                                        <input type="text" class="form-control" disabled="disabled" value="{{ $role }}">
                                                    </div>
                                                </div>
                                                @endif
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="address">Address <span
                                                            class="asterisk">*</span></label>
                                                    <input class="form-control" type="text" id="address"
                                                        placeholder="Enter address" name="address" value="{{ Auth::user()->address }}">


                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="city">City <span
                                                            class="asterisk">*</span></label>
                                                    <input class="form-control" type="text" id="city"
                                                        placeholder="Enter city" name="city" value="{{ Auth::user()->city }}">


                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="state">State <span
                                                            class="asterisk">*</span></label>
                                                    <input class="form-control" type="text" id="state"
                                                        placeholder="Enter state" name="state" value="{{ Auth::user()->state }}">


                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="country">Country <span
                                                            class="asterisk">*</span></label>
                                                    <input class="form-control" type="text" id="country"
                                                        placeholder="Enter country" name="country" value="{{ Auth::user()->country }}">
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="zip">Zip code <span
                                                            class="asterisk">*</span></label>
                                                    <input class="form-control" type="text" id="zip"
                                                        placeholder="Enter ZIP code" name="zip" value="{{ Auth::user()->zip }}">
                                                </div>

                                                <div class="mt-2">
                                                    <button type="submit" id="submit_btn"
                                                        class="btn btn-primary me-2">Update</button>
                                                    <button type="reset"
                                                        class="btn btn-outline-secondary">Cancel</button>
                                                </div>
                                        </form>
                                    </div>
                                    <!-- /Account -->
                                </div>
                                {{-- <div class="card">
                                    <h5 class="card-header">Delete account</h5>
                                    <div class="card-body">
                                        <div class="mb-3 col-12 mb-0">
                                            <div class="alert alert-warning">
                                                <h6 class="alert-heading fw-bold mb-1">
                                                    Are you sure you want to delete your account? </h6>
                                                <p class="mb-0">
                                                    Once you delete your account, there is no going back. Please be
                                                    certain. </p>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteAccountModal">Delete account</button>

                                    </div>

                                </div> --}}
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="default_language_modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you want to set as your primary language?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-primary" id="confirm">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="leaveWorkspaceModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Warning</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want leave this workspace? </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-danger" id="confirm">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="create_language_modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form class="modal-content form-submit-event"
                                action="https://app.inth.pk/superadmin/settings/languages/store" method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel1">Create language </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <input type="hidden" name="_token" value="FRU5EfFdqs5xsuubbFCh59fhRoo5B9LjGtnyOjmI"
                                    autocomplete="off">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="nameBasic" class="form-label">Title <span
                                                    class="asterisk">*</span></label>
                                            <input type="text" class="form-control" name="name"
                                                placeholder="For Example: English" />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="nameBasic" class="form-label">Code <span
                                                    class="asterisk">*</span></label>
                                            <input type="text" class="form-control" name="code"
                                                placeholder="For Example: en" />
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" id="submit_btn" class="btn btn-primary">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal fade" id="edit_language_modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <form class="modal-content form-submit-event"
                                action="https://app.inth.pk/superadmin/settings/languages/update" method="POST">
                                <input type="hidden" name="id" id="language_id">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel1">Update language </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <input type="hidden" name="_token" value="FRU5EfFdqs5xsuubbFCh59fhRoo5B9LjGtnyOjmI"
                                    autocomplete="off">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="nameBasic" class="form-label">Title <span
                                                    class="asterisk">*</span></label>
                                            <input type="text" class="form-control" name="name"
                                                id="language_title" placeholder="For Example: English" />
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" id="submit_btn" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="modal fade" id="create_contract_type_modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <form class="modal-content form-submit-event"
                                action="https://app.inth.pk/master-panel/contracts/store-contract-type"
                                method="POST">
                                <input type="hidden" name="dnr">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel1">
                                        Create contract type</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="nameBasic" class="form-label">Type <span
                                                    class="asterisk">*</span></label>
                                            <input type="text" class="form-control" name="type"
                                                placeholder="Please enter contract type" />
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" id="submit_btn" class="btn btn-primary">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal fade" id="edit_contract_type_modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <form class="modal-content form-submit-event"
                                action="https://app.inth.pk/master-panel/contracts/update-contract-type"
                                method="POST">
                                <input type="hidden" name="dnr">
                                <input type="hidden" id="update_contract_type_id" name="id">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel1">
                                        Update contract type</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="nameBasic" class="form-label">Type <span
                                                    class="asterisk">*</span></label>
                                            <input type="text" class="form-control" name="type"
                                                id="contract_type" placeholder="Please enter contract type" />
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" id="submit_btn" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Warning</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete your account?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <form id="formAccountDeactivation"
                                        action="https://app.inth.pk/master-panel/account/destroy/3" method="POST">
                                        <input type="hidden" name="_token"
                                            value="FRU5EfFdqs5xsuubbFCh59fhRoo5B9LjGtnyOjmI" autocomplete="off">
                                        <input type="hidden" name="_method" value="DELETE"> <button type="submit"
                                            class="btn btn-danger">Yes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel2">Warning</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"> '</button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-danger" id="confirmDelete">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="confirmDeleteSelectedModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel2">Warning</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"> '</button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete selected record(s)?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-danger"
                                        id="confirmDeleteSelections">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="duplicateModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-md" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Warning</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to duplicate?</p>
                                    <div id="titleDiv" class="d-none"><label class="form-label">Update
                                            Title</label><input type="text" class="form-control" id="updateTitle"
                                            placeholder="Enter Title For Item Being Duplicated"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-primary" id="confirmDuplicate">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="timerModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-md" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Time tracker </h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="modal-body">
                                        <div class="stopwatch">
                                            <div class="stopwatch_time">
                                                <input type="text" name="hour" id="hour" value="00"
                                                    class="form-control stopwatch_time_input" readonly>
                                                <div class="stopwatch_time_lable">Hours</div>
                                            </div>
                                            <div class="stopwatch_time">
                                                <input type="text" name="minute" id="minute" value="00"
                                                    class="form-control stopwatch_time_input" readonly>
                                                <div class="stopwatch_time_lable">Minutes</div>
                                            </div>
                                            <div class="stopwatch_time">
                                                <input type="text" name="second" id="second" value="00"
                                                    class="form-control stopwatch_time_input" readonly>
                                                <div class="stopwatch_time_lable">Second</div>
                                            </div>
                                        </div>
                                        <div class="selectgroup selectgroup-pills d-flex justify-content-around mt-3">
                                            <label class="selectgroup-item">
                                                <span class="selectgroup-button selectgroup-button-icon"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    data-bs-original-title="Start" id="start"
                                                    onclick="startTimer()"><i class="bx bx-play"></i></span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <span class="selectgroup-button selectgroup-button-icon"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    data-bs-original-title="Stop" id="end"
                                                    onclick="stopTimer()"><i class="bx bx-stop"></i></span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <span class="selectgroup-button selectgroup-button-icon"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    data-bs-original-title="Pause" id="pause"
                                                    onclick="pauseTimer()"><i class="bx bx-pause"></i></span>
                                            </label>
                                        </div>
                                        <div class="form-group mb-0 mt-3">
                                            <label class="label">Message:</label>
                                            <textarea class="form-control" id="time_tracker_message" placeholder="Please Enter Your Message" name="message"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <a href="https://app.inth.pk/master-panel/time-tracker"
                                            class="btn btn-primary"><i class="bx bxs-time"></i> View timesheet</a>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="stopTimerModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel2">Warning</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"> '</button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to stop the timer?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-danger" id="confirmStop">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="mark_all_notifications_as_read_modal" tabindex="-1"
                        style="display: none;" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to mark all notifications as read? </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-primary"
                                        id="confirmMarkAllAsRead">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="update_notification_status_modal" tabindex="-1"
                        style="display: none;" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to update notification status? </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-primary"
                                        id="confirmNotificationStatus">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="restore_default_modal" tabindex="-1" style="display: none;"
                        aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to restore default template? </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-primary"
                                        id="confirmRestoreDefault">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="sms_instuction_modal" tabindex="-1" style="display: none;"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel1">Sms Gateway Configuration</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="modal-body">
                                        <ul>
                                            <li class="my-4">Read and follow instructions carefully while
                                                configuration sms gateway
                                                setting </li>

                                            <li class="my-4">Firstly open your sms gateway account . You can find
                                                api keys in your
                                                account -> API keys & credentials -> create api key </li>
                                            <li class="my-4">After create key you can see here Account sid and auth
                                                token </li>
                                            <div class="simplelightbox-gallery">
                                                <a href="https://app.inth.pk/storage/images/base_url_and_params.png"
                                                    target="_blank">
                                                    <img src="https://app.inth.pk/storage/images/base_url_and_params.png"
                                                        class="w-100">
                                                </a>
                                            </div>

                                            <li class="my-4">For Base url Messaging -> Send an SMS</li>
                                            <div class="simplelightbox-gallery">
                                                <a href="https://app.inth.pk/storage/images/api_key_and_token.png"
                                                    target="_blank">
                                                    <img src="https://app.inth.pk/storage/images/api_key_and_token.png"
                                                        class="w-100">
                                                </a>
                                            </div>

                                            <li class="my-4">check this for admin panel settings</li>
                                            <div class="simplelightbox-gallery">
                                                <a href="https://app.inth.pk/storage/images/sms_gateway_1.png"
                                                    target="_blank">
                                                    <img src="https://app.inth.pk/storage/images/sms_gateway_1.png"
                                                        class="w-100">
                                                </a>
                                            </div>
                                            <div class="simplelightbox-gallery">
                                                <a href="https://app.inth.pk/storage/images/sms_gateway_2.png"
                                                    target="_blank">
                                                    <img src="https://app.inth.pk/storage/images/sms_gateway_2.png"
                                                        class="w-100">
                                                </a>
                                            </div>
                                            <li class="my-4"><b>Make sure you entered valid data as per instructions
                                                    before proceed</b>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">
                                        Close </button>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="modal fade" id="set_default_view_modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are You Want to Set as Default View?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-primary" id="confirm">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="modal fade" id="confirmSaveColumnVisibility" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are You Want to Save Column Visibility?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-primary" id="confirm">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="createWorkspaceModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Create workspace</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="https://app.inth.pk/master-panel/workspaces/store"
                                    class="form-submit-event" method="POST">
                                    <input type="hidden" name="dnr">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="mb-3">
                                                <label for="title" class="form-label">Title <span
                                                        class="asterisk">*</span></label>
                                                <input class="form-control" type="text" id="title"
                                                    name="title" placeholder="Please enter title"
                                                    value="">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3">
                                                <select id=""
                                                    class="form-control js-example-basic-multiple" name="user_ids[]"
                                                    multiple="multiple" data-placeholder="Type to search">

                                                    <option value="3" selected>
                                                        ahsan ahmed
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3">
                                                <select id=""
                                                    class="form-control js-example-basic-multiple"
                                                    name="client_ids[]" multiple="multiple"
                                                    data-placeholder="Type to search">
                                                    <option value="3">jawad ahmed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <label class="form-check-label" for="primaryWorkspace">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="primaryWorkspace" id="primaryWorkspace">
                                                        Primary Workspace?
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="alert alert-primary alert-dismissible" role="alert">
                                            You will be workspace participant automatically. </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" id="submit_btn"
                                            class="btn btn-primary">Create</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="editWorkspaceModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update workspace</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="https://app.inth.pk/master-panel/workspaces/update"
                                    class="form-submit-event" method="POST">
                                    <input type="hidden" name="_method" value="PUT"> <input type="hidden"
                                        name="id" id="workspace_id">
                                    <input type="hidden" name="dnr">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="mb-3">
                                                <label for="title" class="form-label">Title <span
                                                        class="asterisk">*</span></label>
                                                <input class="form-control" type="text" name="title"
                                                    id="workspace_title" placeholder="Please enter title"
                                                    value="">
                                            </div>
                                        </div>
                                        <div class="row">

                                            <div class="mb-3">
                                                <select id=""
                                                    class="form-control js-example-basic-multiple" name="user_ids[]"
                                                    multiple="multiple" data-placeholder="Type to search">


                                                    <option value="3" selected>
                                                        ahsan ahmed
                                                    </option>
                                                </select>

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3">
                                                <select id=""
                                                    class="form-control js-example-basic-multiple"
                                                    name="client_ids[]" multiple="multiple"
                                                    data-placeholder="Type to search">
                                                    <option value="3" selected>
                                                        jawad ahmed
                                                    </option>
                                                </select>

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <label class="form-check-label" for="updatePrimaryWorkspace">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="primaryWorkspace" id="updatePrimaryWorkspace">
                                                        Primary Workspace?
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" id="submit_btn"
                                            class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>




                    <div class="modal fade" id="confirmUpdateStatusModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Do You Want to Update the Status?</p>
                                    <textarea class="form-control" id="statusNote" placeholder="Optional Note"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary"
                                        id="declineUpdateStatus" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-primary"
                                        id="confirmUpdateStatus">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="confirmUpdatePriorityModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Do You Want to Update the Priority?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary"
                                        id="declineUpdatePriority" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-primary"
                                        id="confirmUpdatePriority">Yes</button>
                                </div>
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
