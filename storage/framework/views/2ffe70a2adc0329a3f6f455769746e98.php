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

        /* Floating Toast Alert Part Kurang */
        #partKurangToastContainer {
            position: fixed;
            top: 80px;
            right: 24px;
            z-index: 99999;
            width: 360px;
            max-width: calc(100vw - 36px);
            pointer-events: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .part-kurang-toast {
            pointer-events: auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.22), 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #ffeeba;
            border-left: 6px solid #ffc107;
            overflow: hidden;
            animation: slideInDown 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .part-kurang-toast-header {
            padding: 10px 14px;
            background: #fff8e1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #fff2b2;
        }
        .part-kurang-toast-body {
            padding: 12px 14px;
        }
        .part-kurang-toast .comment-text {
            background: #fff9e6;
            border-radius: 6px;
            padding: 8px 12px;
            font-weight: 700;
            color: #212529;
            font-size: 0.95rem;
            word-break: break-word;
            border: 1px dashed #ffd54f;
            margin-top: 6px;
            margin-bottom: 6px;
        }

        /* Custom Purple Outline Button (for kategori salah) */
        .btn-outline-purple {
            color: #6f42c1 !important;
            border-color: #6f42c1 !important;
            background-color: transparent !important;
        }
        .btn-outline-purple:hover {
            color: #ffffff !important;
            background-color: #6f42c1 !important;
            border-color: #6f42c1 !important;
        }
        .btn-check:checked + .btn-outline-purple,
        .btn-check:active + .btn-outline-purple,
        .btn-outline-purple.active,
        .btn-outline-purple:active {
            color: #ffffff !important;
            background-color: #6f42c1 !important;
            border-color: #6f42c1 !important;
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.4) !important;
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

    <?php if(Auth::guard('member')->check()): ?>
    <!-- Container Toast Notifikasi Part Kurang Realtime -->
    <div id="partKurangToastContainer"></div>

    <script>
        (function() {
            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function pollPartKurangNotifications() {
                $.ajax({
                    url: "<?php echo e(route('member.part-kurang.active-notifications')); ?>",
                    type: "GET",
                    dataType: "json",
                    cache: false,
                    data: { _t: new Date().getTime() },
                    success: function(res) {
                        if (!res || !res.notifications) return;
                        var container = $('#partKurangToastContainer');
                        var activeIdsOnServer = {};

                        res.notifications.forEach(function(item) {
                            activeIdsOnServer[item.id] = true;

                            var toastElId = 'pk_toast_' + item.id;
                            if ($('#' + toastElId).length === 0) {
                                var catBadge = '';
                                var cat = (item.category || 'kurang').toLowerCase();
                                if (cat === 'kosong') {
                                    catBadge = '<span class="badge bg-danger text-white"><i class="fas fa-times-circle me-1"></i>Kosong</span>';
                                } else if (cat === 'salah') {
                                    catBadge = '<span class="badge text-white" style="background-color: #6f42c1;"><i class="fas fa-exclamation-circle me-1"></i>Salah</span>';
                                } else {
                                    catBadge = '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Kurang</span>';
                                }

                                var html = '' +
                                    '<div class="part-kurang-toast shadow" id="' + toastElId + '">' +
                                    '  <div class="part-kurang-toast-header">' +
                                    '    <div class="d-flex align-items-center gap-2">' +
                                    '      ' + catBadge +
                                    '      <small class="text-muted fw-bold">' + escapeHtml(item.sequence_no) + ' (' + escapeHtml(item.area) + ')</small>' +
                                    '    </div>' +
                                    '    <button type="button" class="btn-close btn-sm ms-2" title="Tutup Notifikasi" data-id="' + item.id + '" style="font-size: 0.75rem;"></button>' +
                                    '  </div>' +
                                    '  <div class="part-kurang-toast-body">' +
                                    '    <div class="d-flex align-items-center mb-1 text-dark">' +
                                    '      <i class="fas fa-user-circle text-secondary me-2 fs-5"></i>' +
                                    '      <div>' +
                                    '        <strong class="d-block text-dark" style="font-size: 0.95rem;">' + escapeHtml(item.reporter_name) + '</strong>' +
                                    '        <small class="text-muted"><i class="far fa-clock me-1"></i>' + escapeHtml(item.comment_time) + '</small>' +
                                    '      </div>' +
                                    '    </div>' +
                                    '    <div class="comment-text">' + escapeHtml(item.comment) + '</div>' +
                                    '  </div>' +
                                    '</div>';

                                container.append(html);
                            }
                        });

                        // Hapus toast di DOM jika sudah tidak pending atau sudah di-dismiss di database
                        $('.part-kurang-toast').each(function() {
                            var tid = $(this).attr('id').replace('pk_toast_', '');
                            if (!activeIdsOnServer[tid]) {
                                $(this).fadeOut(300, function() { $(this).remove(); });
                            }
                        });
                    },
                    error: function(err) {
                        console.warn('Gagal memuat notifikasi part kurang:', err);
                    }
                });
            }

            // Event handler klik tombol tutup pada toast (disimpan langsung ke database)
            $(document).on('click', '#partKurangToastContainer .btn-close', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

                var toast = $('#pk_toast_' + id);
                toast.css({
                    opacity: 0,
                    transform: 'translateY(-20px) scale(0.95)'
                });
                setTimeout(function() {
                    toast.remove();
                }, 300);

                // Simpan status penutupan ke database
                var dismissUrl = "<?php echo e(route('member.part-kurang.dismiss', ':id')); ?>".replace(':id', id);
                $.ajax({
                    url: dismissUrl,
                    type: "POST",
                    dataType: "json",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    error: function(err) {
                        console.warn('Gagal menyimpan penutupan notifikasi ke database:', err);
                    }
                });
            });

            // Jalankan polling pertama kali dan ulangi setiap 5 detik
            $(document).ready(function() {
                // Bersihkan cache lama localStorage jika ada
                try { localStorage.removeItem('dismissed_pk_ids'); } catch(e) {}

                pollPartKurangNotifications();
                setInterval(pollPartKurangNotifications, 5000);
            });
        })();
    </script>
    <?php endif; ?>

    <?php echo $__env->yieldContent('script'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\iseki_marshalling\resources\views/layouts/main.blade.php ENDPATH**/ ?>