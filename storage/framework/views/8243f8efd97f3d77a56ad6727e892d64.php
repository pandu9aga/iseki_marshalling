<?php $__env->startSection('style'); ?>
<style>
    .member-card-photo {
        width: 90px;
        height: 115px;
        object-fit: cover;
        border-radius: 8px;
    }
    .member-photo-placeholder-sm {
        width: 90px;
        height: 115px;
        border-radius: 8px;
        background: #e9ecef;
        color: #6c757d;
    }
    .status-badge-pending {
        background-color: #ffc107;
        color: #212529;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.25em 0.6em;
        border-radius: 6px;
    }
    .status-badge-oke {
        background-color: #198754;
        color: #fff;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.35em 0.75em;
        border-radius: 6px;
    }
    .btn-receive-direct {
        font-size: 0.95rem;
        font-weight: 700;
        padding: 0.45rem 1rem;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(25, 135, 84, 0.3);
        transition: all 0.15s ease-in-out;
    }
    .btn-receive-direct:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(25, 135, 84, 0.45);
    }
    .part-kurang-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        background: #fff;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .part-kurang-card:hover {
        box-shadow: 0 4px 14px rgba(0,0,0,0.07);
    }
    .filter-btn-group .btn {
        font-size: 0.85rem;
        padding: 0.35rem 0.75rem;
    }
    #cameraScannerContainer {
        border-radius: 10px;
        overflow: hidden;
        background: #000;
    }
    #cameraReader video {
        width: 100% !important;
        height: auto !important;
        border-radius: 8px;
    }
    .scan-mode-btn.active {
        background-color: #F36494;
        color: #fff;
        border-color: #F36494;
    }
    .profile-card-wrapper {
        position: relative;
    }
    .profile-chevron-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.92);
        color: #0d6efd;
        border: 1px solid rgba(0, 0, 0, 0.12);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .profile-chevron-btn:hover {
        background: #0d6efd;
        color: #fff;
        transform: translateY(-50%) scale(1.08);
    }
    .profile-chevron-btn.prev {
        left: -12px;
    }
    .profile-chevron-btn.next {
        right: -12px;
    }
    .carousel-item {
        touch-action: pan-y pinch-zoom;
    }
    .step-indicator {
        font-size: 0.82rem;
        font-weight: 700;
        padding: 4px 10px;
        margin: 3px;
        border-radius: 5px;
    }
    .action-tab-btn {
        font-weight: 700;
        border-radius: 8px;
        padding: 8px 18px;
    }
    .action-tab-btn.active {
        background-color: #F36494 !important;
        color: #fff !important;
        border-color: #F36494 !important;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="page-inner">
        <!-- Header Page -->
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-3 g-2">
            <div>
                <h4 class="page-title text-primary mb-0"><i class="fas fa-clipboard-list me-2"></i>Part Kurang</h4>
                <small class="text-muted">Input & Konfirmasi Part Kurang (Tanpa Login)</small>
            </div>
            <div>
                <a href="<?php echo e(route('login')); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Login
                </a>
            </div>
        </div>

        <!-- Tab Pilihan Mode di-hide sesuai permintaan -->
        <div class="card shadow-sm border-0 mb-3" style="display: none !important;">
            <div class="card-body p-2 d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-outline-primary action-tab-btn active" id="tabModeInput">
                    <i class="fas fa-plus-circle me-1"></i>Input Part Kurang
                </button>
                <button type="button" class="btn btn-outline-primary action-tab-btn" id="tabModeReceive">
                    <i class="fas fa-check-circle me-1"></i>Penerimaan Part Kurang
                </button>
            </div>
        </div>

        <!-- ======================= SECTION 1: MODE INPUT PART KURANG ======================= -->
        <div id="sectionInputMode">
            <!-- STEP 1: SCAN QR MEMBER PELAPOR -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="step-indicator bg-warning text-white">
                            Langkah 1: Input NIK
                        </span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary scan-mode-btn active" id="btnMemberUsb">
                                <i class="fas fa-keyboard me-1"></i>Scanner
                            </button>
                            <button type="button" class="btn btn-outline-primary scan-mode-btn" id="btnMemberCamera">
                                <i class="fas fa-camera me-1"></i>Kamera
                            </button>
                        </div>
                    </div>

                    <!-- Input Scanner / Manual NIK Member -->
                    <div id="memberUsbBox">
                        <div class="row align-items-center g-2">
                            <div class="col-12 col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="fas fa-id-card"></i></span>
                                    <input type="text" id="memberScannerInput" class="form-control" placeholder="Scan QR atau ketik NIK lalu Enter / Klik Cari..." autofocus>
                                    <button class="btn btn-primary" type="button" id="btnCheckMemberManual">
                                        <i class="fas fa-search me-1"></i>Cari
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 col-md-6" id="activeMemberBadgeArea">
                                <div class="p-2 border rounded bg-light text-muted d-flex align-items-center">
                                    <i class="fas fa-info-circle text-primary me-2"></i>
                                    <small>Silakan scan QR Member atau ketik NIK secara manual.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kamera Device Scanner Member -->
                    <div id="memberCameraBox" style="display:none;" class="mt-2">
                        <div class="row justify-content-center">
                            <div class="col-12 col-md-8 col-lg-6">
                                <div id="cameraScannerContainer" class="p-2 border position-relative text-center">
                                    <div id="cameraMemberReader" style="width: 100%; min-height: 220px;"></div>
                                    <div class="mt-2 d-flex justify-content-between align-items-center">
                                        <small class="text-white"><i class="fas fa-info-circle me-1"></i>Arahkan kamera ke QR Member</small>
                                        <button type="button" class="btn btn-danger btn-sm" id="btnStopMemberCamera">
                                            <i class="fas fa-stop me-1"></i>Tutup Kamera
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- STEP 2: SCAN QR KANBAN (Aktif setelah member terverifikasi) -->
            <div class="card shadow-sm border-0 mb-3" id="kanbanScanCard" style="display:none;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="step-indicator bg-warning text-white">
                            <i class="fas fa-qrcode me-1"></i>Langkah 2: Scan QR Kanban
                        </span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary scan-mode-btn active" id="btnKanbanUsb">
                                <i class="fas fa-barcode me-1"></i>Scanner
                            </button>
                            <button type="button" class="btn btn-outline-primary scan-mode-btn" id="btnKanbanCamera">
                                <i class="fas fa-camera me-1"></i>Kamera
                            </button>
                        </div>
                    </div>

                    <!-- Input Scanner USB Kanban -->
                    <div id="kanbanUsbBox">
                        <div class="row align-items-center g-2">
                            <div class="col-12 col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="fas fa-barcode"></i></span>
                                    <input type="text" id="kanbanScannerInput" class="form-control" placeholder="Scan QR Kanban disini..." style="text-transform: uppercase;">
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <input type="text" id="sequence_no" class="form-control form-control-sm text-center bg-light" placeholder="Sequence" readonly>
                            </div>
                            <div class="col-4 col-md-3">
                                <input type="text" id="production_date" class="form-control form-control-sm text-center bg-light" placeholder="Prod. Date" readonly>
                            </div>
                            <div class="col-4 col-md-2">
                                <input type="text" id="type" class="form-control form-control-sm text-center bg-light" placeholder="Type" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Kamera Device Scanner Kanban -->
                    <div id="kanbanCameraBox" style="display:none;" class="mt-2">
                        <div class="row justify-content-center">
                            <div class="col-12 col-md-8 col-lg-6">
                                <div id="cameraScannerContainer" class="p-2 border position-relative text-center">
                                    <div id="cameraKanbanReader" style="width: 100%; min-height: 220px;"></div>
                                    <div class="mt-2 d-flex justify-content-between align-items-center">
                                        <small class="text-white"><i class="fas fa-info-circle me-1"></i>Arahkan kamera ke QR Kanban</small>
                                        <button type="button" class="btn btn-danger btn-sm" id="btnStopKanbanCamera">
                                            <i class="fas fa-stop me-1"></i>Tutup Kamera
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Hasil Scan Kanban (Form Input Part Kurang Slider 1 Card) -->
            <div id="resultArea" class="mb-4" style="display:none;"></div>
        </div>

        <!-- ======================= SECTION 2: MODE PENERIMAAN PART KURANG ======================= -->
        <div id="sectionReceiveMode" style="display:none;">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <span class="badge bg-success p-2 fs-6"><i class="fas fa-hand-holding-box me-1"></i>Penerimaan Part Kurang</span>
                    </div>
                    <h5 class="fw-bold mb-2">Scan QR Member Anda</h5>
                    
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-6 col-lg-5">
                            <div class="input-group input-group-lg mb-2">
                                <span class="input-group-text bg-success text-white"><i class="fas fa-qrcode"></i></span>
                                <input type="text" id="receiveMemberScannerInput" class="form-control" placeholder="Scan QR Member disini..." autofocus>
                            </div>
                            <small class="text-muted d-block">Gunakan USB Scanner atau pilih Scanner Kamera di bawah.</small>
                            <button type="button" class="btn btn-outline-success btn-sm mt-2" id="btnReceiveCamera">
                                <i class="fas fa-camera me-1"></i>Gunakan Kamera Device
                            </button>
                        </div>
                    </div>

                    <!-- Kamera Device Scanner Receive -->
                    <div id="receiveCameraBox" style="display:none;" class="mt-3">
                        <div class="row justify-content-center">
                            <div class="col-12 col-md-8 col-lg-6">
                                <div id="cameraScannerContainer" class="p-2 border position-relative text-center">
                                    <div id="cameraReceiveReader" style="width: 100%; min-height: 220px;"></div>
                                    <div class="mt-2 d-flex justify-content-between align-items-center">
                                        <small class="text-white"><i class="fas fa-info-circle me-1"></i>Arahkan kamera ke QR Member</small>
                                        <button type="button" class="btn btn-danger btn-sm" id="btnStopReceiveCamera">
                                            <i class="fas fa-stop me-1"></i>Tutup Kamera
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ======================= SECTION 3: RIWAYAT PART KURANG (INFINITE SCROLL) ======================= -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-4">
                        <h5 class="card-title mb-0 fw-bold text-dark"><i class="fas fa-history text-primary me-2"></i>Riwayat Part Kurang Terkini</h5>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2 align-items-center">
                            <!-- Filter Pencarian -->
                            <div class="input-group input-group-sm" style="max-width: 260px;">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" id="filterSearch" class="form-control" placeholder="Cari sequence, part, area...">
                            </div>
                            <!-- Filter Status -->
                            <div class="btn-group filter-btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary" data-status="all">Semua</button>
                                <button type="button" class="btn btn-outline-warning active" data-status="pending">Pending</button>
                                <button type="button" class="btn btn-outline-success" data-status="oke">Diterima</button>
                            </div>
                            <!-- Refresh Button -->
                            <button type="button" class="btn btn-light btn-sm border" id="btnRefreshList" title="Segarkan Data">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-3 bg-light">
                <div id="partKurangCardsContainer">
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <p class="mt-2 mb-0 small">Memuat riwayat part kurang...</p>
                    </div>
                </div>

                <div id="infiniteScrollStatus" class="text-center py-3" style="display:none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 small text-muted">Memuat lebih banyak...</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ======================= MODAL POPUP PENERIMAAN MEMBER ======================= -->
<div class="modal fade" id="memberReceiveModal" tabindex="-1" aria-labelledby="memberReceiveModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <div class="d-flex align-items-center">
                    <div id="modalMemberPhotoArea" class="me-2"></div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="memberReceiveModalLabel">Laporan Part Kurang</h6>
                        <small id="modalMemberSubtitle" class="text-white-50"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light p-3">
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <span class="fw-bold small text-muted"><i class="fas fa-filter me-1"></i>Filter Laporan Anda:</span>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm modal-filter-btn" data-filter="all">Semua</button>
                        <button type="button" class="btn btn-outline-warning btn-sm modal-filter-btn active" data-filter="pending">Pending</button>
                        <button type="button" class="btn btn-outline-success btn-sm modal-filter-btn" data-filter="oke">Diterima</button>
                    </div>
                </div>

                <div id="modalReportsContainer">
                    <!-- Cards list laporan member akan di-render disini -->
                </div>
            </div>
            <div class="modal-footer py-2">
                <small class="text-muted me-auto"><i class="fas fa-info-circle me-1"></i>Setelah menerima 1 part, modal akan otomatis tertutup dan harus scan ulang QR Member.</small>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script src="<?php echo e(asset('assets/js/plugin/html5-qrcode.min.js')); ?>"></script>
<script>
    // State Global Pelapor
    var activeMember = null; // { nik, nama, photo, team }
    var currentActiveMode = 'input'; // 'input' atau 'receive'

    // State Infinite Scroll Riwayat Bawah
    var currentStatusFilter = 'pending';
    var currentPage = 1;
    var isLoadingMore = false;
    var hasMoreData = true;
    var searchTimeout = null;

    // State Modal Penerimaan
    var currentModalMemberNik = null;
    var currentModalFilter = 'pending';
    var cachedModalReports = [];

    // State Scanner Kamera
    var html5QrMember = null;
    var isCameraMember = false;
    var html5QrKanban = null;
    var isCameraKanban = false;
    var html5QrReceive = null;
    var isCameraReceive = false;

    $(document).ready(function() {
        // Load riwayat list paling bawah
        resetAndLoadRecentList();

        // Switch mode input vs receive
        $('#tabModeInput').on('click', function() {
            $('.action-tab-btn').removeClass('active');
            $(this).addClass('active');
            currentActiveMode = 'input';
            $('#sectionInputMode').show();
            $('#sectionReceiveMode').hide();
            stopReceiveCamera();
            if (activeMember) {
                $('#kanbanScannerInput').focus();
            } else {
                $('#memberScannerInput').focus();
            }
        });

        $('#tabModeReceive').on('click', function() {
            $('.action-tab-btn').removeClass('active');
            $(this).addClass('active');
            currentActiveMode = 'receive';
            $('#sectionInputMode').hide();
            $('#sectionReceiveMode').show();
            stopMemberCamera();
            stopKanbanCamera();
            $('#receiveMemberScannerInput').val('').focus();
        });

        // Input search filter riwayat
        $('#filterSearch').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                resetAndLoadRecentList();
            }, 300);
        });

        // Filter status riwayat
        $('.filter-btn-group .btn').on('click', function() {
            $('.filter-btn-group .btn').removeClass('active');
            $(this).addClass('active');
            currentStatusFilter = $(this).data('status');
            resetAndLoadRecentList();
        });

        $('#btnRefreshList').on('click', function() {
            var icon = $(this).find('i');
            icon.addClass('fa-spin');
            resetAndLoadRecentList(function() {
                icon.removeClass('fa-spin');
            });
        });

        // Filter modal penerimaan
        $('.modal-filter-btn').on('click', function() {
            $('.modal-filter-btn').removeClass('active');
            $(this).addClass('active');
            currentModalFilter = $(this).data('filter');
            renderModalReports();
        });

        // Event Modal ditutup: reset dan fokus ke scan member receive
        $('#memberReceiveModal').on('hidden.bs.modal', function() {
            currentModalMemberNik = null;
            cachedModalReports = [];
            $('#receiveMemberScannerInput').val('').focus();
            resetAndLoadRecentList();
        });

        // Scanner / Manual NIK Member Input
        $('#memberScannerInput').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                processMemberScan($(this).val());
            }
        });

        // Tombol Cari Manual NIK
        $('#btnCheckMemberManual').on('click', function() {
            processMemberScan($('#memberScannerInput').val());
        });

        // Scanner USB Kanban Input
        $('#kanbanScannerInput').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                processKanbanScan($(this).val());
            }
        });

        // Scanner USB Member Receive
        $('#receiveMemberScannerInput').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                processReceiveMemberScan($(this).val());
            }
        });

        // Toggle Mode USB / Kamera Member Input
        $('#btnMemberUsb').on('click', function() {
            $('#btnMemberUsb').addClass('active');
            $('#btnMemberCamera').removeClass('active');
            $('#memberUsbBox').show();
            $('#memberCameraBox').hide();
            stopMemberCamera();
            $('#memberScannerInput').focus();
        });

        $('#btnMemberCamera').on('click', function() {
            $('#btnMemberCamera').addClass('active');
            $('#btnMemberUsb').removeClass('active');
            $('#memberUsbBox').hide();
            $('#memberCameraBox').show();
            startMemberCamera();
        });

        $('#btnStopMemberCamera').on('click', function() {
            $('#btnMemberUsb').trigger('click');
        });

        // Toggle Mode USB / Kamera Kanban Input
        $('#btnKanbanUsb').on('click', function() {
            $('#btnKanbanUsb').addClass('active');
            $('#btnKanbanCamera').removeClass('active');
            $('#kanbanUsbBox').show();
            $('#kanbanCameraBox').hide();
            stopKanbanCamera();
            $('#kanbanScannerInput').focus();
        });

        $('#btnKanbanCamera').on('click', function() {
            $('#btnKanbanCamera').addClass('active');
            $('#btnKanbanUsb').removeClass('active');
            $('#kanbanUsbBox').hide();
            $('#kanbanCameraBox').show();
            startKanbanCamera();
        });

        $('#btnStopKanbanCamera').on('click', function() {
            $('#btnKanbanUsb').trigger('click');
        });

        // Toggle Kamera Receive
        $('#btnReceiveCamera').on('click', function() {
            $('#receiveCameraBox').show();
            startReceiveCamera();
        });

        $('#btnStopReceiveCamera').on('click', function() {
            stopReceiveCamera();
            $('#receiveCameraBox').hide();
            $('#receiveMemberScannerInput').focus();
        });

        // Infinite Scroll Window
        $(window).on('scroll', function() {
            if (isLoadingMore || !hasMoreData) return;
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 250) {
                loadMoreRecentList();
            }
        });
    });

    // =========================================================================
    // 1. SCAN MEMBER INPUT (Langkah 1)
    // =========================================================================
    function processMemberScan(raw) {
        if (!raw) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan scan QR Member atau ketik NIK terlebih dahulu.',
                confirmButtonColor: '#F36494'
            });
            $('#memberScannerInput').focus();
            return;
        }
        raw = raw.trim();

        // Split by ';' ambil index 0 (bisa berupa QR string atau NIK langsung)
        var parts = raw.split(';');
        var nik = parts[0].trim();

        if (!nik) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Valid',
                text: 'NIK member tidak ditemukan.',
                confirmButtonColor: '#F36494'
            });
            $('#memberScannerInput').val('').focus();
            return;
        }

        $.ajax({
            url: '<?php echo e(route("public.part-kurang.check-member")); ?>',
            type: 'POST',
            data: {
                _token: '<?php echo e(csrf_token()); ?>',
                qr: raw
            },
            success: function(res) {
                if (res.valid) {
                    activeMember = res.member;

                    // Tampilkan badge member aktif
                    var photoHtml = activeMember.photo ?
                        '<img src="' + activeMember.photo + '" class="rounded-circle border me-2" style="width:40px;height:40px;object-fit:cover;">' :
                        '<div class="rounded-circle bg-white text-secondary border me-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="fas fa-user"></i></div>';

                    $('#activeMemberBadgeArea').html(
                        '<div class="p-2 border rounded bg-white shadow-sm d-flex align-items-center justify-content-between">' +
                        '  <div class="d-flex align-items-center">' +
                             photoHtml +
                        '    <div>' +
                        '      <strong class="text-primary d-block">' + escHtml(activeMember.nama) + '</strong>' +
                        '      <small class="text-muted">NIK: ' + escHtml(activeMember.nik) + ' &bull; ' + escHtml(activeMember.team) + '</small>' +
                        '    </div>' +
                        '  </div>' +
                        '  <button type="button" class="btn btn-outline-danger btn-sm" onclick="resetActiveMember()"><i class="fas fa-times me-1"></i>Ganti</button>' +
                        '</div>'
                    );

                    $('#memberScannerInput').val('');
                    // Sembunyikan kamera jika belum tersembunyi (dari mode USB)
                    if (isCameraMember) {
                        isCameraMember = false;
                        if (html5QrMember) { html5QrMember.stop().catch(function() {}); }
                    }
                    $('#memberCameraBox').hide();
                    $('#btnMemberUsb').addClass('active');
                    $('#btnMemberCamera').removeClass('active');

                    // Buka Langkah 2: Scan QR Kanban
                    $('#kanbanScanCard').slideDown(250);
                    $('#kanbanScannerInput').val('').focus();

                    Swal.fire({
                        icon: 'success',
                        title: 'Member Terverifikasi',
                        text: 'Halo ' + activeMember.nama + ', silakan scan QR Kanban.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Member Tidak Ditemukan',
                        text: res.message || 'NIK tidak terdaftar sebagai karyawan.',
                        confirmButtonColor: '#F36494'
                    });
                    $('#memberScannerInput').val('').focus();
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan saat memeriksa data member.',
                    confirmButtonColor: '#F36494'
                });
                $('#memberScannerInput').val('').focus();
            }
        });
    }

    function resetActiveMember() {
        activeMember = null;
        $('#activeMemberBadgeArea').html(
            '<div class="p-2 border rounded bg-light text-muted d-flex align-items-center">' +
            '  <i class="fas fa-info-circle text-primary me-2"></i>' +
            '  <small>Silakan scan QR Member atau ketik NIK secara manual.</small>' +
            '</div>'
        );
        stopKanbanCamera();
        $('#kanbanCameraBox').hide();
        $('#kanbanUsbBox').show();
        $('#btnKanbanUsb').addClass('active');
        $('#btnKanbanCamera').removeClass('active');

        $('#kanbanScanCard').slideUp(200);
        $('#resultArea').slideUp(200).empty();
        $('#memberScannerInput').val('').focus();
    }

    // =========================================================================
    // 2. SCAN KANBAN & FORM CATATAN
    // =========================================================================
    function processKanbanScan(text) {
        if (!text) return;
        text = text.toUpperCase().trim();
        var parts = text.split(';');
        if (parts.length >= 3) {
            $('#sequence_no').val(parts[0]);
            $('#production_date').val(parts[1]);
            $('#type').val(parts[2]);
            $('#kanbanScannerInput').val('');

            // Sembunyikan kamera jika sedang aktif (jika dari input USB, hentikan kamera jika ada)
            if (isCameraKanban) {
                $('#kanbanCameraBox').hide();
                $('#kanbanUsbBox').show();
                $('#btnKanbanUsb').addClass('active');
                $('#btnKanbanCamera').removeClass('active');
                isCameraKanban = false;
                if (html5QrKanban) { html5QrKanban.stop().catch(function() {}); }
            }

            searchKanbanRecords(parts[0], parts[1]);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Format QR Tidak Valid',
                text: 'Format: Sequence_No;Production_Date;Type',
                confirmButtonColor: '#F36494'
            });
            $('#kanbanScannerInput').val('').focus();
        }
    }

    function searchKanbanRecords(seq, prodDate) {
        $('#resultArea').html(
            '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Mencari data marshalling...</p></div>'
        ).show();

        // Scroll ke resultArea agar tampil di layar
        $('html, body').animate({
            scrollTop: $('#resultArea').offset().top - 80
        }, 300);

        $.ajax({
            url: '<?php echo e(route("public.part-kurang.search-kanban")); ?>',
            type: 'GET',
            data: {
                sequence_no: seq,
                production_date: prodDate
            },
            success: function(res) {
                if (!res.found) {
                    $('#resultArea').html(
                        '<div class="alert alert-warning text-center"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p class="mb-0">' + escHtml(res.message) + '</p></div>'
                    ).show();
                    return;
                }

                var records = res.records;
                var isMultiple = records.length > 1;

                var html = '<div class="row justify-content-center">';
                html += '  <div class="col-12 col-md-10 col-lg-8">';
                html += '    <div class="card shadow border-0">';
                html += '      <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">';
                html += '        <div>';
                html += '          <span class="badge bg-primary me-2 fs-6"><i class="fas fa-barcode me-1"></i>Seq: ' + escHtml(records[0].Sequence_No) + '</span>';
                html += '          <span class="badge bg-secondary me-2 fs-6">' + escHtml(records[0].Production_Date) + '</span>';
                html += '          <span class="badge bg-info fs-6">' + escHtml(records[0].Type) + '</span>';
                html += '        </div>';
                if (isMultiple) {
                    html += '        <span class="badge bg-dark" id="areaCounterBadge">1 / ' + records.length + ' Area</span>';
                }
                html += '      </div>';
                html += '      <div class="card-body p-3">';

                if (isMultiple) {
                    html += '      <div id="areaCarousel" class="carousel slide" data-bs-ride="false" data-bs-interval="false">';
                    html += '        <div class="carousel-inner">';
                }

                $.each(records, function(idx, r) {
                    if (isMultiple) {
                        html += '      <div class="carousel-item ' + (idx === 0 ? 'active' : '') + '">';
                    }

                    html += '        <div class="profile-card-wrapper mb-3">';
                    if (isMultiple) {
                        html += '          <div class="profile-chevron-btn prev" onclick="$(\'#areaCarousel\').carousel(\'prev\')"><i class="fas fa-chevron-left"></i></div>';
                        html += '          <div class="profile-chevron-btn next" onclick="$(\'#areaCarousel\').carousel(\'next\')"><i class="fas fa-chevron-right"></i></div>';
                    }

                    html += '          <div class="d-flex align-items-center p-2 bg-light rounded border">';
                    if (r.Member_Photo) {
                        html += '            <img src="' + r.Member_Photo + '" class="member-card-photo border me-3" onerror="this.outerHTML=\'<div class=\\\'member-photo-placeholder-sm d-flex align-items-center justify-content-center border me-3\\\'><i class=\\\'fas fa-user fa-2x text-secondary\\\'></i></div>\'">';
                    } else {
                        html += '            <div class="member-photo-placeholder-sm d-flex align-items-center justify-content-center border me-3"><i class="fas fa-user fa-2x text-secondary"></i></div>';
                    }
                    html += '            <div class="flex-grow-1">';
                    html += '              <span class="badge bg-primary mb-1 fs-6"><i class="fas fa-map-marker-alt me-1"></i>' + escHtml(r.Area_Label) + '</span>';
                    html += '              <h6 class="mb-0 fw-bold text-dark">' + escHtml(r.Member_Name) + '</h6>';
                    html += '              <small class="text-muted d-block">NIK: ' + escHtml(r.Member_Nik) + '</small>';
                    html += '              <small class="text-muted d-block">Waktu Record: ' + escHtml(r.Time_Record) + '</small>';
                    html += '            </div>';
                    html += '          </div>';
                    html += '        </div>';

                    // Form Input Part Kurang
                    html += '        <form onsubmit="submitComment(event, ' + r.Id_Record + ')">';
                    html += '          <div class="mb-2">';
                    html += '            <label class="form-label fw-bold"><i class="fas fa-pen me-1"></i>Input Catatan Part Kurang (' + escHtml(r.Area_Label) + ') <span class="text-danger">*</span>:</label>';
                    html += '            <textarea id="commentInput_' + r.Id_Record + '" class="form-control" rows="2" placeholder="Tuliskan part apa yang kurang di area ' + escHtml(r.Area_Label) + '..." required></textarea>';
                    html += '            <div class="invalid-feedback">Catatan part kurang wajib diisi.</div>';
                    html += '          </div>';
                    html += '          <div class="d-flex justify-content-between align-items-center">';
                    html += '            <small class="text-muted"><i class="fas fa-user me-1"></i>Pelapor: <strong>' + escHtml(activeMember.nama) + '</strong> (' + escHtml(activeMember.nik) + ')</small>';
                    html += '            <button type="submit" id="submitBtn_' + r.Id_Record + '" class="btn btn-primary btn-sm px-3 fw-bold">';
                    html += '              <i class="fas fa-save me-1"></i>Simpan';
                    html += '            </button>';
                    html += '          </div>';
                    html += '        </form>';

                    if (isMultiple) {
                        html += '      </div>'; // End carousel-item
                    }
                });

                if (isMultiple) {
                    html += '        </div>'; // End carousel-inner
                    html += '      </div>'; // End carousel
                }

                html += '      </div>'; // End card-body
                html += '    </div>';
                html += '  </div>';
                html += '</div>';

                $('#resultArea').html(html).show();

                // Scroll ke resultArea agar user langsung melihat hasilnya
                $('html, body').animate({
                    scrollTop: $('#resultArea').offset().top - 80
                }, 400);

                if (isMultiple) {
                    $('#areaCarousel').on('slid.bs.carousel', function() {
                        var currentIndex = $('div.carousel-item.active').index() + 1;
                        $('#areaCounterBadge').text(currentIndex + ' / ' + records.length + ' Area');
                    });

                    // Touch Swipe
                    var carouselEl = document.getElementById('areaCarousel');
                    var touchStartX = 0;
                    var touchEndX = 0;

                    carouselEl.addEventListener('touchstart', function(e) {
                        touchStartX = e.changedTouches[0].screenX;
                    }, { passive: true });

                    carouselEl.addEventListener('touchend', function(e) {
                        touchEndX = e.changedTouches[0].screenX;
                        var diffX = touchEndX - touchStartX;
                        if (Math.abs(diffX) > 40) {
                            if (diffX < 0) {
                                $('#areaCarousel').carousel('next');
                            } else {
                                $('#areaCarousel').carousel('prev');
                            }
                        }
                    }, { passive: true });
                }
            },
            error: function() {
                $('#resultArea').html(
                    '<div class="alert alert-danger text-center"><i class="fas fa-times-circle fa-2x mb-2"></i><p class="mb-0">Terjadi kesalahan saat mencari data kanban.</p></div>'
                ).fadeIn(200);
            }
        });
    }

    function submitComment(e, recordId) {
        e.preventDefault();
        var textarea = $('#commentInput_' + recordId);
        var comment = textarea.val();
        var btn = $('#submitBtn_' + recordId);

        if (!activeMember || !activeMember.nik) {
            Swal.fire({
                icon: 'warning',
                title: 'Pelapor Belum Di-scan',
                text: 'Silakan scan QR Member terlebih dahulu di Langkah 1.',
                confirmButtonColor: '#F36494'
            });
            return;
        }

        if (!comment || !comment.trim()) {
            textarea.addClass('is-invalid').focus();
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Catatan part kurang wajib diisi!',
                confirmButtonColor: '#F36494'
            });
            return;
        }

        textarea.removeClass('is-invalid');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

        $.ajax({
            url: '<?php echo e(url("part-kurang")); ?>/' + recordId + '/store',
            type: 'POST',
            data: {
                _token: '<?php echo e(csrf_token()); ?>',
                comment: comment,
                perakitan_nik: activeMember.nik
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Simpan');
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Catatan part kurang berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // Sesuai alur: setiap kali simpan sukses, card hasil scan hilang dan sesi member direset
                    // sehingga user harus scan QR member lagi jika mau input berikutnya
                    $('#resultArea').slideUp(250, function() {
                        $(this).empty();
                    });

                    $('#sequence_no').val('');
                    $('#production_date').val('');
                    $('#type').val('');
                    $('#kanbanScannerInput').val('');

                    resetActiveMember();
                    resetAndLoadRecentList();
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Simpan');
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan saat menyimpan catatan part kurang.',
                    confirmButtonColor: '#F36494'
                });
            }
        });
    }

    // =========================================================================
    // 3. SCAN MEMBER RECEIVE (Mode Penerimaan -> Modal Popup)
    // =========================================================================
    function processReceiveMemberScan(raw) {
        if (!raw) return;
        raw = raw.trim();

        // Split by ';' ambil index 0
        var parts = raw.split(';');
        var nik = parts[0].trim();

        if (!nik) {
            Swal.fire({
                icon: 'warning',
                title: 'QR Tidak Valid',
                text: 'NIK member tidak ditemukan di dalam QR.',
                confirmButtonColor: '#F36494'
            });
            $('#receiveMemberScannerInput').val('').focus();
            return;
        }

        stopReceiveCamera();
        $('#receiveCameraBox').hide();

        Swal.fire({
            title: 'Memuat Laporan...',
            allowOutsideClick: false,
            didOpen: function() {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '<?php echo e(route("public.part-kurang.member-reports")); ?>',
            type: 'GET',
            data: {
                nik: nik,
                status: 'all'
            },
            success: function(res) {
                Swal.close();
                if (res.success) {
                    currentModalMemberNik = nik;
                    cachedModalReports = res.reports || [];

                    // Header modal
                    var member = res.member;
                    var photoHtml = member.photo ?
                        '<img src="' + member.photo + '" class="rounded-circle border" style="width:42px;height:42px;object-fit:cover;">' :
                        '<div class="rounded-circle bg-white text-secondary border d-flex align-items-center justify-content-center" style="width:42px;height:42px;"><i class="fas fa-user"></i></div>';

                    $('#modalMemberPhotoArea').html(photoHtml);
                    $('#modalMemberSubtitle').text(member.nama + ' (NIK: ' + member.nik + ') - ' + res.pending_count + ' Pending');

                    currentModalFilter = 'pending';
                    $('.modal-filter-btn').removeClass('active');
                    $('.modal-filter-btn[data-filter="pending"]').addClass('active');

                    renderModalReports();

                    var modal = new bootstrap.Modal(document.getElementById('memberReceiveModal'));
                    modal.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Gagal memuat data laporan member.',
                        confirmButtonColor: '#F36494'
                    });
                }
            },
            error: function() {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan saat memuat data laporan.',
                    confirmButtonColor: '#F36494'
                });
                $('#receiveMemberScannerInput').val('').focus();
            }
        });
    }

    function renderModalReports() {
        var container = $('#modalReportsContainer');
        container.empty();

        var filtered = cachedModalReports;
        if (currentModalFilter !== 'all') {
            filtered = cachedModalReports.filter(function(item) {
                return item.Status === currentModalFilter;
            });
        }

        if (filtered.length === 0) {
            container.html(
                '<div class="text-center py-5 text-muted">' +
                '  <i class="fas fa-clipboard-check fa-3x mb-2 text-secondary"></i>' +
                '  <p class="mb-0 fw-bold">Tidak ada laporan part kurang dengan filter ini.</p>' +
                '</div>'
            );
            return;
        }

        $.each(filtered, function(idx, item) {
            var isOke = item.Status === 'oke';
            var statusBadge = isOke ?
                '<span class="status-badge-oke"><i class="fas fa-check-circle me-1"></i>Sudah Diterima</span>' :
                '<span class="status-badge-pending"><i class="fas fa-clock me-1"></i>Pending</span>';

            var cardHtml = '<div class="card mb-3 shadow-sm border" id="modalCard_' + item.Id_Part_Kurang + '">';
            cardHtml += '  <div class="card-body p-3">';
            cardHtml += '    <div class="d-flex justify-content-between align-items-center mb-2">';
            cardHtml += '      <div>';
            cardHtml += '        <span class="badge bg-primary me-1 fs-6">Seq: ' + escHtml(item.Sequence_No) + '</span>';
            cardHtml += '        <span class="badge bg-secondary me-1">' + escHtml(item.Production_Date) + '</span>';
            cardHtml += '        <span class="badge bg-info">' + escHtml(item.Type) + '</span>';
            cardHtml += '      </div>';
            cardHtml += '      <div id="modalStatusBadge_' + item.Id_Part_Kurang + '">' + statusBadge + '</div>';
            cardHtml += '    </div>';

            cardHtml += '    <div class="row g-2 mb-2 align-items-center bg-light p-2 rounded">';
            cardHtml += '      <div class="col-12 col-md-6">';
            cardHtml += '        <small class="text-muted d-block"><i class="fas fa-map-marker-alt text-primary me-1"></i>Area: <strong>' + escHtml(item.Area_Label) + '</strong></small>';
            cardHtml += '        <small class="text-muted d-block"><i class="fas fa-user text-secondary me-1"></i>Marshalling: ' + escHtml(item.Member_Name) + ' (' + escHtml(item.Member_Nik) + ')</small>';
            cardHtml += '      </div>';
            cardHtml += '      <div class="col-12 col-md-6 text-md-end">';
            cardHtml += '        <small class="text-muted d-block"><i class="fas fa-calendar-alt me-1"></i>Waktu: ' + escHtml(item.Perakitan_Comment_Time || '-') + '</small>';
            if (item.Received_Time) {
                cardHtml += '        <small class="text-success d-block"><i class="fas fa-check-double me-1"></i>Diterima: ' + escHtml(item.Received_Time) + '</small>';
            }
            cardHtml += '      </div>';
            cardHtml += '    </div>';

            cardHtml += '    <div class="p-2 border rounded bg-white mb-2">';
            cardHtml += '      <strong class="d-block text-danger small mb-1"><i class="fas fa-exclamation-circle me-1"></i>Catatan Part Kurang:</strong>';
            cardHtml += '      <div class="fw-semibold text-dark">' + escHtml(item.Perakitan_Comment) + '</div>';
            cardHtml += '    </div>';

            cardHtml += '    <div class="d-flex justify-content-end align-items-center mt-2" id="modalActionArea_' + item.Id_Part_Kurang + '">';
            if (!isOke) {
                cardHtml += '      <button type="button" class="btn btn-success btn-sm fw-bold px-3" onclick="confirmReceiveModal(' + item.Id_Part_Kurang + ')">';
                cardHtml += '        <i class="fas fa-check-circle me-1"></i>Sudah Diterima';
                cardHtml += '      </button>';
            } else {
                cardHtml += '      <span class="text-success small fw-semibold"><i class="fas fa-check-double me-1"></i>Telah Diterima</span>';
            }
            cardHtml += '    </div>';

            cardHtml += '  </div>';
            cardHtml += '</div>';

            container.append(cardHtml);
        });
    }

    function confirmReceiveModal(id) {
        Swal.fire({
            title: 'Konfirmasi Penerimaan',
            text: 'Apakah part kurang ini sudah benar-benar Anda terima?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check-circle me-1"></i>Ya, Sudah Diterima',
            cancelButtonText: 'Batal'
        }).then(function(res) {
            if (res.isConfirmed) {
                $.ajax({
                    url: '<?php echo e(url("part-kurang")); ?>/' + id + '/receive',
                    type: 'POST',
                    data: {
                        _token: '<?php echo e(csrf_token()); ?>'
                    },
                    success: function(resp) {
                        if (resp.success) {
                            // Tutup modal penerimaan secara otomatis agar member harus scan ulang lagi
                            var modalEl = document.getElementById('memberReceiveModal');
                            var modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) {
                                modalInstance.hide();
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Diterima',
                                text: 'Part kurang berhasil dikonfirmasi diterima. Silakan scan QR Member kembali jika ingin menerima part lainnya.',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Bersihkan input dan fokuskan kembali ke scan member receive
                            $('#receiveMemberScannerInput').val('').focus();

                            // Update riwayat umum di background
                            resetAndLoadRecentList();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal memperbarui status part kurang.',
                            confirmButtonColor: '#F36494'
                        });
                    }
                });
            }
        });
    }

    // =========================================================================
    // 4. RIWAYAT PART KURANG UMUM (INFINITE SCROLL)
    // =========================================================================
    function resetAndLoadRecentList(callback) {
        currentPage = 1;
        hasMoreData = true;
        isLoadingMore = false;
        $('#partKurangCardsContainer').html(
            '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2 mb-0 small">Memuat riwayat part kurang...</p></div>'
        );
        fetchRecentList(currentPage, true, callback);
    }

    function loadMoreRecentList() {
        if (isLoadingMore || !hasMoreData) return;
        currentPage++;
        $('#infiniteScrollStatus').show();
        fetchRecentList(currentPage, false);
    }

    function fetchRecentList(page, isReset, callback) {
        isLoadingMore = true;
        var search = $('#filterSearch').val();

        $.ajax({
            url: '<?php echo e(route("public.part-kurang.recent-list")); ?>',
            type: 'GET',
            data: {
                page: page,
                status: currentStatusFilter,
                search: search
            },
            success: function(res) {
                isLoadingMore = false;
                $('#infiniteScrollStatus').hide();

                if (isReset) {
                    $('#partKurangCardsContainer').empty();
                }

                if (!res.records || res.records.length === 0) {
                    if (isReset) {
                        $('#partKurangCardsContainer').html(
                            '<div class="text-center py-5 text-muted">' +
                            '  <i class="fas fa-clipboard-list fa-3x mb-2 text-secondary"></i>' +
                            '  <p class="mb-0 fw-bold">Belum ada data part kurang.</p>' +
                            '</div>'
                        );
                    }
                    hasMoreData = false;
                    if (typeof callback === 'function') callback();
                    return;
                }

                hasMoreData = res.has_more;
                appendRecentCards(res.records);
                if (typeof callback === 'function') callback();
            },
            error: function() {
                isLoadingMore = false;
                $('#infiniteScrollStatus').hide();
                if (isReset) {
                    $('#partKurangCardsContainer').html(
                        '<div class="alert alert-danger text-center">Gagal memuat riwayat part kurang.</div>'
                    );
                }
                if (typeof callback === 'function') callback();
            }
        });
    }

    function appendRecentCards(records) {
        var container = $('#partKurangCardsContainer');
        $.each(records, function(idx, item) {
            var isOke = item.Status === 'oke';
            var statusBadge = isOke ?
                '<span class="status-badge-oke"><i class="fas fa-check-circle me-1"></i>Sudah Diterima</span>' :
                '<div class="d-flex align-items-center gap-2">' +
                '  <span class="status-badge-pending"><i class="fas fa-clock me-1"></i>Pending</span>' +
                '  <button type="button" class="btn btn-success btn-receive-direct" onclick="confirmReceiveDirect(' + item.Id_Part_Kurang + ')" title="Klik untuk konfirmasi penerimaan part">' +
                '    <i class="fas fa-check-circle me-1"></i>Diterima' +
                '  </button>' +
                '</div>';

            var cardHtml = '<div class="card part-kurang-card mb-3 p-3 shadow-sm">';
            cardHtml += '  <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 pb-2 border-bottom">';
            cardHtml += '    <div>';
            cardHtml += '      <span class="badge bg-primary me-1 fs-6">Seq: ' + escHtml(item.Sequence_No) + '</span>';
            cardHtml += '      <span class="badge bg-secondary me-1">' + escHtml(item.Production_Date) + '</span>';
            cardHtml += '      <span class="badge bg-info">' + escHtml(item.Type) + '</span>';
            cardHtml += '    </div>';
            cardHtml += '    <div class="mt-1 mt-md-0">' + statusBadge + '</div>';
            cardHtml += '  </div>';

            cardHtml += '  <div class="row g-2 align-items-center mb-2">';
            cardHtml += '    <div class="col-12 col-md-6">';
            cardHtml += '      <small class="text-muted d-block"><i class="fas fa-map-marker-alt text-primary me-1"></i>Area Marshalling: <strong>' + escHtml(item.Area_Label) + '</strong></small>';
            cardHtml += '      <small class="text-muted d-block"><i class="fas fa-user me-1"></i>Member: ' + escHtml(item.Member_Name) + ' (' + escHtml(item.Member_Nik) + ')</small>';
            cardHtml += '    </div>';
            cardHtml += '    <div class="col-12 col-md-6 text-md-end">';
            cardHtml += '      <small class="text-muted d-block"><i class="fas fa-user-edit text-info me-1"></i>Pelapor: <strong>' + escHtml(item.Perakitan_Name) + '</strong> (' + escHtml(item.Perakitan_Nik) + ')</small>';
            cardHtml += '      <small class="text-muted d-block"><i class="fas fa-clock me-1"></i>Waktu: ' + escHtml(item.Perakitan_Comment_Time || '-') + '</small>';
            cardHtml += '    </div>';
            cardHtml += '  </div>';

            cardHtml += '  <div class="p-2 border rounded bg-white">';
            cardHtml += '    <strong class="d-block text-danger small mb-1"><i class="fas fa-exclamation-circle me-1"></i>Catatan:</strong>';
            cardHtml += '    <div class="fw-semibold text-dark">' + escHtml(item.Perakitan_Comment) + '</div>';
            cardHtml += '  </div>';

            cardHtml += '</div>';
            container.append(cardHtml);
        });
    }

    function confirmReceiveDirect(id) {
        Swal.fire({
            title: 'Konfirmasi Penerimaan',
            text: 'Apakah part kurang ini sudah benar-benar Anda terima?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check-circle me-1"></i>Ya, Diterima',
            cancelButtonText: 'Batal'
        }).then(function(res) {
            if (res.isConfirmed) {
                $.ajax({
                    url: '<?php echo e(url("part-kurang")); ?>/' + id + '/receive',
                    type: 'POST',
                    data: {
                        _token: '<?php echo e(csrf_token()); ?>'
                    },
                    success: function(resp) {
                        if (resp.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Diterima',
                                text: 'Status part kurang telah diubah menjadi Sudah Diterima.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            resetAndLoadRecentList();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal memperbarui status part kurang.',
                            confirmButtonColor: '#F36494'
                        });
                    }
                });
            }
        });
    }

    // =========================================================================
    // 5. KAMERA DEVICE HELPER FUNCTIONS
    // =========================================================================
    function startMemberCamera() {
        if (html5QrMember && isCameraMember) return;
        html5QrMember = new Html5Qrcode("cameraMemberReader");
        html5QrMember.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            function(decodedText) {
                // Langsung sembunyikan kamera, stop async di background
                $('#memberCameraBox').hide();
                $('#memberUsbBox').show();
                $('#btnMemberUsb').addClass('active');
                $('#btnMemberCamera').removeClass('active');
                isCameraMember = false;
                html5QrMember.stop().catch(function() {});
                processMemberScan(decodedText);
            },
            function(err) {}
        ).then(function() {
            isCameraMember = true;
        }).catch(function(err) {
            Swal.fire({
                icon: 'error',
                title: 'Kamera Gagal Dibuka',
                text: 'Pastikan browser memiliki izin mengakses kamera.',
                confirmButtonColor: '#F36494'
            });
            $('#btnMemberUsb').trigger('click');
        });
    }

    function stopMemberCamera() {
        if (html5QrMember && isCameraMember) {
            html5QrMember.stop().then(function() {
                isCameraMember = false;
            }).catch(function() {
                isCameraMember = false;
            });
        }
    }

    function startKanbanCamera() {
        if (html5QrKanban && isCameraKanban) return;
        html5QrKanban = new Html5Qrcode("cameraKanbanReader");
        html5QrKanban.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            function(decodedText) {
                // Langsung sembunyikan kamera dan proses scan, stop async di background
                $('#kanbanCameraBox').hide();
                $('#kanbanUsbBox').show();
                $('#btnKanbanUsb').addClass('active');
                $('#btnKanbanCamera').removeClass('active');
                isCameraKanban = false;
                html5QrKanban.stop().catch(function() {});
                processKanbanScan(decodedText);
            },
            function(err) {}
        ).then(function() {
            isCameraKanban = true;
        }).catch(function(err) {
            Swal.fire({
                icon: 'error',
                title: 'Kamera Gagal Dibuka',
                text: 'Pastikan browser memiliki izin mengakses kamera.',
                confirmButtonColor: '#F36494'
            });
            $('#btnKanbanUsb').trigger('click');
        });
    }

    function stopKanbanCamera() {
        if (html5QrKanban && isCameraKanban) {
            html5QrKanban.stop().then(function() {
                isCameraKanban = false;
            }).catch(function() {
                isCameraKanban = false;
            });
        }
    }

    function startReceiveCamera() {
        if (html5QrReceive && isCameraReceive) return;
        html5QrReceive = new Html5Qrcode("cameraReceiveReader");
        html5QrReceive.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            function(decodedText) {
                stopReceiveCamera();
                processReceiveMemberScan(decodedText);
            },
            function(err) {}
        ).then(function() {
            isCameraReceive = true;
        }).catch(function(err) {
            Swal.fire({
                icon: 'error',
                title: 'Kamera Gagal Dibuka',
                text: 'Pastikan browser memiliki izin mengakses kamera.',
                confirmButtonColor: '#F36494'
            });
            $('#btnStopReceiveCamera').trigger('click');
        });
    }

    function stopReceiveCamera() {
        if (html5QrReceive && isCameraReceive) {
            html5QrReceive.stop().then(function() {
                isCameraReceive = false;
            }).catch(function() {
                isCameraReceive = false;
            });
        }
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\iseki_marshalling\resources\views/public/part-kurang/index.blade.php ENDPATH**/ ?>