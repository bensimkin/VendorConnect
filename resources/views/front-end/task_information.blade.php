@include('layout.header')
<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('layout.sidebar')
        <!-- Menu -->
        <!-- Layout container -->
        <div class="layout-page">
            @include('layout.navbar')
            <!-- Navbar -->
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <div class="align-items-center d-flex justify-content-between m-4">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('dashboard.view') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('task.view') }}">Tasks</a>
                                    </li>
                                    <li class="breadcrumb-item active">
                                        {{ $title }}
                                    </li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            {{-- <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_status_modal">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                    data-bs-placement="right" data-bs-original-title="Create status">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </a> --}}
                        </div>
                        <div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h2 class="fw-bold">{{ $tasksInfo->title }}
                                                <a href="#" class="mx-2" target="_blank">
                                                </a>
                                            </h2>
                                            <a href="#" style="color: rgb(142, 22, 22)">Time Left: <span
                                                    id="countdown"></span></a>
                                                    <script>
                                                        // Set the end date (ISO format YYYY-MM-DD for better compatibility)
                                                        var endDate = new Date("{{ \Carbon\Carbon::parse($tasksInfo->end_date)->format('Y-m-d') }}T23:59:59").getTime();

                                                        // Update the countdown every 1 second
                                                        var countdownInterval = setInterval(function() {
                                                            // Get today's date and time
                                                            var now = new Date().getTime();

                                                            // Find the distance between now and the end date
                                                            var distance = endDate - now;

                                                            // Time calculations for days, hours, minutes, and seconds
                                                            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                                            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                                                            // Display the result in the element with id="countdown"
                                                            document.getElementById("countdown").innerHTML = days + "d " + hours + "h " +
                                                                minutes + "m " + seconds + "s ";

                                                            // If the countdown is finished, display some text
                                                            if (distance < 0) {
                                                                clearInterval(countdownInterval);
                                                                document.getElementById("countdown").innerHTML = "EXPIRED";
                                                            }
                                                        }, 1000);
                                                    </script>

                                            <div class="row">
                                                <div class="col-md-6 mt-3 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label" for="start_date">Tasker</label>
                                                            <ul
                                                                class="list-unstyled users-list m-0 avatar-group d-flex align-items-center flex-wrap">
                                                                @if ($tasksInfo->taskUsers->isNotEmpty())
                                                                    @foreach ($tasksInfo->taskUsers as $users)
                                                                        <li class="avatar avatar-sm pull-up"
                                                                            title="{{ $users->first_name }} {{ $users->last_name }}">
                                                                            {{-- <a href="/master-panel/users/profile/3" --}}
                                                                            <a href="#">
                                                                                <img src="{{ asset('storage/' . $users->photo) }}"
                                                                                    class="rounded-circle"
                                                                                    alt="{{ $users->first_name }} {{ $users->last_name }}">
                                                                            </a>
                                                                        </li>
                                                                        <small
                                                                            style="margin-left: 10px;font-weight:bold">{{ $users->first_name }}
                                                                            {{ $users->last_name }}</small>
                                                                    @endforeach
                                                                    {{-- <a href="javascript:void(0)"
                                                                        class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-task update-users-clients"
                                                                        data-id="1"><span class="bx bx-edit"></span></a> --}}
                                                                @else
                                                                    <p><span class="badge bg-primary">Not
                                                                            Assigned</span></p>
                                                                @endif

                                                            </ul>

                                                        </div>
                                                        <div class="col-md-6">

                                                            <label class="form-label" for="start_date">Requester</label>
                                                            <ul
                                                                class="list-unstyled users-list m-0 avatar-group d-flex align-items-center flex-wrap">


                                                                <li class="avatar avatar-sm pull-up"
                                                                    title="{{ $requester->first_name }} {{ $requester->last_name }}">
                                                                    {{-- <a href="/master-panel/users/profile/3" --}}
                                                                    <a href="#">
                                                                        <img src="{{ asset('storage/' . $requester->photo) }}"
                                                                            class="rounded-circle"
                                                                            alt="{{ $requester->first_name }} {{ $requester->last_name }}">
                                                                    </a>
                                                                </li>
                                                                <small
                                                                    style="margin-left: 10px;font-weight:bold">{{ $requester->first_name }}
                                                                    {{ $requester->last_name }}</small>

                                                                {{-- <a href="javascript:void(0)"
                                                                        class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-task update-users-clients"
                                                                        data-id="1"><span class="bx bx-edit"></span></a>
                                                                    --}}
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6  mt-3 mb-3">
                                                    <label class="form-label"
                                                        for="end_date"><?= get_label('clients', 'Clients') ?></label>
                                                    <?php
                                                        if (count($tasksInfo->taskClients) > 0) { ?>
                                                    <ul
                                                        class="list-unstyled users-list m-0 avatar-group d-flex align-items-center flex-wrap">
                                                        @foreach ($tasksInfo->taskClients as $clients)
                                                            <a  @if ($current_User->hasRole('Admin') || $current_User->hasRole('Requester')) href="{{ route('clients.edit', ['id' => $clients->id]) }}"  @else href="#" @endif
                                                                style="display: flex;align-items:center">
                                                                <li class="avatar avatar-sm pull-up"
                                                                    title="{{ $clients->first_name }} {{ $clients->last_name }}">
                                                                    <img src="{{ asset('storage/' . $clients->photo) }}"
                                                                        class="rounded-circle">
                                                                </li>
                                                                <small
                                                                    style="margin-left: 10px;font-weight:bold;text-decoration:none;">{{ $clients->first_name }}
                                                                    {{ $clients->last_name }}</small>
                                                            </a>
                                                        @endforeach
                                                    </ul>
                                                    <?php } else { ?>
                                                    <p><span
                                                            class="badge bg-primary"><?= get_label('not_assigned', 'Not assigned') ?></span>
                                                    </p>
                                                    <?php } ?>
                                                </div>
                                                <div class="row">
                                                    {{-- @if ($current_User->hasRole('Admin') || $current_User->hasRole('Requester') || $current_User->hasRole('Tasker'))
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Status</label>
                                                            <div class="input-group">
                                                                <select
                                                                    class="form-select form-select-sm select-bg-label-secondary"
                                                                    id="statusSelect" data-id="1"
                                                                    data-original-status-id="1"
                                                                    data-original-color-class="select-bg-label-secondary"
                                                                    data-type="task">
                                                                    <option value="1"
                                                                        class="badge bg-label-secondary" selected>
                                                                        {{ $tasksInfo->status->title }}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    @else --}}
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Status</label>
                                                        <form action="{{ route('tasks.update_status') }}"
                                                            method="POST" class="form-change-event">
                                                            <div class="input-group">

                                                                @csrf
                                                                <input type="hidden" name="task_id"
                                                                    value="{{ $tasksInfo->id }}">

                                                                <select
                                                                    class="form-select form-select-sm select-bg-label-secondary"
                                                                    id="statusSelect" data-id="1"
                                                                    data-original-status-id="1"
                                                                    data-original-color-class="select-bg-label-secondary"
                                                                    data-type="task" name="newStatus"
                                                                    {{ $is_expire == 'EXPIRED' && $tasksInfo->close_deadline == 1 ? 'disabled' : '' }}>
                                                                    @foreach ($statuses as $status)
                                                                        @php
                                                                            $selected = '';
                                                                        @endphp
                                                                        @if ($status->title == $tasksInfo->status->title)
                                                                            @php
                                                                                $selected = 'selected';
                                                                            @endphp
                                                                        @endif
                                                                        @if ($tasksInfo->close_deadline == 1 && $is_expire == 'EXPIRED')
                                                                            @if ($status->title == 'Rejected')
                                                                                @php
                                                                                    $selected = 'selected';
                                                                                @endphp
                                                                            @endif
                                                                        @endif
                                                                        <option value="{{ $status->id }}"
                                                                            class="badge bg-label-secondary"
                                                                            {{ $selected }}>
                                                                            {{ $status->title }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>

                                                            </div>
                                                        </form>

                                                    </div>
                                                    {{-- @endif --}}
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Task Type Name</label>
                                                        <div class="input-group">
                                                            <select
                                                                class="form-select form-select-sm select-bg-label-secondary"
                                                                id="statusSelect" data-id="1"
                                                                data-original-status-id="1"
                                                                data-original-color-class="select-bg-label-secondary"
                                                                data-type="task"
                                                                {{ $is_expire == 'EXPIRED' ? 'disabled' : '' }}>
                                                                <option value="1" class="badge bg-label-secondary"
                                                                    selected>
                                                                    {{ $tasksInfo->taskType->task_type }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="prioritySelect" class="form-label">Priority</label>
                                                    <div class="input-group">
                                                        <select
                                                            class="form-select form-select-sm select-bg-label-secondary"
                                                            id="prioritySelect" data-id="1"
                                                            data-original-priority-id=""
                                                            data-original-color-class="select-bg-label-secondary"
                                                            data-type="task"
                                                            {{ $is_expire == 'EXPIRED' ? 'disabled' : '' }}>
                                                            <option value="1" class="badge bg-label-danger">
                                                                {{ $tasksInfo->priority->title }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-0">
                            <div class="card  mb-4">
                                <div class="card-body">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="mb-3">
                                                <label class="form-label" for="description">Description</label>
                                                <div class="input-group input-group-merge">
                                                    <div class="form-control" rows="5" readonly>
                                                        <p>{!! $tasksInfo->description !!}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label" for="start_date">Starts at</label>
                                                <div class="input-group input-group-merge">
                                                    <input type="text" name="start_date" class="form-control"
                                                        placeholder=""
                                                        value="{{ \Carbon\Carbon::parse($tasksInfo->start_date)->format('m-d-Y') }}"
                                                        readonly />
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label" for="start_date">End Date</label>
                                                <div class="input-group input-group-merge">
                                                    <input type="text" name="start_date" class="form-control"
                                                        placeholder=""
                                                        value="{{ \Carbon\Carbon::parse($tasksInfo->end_date)->format('m-d-Y') }}"
                                                        readonly />
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label" for="due_date">close by deadline <span
                                                        class="asterisk">*</span></label>
                                                @if ($tasksInfo->close_deadline == 1)
                                                    <label for=""
                                                        style="margin-left: 20px;font-weight:bold;">Deadline Closed
                                                        before Deadline Date and Time</label>
                                                @endif

                                                @if ($current_User->hasRole('Admin') || $current_User->hasRole('Requester'))
                                                    <form action="{{ route('tasks.update_deadline') }}"
                                                        method="POST" class="form-change-event">
                                                        <div class="input-group">
                                                            {{-- {{ $is_expire == 'EXPIRED' ? 'disabled' : '' }} --}}
                                                            @csrf
                                                            <input type="hidden" name="task_id"
                                                                value="{{ $tasksInfo->id }}">
                                                            <select name="close_deadline" id="close_deadline_update"
                                                                class="form-select text-capitalize js-example-basic-multiple"
                                                                >
                                                                @php
                                                                    $zselected = '';
                                                                    $oselected = '';
                                                                @endphp
                                                                @if ($tasksInfo->close_deadline == 0)
                                                                    @php
                                                                        $zselected = 'selected';
                                                                    @endphp
                                                                @endif
                                                                @if ($tasksInfo->close_deadline == 1)
                                                                    @php
                                                                        $oselected = 'selected';
                                                                    @endphp
                                                                @endif
                                                                <option value="0" {{ $zselected }}>No</option>
                                                                <option value="1" {{ $oselected }}>yes
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" name="start_date" class="form-control"
                                                            placeholder=""
                                                            value="{{ $tasksInfo->close_deadline == 0 ? 'No' : 'Yes' }}"
                                                            readonly />
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3">
                                                <label class="form-label" for="description">Note</label>
                                                <div class="input-group input-group-merge">
                                                    <div class="form-control" rows="5" readonly>
                                                        <p>{!! $tasksInfo->description !!}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="media_type_id" value="{{ $tasksInfo->id }}">
                                <input type="hidden" id="media_type_token" value="{{ csrf_token() }}">
                            </div>
                            <hr class="my-0">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h2 class="fw-bold">Brief
                                                <a href="#" class="mx-2" target="_blank">
                                                </a>
                                            </h2>
                                            @if ($tasksInfo->close_deadline == 1 && $is_expire == 'EXPIRED')
                                                <h4>Deadline Closed</h4>
                                            @else
                                                @forelse ($briefQuestions as $briefQuestionCollection)

                                                    @foreach ($briefQuestionCollection as $briefQuestion)
                                                        <div class="col-md-12 mb-4">
                                                            <div class="mb-3">
                                                                <label class="form-label"
                                                                    for="description">{{ $briefQuestion->question_type == 1 ? 'Brief Information' : 'Brief Information' }}</label>
                                                                <div class="input-group input-group-merge">
                                                                    <div class="form-control" rows="5" readonly>
                                                                        <p>{!! $briefQuestion->question_text !!}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12 mb-4">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="description">
                                                                    Answer</label>
                                                                {{-- <div class="input-group input-group-merge"> --}}
                                                                <form class="form-submit-event"
                                                                    action="{{ route('task.question_answer') }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <input type="hidden" name="task_id"
                                                                        value="{{ $tasksInfo->id }}">
                                                                    <input type="hidden" name="question_id"
                                                                        value="{{ $briefQuestion->id }}">

                                                                    @php
                                                                        $questionAnswer = \App\Models\QuestionAnswered::where(
                                                                            'question_id',
                                                                            $briefQuestion->id,
                                                                        )
                                                                            ->where('task_id', $tasksInfo->id)
                                                                            ->first();
                                                                        if(!$questionAnswer)
                                                                        {
                                                                            $questionAnswer = \App\Models\QuestionAnswered::where(
                                                                            'question_id',
                                                                            $briefQuestion->id,
                                                                        )
                                                                            ->where('task_id', 0)
                                                                            ->first();
                                                                        }
                                                                    @endphp

                                                                        @if ($current_User->hasRole('Tasker'))
                                                                            <div
                                                                                style="border: 1px solid #d9dee3; padding:20px;border-radius:8px">
                                                                                @if ($questionAnswer)
                                                                                    {!! $questionAnswer->question_answer !!}
                                                                                @endif
                                                                            </div>
                                                                        @endif

                                                                        @if ($current_User->hasRole('Admin') || $current_User->hasRole('Requester'))
                                                                            <textarea class="form-control description" rows="5" name="question_answer"
                                                                                placeholder="Please enter Answer">
                                                                                @if ($questionAnswer)
                                                                                    {!! $questionAnswer->question_answer !!}
                                                                                @endif

                                                                            </textarea>
                                                                        @endif



                                                                    @php
                                                                        $answerBy = '';
                                                                    @endphp
                                                                    @if ($questionAnswer)
                                                                        @php
                                                                            $answerBy = \App\Models\User::where(
                                                                                'id',
                                                                                $questionAnswer->answer_by,
                                                                            )->first();
                                                                        @endphp
                                                                    @endif
                                                                    <input name="check_brief" type="checkbox" class="d-none"
                                                                        @if ($questionAnswer) @if ($questionAnswer->check_brief == 1) checked @endif
                                                                        @endif>
                                                                    <label class="form-label d-none" for="description">
                                                                        Done
                                                                    </label>
                                                                    <small class="mt-3 d-inline-block  d-none">Answered by:
                                                                        @if ($answerBy)
                                                                            <em>{{ $answerBy->first_name . ' ' . $answerBy->last_name }}</em>
                                                                        @else
                                                                            <em>-</em>
                                                                        @endif
                                                                    </small>
                                                                    <div class="submit__btn text-end">
                                                                        @if ($current_User->hasRole('Admin') || $current_User->hasRole('Requester'))
                                                                            <button type="submit"
                                                                                class="btn btn-primary mt-3"
                                                                                id="submit_btn">Send</button>
                                                                        @endif
                                                                    </div>
                                                                </form>
                                                                {{-- </div> --}}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @empty
                                                    <div class="error_found text-center">
                                                        <img src="{{ asset('front-end/assets/image/not-found.jpg') }}"
                                                            alt="" loading="lazy">
                                                        <span>Brief Question Not found</span>
                                                    </div>
                                                    {{-- <h1>Brief Question Not found</h1> --}}
                                                @endforelse

                                                @foreach ($briefChecklists as $briefCheckListCollection)
                                                    @foreach ($briefCheckListCollection as $briefchecklist)
                                                        <form class="form-submit-event"
                                                            action="{{ route('task.checklist_answer') }}"
                                                            method="POST">
                                                            @csrf
                                                            <input type="hidden" name="checklist_id"
                                                                value="{{ $briefchecklist->id }}">
                                                            <input type="hidden" name="task_id"
                                                                value="{{ $tasksInfo->id }}">
                                                            <div class="col-md-12 mb-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label"
                                                                        for="description">Checklist</label>
                                                                    <div class="input-group input-group-merge">
                                                                        <div class="form-control" rows="5"
                                                                            readonly>
                                                                            @php
                                                                                $checklistAnswer = \App\Models\ChecklistAnswered::where(
                                                                                    'checklist_id',
                                                                                    $briefchecklist->id,
                                                                                )
                                                                                    ->where('task_id', $tasksInfo->id)
                                                                                    ->first();

                                                                                if ($checklistAnswer) {
                                                                                    if (
                                                                                        is_string(
                                                                                            $checklistAnswer->checklist_answer,
                                                                                        )
                                                                                    ) {
                                                                                        // If it's a string, try to decode it from JSON
                                                                                        $checlistAnswered = json_decode(
                                                                                            $checklistAnswer->checklist_answer,
                                                                                            true,
                                                                                        );
                                                                                    } else {
                                                                                        $checlistAnswered =
                                                                                            $checklistAnswer->checklist_answer;
                                                                                    }
                                                                                } else {
                                                                                    $checlistAnswered = [];
                                                                                }

                                                                                // var_dump($checlistAnswered);

                                                                            @endphp
                                                                            @foreach (json_decode($briefchecklist->checklist) as $check)
                                                                                @php
                                                                                    $class = '';
                                                                                @endphp
                                                                                @if ($current_User->hasRole('Admin') || $current_User->hasRole('Requester'))
                                                                                    @php
                                                                                        $class = 'form-click-event';
                                                                                    @endphp
                                                                                @endif
                                                                                <input type="checkbox"
                                                                                    class="{{ $class }}"
                                                                                    name="check_brief[]"
                                                                                    value="{{ $check }}"
                                                                                    {{ in_array($check, $checlistAnswered) ? 'checked' : '' }}>
                                                                                {{ $check }}
                                                                                <br>
                                                                            @endforeach
                                                                            <div class="submit__btn text-end">
                                                                                @if ($current_User->hasRole('Admin') || $current_User->hasRole('Requester'))
                                                                                    {{-- <button type="submit"
                                                                            class="btn btn-primary"
                                                                            id="submit_btn">Send</button> --}}
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    @endforeach
                                                @endforeach
                                            @endif



                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="nav-align-top mt-2">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <button type="button" style="padding: 10px"
                                            class="nav-link @if ($tasksInfo->close_deadline == 1 && $is_expire == 'EXPIRED') active d-none @endif"
                                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-media"
                                            aria-controls="navs-top-media">
                                            <i class="menu-icon tf-icons bx bx-image-alt text-success"></i>Media
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button type="button" style="padding: 10px"
                                            class="nav-link @if ($tasksInfo->close_deadline == '1') active @endif"
                                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-messages"
                                            aria-controls="navs-top-messages">
                                            <i class="menu-icon tf-icons bx bx-message text-info"></i>Messages
                                        </button>
                                    </li>

                                    {{-- <li class="nav-item">
                                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                            data-bs-target="#navs-top-activity-log"
                                            aria-controls="navs-top-activity-log">
                                            <i class="menu-icon tf-icons bx bx-line-chart text-info"></i>Activity Log
                                        </button>
                                    </li> --}}
                                </ul>


                                <div class="tab-content">


                                    <div class="tab-pane fade @if ($tasksInfo->close_deadline == 1 && $is_expire == 'EXPIRED') hide @else active show @endif"
                                        id="navs-top-media" role="tabpanel">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div></div>
                                                <a href="javascript:void(0);" data-bs-toggle="modal"
                                                    data-bs-target="#add_media_modal">
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        data-bs-toggle="tooltip" data-bs-placement="left"
                                                        data-bs-original-title="Add Media">
                                                        <i class="bx bx-plus"></i>
                                                    </button>
                                                </a>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <input type="hidden" id="data_type" value="task-media">
                                                <input type="hidden" id="data_table" value="task_media_table">
                                                {{-- <input type="hidden" id="save_column_visibility"> --}}
                                                <table id="task_media_table" data-toggle="table"
                                                    data-loading-template="loadingTemplate"
                                                    data-url="/tasks/get-media/{{ $tasksInfo->id }}"
                                                    data-icons-prefix="bx" data-icons="icons"
                                                    data-show-refresh="true" data-total-field="total"
                                                    data-trim-on-search="false" data-data-field="rows"
                                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                                    data-side-pagination="server" data-show-columns="true"
                                                    data-pagination="true" data-sort-name="id" data-sort-order="desc"
                                                    data-mobile-responsive="true"
                                                    data-query-params="queryParamsTaskMedia">
                                                    <thead>
                                                        <tr>
                                                            <th data-checkbox="true"></th>
                                                            <th data-field="id" data-visible="true"
                                                                data-sortable="true">
                                                                ID</th>
                                                            <th data-field="file" data-visible="true"
                                                                data-sortable="true">File</th>
                                                            <th data-field="file_name" data-sortable="true"
                                                                data-visible="false">File name</th>
                                                            <th data-field="file_size" data-visible="true"
                                                                data-sortable="true">File size</th>
                                                            <th data-field="created_at" data-sortable="true"
                                                                data-visible="false">Created at</th>
                                                            <th data-field="updated_at" data-sortable="true"
                                                                data-visible="false">Updated at</th>
                                                            <th data-field="actions" data-visible="true"
                                                                data-sortable="false">Actions</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade @if ($tasksInfo->close_deadline == '1') active show @endif"
                                        id="navs-top-messages" role="tabpanel">
                                        <div class="col-md-12 text-end mb-2">
                                            <a href="javascript:void(0);" data-bs-toggle="modal"
                                                data-bs-target="#chat_btn">
                                                <button type="button" class="btn btn-sm btn-primary chat_btn"
                                                    data-bs-toggle="tooltip" data-bs-placement="right"
                                                    data-bs-original-title="Chat">
                                                    <i class="bx bx-plus"></i>
                                                </button>
                                            </a>
                                        </div>
                                        <div class="col-12">
                                            <div class="table-responsive text-nowrap">
                                                <input type="hidden" id="data_type" value="task-messages">
                                                <input type="hidden" id="data_table" value="ch_messages">
                                                <input type="hidden" id="type_id" value="1">
                                                {{-- <input type="hidden" id="save_column_visibility"> --}}
                                                <table id="ch_messages" data-toggle="table"
                                                    data-loading-template="loadingTemplate"
                                                    data-url="/tasks/get-message/{{ $tasksInfo->id }}"
                                                    data-icons-prefix="bx" data-icons="icons"
                                                    data-show-refresh="true" data-total-field="total"
                                                    data-trim-on-search="false" data-data-field="rows"
                                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                                    data-side-pagination="server" data-show-columns="true"
                                                    data-pagination="true" data-sort-name="id" data-sort-order="desc"
                                                    data-mobile-responsive="true" data-query-params="queryParams">
                                                    <thead>
                                                        <tr>
                                                            <th data-checkbox="true"></th>
                                                            <th data-field="id" data-visible="false"
                                                                data-sortable="true">ID</th>
                                                            <th data-field="sender_id" data-visible="true"
                                                                data-sortable="true">Sender</th>
                                                            <th data-field="message_text" data-visible="true"
                                                                data-sortable="true">Message</th>
                                                            <th data-field="sent_at" data-visible="true"
                                                                data-sortable="true">Sent at</th>
                                                            <th data-field="actions" data-visible="true">Actions</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="tab-pane fade" id="navs-top-activity-log" role="tabpanel">
                                        <div class="col-12">
                                            <div class="row mt-4">
                                                <div class="mb-3 col-md-4">
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" id="activity_log_between_date"
                                                            class="form-control" placeholder="Date between"
                                                            autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <select class="form-select" id="user_filter"
                                                        aria-label="Default select example">
                                                        <option value="">Select User</option>
                                                        <option value="3">ahsan ahmed</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <select class="form-select" id="client_filter"
                                                        aria-label="Default select example">
                                                        <option value="">Select client</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <select class="form-select" id="activity_filter"
                                                        aria-label="Default select example">
                                                        <option value="">Select activity</option>
                                                        <option value="created">Created</option>
                                                        <option value="updated">Updated</option>
                                                        <option value="duplicated">Duplicated</option>
                                                        <option value="uploaded">Uploaded</option>
                                                        <option value="deleted">Deleted</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <input type="hidden" id="activity_log_between_date_from">
                                                <input type="hidden" id="activity_log_between_date_to">
                                                <input type="hidden" id="data_type" value="activity-log">
                                                <input type="hidden" id="data_table" value="activity_log_table">
                                                <input type="hidden" id="type_id" value="1">
                                                <input type="hidden" id="save_column_visibility">
                                                <table id="activity_log_table" data-toggle="table"
                                                    data-loading-template="loadingTemplate"
                                                    data-url="/activity-log/list" data-icons-prefix="bx"
                                                    data-icons="icons" data-show-refresh="true"
                                                    data-total-field="total" data-trim-on-search="false"
                                                    data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]"
                                                    data-search="true" data-side-pagination="server"
                                                    data-show-columns="true" data-pagination="true"
                                                    data-sort-name="id" data-sort-order="desc"
                                                    data-mobile-responsive="true" data-query-params="queryParams">
                                                    <thead>
                                                        <tr>
                                                            <th data-checkbox="true"></th>
                                                            <th data-field="id" data-visible="true"
                                                                data-sortable="true">ID</th>
                                                            <th data-field="actor_id" data-visible="false"
                                                                data-sortable="true">Actor ID</th>
                                                            <th data-field="actor_name" data-visible="true"
                                                                data-sortable="true">Actor name</th>
                                                            <th data-field="actor_type" data-visible="false"
                                                                data-sortable="true">Actor type</th>
                                                            <th data-field="type_id" data-visible="false"
                                                                data-sortable="true">Type ID</th>
                                                            <th data-field="parent_type_id" data-visible="false"
                                                                data-sortable="true">Parent type ID</th>
                                                            <th data-field="activity" data-visible="true"
                                                                data-sortable="true">Activity</th>
                                                            <th data-field="type" data-visible="true"
                                                                data-sortable="true">Type</th>
                                                            <th data-field="parent_type" data-visible="false"
                                                                data-sortable="true">Parent type</th>
                                                            <th data-field="type_title" data-visible="true"
                                                                data-sortable="true">Type title</th>
                                                            <th data-field="parent_type_title" data-visible="false"
                                                                data-sortable="true">Parent type title</th>
                                                            <th data-field="message" data-visible="false"
                                                                data-sortable="true">Message</th>
                                                            <th data-field="created_at" data-visible="false"
                                                                data-sortable="true">Created at</th>
                                                            <th data-field="updated_at" data-visible="false"
                                                                data-sortable="true">Updated at</th>
                                                            <th data-field="actions" data-visible="true">Actions</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div> --}}
                        </div>
                    </div>
                    <div class="modal fade" id="add_media_modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <form class="modal-content form-horizontal" id="media-upload"
                                action="{{ route('tasks.upload_media') }}" method="POST"
                                enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel1">Add Media</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- <div class="alert alert-primary alert-dismissible" role="alert">Storage
                                            type is set as local storage, <a href="/settings/media-storage"
                                                target="_blank">Click here to change</a></div> -->
                                    <div class="dropzone dz-clickable" id="media-upload-dropzone">
                                    </div>
                                    <div class="form-group mt-4 text-center">
                                        <button class="btn btn-primary" id="upload_media_btn">Upload</button>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="form-group" id="error_box">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="modal fade" id="chat_modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form class="modal-content form-submit-event"
                                action="{{ route('tasks.upload_message') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $tasksInfo->id }}">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel1">Chat</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col">
                                            <label class="form-label" for="questiondescription">Write Your
                                                Message<span class="asterisk">*</span></label>
                                            <textarea class="form-control description" rows="5" name="message_text" placeholder="Please enter Message"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" id="submit_btn">Send</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <script>
                    var label_delete = 'Delete';
                </script>
                <script src="{{ asset('front-end/assets/js/pages/task-information.js') }}"></script>

                <div class="modal fade" id="chat_popup" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form class="modal-content form-submit-event" action="#" method="POST">


                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Create status </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <input type="hidden" name="_token" value="Bvk4UsJMuturq3MSs0clSy3lmOnajmgtjAB1AG7o"
                                autocomplete="off">
                            <div class="modal-body">

                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="nameBasic" class="form-label">Title <span
                                                class="asterisk">*</span></label>
                                        <input type="text" id="nameBasic" class="form-control" name="title"
                                            placeholder="Please enter title" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="nameBasic" class="form-label">COLOR <span
                                                class="asterisk">*</span></label>
                                        <select class="form-select" id="color" name="color">
                                            <option class="badge bg-label-primary" value="primary">
                                                Primary </option>
                                            <option class="badge bg-label-secondary" value="secondary">
                                                Secondary</option>
                                            <option class="badge bg-label-success" value="success">
                                                Success</option>
                                            <option class="badge bg-label-danger" value="danger">
                                                Danger</option>
                                            <option class="badge bg-label-warning" value="warning">
                                                Warning</option>
                                            <option class="badge bg-label-info" value="info">Info </option>
                                            <option class="badge bg-label-dark" value="dark">Dark </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Roles Can Set the Status <i
                                                class='bx bx-info-circle text-primary' data-bs-toggle="tooltip"
                                                data-bs-offset="0,4" data-bs-placement="top" title=""
                                                data-bs-original-title="Including Admin and Roles with All Data Access Permission, Users/Clients Under Selected Role(s) Will Have Permission to Set This Status."></i></label>
                                        <div class="input-group">
                                            <select class="form-control js-example-basic-multiple" name="role_ids[]"
                                                multiple="multiple" data-placeholder="Type to search">
                                                <option value="21">client</option>
                                                <option value="9">member</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Close</label>
                                </button>
                                <button type="submit" class="btn btn-primary"
                                    id="submit_btn">Create</label></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel2">Warning</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    '</button>
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


    $(document).on('change', '.form-click-event', function(e) {
        // e.preventDefault();
        var form = $(this).closest('.form-submit-event')[0];

        // Create FormData object
        var formData = new FormData(form);
        console.log(form); // Check if this logs the correct form element
        var currentForm = $(this);
        var submit_btn = $(this).find('#submit_btn');
        var btn_html = submit_btn.html();
        var btn_val = submit_btn.val();
        var redirect_url = currentForm.find('input[name="redirect_url"]').val();
        redirect_url = (typeof redirect_url !== 'undefined' && redirect_url) ? redirect_url : '';
        var button_text = (btn_html != '' || btn_html != 'undefined') ? btn_html : btn_val;
        var tableInput = currentForm.find('input[name="table"]');
        var tableID = tableInput.length ? tableInput.val() : 'table';
        $.ajax({
            url: $(form).attr('action'), // Get the action URL from the form
            type: $(form).attr('method'), // Get the method from the form
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').attr('value')
            },
            beforeSend: function() {
                submit_btn.html(label_please_wait);
                submit_btn.attr('disabled', true);
            },
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(result) {
                submit_btn.html(button_text);
                submit_btn.attr('disabled', false);
                if (result['error'] == true) {
                    toastr.error(result['message']);
                } else {
                    if ($('.empty-state').length > 0) {
                        if (result.hasOwnProperty('message')) {
                            toastr.success(result['message']);
                            // Show toastr for 3 seconds before reloading or redirecting
                            setTimeout(handleRedirection, 3000);
                        } else {
                            handleRedirection();
                        }
                    } else {
                        if (currentForm.find('input[name="dnr"]').length > 0) {
                            var modalWithClass = $('.modal.fade.show');
                            if (modalWithClass.length > 0) {
                                var idOfModal = modalWithClass.attr('id');
                                $('#' + idOfModal).modal('hide');
                                $('#' + tableID).bootstrapTable('refresh');
                                currentForm[0].reset();
                                var partialLeaveCheckbox = $('#partialLeave');
                                if (partialLeaveCheckbox.length) {
                                    partialLeaveCheckbox.trigger('change');
                                }
                                resetDateFields(currentForm);
                                if (idOfModal == 'create_status_modal') {
                                    var dropdownSelector = modalWithClass.find(
                                        'select[name="status_id"]');
                                    if (dropdownSelector.length) {
                                        var newItem = result.status;
                                        var newOption = $('<option></option>')
                                            .attr('value', newItem.id)
                                            .attr('data-color', newItem.color)
                                            .attr('selected', true)
                                            .text(newItem.title + ' (' + newItem.color + ')');
                                        $(dropdownSelector).append(newOption);
                                        var openModalId = dropdownSelector.closest(
                                            '.modal.fade.show').attr('id');
                                        // List of all possible modal IDs
                                        var modalIds = ['#create_project_modal',
                                            '#edit_project_modal', '#create_task_modal',
                                            '#edit_task_modal'
                                        ];
                                        // Iterate through each modal ID
                                        modalIds.forEach(function(modalId) {
                                            // If the modal ID is not the open one
                                            if (modalId !== '#' + openModalId) {
                                                // Find the select element within the modal
                                                var otherModalSelector = $(modalId).find(
                                                    'select[name="status_id"]');
                                                // Create a new option without 'selected' attribute
                                                var otherOption = $('<option></option>')
                                                    .attr('value', newItem.id)
                                                    .attr('data-color', newItem.color)
                                                    .text(newItem.title + ' (' + newItem
                                                        .color + ')');
                                                // Append the option to the select element in the modal
                                                otherModalSelector.append(otherOption);
                                            }
                                        });
                                    }
                                }
                                if (idOfModal == 'create_priority_modal') {
                                    var dropdownSelector = modalWithClass.find(
                                        'select[name="priority_id"]');
                                    if (dropdownSelector.length) {
                                        var newItem = result.priority;
                                        var newOption = $('<option></option>')
                                            .attr('value', newItem.id)
                                            .attr('class', 'badge bg-label-' + newItem.color)
                                            .attr('selected', true)
                                            .text(newItem.title + ' (' + newItem.color + ')');
                                        $(dropdownSelector).append(newOption);
                                        var openModalId = dropdownSelector.closest(
                                            '.modal.fade.show').attr('id');
                                        // List of all possible modal IDs
                                        var modalIds = ['#create_project_modal',
                                            '#edit_project_modal', '#create_task_modal',
                                            '#edit_task_modal'
                                        ];
                                        // Iterate through each modal ID
                                        modalIds.forEach(function(modalId) {
                                            // If the modal ID is not the open one
                                            if (modalId !== '#' + openModalId) {
                                                // Find the select element within the modal
                                                var otherModalSelector = $(modalId).find(
                                                    'select[name="priority_id"]');
                                                // Create a new option without 'selected' attribute
                                                var otherOption = $('<option></option>')
                                                    .attr('value', newItem.id)
                                                    .attr('class', 'badge bg-label-' +
                                                        newItem.color)
                                                    .text(newItem.title + ' (' + newItem
                                                        .color + ')');
                                                // Append the option to the select element in the modal
                                                otherModalSelector.append(otherOption);
                                            }
                                        });
                                    }
                                }
                                if (idOfModal == 'create_tag_modal') {
                                    var dropdownSelector = modalWithClass.find(
                                        'select[name="tag_ids[]"]');
                                    if (dropdownSelector.length) {
                                        var newItem = result.tag;
                                        var newOption = $('<option></option>')
                                            .attr('value', newItem.id)
                                            .attr('data-color', newItem.color)
                                            .attr('selected', true)
                                            .text(newItem.title);
                                        $(dropdownSelector).append(newOption);
                                        $(dropdownSelector).trigger('change');
                                        var openModalId = dropdownSelector.closest(
                                            '.modal.fade.show').attr('id');
                                        // List of all possible modal IDs
                                        var modalIds = ['#create_project_modal',
                                            '#edit_project_modal'
                                        ];
                                        // Iterate through each modal ID
                                        modalIds.forEach(function(modalId) {
                                            // If the modal ID is not the open one
                                            if (modalId !== '#' + openModalId) {
                                                // Find the select element within the modal
                                                var otherModalSelector = $(modalId).find(
                                                    'select[name="tag_ids[]"]');
                                                // Create a new option without 'selected' attribute
                                                var otherOption = $('<option></option>')
                                                    .attr('value', newItem.id)
                                                    .attr('data-color', newItem.color)
                                                    .text(newItem.title);
                                                // Append the option to the select element in the modal
                                                otherModalSelector.append(otherOption);
                                            }
                                        });
                                    }
                                }
                                if (idOfModal == 'create_item_modal') {
                                    var dropdownSelector = $('#item_id');
                                    if (dropdownSelector.length) {
                                        var newItem = result.item;
                                        var newOption = $('<option></option>')
                                            .attr('value', newItem.id)
                                            .attr('selected', true)
                                            .text(newItem.title);
                                        $(dropdownSelector).append(newOption);
                                        $(dropdownSelector).trigger('change');
                                    }
                                }
                                if (idOfModal === 'create_contract_type_modal') {
                                    var dropdownSelector = modalWithClass.find(
                                        'select[name="contract_type_id"]');
                                    if (dropdownSelector.length) {
                                        var newItem = result.ct;
                                        var newOption = $('<option></option>')
                                            .attr('value', newItem.id)
                                            .attr('selected', true)
                                            .text(newItem.type);
                                        // Append and select the new option in the current modal
                                        dropdownSelector.append(newOption);
                                        var openModalId = dropdownSelector.closest(
                                            '.modal.fade.show').attr('id');
                                        var otherModalId = openModalId === 'create_contract_modal' ?
                                            '#edit_contract_modal' : '#create_contract_modal';
                                        var otherModalSelector = $(otherModalId).find(
                                            'select[name="contract_type_id"]');
                                        // Create a new option for the other modal without 'selected' attribute
                                        var otherOption = $('<option></option>')
                                            .attr('value', newItem.id)
                                            .text(newItem.type);
                                        // Append the option to the other modal
                                        otherModalSelector.append(otherOption);
                                    }
                                }
                                if (idOfModal == 'create_pm_modal') {
                                    var dropdownSelector = $('select[name="payment_method_id"]');
                                    if (dropdownSelector.length) {
                                        var newItem = result.pm;
                                        var newOption = $('<option></option>')
                                            .attr('value', newItem.id)
                                            .attr('selected', true)
                                            .text(newItem.title);
                                        $(dropdownSelector).append(newOption);
                                        $(dropdownSelector).trigger('change');
                                    }
                                }
                                if (idOfModal == 'create_allowance_modal') {
                                    var dropdownSelector = $('select[name="allowance_id"]');
                                    if (dropdownSelector.length) {
                                        var newItem = result.allowance;
                                        var newOption = $('<option></option>')
                                            .attr('value', newItem.id)
                                            .attr('selected', true)
                                            .text(newItem.title);
                                        $(dropdownSelector).append(newOption);
                                        $(dropdownSelector).trigger('change');
                                    }
                                }
                                if (idOfModal == 'create_deduction_modal') {
                                    var dropdownSelector = $('select[name="deduction_id"]');
                                    if (dropdownSelector.length) {
                                        var newItem = result.deduction;
                                        var newOption = $('<option></option>')
                                            .attr('value', newItem.id)
                                            .attr('selected', true)
                                            .text(newItem.title);
                                        $(dropdownSelector).append(newOption);
                                        $(dropdownSelector).trigger('change');
                                    }
                                }
                            }
                            toastr.success(result['message']);
                        } else {
                            if (result.hasOwnProperty('message')) {
                                toastr.success(result['message']);
                                // Show toastr for 3 seconds before reloading or redirecting
                                setTimeout(handleRedirection, 3000);
                            } else {
                                handleRedirection();
                            }
                        }
                    }
                }
            },
            error: function(xhr, status, error) {
                submit_btn.html(button_text);
                submit_btn.attr('disabled', false);
                if (xhr.status === 422) {
                    // Handle validation errors here
                    var response = xhr.responseJSON; // Assuming you're returning JSON
                    if (response.error) {
                        // Handle the general error message
                        var generalErrorMessage = response.message;
                        toastr.error(generalErrorMessage);
                    }
                    // You can access validation errors from the response object
                    var errors = response.errors;
                    for (var key in errors) {
                        if (errors.hasOwnProperty(key) && Array.isArray(errors[key])) {
                            errors[key].forEach(function(error) {
                                toastr.error(error);
                            });
                        }
                    }
                    // Example: Display the first validation error message
                    toastr.error(label_please_correct_errors);
                    // Assuming you have a list of all input fields with error messages
                    var inputFields = currentForm.find('input[name], select[name], textarea[name]');
                    inputFields = $(inputFields.toArray().reverse());
                    // Iterate through all input fields
                    inputFields.each(function() {
                        var inputField = $(this);
                        var fieldName = inputField.attr('name');
                        var errorMessageElement;
                        if (errors && errors[fieldName]) {
                            if (inputField.attr('type') !== 'radio' && inputField.attr(
                                    'type') !== 'hidden') {
                                // Check if the error message element already exists
                                errorMessageElement = inputField.next(
                                    '.text-danger.error-message');
                                // If it doesn't exist, create and append it
                                if (errorMessageElement.length === 0) {
                                    errorMessageElement = $(
                                        '<span class="text-danger error-message"></span>'
                                        );
                                    inputField.after(errorMessageElement);
                                }
                            } else {
                                errorMessageElement = inputField.next(
                                    '.text-danger.error-message');
                            }
                            // If there is a validation error message for this field, display it
                            if (errorMessageElement && errorMessageElement.length > 0) {
                                if (errors[fieldName][0].includes('required')) {
                                    errorMessageElement.text('This field is required.');
                                } else if (errors[fieldName][0].includes(
                                        'format is invalid')) {
                                    errorMessageElement.text('Only numbers allowed.');
                                } else {
                                    errorMessageElement.text(errors[fieldName]);
                                }
                                inputField[0].scrollIntoView({
                                    behavior: "smooth",
                                    block: "start"
                                });
                                inputField.focus();
                            }
                        } else {
                            // If there is no validation error message, clear the existing message
                            errorMessageElement = inputField.next('.error-message');
                            if (errorMessageElement.length === 0) {
                                errorMessageElement = inputField.parent().nextAll(
                                    '.error-message').first();
                            }
                            if (errorMessageElement && errorMessageElement.length > 0) {
                                errorMessageElement.remove();
                            }
                        }
                    });
                } else {
                    // Handle other errors (non-validation errors) here
                    toastr.error(error);
                }
            }
        });

        function handleRedirection() {
            if (redirect_url === '') {
                // window.location.reload(); // Reload the current page
            } else {
                // window.location.href = redirect_url; // Redirect to specified URL
            }
        }
    });
</script>
</body>

</html>
