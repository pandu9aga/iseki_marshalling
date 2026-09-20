<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Marshalling System</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="<?php echo e(asset('assets/img/kaiadmin/favicon.png')); ?>" type="image/x-icon" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/fonts.min.css')); ?>" />
    <script>var baseUrl = "<?php echo e(asset('')); ?>";</script>
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/bootstrap.min.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins.min.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/kaiadmin.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/all.min.css')); ?>" />

    <!-- Dynamic Favicon -->
    <script src="/iseki_pro_app/js/dynamic-favicon.js"></script>
    <script>document.addEventListener("DOMContentLoaded", function() { setDynamicFavicon("trolley", "Marshalling"); });</script>

    <!-- Dynamic Favicon Assets -->
    <link rel="stylesheet" href="/iseki_pro_app/css/icon.css">
    <script src="/iseki_pro_app/js/dynamic-favicon.js"></script>
    <script>document.addEventListener("DOMContentLoaded", function() { setDynamicFavicon("trolley", "Marshalling"); });</script>

    <style>
        :root {
            --primary: #F36494;
            --primary-color: #F36494;
            --primary-light: #f8bbd0;
        }
        .sidebar .sidebar-wrapper .nav .nav-item.active a {
            color: #F36494 !important;
        }
        .sidebar .sidebar-wrapper .nav .nav-item.active a .fas,
        .sidebar .sidebar-wrapper .nav .nav-item.active a .far {
            color: #F36494 !important;
        }
        .sidebar .nav > .nav-item a {
            color: #333 !important;
        }
        .sidebar .nav > .nav-item a i {
            color: #555 !important;
        }
        .btn-primary {
            background-color: #F36494 !important;
            border-color: #F36494 !important;
        }
        .main-panel {
            overflow-y: auto;
        }
        .main-panel > .container,
        .main-panel > .container-fluid {
            overflow: visible;
        }
        .main-panel > .container {
            margin-top: 60px !important;
            padding-top: 0.5rem !important;
        }
        .main-panel .page-header {
            margin-bottom: 0.5rem !important;
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
            color: #F36494 !important;
        }
        .page-item.active .page-link {
            background-color: #F36494 !important;
            border-color: #F36494 !important;
        }
        a {
            color: #F36494;
        }
        .form-check-input:checked {
            background-color: #F36494;
            border-color: #F36494;
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
        .logo-header[data-background-color="purple"],
        .navbar-header[data-background-color="purple"] {
            background: #F36494 !important;
        }
        @media screen and (max-width: 991.5px) {
            .sidebar .logo-header span.fw-bold {
                color: #FFFFFF !important;
            }
        }
        /* Style default (untuk ukuran layar normal/besar) */
        .marshalling-text {
            font-size: 16px;
            color: #F36494;
        }

        /* Style saat ukuran layar kecil (contoh: lebar layar di bawah 768px / mode mobile) */
        @media (max-width: 767px) {
            .marshalling-text {
                color: #FFFFFF;
            }
        }
    </style>
    <?php echo $__env->yieldContent('style'); ?>
</head>
<body>
    <?php if(!request()->routeIs('login')): ?>
    <div class="wrapper">
        <div class="sidebar" data-background-color="white">
            <div class="sidebar-logo">
                <div class="logo-header" data-background-color="white">
                    <a href="<?php echo e(route('login')); ?>" class="logo d-flex align-items-center text-decoration-none ps-3">
                        <span class="marshalling-text fw-bold">Marshalling</span>
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
                        <?php if(Auth::guard('admin')->check()): ?>
                        <li class="nav-item <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('admin.dashboard')); ?>">
                                <i class="fas fa-tachometer-alt"></i>
                                <p class="<?php echo e(request()->routeIs('admin.dashboard') ? 'text-primary' : ''); ?>">Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('admin.users.index')); ?>">
                                <i class="fas fa-users-cog"></i>
                                <p class="<?php echo e(request()->routeIs('admin.users.*') ? 'text-primary' : ''); ?>">User Admin</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('admin.types.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('admin.types.index')); ?>">
                                <i class="fas fa-car"></i>
                                <p class="<?php echo e(request()->routeIs('admin.types.*') ? 'text-primary' : ''); ?>">Type Traktor</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('admin.records.*') && !request()->routeIs('admin.ng.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('admin.records.index')); ?>">
                                <i class="fas fa-file-alt"></i>
                                <p>Record List</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('admin.ng.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('admin.ng.index')); ?>">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p class="<?php echo e(request()->routeIs('admin.ng.*') ? 'text-primary' : ''); ?>">NG</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('admin.empty-part.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('admin.empty-part.index')); ?>">
                                <i class="fas fa-box-open"></i>
                                <p class="<?php echo e(request()->routeIs('admin.empty-part.*') ? 'text-primary' : ''); ?>">Empty Part</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('admin.report-empty.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('admin.report-empty.list')); ?>">
                                <i class="fas fa-flag"></i>
                                <p class="<?php echo e(request()->routeIs('admin.report-empty.*') ? 'text-primary' : ''); ?>">Report Empty</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('admin.part-kurang.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('admin.part-kurang.list')); ?>">
                                <i class="fas fa-clipboard-list"></i>
                                <p class="<?php echo e(request()->routeIs('admin.part-kurang.*') ? 'text-primary' : ''); ?>">Part Kurang</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('admin.punishments.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('admin.punishments.index')); ?>">
                                <i class="fas fa-gavel"></i>
                                <p class="<?php echo e(request()->routeIs('admin.punishments.*') ? 'text-primary' : ''); ?>">Punishment</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('admin.member-areas.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('admin.member-areas.index')); ?>">
                                <i class="fas fa-map-marked-alt"></i>
                                <p class="<?php echo e(request()->routeIs('admin.member-areas.*') ? 'text-primary' : ''); ?>">Member Area</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if(Auth::guard('member')->check()): ?>
                        <li class="nav-item <?php echo e(request()->routeIs('member.record.create') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('member.record.create')); ?>">
                                <i class="fas fa-qrcode"></i>
                                <p class="<?php echo e(request()->routeIs('member.record.create') ? 'text-primary' : ''); ?>">Scan Record</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if(Auth::guard('perakitan')->check()): ?>
                        <li class="nav-item <?php echo e(request()->routeIs('perakitan.dashboard') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('perakitan.dashboard')); ?>">
                                <i class="fas fa-tachometer-alt"></i>
                                <p class="<?php echo e(request()->routeIs('perakitan.dashboard') ? 'text-primary' : ''); ?>">Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('perakitan.kanban.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('perakitan.kanban.index')); ?>">
                                <i class="fas fa-qrcode"></i>
                                <p class="<?php echo e(request()->routeIs('perakitan.kanban.*') ? 'text-primary' : ''); ?>">Scan Kanban</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('perakitan.prosedur.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('perakitan.prosedur.index')); ?>">
                                <i class="fas fa-file-pdf"></i>
                                <p class="<?php echo e(request()->routeIs('perakitan.prosedur.*') ? 'text-primary' : ''); ?>">Prosedur</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('perakitan.comment.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('perakitan.comment.index')); ?>">
                                <i class="fas fa-comment-dots"></i>
                                <p class="<?php echo e(request()->routeIs('perakitan.comment.*') ? 'text-primary' : ''); ?>">Part Kurang</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if(!Auth::guard('admin')->check() && !Auth::guard('member')->check() && !Auth::guard('perakitan')->check()): ?>
                        <li class="nav-item <?php echo e(request()->routeIs('public.part-kurang.*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('public.part-kurang.index')); ?>">
                                <i class="fas fa-clipboard-list"></i>
                                <p class="<?php echo e(request()->routeIs('public.part-kurang.*') ? 'text-primary' : ''); ?>">Part Kurang</p>
                            </a>
                        </li>
                        <li class="nav-item <?php echo e(request()->routeIs('login') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('login')); ?>">
                                <i class="fas fa-sign-in-alt"></i>
                                <p>Halaman Login</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <div class="logo-header" data-background-color="purple">
                        <a href="<?php echo e(route('login')); ?>" class="logo d-flex align-items-center text-decoration-none ps-3">
                            <span class="fw-bold text-white" style="font-size: 14px;">Marshalling</span>
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
                            <?php if(Auth::guard('admin')->check() || Auth::guard('member')->check() || Auth::guard('perakitan')->check()): ?>
                            <li class="nav-item dropdown hidden-caret">
                                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                                    <span class="profile-username">
                                        <span class="fw-bold text-white">
                                            <?php if(Auth::guard('admin')->check()): ?>
                                                 <?php echo e(Auth::guard('admin')->user()->name); ?>

                                            <?php elseif(Auth::guard('member')->check()): ?>
                                                <?php echo e(Auth::guard('member')->user()->nama); ?>

                                            <?php elseif(Auth::guard('perakitan')->check()): ?>
                                                <?php echo e(Auth::guard('perakitan')->user()->nama); ?>

                                            <?php endif; ?>
                                        </span>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-user animated fadeIn">
                                    <div class="dropdown-user-scroll scrollbar-outer">
                                        <li>
                                            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="dropdown-item">Logout</button>
                                            </form>
                                        </li>
                                    </div>
                                </ul>
                            </li>
                            <li class="nav-item d-lg-none">
                                <div class="nav-link text-white fw-bold">
                                    <i class="fas fa-user"></i>
                                    <?php if(Auth::guard('admin')->check()): ?> <?php echo e(Auth::guard('admin')->user()->name); ?>

                                    <?php elseif(Auth::guard('member')->check()): ?> <?php echo e(Auth::guard('member')->user()->nama); ?>

                                    <?php elseif(Auth::guard('perakitan')->check()): ?> <?php echo e(Auth::guard('perakitan')->user()->nama); ?>

                                    <?php endif; ?>
                                </div>
                            </li>
                            <li class="nav-item d-lg-none">
                                <a class="nav-link text-white fw-bold" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                            <?php else: ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('login')); ?>" class="btn btn-light btn-sm text-primary fw-bold px-3">
                                    <i class="fas fa-sign-in-alt me-1"></i>Login
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </nav>
            </div>

            <?php if(session('success') || session('error') || $errors->any()): ?>
            <div class="container-fluid pt-2">
                <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>

            <footer class="footer">
                <div class="container-fluid d-flex justify-content-between">
                    <div class="copyright">
                        <script>document.write(new Date().getFullYear());</script>, Iseki <span class="text-primary">Marshalling</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <?php else: ?>
    <div class="wrapper" style="padding: 10px;">
        <?php echo $__env->yieldContent('content'); ?>
    </div>
    <?php endif; ?>

    <script src="<?php echo e(asset('assets/js/core/jquery-3.7.1.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/core/popper.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/core/bootstrap.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugin/datatables/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugin/sweetalert/sweetalert2.all.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/kaiadmin.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/sound-cache.js')); ?>?v=<?php echo e(file_exists(public_path('assets/js/sound-cache.js')) ? filemtime(public_path('assets/js/sound-cache.js')) : '1'); ?>"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        if (window.SoundCache) {
            window.SoundCache.init("<?php echo e(asset('')); ?>");
        }
    </script>
    <?php echo $__env->yieldContent('script'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\iseki_marshalling\resources\views/layouts/main.blade.php ENDPATH**/ ?>