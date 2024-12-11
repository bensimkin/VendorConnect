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
                                        <a href="#">Clients</a>
                                    </li>
                                    <li class="breadcrumb-item active">
                                        {{ $title }} </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    {{-- <div class="alert alert-primary" role="alert">
                        As Account Creation Email Status Is Active, Please Ensure Email Settings Are Configured and
                        Operational (Not Applicable If the Client Is for Internal Purposes).

                    </div> --}}

                    <div class="card">
                        <div class="card-body">
                            @if (session('message'))
                                <div class="alert add_client_message">
                                    {{ session('message') }}
                                </div>
                            @endif
                            <form action="{{ route('clients.store') }}" method="POST" class="form-submit-event"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="redirect_url" value="/clients">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        {{-- <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="internal_client"
                                                name="internal_purpose">
                                            <label class="form-check-label" for="internal_client">Is this a client for
                                                internal purpose only?</label>
                                            <i class='bx bx-info-circle text-primary' data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Select this option if you want to create a client for internal use only, without granting account access to the client."></i>
                                        </div> --}}
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="firstName" class="form-label">First name <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="text" id="first_name" name="first_name"
                                            placeholder="Please enter first name" value="">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="lastName" class="form-label">Last name <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="text" name="last_name" id="last_name"
                                            placeholder="Please enter last name" value="">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">E-mail <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="text" id="email" name="email"
                                            placeholder="Please enter email" value="" autocomplete="off">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Country code and phone number</label>
                                        <div class="input-group">
                                            <!-- Country Code Input -->
                                            <input type="text" name="country_code"
                                                class="form-control country-code-input" placeholder="+1" value="">
                                            <!-- Mobile Number Input -->
                                            <input type="text" name="phone" class="form-control"
                                                placeholder="1234567890" value="">
                                        </div>
                                    </div>
                                    {{-- <div class="col-md-6 mb-3" id="passDiv">
                                        <label for="password" class="form-label">Password <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="password" id="password" name="password"
                                            placeholder="Please enter password" autocomplete="off">
                                    </div>
                                    <div class="col-md-6 mb-3" id="confirmPassDiv">
                                        <label for="password_confirmation" class="form-label">Confirm password <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="password" id="password_confirmation"
                                            name="password_confirmation" placeholder="Please re enter password"
                                            autocomplete="off">
                                    </div> --}}
                                    <div class="col-md-6 d-none mb-3">
                                        <label for="dob" class="form-label">Date of birth <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="text" id="dob" name="dob"
                                            placeholder="Please select" autocomplete="off">
                                    </div>
                                    <div class="col-md-6 d-none mb-3">
                                        <label for="doj" class="form-label">Date of joining <span
                                                class="asterisk">*</span></label>
                                        <input class="form-control" type="text" id="doj" name="doj"
                                            placeholder="Please select" autocomplete="off">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="company" class="form-label">Company</label>
                                        <input class="form-control" type="text" id="company" name="company"
                                            placeholder="Please enter company name" value="">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <input class="form-control" type="text" id="address" name="address"
                                            placeholder="Please enter address" value="">
                                    </div>
                                    {{-- <div class="mb-3 col-md-6">
                                        <label class="form-label" for="role">Role <span
                                                class="asterisk">*</span></label>
                                        <select class="form-select text-capitalize js-example-basic-multiple"
                                            id="role" name="role">
                                            <option value="">Please select</option>
                                            @foreach ($UserRoles as $roles)
                                            @php
                                                $selected = '';
                                            @endphp
                                            @if ($roles->name == 'Requester')
                                                @php
                                                    $selected = 'selected';
                                                @endphp
                                            @endif
                                            <option value="{{$roles->id}}" {{ $selected }}>{{$roles->name}}</option>
                                            @endforeach
                                        </select>
                                        <!-- </div> -->
                                    </div> --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input class="form-control" type="text" id="city" name="city"
                                            placeholder="Please enter city" value="">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input class="form-control" type="text" id="state" name="state"
                                            placeholder="Please enter state" value="">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="country" class="form-label">Country</label>
                                        <input class="form-control" type="text" id="country" name="country"
                                            placeholder="Please enter country" value="">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="zip" class="form-label">Zip code</label>
                                        <input class="form-control" type="text" id="zip" name="zip"
                                            placeholder="Please enter ZIP code" value="">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label" for="client_note">Note</label>
                                        <textarea class="form-control description" rows="5" name="client_note" placeholder="Please Enter Note"></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="profile" class="form-label">Profile picture</label>
                                        <input class="form-control" type="file" id="profile" name="profile">
                                        <p class="text-muted mt-2">Allowed JPG or PNG.</p>
                                    </div>
                                    {{-- <div class="col-md-6 mb-3" id="statusDiv">
                                        <label class="form-label" for="">Status (<small
                                                class="text-muted mt-2">If Deactivated, the Client Won't Be Able to Log
                                                In to Their Account</small>)</label>
                                        <div class="">
                                            <div class="btn-group btn-group d-flex justify-content-center"
                                                role="group" aria-label="Basic radio toggle button group">
                                                <input type="radio" class="btn-check" id="client_active"
                                                    name="status" value="1">
                                                <label class="btn btn-outline-primary"
                                                    for="client_active">Active</label>
                                                <input type="radio" class="btn-check" id="client_deactive"
                                                    name="status" value="0" checked>
                                                <label class="btn btn-outline-primary"
                                                    for="client_deactive">Deactive</label>
                                            </div>
                                        </div>
                                    </div> --}}
                                    <div class="col-md-6">
                                        <label  class="form-label" for="file-upload">Choose files to upload:</label>
                                        <input  class="form-control" type="file" id="file-upload" name="files[]" multiple>
                                    </div>
                                    {{-- <div class="col-md-6 mb-3" id="requireEvDiv">
                                        <label class="form-label" for="">
                                            Require email verification? <i class='bx bx-info-circle text-primary'
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="If Yes is selected, client will receive a verification link via email. Please ensure that email settings are configured and operational."></i>
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
                                            id="submit_btn">Create</button>
                                        <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- <div class="modal fade" id="default_language_modal" tabindex="-1" aria-hidden="true">
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
                </div> --}}
                {{-- <div class="modal fade" id="leaveWorkspaceModal" tabindex="-1" aria-hidden="true">
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
                 --}}
                {{-- <div class="modal fade" id="create_language_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form class="modal-content form-submit-event"
                            action="#" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Create language </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <input type="hidden" name="_token" value="JGBo8EvfwiAkbRoazhW9eG7iXuVvWqkXMaNlMpRS"
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
                </div> --}}

                {{-- <div class="modal fade" id="edit_language_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-sm" role="document">
                        <form class="modal-content form-submit-event"
                            action="#" method="POST">
                            <input type="hidden" name="id" id="language_id">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Update language </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <input type="hidden" name="_token" value="JGBo8EvfwiAkbRoazhW9eG7iXuVvWqkXMaNlMpRS"
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
                </div> --}}

                {{-- <div class="modal fade" id="create_contract_type_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-sm" role="document">
                        <form class="modal-content form-submit-event"
                            action="#" method="POST">
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
                </div> --}}
                {{-- <div class="modal fade" id="edit_contract_type_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-sm" role="document">
                        <form class="modal-content form-submit-event"
                            action="#" method="POST">
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
                                        <input type="text" class="form-control" name="type" id="contract_type"
                                            placeholder="Please enter contract type" />
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
                </div> --}}

                {{-- <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
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
                                    action="x" method="POST">
                                    <input type="hidden" name="_token"
                                        value="JGBo8EvfwiAkbRoazhW9eG7iXuVvWqkXMaNlMpRS" autocomplete="off"> <input
                                        type="hidden" name="_method" value="DELETE"> <button type="submit"
                                        class="btn btn-danger">Yes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div> --}}

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


<script>
    if (document.getElementById("system-update-dropzone")) {
        var is_error = false;
        if (!$("#system-update").hasClass("dropzone")) {
            var systemDropzone = new Dropzone("#system-update-dropzone", {
                url: $("#system-update").attr("action"),
                paramName: "update_file",
                autoProcessQueue: false,
                parallelUploads: 1,
                maxFiles: 1,
                acceptedFiles: ".zip",
                timeout: 360000,
                autoDiscover: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"), // Pass the CSRF token as a header
                },
                addRemoveLinks: true,
                dictRemoveFile: "x",
                dictMaxFilesExceeded: "Only 1 file can be uploaded at a time",
                dictResponseError: "Error",
                uploadMultiple: true,
                dictDefaultMessage: '<p><input type="button" value="Select Files" class="btn btn-primary" /><br> or <br> Drag & Drop System Update / Installable / Plugin\'s .zip file Here</p>',
            });
            systemDropzone.on("addedfile", function(file) {
                var i = 0;
                if (this.files.length) {
                    var _i, _len;
                    for (_i = 0, _len = this.files.length; _i < _len - 1; _i++) {
                        if (
                            this.files[_i].name === file.name &&
                            this.files[_i].size === file.size &&
                            this.files[_i].lastModifiedDate.toString() ===
                            file.lastModifiedDate.toString()
                        ) {
                            this.removeFile(file);
                            i++;
                        }
                    }
                }
            });
            systemDropzone.on("error", function(file, response) {
                console.log(response);
            });
            systemDropzone.on("sending", function(file, xhr, formData) {
                formData.append("flash_message", 1);
                xhr.onreadystatechange = function(response) {
                    console.log(response);
                    // return;
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                };
            });
            $("#system_update_btn").on("click", function(e) {
                e.preventDefault();
                if (is_error == false) {
                    if (systemDropzone.files.length === 0) {
                        // Show toast message if no file is selected
                        toastr.error("Please select a file to upload.");
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                    $("#system_update_btn").attr('disabled', true).text(label_please_wait);
                    systemDropzone.processQueue();
                }
            });
        }
    }

    // console.log(document.getElementById("#media-upload"));
    if (document.getElementById("media-upload-dropzone")) {
        var is_error = false;
        var mediaDropzone = new Dropzone("#media-upload-dropzone", {
            url: $("#media-upload").attr("action"),
            paramName: "media_files",
            autoProcessQueue: false,
            timeout: 360000,
            autoDiscover: false,

            addRemoveLinks: true,
            dictRemoveFile: "x",
            dictResponseError: "Error",
            uploadMultiple: true,
            dictDefaultMessage: '<p><input type="button" value="Select" class="btn btn-primary" /><br> or <br> Drag & Drop Files Here</p>',
        });
        mediaDropzone.on("addedfile", function(file) {
            console.log(file);
            var i = 0;
            if (this.files.length) {
                var _i, _len;
                for (_i = 0, _len = this.files.length; _i < _len - 1; _i++) {
                    if (
                        this.files[_i].name === file.name &&
                        this.files[_i].size === file.size &&
                        this.files[_i].lastModifiedDate.toString() ===
                        file.lastModifiedDate.toString()
                    ) {
                        this.removeFile(file);
                        i++;
                    }
                }
            }
        });
        mediaDropzone.on("error", function(file, response) {
            console.log(response);
            return;
        });
        mediaDropzone.on("sending", function(file, xhr, formData) {
            var id = $("#media_type_id").val();
            var token = $("#media_type_token").val();
            formData.append("flash_message", 1);
            formData.append("id", id);
            formData.append("_token", token);
            xhr.onreadystatechange = function(response) {
                setTimeout(function() {
                    location.reload();
                }, 2000);
            };
        });
        $("#upload_media_btn").on("click", function(e) {
            e.preventDefault();
            console.log(mediaDropzone.getQueuedFiles());
            console.log(is_error);
            if (mediaDropzone.getQueuedFiles().length > 0) {
                if (is_error == false) {
                    $("#upload_media_btn").attr('disabled', true).text(label_please_wait);
                    mediaDropzone.processQueue();
                    return;
                }
            } else {
                toastr.error('No file(s) chosen.');
            }
        });
    }
</script>
</body>

</html>
