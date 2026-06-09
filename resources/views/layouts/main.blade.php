<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Marshalling System</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="{{ asset('assets/img/kaiadmin/favicon.png') }}" type="image/x-icon" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fonts.min.css') }}" />
    <script>var baseUrl = "{{ asset('') }}";</script>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}" />

    <style>
        :root {
            --primary: #e91e63;
            --primary-color: #e91e63;
            --primary-light: #f8bbd0;
        }
        [data-background-color="purple"] {
            background: #c2185b !important;
        }
        .sidebar[data-background-color="dark"] .sidebar-wrapper .nav .nav-item.active a {
            color: #e91e63 !important;
        }
        .sidebar .sidebar-wrapper .nav .nav-item.active a .fas,
        .sidebar .sidebar-wrapper .nav .nav-item.active a .far {
            color: #e91e63 !important;
        }
        .btn-primary {
            background-color: #e91e63 !important;
            border-color: #e91e63 !important;
        }
        .main-panel {
            overflow-y: auto;
        }
        .main-panel > .container,
        .main-panel > .container-fluid {
            overflow: visible;
        }
        .btn-primary:hover {
            background-color: #c2185b !important;
            border-color: #c2185b !important;
        }
        .btn-info {
            background-color: #1e65e9ff !important;
            border-color: #1e65e9ff !important;
        }
        .btn-info:hover {
            background-color: #1865c2ff !important;
            border-color: #1865c2ff !important;
        }
        .text-primary {
            color: #e91e63 !important;
        }
        .page-item.active .page-link {
            background-color: #e91e63 !important;
            border-color: #e91e63 !important;
        }
        a {
            color: #e91e63;
        }
        .form-check-input:checked {
            background-color: #e91e63;
            border-color: #e91e63;
        }
        table.dataTable tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        table.dataTable tbody tr:hover {
            background-color: rgba(233, 30, 99, 0.05) !important;
        }
        .nav-pills .nav-link.active {
            background-color: #e91e63;
        }
    </style>
    @yield('style')
</head>
<body>
    @if(!request()->routeIs('login'))
    <div class="wrapper">
        <div class="sidebar" data-background-color="dark">
            <div class="sidebar-logo">
                <div class="logo-header" data-background-color="purple">
                    <a href="{{ route('login') }}" class="logo d-flex align-items-center">
                        <img src="{{ asset('assets/img/kaiadmin/logo_light.png') }}" alt="navbar brand" class="navbar-brand" height="30" />
                        <span class="text-white fw-bold ms-2 d-lg-none" style="font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                            @if(Auth::guard('admin')->check())
                                {{ Auth::guard('admin')->user()->name }}
                            @elseif(Auth::guard('member')->check())
                                {{ Auth::guard('member')->user()->nama }}
                            @endif
                        </span>
                    </a>
                    <div class="nav-toggle">
                        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                    </div>
                    <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
                </div>
            </div>
            <div class="sidebar-wrapper scrollbar scrollbar-inner">
                <div class="sidebar-content">
                    <ul class="nav nav-primary">
                        @if(Auth::guard('admin')->check())
                        <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users-cog"></i>
                                <p>User Admin</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.types.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.types.index') }}">
                                <i class="fas fa-car"></i>
                                <p>Type Traktor</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.records.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.records.index') }}">
                                <i class="fas fa-file-alt"></i>
                                <p>Record List</p>
                            </a>
                        </li>
                        @endif

                        @if(Auth::guard('member')->check())
                        <li class="nav-item {{ request()->routeIs('member.records.index') ? 'active' : '' }}">
                            <a href="{{ route('member.records.index') }}">
                                <i class="fas fa-list"></i>
                                <p>Record List</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('member.record.create') ? 'active' : '' }}">
                            <a href="{{ route('member.record.create') }}">
                                <i class="fas fa-qrcode"></i>
                                <p>Scan Record</p>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <div class="logo-header" data-background-color="purple">
                        <a href="{{ route('login') }}" class="logo d-flex align-items-center">
                            <img src="{{ asset('assets/img/kaiadmin/logo_light.png') }}" alt="navbar brand" class="navbar-brand" height="30" />
                            <span class="text-white fw-bold ms-2 d-lg-none" style="font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                                @if(Auth::guard('admin')->check())
                                    {{ Auth::guard('admin')->user()->name }}
                                @elseif(Auth::guard('member')->check())
                                    {{ Auth::guard('member')->user()->nama }}
                                @endif
                            </span>
                        </a>
                        <div class="nav-toggle">
                            <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                            <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                        </div>
                        <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
                    </div>
                </div>
                <nav class="navbar navbar-header navbar-expand-lg border-bottom" data-background-color="purple">
                    <div class="container-fluid">
                        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                            <li class="nav-item dropdown hidden-caret">
                                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                                    <span class="profile-username">
                                        <span class="fw-bold text-white">
                                            @if(Auth::guard('admin')->check())
                                                {{ Auth::guard('admin')->user()->name }}
                                            @elseif(Auth::guard('member')->check())
                                                {{ Auth::guard('member')->user()->nama }}
                                            @endif
                                        </span>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-user animated fadeIn">
                                    <div class="dropdown-user-scroll scrollbar-outer">
                                        <li>
                                            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">Logout</button>
                                            </form>
                                        </li>
                                    </div>
                                </ul>
                            </li>
                            <li class="nav-item d-lg-none">
                                <div class="nav-link text-white fw-bold">
                                    <i class="fas fa-user"></i>
                                    @if(Auth::guard('admin')->check()) {{ Auth::guard('admin')->user()->name }}
                                    @elseif(Auth::guard('member')->check()) {{ Auth::guard('member')->user()->nama }}
                                    @endif
                                </div>
                            </li>
                            <li class="nav-item d-lg-none">
                                <a class="nav-link text-white fw-bold" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>

            <div class="container-fluid py-3">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
            </div>

            @yield('content')

            <footer class="footer">
                <div class="container-fluid d-flex justify-content-between">
                    <div class="copyright">
                        <script>document.write(new Date().getFullYear());</script>, Iseki <span class="text-primary">Marshalling</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    @else
    <div class="wrapper" style="padding: 10px;">
        @yield('content')
    </div>
    @endif

    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @yield('script')
</body>
</html>
