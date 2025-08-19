    <!-- Navbar -->
    <div id="section-not-to-print">
        <nav class="nav_bar">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="row align-items-center">
                            <div class="col-2 col-md-1 d-xl-none">
                                <div
                                    class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                                    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                                        <i class="bx bx-menu bx-sm"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-10 col-md-10">
                                <div class="nav_breadcrumbs">
                                    <h2 class="m-0 p-0">{{ $title }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row align-items-center justify-content-end">
                            <div class="col-md-12 d-block d-xl-none mt-3 mb-3">
                                {{-- <div class="search_navigation">
                                    <input type="text" class="nav_search" name="search_nav"
                                        placeholder="search here...">
                                    <div class="seacr_icon">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </div>
                                </div> --}}
                            </div>
                            <div class="col-md-12 text-right">
                                <div class="nav_links">
                                    <ul class="list-unstyled d-flex m-0 align-items-center">
                                        <li class="d-none d-xl-block">
                                            {{-- <div class="search_navigation">
                                                <input type="text" class="nav_search" name="search_nav"
                                                    placeholder="search here...">
                                                <div class="seacr_icon">
                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                </div>
                                            </div> --}}
                                        </li>

                                        {{-- Notifications --}}
                                        <li class="nav-item navbar-dropdown dropdown me-2">
                                            <a class="nav-link position-relative" href="{{ route('notification.view') }}">
                                                <i class='bx bx-bell bx-sm'></i> 
                                                <span id="unreadNotificationsCount" class="badge rounded-pill badge-center h-px-20 w-px-20 bg-danger d-none">0</span>
                                            </a>
                                        </li>
                                        
                                        {{-- Dark Mode Toggle --}}
                                        <li class="nav-item me-2">
                                            <button id="darkModeToggle" class="btn btn-sm btn-light rounded-circle p-2" title="Toggle dark mode">
                                                <i class="bx bx-moon"></i>
                                            </button>
                                        </li>

                                        <li class="profile">
                                            <!-- User -->
                                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                                data-bs-toggle="dropdown">
                                                <div class="profile__image">
                                                    <img src="{{ asset('storage/'.Auth::user()->photo) }}"
                                                        alt="">
                                                </div>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="#">
                                                        <div class="d-flex">
                                                            <div class="flex-shrink-0 me-3">
                                                                <div class="avatar avatar-online">
                                                                    <img src="{{ asset('storage/'.Auth::user()->photo) }}"
                                                                        alt class="w-px-40  rounded-circle" />
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <span class="fw-semibold d-block">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
                                                                {{-- <small class="text-muted text-capitalize">
                                                                    admin
                                                                </small> --}}
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <div class="dropdown-divider"></div>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('user.profile.view',Auth::user()->id) }}">
                                                        <i class="bx bx-user me-2"></i>
                                                        <span class="align-middle">My Profile</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <div class="dropdown-divider"></div>
                                                </li>
                                                <li>
                                                    <form action="{{ route('logout') }}" method="POST"
                                                        class="dropdown-item">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"><i
                                                                class="bx bx-log-out-circle"></i> Logout</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </li>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
    <script>
        var label_search = 'Search';
    </script>
    <script src="{{ asset('front-end/assets/js/pages/navbar.js') }}"></script>
