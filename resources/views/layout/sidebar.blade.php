    <!-- Sidebar -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme menu-container">
        <div class="app-brand demo">
            <a href="#" class="app-brand-link">
                <span class="app-brand-logo demo">
                    <img src="{{ asset('front-end/assets/image/vendorconnect.png') }}" width="200px" alt="" />
                </span>
            </a>
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
        </div>
        <ul class="menu-inner py-1">
            <hr class="dropdown-divider" />
            <!-- Dashboard -->
            <li class="menu-item active">
                <a href="{{ route('dashboard.view') }}" class="menu-link">
                    <i class="fa-solid fa-house-circle-check"></i>
                    <div>Dashboard</div>
                </a>
            </li>
            @php
                $permissions = session('permissions');
                @endphp
            @if (in_array('manage_tasks', $permissions))
            <li class="menu-item ">
                <a href="{{ route('task.view') }}" class="menu-link">
                    <i class="fa-regular fa-clipboard"></i>
                    <div>Tasks</div>
                </a>
            </li>
            @endif

            @if (in_array('manage_task_types', $permissions))
            <li class="menu-item ">
                <a href="{{ route('tasktype.view') }}" class="menu-link">
                    <i class="fa-solid fa-list-check"></i>
                    <div>Task Type</div>
                </a>
            </li>
            @endif

            @if (in_array('manage_task_brief_templates', $permissions))
            <li class="menu-item ">
                <a href="{{ route('task.breif.view') }}" class="menu-link">
                    <i class="fa-solid fa-thumbtack"></i>
                    <div>Task Brief Templates</div>
                </a>
            </li>
            @endif

            @if (in_array('manage_task_brief_question', $permissions))
                <li class="menu-item ">
                    <a href="{{ route('task.breif.question.view') }}" class="menu-link">
                        <i class="fa-regular fa-circle-question"></i>
                        <div>Task Brief Infomation</div>
                    </a>
                </li>
            @endif

            @if (in_array('manage_task_brief_question', $permissions))
                <li class="menu-item ">
                    <a href="{{ route('view.check.list') }}" class="menu-link">
                        <i class="fa-solid fa-briefcase"></i>
                        <div>Check Brief Item</div>
                    </a>
                </li>
             @endif

            @if (in_array('manage_statuses', $permissions))
                <li class="menu-item ">
                    <a href="{{ route('statuses.view') }}" class="menu-link">
                        <i class="fa-solid fa-bars-progress"></i>
                        <div>Statuses</div>
                    </a>
                </li>
            @endif

            @if (in_array('manage_priorities', $permissions))
                <li class="menu-item ">
                    <a href="{{ route('priority.view') }}" class="menu-link">
                        <i class="fa-solid fa-key"></i>
                        <div>Priorities</div>
                    </a>
                </li>
            @endif

            {{-- @if (in_array('manage_tags', $permissions))
                <li class="menu-item ">
                    <a href="{{ route('tags.view') }}" class="menu-link">
                        <i class="fa-solid fa-tags"></i>
                        <div>Tags</div>
                    </a>
                </li>
            @endif --}}

            @if (in_array('manage_users', $permissions))
                <li class="menu-item ">
                    <a href="{{ route('user.view') }}" class="menu-link">
                        <i class="fa-solid fa-users"></i>
                        <div>Users</div>
                    </a>
                </li>
            @endif

            @if (in_array('manage_user_role', $permissions))
            <li class="menu-item ">
                <a href="{{ route('user.role.view') }}" class="menu-link">
                    <i class="fa-solid fa-person-circle-check"></i>
                    <div>User Role</div>
                </a>
            </li>
            @endif

            @if (in_array('manage_clients', $permissions))
            <li class="menu-item ">
                <a href="{{ route('clients.view') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div>Clients</div>
                </a>
            </li>
            @endif
        </ul>
    </aside>
    <!-- Sidebar -->
