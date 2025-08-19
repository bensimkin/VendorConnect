<script src="{{ asset('front-end/assets/js/time-tracker.js') }}"></script>
{{-- <div onclick="toggleChatIframe()">
    <span>
        <a href="javascript:void(0);">
            <img src="https://app.inth.pk/storage/chat-icon.png" class="chat-img" alt="Chat">
        </a>
    </span>
</div> --}}
<div id="chatIframeContainer" class="chat-iframe-container">
    <iframe src="/chat" frameborder="0" class="chat-iframe"></iframe>
</div>
<!-- / Layout wrapper -->
<!-- Timer image -->
{{-- <div onclick="open_timer_section()">
    <span>
        <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="left"
            data-bs-original-title="Time tracker">
            <img src="https://app.inth.pk/storage/94150-clock.png" class="timer-img" id="timer-image" alt="Timer"
                data-bs-toggle="modal" data-bs-target="#timerModal">
        </a>
    </span>
</div> --}}
<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{ asset('front-end/assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('front-end/assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('front-end/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('front-end/assets/vendor/js/menu.js') }}"></script>
<!-- endbuild -->
<!-- Main JS -->
<script src="{{ asset('front-end/assets/js/main.js') }}"></script>
<script src="{{ asset('front-end/assets/js/ui-toasts.js') }}"></script>
<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="{{ asset('front-end/assets/js/buttons.js') }}"></script>
<!-- select2 js -->
<script src="{{ asset('front-end/assets/js/select2.min.js') }}"></script>
<!-- Bootstrap-table -->
<script src="{{ asset('front-end/assets/js/bootstrap-table/bootstrap-table.min.js') }}"></script>
<script src="{{ asset('front-end/assets/js/bootstrap-table/bootstrap-table.min.js') }}"></script>
<script src="{{ asset('front-end/assets/js/bootstrap-table/bootstrap-table-export.min.js') }}"></script>
<script src="{{ asset('front-end/assets/js/bootstrap-table/tableExport.min.js') }}"></script>
<!-- Dragula -->
<script src="{{ asset('front-end/assets/js/dragula.min.js') }}"></script>
<script src="{{ asset('front-end/assets/js/popper.js') }}"></script>
<!-- Toastr -->
<script src="{{ asset('front-end/assets/js/toastr.min.js') }}"></script>
<script src="{{ asset('front-end/assets/js/tinymce.min.js') }}"></script>
<script src="{{ asset('front-end/assets/js/tinymce-jquery.min.js') }}"></script>
<!-- Date picker -->
<script src="{{ asset('front-end/assets/js/moment.min.js') }}"></script>
<script src="{{ asset('front-end/assets/js/daterangepicker.js') }}"></script>
<script src="{{ asset('front-end/assets/lightbox/lightbox.min.js') }}"></script>
<script src="{{ asset('front-end/assets/js/dropzone.min.js') }}"></script>
<script src="{{ asset('front-end/assets/js/extended-ui-perfect-scrollbar.js') }}"></script>
<script src="{{ asset('front-end/assets/js/fullcalendar/core/main.js') }}"></script>
<script src="{{ asset('front-end/assets/js/fullcalendar/interaction/main.js') }}"></script>
<script src="{{ asset('front-end/assets/js/fullcalendar/daygrid/main.js') }}"></script>
<script src="{{ asset('front-end/assets/js/fullcalendar/list/main.js') }}"></script>
<script src="{{ asset('front-end/assets/js/fullcalendar/google-calendar/main.js') }}"></script>

<script>
    var authUserId = '3';
</script>
<!-- Custom js -->
<script>
    var csrf_token = '666JQNyxLLoGtnybmDx7pPnh3iRzQLGSexD7GRaA';
    var js_date_format = 'DD-MM-YYYY';
</script>
<script src="{{ asset('front-end/assets/js/custom.js') }}"></script>
<script>
    // toastr.options = {
    //     "positionClass": "toast-top-right",
    //     "showDuration": "300",
    //     "hideDuration": "1000",
    //     "timeOut": "5000",
    //     "progressBar": true,
    //     "extendedTimeOut": "1000",
    //     "closeButton": true
    // };
    // toastr.error('You must be participant in atleast one workspace', 'Error');
</script>
