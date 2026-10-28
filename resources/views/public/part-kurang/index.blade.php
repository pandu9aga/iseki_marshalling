@extends('layouts.main')

@section('style')
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
        @keyframes boxFadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .box-card {
            opacity: 0;
            animation: boxFadeIn 0.4s ease-out forwards;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .box-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(243, 100, 148, 0.25);
        }
        .box-card-photo {
            width: 100%;
            height: auto;
            aspect-ratio: 1 / 1;
            object-fit: cover;
        }
        .filter-btn-group .btn {
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
        }
        .camera-scanner-container {
            border-radius: 10px;
            overflow: hidden;
            background: #000;
            position: relative;
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
        #cameraMemberReader, #cameraKanbanReader, #cameraReceiveReader {
            position: relative;
            cursor: pointer;
        }
        #cameraMemberReader video, #cameraKanbanReader video, #cameraReceiveReader video {
            width: 100% !important;
            height: auto !important;
            border-radius: 8px;
            cursor: pointer;
        }
        .camera-focus-ring {
            position: absolute;
            width: 60px;
            height: 60px;
            border: 2px solid #20c997;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(32, 201, 151, 0.7);
            pointer-events: none;
            transform: translate(-50%, -50%) scale(1.4);
            opacity: 1;
            transition: transform 0.25s ease-out, opacity 0.4s ease-out;
            z-index: 9999;
        }
        .camera-focus-ring.active {
            transform: translate(-50%, -50%) scale(1);
            opacity: 0.9;
        }
        .camera-focus-ring.fade-out {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.85);
        }

        /* ===== Layout modal hasil scan kanban ===== */
        .kanban-layout {
            display: flex;
            height: 100%;
        }
        .kanban-sidebar {
            width: 300px;
            flex-shrink: 0;
            overflow-y: auto;
            background: #fff;
        }
        .kanban-main {
            flex: 1 1 auto;
            min-width: 0;
            overflow-y: auto;
        }
        .profile-row {
            display: block;
        }

        /* ===== Mobile ===== */
        @media (max-width: 767.98px) {
            #kanbanResultModal .modal-header {
                flex-wrap: wrap;
                gap: .4rem;
                padding: .5rem .75rem;
            }
            #kanbanResultModal .modal-header .badge.fs-6 {
                font-size: .75rem !important;
            }
            #kanbanResultModal .modal-header .btn-close {
                margin-left: auto;
            }

            .kanban-layout {
                flex-direction: column;
                height: auto;
            }
            .kanban-sidebar {
                width: 100%;
                overflow: visible;
                border-right: 0 !important;
                border-bottom: 1px solid #dee2e6;
            }
            .kanban-main {
                overflow: visible;
                padding: .75rem !important;
            }

            /* Sembunyikan profil marshalling & pelapor di mobile, form lapor tetap tampil */
            .profile-row {
                display: none;
            }

            #boxListContainer > [class*="col-"] {
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }

            .box-card .card-body {
                padding: .5rem !important;
            }
            .box-card .card-body h5 {
                font-size: .95rem;
            }
            .box-card .card-body .fa-2x {
                font-size: 1.3em;
            }

            #modalReportsContainer .card-body > .d-flex:first-child {
                flex-wrap: wrap;
                gap: .35rem;
            }
            #memberReceiveModal .modal-footer {
                flex-wrap: wrap;
            }

            .page-header .btn {
                font-size: .75rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="page-inner">
            <!-- Header Page -->
            <div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-3 g-2">
                <div>
                    <h4 class="page-title text-primary mb-0"><i class="fas fa-clipboard-list me-2"></i>Part Kurang</h4>
                    <small class="text-muted">Input & Konfirmasi Part Kurang (Tanpa Login)</small>
                </div>
                <div>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">
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
                                    <div class="camera-scanner-container p-2 border position-relative text-center">
                                        <div id="cameraMemberReader" style="width: 100%; min-height: 220px;"></div>
                                        <div class="mt-2 d-flex justify-content-between align-items-center flex-wrap gap-1">
                                            <small class="text-white"><i class="fas fa-hand-pointer me-1 text-warning"></i>Ketuk preview kamera untuk fokus</small>
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
                                    <div class="camera-scanner-container p-2 border position-relative text-center">
                                        <div id="cameraKanbanReader" style="width: 100%; min-height: 220px;"></div>
                                        <div class="mt-2 d-flex justify-content-between align-items-center flex-wrap gap-1">
                                            <small class="text-white"><i class="fas fa-hand-pointer me-1 text-warning"></i>Ketuk preview kamera untuk fokus</small>
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
                                    <div class="camera-scanner-container p-2 border position-relative text-center">
                                        <div id="cameraReceiveReader" style="width: 100%; min-height: 220px;"></div>
                                        <div class="mt-2 d-flex justify-content-between align-items-center flex-wrap gap-1">
                                            <small class="text-white"><i class="fas fa-hand-pointer me-1 text-warning"></i>Ketuk preview kamera untuk fokus</small>
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

    <!-- ======================= MODAL HASIL SCAN KANBAN (FULLSCREEN: SIDEBAR KIRI + BOX CARDS KANAN, STACKED DI MOBILE) ======================= -->
    <div class="modal fade" id="kanbanResultModal" tabindex="-1" aria-labelledby="kanbanResultModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable m-0">
            <div class="modal-content border-0">
                <div class="modal-header bg-white border-bottom py-2">
                    <div id="kanbanResultModalBadges" class="d-flex align-items-center flex-wrap gap-1">
                        <h6 class="modal-title fw-bold mb-0 me-2" id="kanbanResultModalLabel">Hasil Scan Kanban</h6>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnPrevArea" onclick="navigateArea(-1)"><i class="fas fa-chevron-left"></i></button>
                        <span class="badge bg-dark" id="areaCounterBadge">1 / 1</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnNextArea" onclick="navigateArea(1)"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="kanban-layout">
                        <!-- Sidebar: profil marshalling + pelapor + form lapor -->
                        <div class="kanban-sidebar border-end">
                            <div class="p-3">
                                <div class="profile-row">
                                    <div>
                                        <small class="text-muted text-uppercase fw-bold d-block mb-1" style="letter-spacing:0.05em;">Marshalling</small>
                                        <div class="card border-0 shadow-sm mb-3">
                                            <div class="card-body text-center p-3" id="marshallingProfileSidebar"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase fw-bold d-block mb-1" style="letter-spacing:0.05em;">Pelapor</small>
                                        <div class="card border-0 shadow-sm mb-3">
                                            <div class="card-body text-center p-3" id="reporterProfileSidebar"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main content: box card dari foto_part untuk area yang aktif -->
                        <div class="kanban-main p-3 bg-light">
                            <h6 class="fw-bold text-primary mb-3" id="boxListAreaTitle"></h6>
                            <div id="boxListContainer" class="row"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="{{ asset('assets/js/plugin/html5-qrcode.min.js') }}"></script>
    <script>
        // State Global Pelapor
        var activeMember = null; // { nik, nama, photo, team }
        var currentActiveMode = 'input'; // 'input' atau 'receive'

        // State Modal Hasil Scan Kanban
        var currentKanbanRecords = []; // daftar Area/Record hasil scan kanban
        var currentKanbanRecordIndex = 0; // index Area yang sedang aktif di main content
        var currentItems = []; // daftar item/part yang sedang ditampilkan di main content
        var refreshListAfterModalClose = false; // true jika submit sukses, refresh riwayat setelah modal benar-benar tertutup

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

            // Event Modal hasil scan kanban ditutup: bersihkan isi & fokus kembali ke scan kanban
            // (jika ditutup tanpa submit, member yang sudah di-scan tetap aktif untuk scan kanban lain)
            $('#kanbanResultModal').on('hidden.bs.modal', function() {
                $('#boxListContainer').empty();
                $('#marshallingProfileSidebar').empty();
                $('#reporterProfileSidebar').empty();
                $('#boxListAreaTitle').empty();
                currentKanbanRecords = [];
                currentKanbanRecordIndex = 0;
                currentItems = [];

                if (refreshListAfterModalClose) {
                    // Modal ditutup manual setelah minimal 1 laporan berhasil disimpan selama sesi ini:
                    // reset sesi pelapor, bersihkan input kanban, lalu refresh riwayat di bawah.
                    refreshListAfterModalClose = false;
                    $('#sequence_no').val('');
                    $('#production_date').val('');
                    $('#type').val('');
                    $('#kanbanScannerInput').val('');
                    resetActiveMember();
                    resetAndLoadRecentList();
                } else if (activeMember) {
                    // Ditutup tanpa laporan apa pun: member tetap aktif untuk scan kanban lain
                    $('#kanbanScannerInput').val('').focus();
                }
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
                url: '{{ route("public.part-kurang.check-member") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
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
            closeKanbanResultModal();
            $('#memberScannerInput').val('').focus();
        }

        function closeKanbanResultModal() {
            var modalEl = document.getElementById('kanbanResultModal');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
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
            Swal.fire({
                title: 'Mencari Data...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route("public.part-kurang.search-kanban") }}',
                type: 'GET',
                data: {
                    sequence_no: seq,
                    production_date: prodDate
                },
                success: function(res) {
                    Swal.close();

                    if (!res.found) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Ditemukan',
                            text: res.message,
                            confirmButtonColor: '#F36494'
                        });
                        $('#kanbanScannerInput').val('').focus();
                        return;
                    }

                    var records = res.records;
                    currentKanbanRecords = records;
                    currentKanbanRecordIndex = 0;

                    // Header badges modal
                    $('#kanbanResultModalBadges').html(
                        '<span class="badge bg-primary me-2 fs-6"><i class="fas fa-barcode me-1"></i>Seq: ' + escHtml(records[0].Sequence_No) + '</span>' +
                        '<span class="badge bg-secondary me-2 fs-6">' + escHtml(records[0].Production_Date) + '</span>' +
                        '<span class="badge bg-info fs-6">' + escHtml(records[0].Type) + '</span>'
                    );

                    // Sidebar: profil pelapor (card, tetap sama selama modal terbuka)
                    var photoHtml = activeMember.photo ?
                        '<img src="' + activeMember.photo + '" class="rounded-circle border mb-2" style="width:80px;height:80px;object-fit:cover;">' :
                        '<div class="rounded-circle bg-light text-secondary border mb-2 d-flex align-items-center justify-content-center mx-auto" style="width:80px;height:80px;"><i class="fas fa-user fa-2x"></i></div>';

                    $('#reporterProfileSidebar').html(
                        photoHtml +
                        '<h6 class="fw-bold mb-0">' + escHtml(activeMember.nama) + '</h6>' +
                        '<small class="text-muted d-block mb-1">NIK: ' + escHtml(activeMember.nik) + '</small>' +
                        '<span class="badge bg-light text-dark border">' + escHtml(activeMember.team) + '</span>'
                    );

                    // Tampilkan box untuk area pertama, lalu buka modal
                    selectKanbanArea(0);

                    var modalEl = document.getElementById('kanbanResultModal');
                    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat mencari data kanban.',
                        confirmButtonColor: '#F36494'
                    });
                }
            });
        }

        // Tombol chevron kiri/kanan di header modal untuk pindah area
        function navigateArea(delta) {
            var next = currentKanbanRecordIndex + delta;
            if (next < 0 || next >= currentKanbanRecords.length) return;
            selectKanbanArea(next);
        }

        // Pindah area aktif: update profil member marshalling, counter, tombol chevron & muat ulang box
        function selectKanbanArea(idx) {
            currentKanbanRecordIndex = idx;
            var record = currentKanbanRecords[idx];
            if (!record) return;

            var letter = String.fromCharCode(65 + (idx % 26));
            $('#boxListAreaTitle').html('<span class="badge bg-primary me-2">' + letter + '</span>' + escHtml(record.Area_Label));

            // Profil member marshalling (pemilik area/record yang sedang aktif)
            var memberPhotoHtml = record.Member_Photo ?
                '<img src="' + record.Member_Photo + '" class="rounded-circle border mb-2" style="width:80px;height:80px;object-fit:cover;">' :
                '<div class="rounded-circle bg-light text-secondary border mb-2 d-flex align-items-center justify-content-center mx-auto" style="width:80px;height:80px;"><i class="fas fa-user fa-2x"></i></div>';

            $('#marshallingProfileSidebar').html(
                memberPhotoHtml +
                '<span class="badge bg-primary mb-1"><i class="fas fa-map-marker-alt me-1"></i>' + escHtml(record.Area_Label) + '</span>' +
                '<h6 class="fw-bold mb-0">' + escHtml(record.Member_Name) + '</h6>' +
                '<small class="text-muted d-block">NIK: ' + escHtml(record.Member_Nik) + '</small>' +
                '<small class="text-muted d-block">Waktu Record: ' + escHtml(record.Time_Record) + '</small>'
            );

            // Counter & state tombol chevron
            $('#areaCounterBadge').text((idx + 1) + ' / ' + currentKanbanRecords.length);
            $('#btnPrevArea').prop('disabled', idx === 0);
            $('#btnNextArea').prop('disabled', idx === currentKanbanRecords.length - 1);

            loadBoxesForRecord(record);
        }

        // Cek response AJAX masih relevan (area aktif belum berpindah sejak request dikirim)
        function isStillActiveRecord(record) {
            var current = currentKanbanRecords[currentKanbanRecordIndex];
            return current && current.Id_Record === record.Id_Record;
        }

        // Ambil daftar box (dari master marshallings + foto_parts) untuk Type+Area record aktif.
        // Jika NIK pelapor sudah terdaftar di member_perakitans untuk area ini, langsung
        // tampilkan item box tsb tanpa lewat daftar box.
        function loadBoxesForRecord(record) {
            $('#boxListContainer').html(
                '<div class="col-12 text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2 mb-0 small">Memuat box...</p></div>'
            );

            $.ajax({
                url: '{{ route("public.part-kurang.record-boxes") }}',
                type: 'GET',
                data: {
                    type: record.Type,
                    area: record.Area,
                    nik: activeMember ? activeMember.nik : null
                },
                success: function(res) {
                    console.log(res)
                    if (!isStillActiveRecord(record)) return;

                    var boxes = res.boxes || [];
                    var assignedBoxes = res.assigned_boxes || [];

                    if (assignedBoxes.length > 0) {
                        // Reporter sudah terdaftar di member_perakitans untuk area ini: langsung ke item
                        loadItemsForRecord(record, assignedBoxes, false);
                        return;
                    }

                    if (boxes.length === 0) {
                        $('#boxListContainer').html('<div class="col-12 text-center text-muted py-4">Belum ada box untuk area ini.</div>');
                        return;
                    }

                    $('#boxListAreaTitle').html(
                        '<span class="badge bg-primary me-2">' + String.fromCharCode(65 + (currentKanbanRecordIndex % 26)) + '</span>' + escHtml(record.Area_Label)
                    );

                    var html = '';
                    $.each(boxes, function(i, b) {
                        html += '<div class="col-md-3 col-sm-4 col-6 mb-3">';
                        html += '  <div class="card text-center border-primary box-card" style="animation-delay:' + (i * 0.08) + 's;cursor:pointer;" onclick="loadItemsForRecord(currentKanbanRecords[' + currentKanbanRecordIndex + '], [\'' + escHtml(b.Box).replace(/'/g, "\\'") + '\'], true)">';
                        html += '    <div class="card-body py-3">';
                        html += '      <i class="fas fa-box fa-2x text-primary mb-2"></i>';
                        html += '      <h5 class="mb-1 text-primary">Box ' + escHtml(b.Box) + '</h5>';
                        html += '      <small class="text-muted">' + b.Qty_Parts + ' part</small>';
                        html += '    </div>';
                        html += '  </div>';
                        html += '</div>';
                    });

                    $('#boxListContainer').html(html);
                },
                error: function() {
                    if (!isStillActiveRecord(record)) return;
                    $('#boxListContainer').html('<div class="col-12 text-center text-danger py-4">Gagal memuat data box.</div>');
                }
            });
        }

        // Ambil daftar item/part untuk satu atau beberapa box, lalu render sebagai card.
        // showBack: tampilkan tombol "Kembali ke Box" (dipakai saat masuk dari klik box manual).
        function loadItemsForRecord(record, boxes, showBack) {
            $('#boxListContainer').html(
                '<div class="col-12 text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2 mb-0 small">Memuat item...</p></div>'
            );

            var letter = String.fromCharCode(65 + (currentKanbanRecordIndex % 26));
            var titleHtml = '<span class="badge bg-primary me-2">' + letter + '</span>' + escHtml(record.Area_Label) + ' &bull; Box ' + escHtml(boxes.join(', '));
            if (showBack) {
                titleHtml += ' <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="loadBoxesForRecord(currentKanbanRecords[' + currentKanbanRecordIndex + '])"><i class="fas fa-arrow-left me-1"></i>Kembali ke Box</button>';
            }
            $('#boxListAreaTitle').html(titleHtml);

            $.ajax({
                url: '{{ route("public.part-kurang.record-items") }}',
                type: 'GET',
                data: {
                    type: record.Type,
                    area: record.Area,
                    boxes: boxes
                },
                success: function(res) {
                    if (!isStillActiveRecord(record)) return;

                    var items = res.items || [];
                    currentItems = items;
                    if (items.length === 0) {
                        $('#boxListContainer').html('<div class="col-12 text-center text-muted py-4">Belum ada part untuk box ini.</div>');
                        return;
                    }

                    var html = '';
                    $.each(items, function(i, it) {
                        html += '<div class="col-6 col-md-4 mb-3">';
                        html += '  <div class="card h-100 box-card item-card" style="animation-delay:' + (i * 0.05) + 's;cursor:pointer;" onclick="reportItemIssue(' + i + ')">';
                        if (it.Photo) {
                            html += '    <img src="' + it.Photo + '" class="card-img-top box-card-photo" onerror="this.remove()">';
                        }
                        html += '    <div class="card-body p-3">';
                        html += '      <span class="badge bg-secondary mb-1">Box ' + escHtml(it.Box) + '</span>';
                        html += '      <h6 class="mb-1 fw-bold">' + escHtml(it.Name_Part) + '</h6>';
                        html += '      <small class="text-muted d-block">Qty: ' + escHtml(it.Qty) + '</small>';
                        html += '    </div>';
                        html += '  </div>';
                        html += '</div>';
                    });

                    $('#boxListContainer').html(html);
                },
                error: function() {
                    if (!isStillActiveRecord(record)) return;
                    $('#boxListContainer').html('<div class="col-12 text-center text-danger py-4">Gagal memuat data item.</div>');
                }
            });
        }

        // Klik item -> pilih kendala (Kosong/Salah langsung submit, Kurang minta input jumlah)
        // Bootstrap 5 punya focus-trap yang menarik paksa fokus balik ke modal setiap kali
        // elemen di luar modal (termasuk input SweetAlert2) mendapat fokus. Render Swal
        // sebagai anak dari #kanbanResultModal supaya dianggap "di dalam" modal dan input bisa diketik.
        function swalInModal(opts) {
            var modalEl = document.getElementById('kanbanResultModal');
            if (modalEl && modalEl.classList.contains('show')) {
                opts.target = modalEl;
            }
            return Swal.fire(opts);
        }

        function reportItemIssue(idx) {
            var item = currentItems[idx];
            var record = currentKanbanRecords[currentKanbanRecordIndex];
            if (!item || !record) return;

            if (!activeMember || !activeMember.nik) {
                swalInModal({
                    icon: 'warning',
                    title: 'Pelapor Belum Di-scan',
                    text: 'Silakan scan QR Member terlebih dahulu di Langkah 1.',
                    confirmButtonColor: '#F36494'
                });
                return;
            }

            swalInModal({
                title: escHtml(item.Name_Part),
                text: 'Pilih kendala untuk part ini:',
                icon: 'question',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-times-circle me-1"></i>Kosong',
                denyButtonText: '<i class="fas fa-exclamation-triangle me-1"></i>Salah',
                cancelButtonText: '<i class="fas fa-minus-circle me-1"></i>Kurang',
                confirmButtonColor: '#dc3545',
                denyButtonColor: '#6f42c1',
                cancelButtonColor: '#ffc107'
            }).then(function(result) {
                if (result.isConfirmed) {
                    submitItemIssue(record.Id_Record, 'kosong', item, item.Qty);
                } else if (result.isDenied) {
                    submitItemIssue(record.Id_Record, 'salah', item, item.Qty);
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    swalInModal({
                        title: 'Jumlah Ditemukan',
                        input: 'number',
                        inputLabel: 'Part "' + item.Name_Part + '" (dari total ' + item.Qty + ')',
                        inputAttributes: { min: 0, max: item.Qty, step: 1 },
                        inputValue: '',
                        showCancelButton: true,
                        confirmButtonText: 'Simpan',
                        confirmButtonColor: '#F36494',
                        inputValidator: function(value) {
                            if (value === '' || value === null || isNaN(value) || Number(value) < 0) {
                                return 'Jumlah wajib diisi dengan angka valid.';
                            }
                        }
                    }).then(function(qtyResult) {
                        if (qtyResult.isConfirmed) {
                            submitItemIssue(record.Id_Record, 'kurang', item, qtyResult.value);
                        }
                    });
                }
            });
        }

        // Kirim laporan part kurang untuk 1 item. Pesan otomatis: "{item} {jumlah} {lokasi rack}"
        function submitItemIssue(recordId, category, item, jumlah) {
            var comment = item.Code_Rack + ' ' + item.Location_Rack + ' (' + jumlah + ') ';

            swalInModal({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ url("part-kurang") }}/' + recordId + '/store',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    comment: comment,
                    category: category,
                    perakitan_nik: activeMember.nik
                },
                success: function(res) {
                    if (res.success) {
                        swalInModal({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Catatan part kurang berhasil disimpan.',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Modal TIDAK ditutup otomatis, supaya user bisa lapor beberapa item
                        // sekaligus selama masih di area/kanban yang sama.
                        // Reset member & refresh riwayat baru dilakukan saat modal ditutup manual
                        // (tombol close), lihat hidden.bs.modal di bawah.
                        refreshListAfterModalClose = true;
                    }
                },
                error: function() {
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
                url: '{{ route("public.part-kurang.member-reports") }}',
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

                var cat = (item.Category || 'kurang').toLowerCase();
                var categoryBadge = '';
                if (cat === 'kosong') {
                    categoryBadge = '<span class="badge bg-danger me-1"><i class="fas fa-times-circle me-1"></i>KOSONG</span>';
                } else if (cat === 'salah') {
                    categoryBadge = '<span class="badge text-white me-1" style="background-color:#6f42c1;"><i class="fas fa-exclamation-triangle me-1"></i>SALAH</span>';
                } else {
                    categoryBadge = '<span class="badge bg-warning text-dark me-1"><i class="fas fa-minus-circle me-1"></i>KURANG</span>';
                }

                var cardHtml = '<div class="card mb-3 shadow-sm border" id="modalCard_' + item.Id_Part_Kurang + '">';
                cardHtml += '  <div class="card-body p-3">';
                cardHtml += '    <div class="d-flex justify-content-between align-items-center mb-2">';
                cardHtml += '      <div>';
                cardHtml += '        <span class="badge bg-primary me-1 fs-6">Seq: ' + escHtml(item.Sequence_No) + '</span>';
                cardHtml += '        <span class="badge bg-secondary me-1">' + escHtml(item.Production_Date) + '</span>';
                cardHtml += '        <span class="badge bg-info me-1">' + escHtml(item.Type) + '</span>';
                cardHtml += '        ' + categoryBadge;
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
                        url: '{{ url("part-kurang") }}/' + id + '/receive',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(r) {
                            if (r.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Part kurang berhasil dikonfirmasi diterima.',
                                    timer: 1200,
                                    showConfirmButton: false
                                });

                                // Update cached item
                                var found = cachedModalReports.find(function(it) {
                                    return it.Id_Part_Kurang == id;
                                });
                                if (found) {
                                    found.Status = 'oke';
                                    found.Received_Time = 'Baru Saja';
                                }

                                // Re-render
                                renderModalReports();
                                fetchRecentList(1, true);
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
                url: '{{ route("public.part-kurang.recent-list") }}',
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

                var cat = (item.Category || 'kurang').toLowerCase();
                var categoryBadge = '';
                if (cat === 'kosong') {
                    categoryBadge = '<span class="badge bg-danger me-1"><i class="fas fa-times-circle me-1"></i>KOSONG</span>';
                } else if (cat === 'salah') {
                    categoryBadge = '<span class="badge text-white me-1" style="background-color:#6f42c1;"><i class="fas fa-exclamation-triangle me-1"></i>SALAH</span>';
                } else {
                    categoryBadge = '<span class="badge bg-warning text-dark me-1"><i class="fas fa-minus-circle me-1"></i>KURANG</span>';
                }

                var cardHtml = '<div class="card part-kurang-card mb-3 p-3 shadow-sm">';
                cardHtml += '  <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 pb-2 border-bottom">';
                cardHtml += '    <div>';
                cardHtml += '      <span class="badge bg-primary me-1 fs-6">Seq: ' + escHtml(item.Sequence_No) + '</span>';
                cardHtml += '      <span class="badge bg-secondary me-1">' + escHtml(item.Production_Date) + '</span>';
                cardHtml += '      <span class="badge bg-info me-1">' + escHtml(item.Type) + '</span>';
                cardHtml += '      ' + categoryBadge;
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
                        url: '{{ url("part-kurang") }}/' + id + '/receive',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
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

        // Fungsi pembersihan & pelepasan hardware kamera secara tuntas
        function forceReleaseCamera(containerSelector) {
            var $video = $(containerSelector).find('video');
            if ($video.length) {
                $video.each(function() {
                    if (this.srcObject) {
                        var stream = this.srcObject;
                        var tracks = stream.getTracks();
                        tracks.forEach(function(track) {
                            try { track.stop(); } catch(e) {}
                        });
                        this.srcObject = null;
                    }
                });
            }
        }

        function stopMemberCamera() {
            if (html5QrMember && isCameraMember) {
                html5QrMember.stop().then(function() {
                    isCameraMember = false;
                    forceReleaseCamera('#cameraMemberReader');
                }).catch(function() {
                    isCameraMember = false;
                    forceReleaseCamera('#cameraMemberReader');
                });
            } else {
                forceReleaseCamera('#cameraMemberReader');
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
                    forceReleaseCamera('#cameraKanbanReader');
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
                    forceReleaseCamera('#cameraKanbanReader');
                }).catch(function() {
                    isCameraKanban = false;
                    forceReleaseCamera('#cameraKanbanReader');
                });
            } else {
                forceReleaseCamera('#cameraKanbanReader');
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
                    forceReleaseCamera('#cameraReceiveReader');
                }).catch(function() {
                    isCameraReceive = false;
                    forceReleaseCamera('#cameraReceiveReader');
                });
            } else {
                forceReleaseCamera('#cameraReceiveReader');
            }
        }

        // Tutup seluruh kamera scanner yang sedang aktif dan kembalikan UI ke mode USB
        function closeAllCamerasToUsbMode() {
            if (isCameraMember) {
                stopMemberCamera();
                $('#memberCameraBox').hide();
                $('#memberUsbBox').show();
                $('#btnMemberUsb').addClass('active');
                $('#btnMemberCamera').removeClass('active');
            }
            if (isCameraKanban) {
                stopKanbanCamera();
                $('#kanbanCameraBox').hide();
                $('#kanbanUsbBox').show();
                $('#btnKanbanUsb').addClass('active');
                $('#btnKanbanCamera').removeClass('active');
            }
            if (isCameraReceive) {
                stopReceiveCamera();
                $('#receiveCameraBox').hide();
            }
        }

        // Auto close kamera saat pindah tab, minimize browser, atau keluar browser
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden') {
                closeAllCamerasToUsbMode();
            }
        });

        window.addEventListener('pagehide', function() {
            closeAllCamerasToUsbMode();
        });

        window.addEventListener('beforeunload', function() {
            closeAllCamerasToUsbMode();
        });

        // Trigger Auto Focus saat preview kamera diklik / disentuh
        function triggerCameraAutoFocus(containerSelector, event) {
            var $container = $(containerSelector);
            if (!$container.length) return;

            // 1. Tampilkan animasi focus ring di titik klik/sentuh
            var offset = $container.offset();
            var clickX = (event.pageX || (event.originalEvent && event.originalEvent.touches && event.originalEvent.touches[0].pageX)) - offset.left;
            var clickY = (event.pageY || (event.originalEvent && event.originalEvent.touches && event.originalEvent.touches[0].pageY)) - offset.top;

            if (isNaN(clickX) || isNaN(clickY) || clickX <= 0 || clickY <= 0) {
                clickX = $container.width() / 2;
                clickY = $container.height() / 2;
            }

            var $ring = $('<div class="camera-focus-ring"></div>');
            $ring.css({ left: clickX + 'px', top: clickY + 'px' });
            $container.append($ring);

            setTimeout(function() {
                $ring.addClass('active');
            }, 10);

            setTimeout(function() {
                $ring.addClass('fade-out');
                setTimeout(function() {
                    $ring.remove();
                }, 400);
            }, 600);

            // 2. Dapatkan MediaStreamTrack dari video element
            var videoEl = $container.find('video')[0];
            if (!videoEl || !videoEl.srcObject) return;

            var stream = videoEl.srcObject;
            var tracks = stream.getVideoTracks();
            if (!tracks || !tracks.length) return;

            var track = tracks[0];
            if (!track.getCapabilities || !track.applyConstraints) return;

            try {
                var capabilities = track.getCapabilities();
                if (capabilities.focusMode) {
                    // Jika browser mendukung focusMode continuous / single-shot
                    track.applyConstraints({
                        advanced: [{ focusMode: "continuous" }]
                    }).catch(function() {
                        track.applyConstraints({
                            advanced: [{ focusMode: "manual" }]
                        }).then(function() {
                            setTimeout(function() {
                                track.applyConstraints({
                                    advanced: [{ focusMode: "continuous" }]
                                }).catch(function() {});
                            }, 100);
                        }).catch(function() {});
                    });
                }

                // Jika browser mendukung pointsOfInterest (tap-to-focus point)
                if (capabilities.pointsOfInterest) {
                    var normX = Math.min(1, Math.max(0, clickX / $container.width()));
                    var normY = Math.min(1, Math.max(0, clickY / $container.height()));
                    track.applyConstraints({
                        advanced: [{
                            pointsOfInterest: [{ x: normX, y: normY }]
                        }]
                    }).catch(function() {});
                }
            } catch (e) {
                console.log("Autofocus apply constraints info:", e);
            }
        }

        // Pasang listener tap/click pada semua kamera scanner reader
        $('#cameraMemberReader, #cameraKanbanReader, #cameraReceiveReader').on('click', function(e) {
            triggerCameraAutoFocus(this, e);
        });

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
@endsection
