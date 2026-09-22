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
    .record-comment-box {
        border-radius: 10px;
        transition: all 0.2s ease-in-out;
    }
    .record-comment-box:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .status-badge-pending {
        background-color: #ffc107;
        color: #212529;
        font-weight: 600;
        padding: 0.35em 0.7em;
        border-radius: 6px;
    }
    .status-badge-oke {
        background-color: #198754;
        color: #fff;
        font-weight: 600;
        padding: 0.35em 0.7em;
        border-radius: 6px;
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
        transition: all 0.2s ease-in-out;
    }
    .profile-chevron-btn:hover {
        background: #0d6efd;
        color: #fff;
        transform: translateY(-50%) scale(1.1);
    }
    .profile-chevron-btn.btn-prev {
        left: -12px;
    }
    .profile-chevron-btn.btn-next {
        right: -12px;
    }
    #areaCarousel {
        touch-action: pan-y pinch-zoom;
    }
    .btn-outline-purple {
        color: #6f42c1;
        border-color: #6f42c1;
        background-color: transparent;
    }
    .btn-outline-purple:hover {
        color: #fff;
        background-color: #6f42c1;
        border-color: #6f42c1;
    }
    .btn-check:checked + .btn-outline-purple {
        color: #fff !important;
        background-color: #6f42c1 !important;
        border-color: #6f42c1 !important;
        box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.5);
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center mb-3">
            <h4 class="page-title text-primary mb-0"><i class="fas fa-comment-dots me-2"></i>Part Kurang</h4>
            <span class="badge bg-light text-dark border"><i class="fas fa-user-check me-1"></i>{{ Auth::guard('perakitan')->user()->nama ?? 'Perakitan' }}</span>
        </div>

        <!-- 1. SCAN QR KANBAN CARD (Dukungan USB Scanner & Kamera Scanner) -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold small text-muted"><i class="fas fa-qrcode me-1"></i>Scan QR:</span>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary scan-mode-btn active" id="btnModeUsb">
                            <i class="fas fa-barcode me-1"></i>Scanner
                        </button>
                        <button type="button" class="btn btn-outline-primary scan-mode-btn" id="btnModeCamera">
                            <i class="fas fa-camera me-1"></i>Kamera
                        </button>
                    </div>
                </div>

                <!-- Input Scanner USB -->
                <div id="usbScannerBox">
                    <div class="row align-items-center g-2">
                        <div class="col-12 col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white"><i class="fas fa-barcode"></i></span>
                                <input type="text" id="scannerInput" class="form-control" placeholder="Scan QR Kanban disini..." autofocus style="text-transform: uppercase;">
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

                <!-- Kamera Device Scanner (html5qrcode) -->
                <div id="cameraScannerBox" style="display:none;" class="mt-2">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-8 col-lg-6">
                            <div id="cameraScannerContainer" class="p-2 border position-relative text-center">
                                <div id="cameraReader" style="width: 100%; min-height: 220px;"></div>
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <small class="text-white"><i class="fas fa-info-circle me-1"></i>Arahkan kamera ke QR Kanban</small>
                                    <button type="button" class="btn btn-danger btn-sm" id="btnStopCamera">
                                        <i class="fas fa-stop me-1"></i>Tutup Kamera
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Hasil Scan Kanban (Form Input Part Kurang 1 Card Slider) -->
        <div id="resultArea" class="mb-4" style="display:none;"></div>

        <!-- 2. DAFTAR PART KURANG DENGAN FILTER & INFINITE SCROLL (5 CARD PER LOAD) -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-4">
                        <h5 class="card-title mb-0 fw-bold text-dark"><i class="fas fa-list-alt text-primary me-2"></i>Riwayat Part Kurang</h5>
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
                <!-- Container Card List Per Row -->
                <div id="partKurangCardsContainer">
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <p class="mt-2 mb-0 small">Memuat daftar part kurang...</p>
                    </div>
                </div>

                <!-- Infinite Scroll Trigger / Status -->
                <div id="infiniteScrollStatus" class="text-center py-3" style="display:none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 small text-muted">Memuat lebih banyak...</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/js/plugin/html5-qrcode.min.js') }}"></script>
<script>
    var currentStatusFilter = 'pending';
    var searchTimeout = null;

    // State Infinite Scroll
    var currentPage = 1;
    var isLoadingMore = false;
    var hasMoreData = true;

    // State Scanner Kamera (html5-qrcode)
    var html5QrCode = null;
    var isCameraScanning = false;

    $(document).ready(function() {
        // Load initial page of my list
        resetAndLoadMyList();

        $('#filterSearch').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                resetAndLoadMyList();
            }, 300);
        });

        $('.filter-btn-group .btn').on('click', function() {
            $('.filter-btn-group .btn').removeClass('active');
            $(this).addClass('active');
            currentStatusFilter = $(this).data('status');
            resetAndLoadMyList();
        });

        $('#btnRefreshList').on('click', function() {
            var icon = $(this).find('i');
            icon.addClass('fa-spin');
            resetAndLoadMyList(function() {
                icon.removeClass('fa-spin');
            });
        });

        // Infinite Scroll Listener pada Window
        $(window).on('scroll', function() {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 150) {
                if (!isLoadingMore && hasMoreData) {
                    loadMoreList();
                }
            }
        });

        // Toggle Mode Scanner: USB vs Kamera
        $('#btnModeUsb').on('click', function() {
            $('.scan-mode-btn').removeClass('active');
            $(this).addClass('active');
            stopCameraScanner();
            $('#cameraScannerBox').slideUp(200);
            $('#usbScannerBox').slideDown(200, function() {
                $('#scannerInput').focus();
            });
        });

        $('#btnModeCamera').on('click', function() {
            $('.scan-mode-btn').removeClass('active');
            $(this).addClass('active');
            $('#usbScannerBox').slideUp(200);
            $('#cameraScannerBox').slideDown(200, function() {
                startCameraScanner();
            });
        });

        $('#btnStopCamera').on('click', function() {
            $('#btnModeUsb').trigger('click');
        });
    });

    // Scanner USB Handler
    $('#scannerInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            processScan($(this).val());
        }
    });

    // Scanner Kamera Handler (html5-qrcode)
    function startCameraScanner() {
        if (isCameraScanning) return;

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("cameraReader");
        }

        var config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            function(decodedText, decodedResult) {
                // Berhasil membaca QR dari kamera
                if (decodedText) {
                    // Beri feedback audio/haptic jika tersedia
                    if (navigator.vibrate) navigator.vibrate(100);
                    processScan(decodedText);
                    stopCameraScanner();
                    $('#btnModeUsb').trigger('click');
                }
            },
            function(errorMessage) {
                // scanning... abaikan error frame-by-frame
            }
        ).then(function() {
            isCameraScanning = true;
        }).catch(function(err) {
            console.error("Gagal membuka kamera: ", err);
            Swal.fire({
                icon: 'error',
                title: 'Kamera Tidak Dapat Dibuka',
                text: 'Pastikan izin kamera telah diberikan pada browser atau gunakan Scanner USB.',
                confirmButtonColor: '#F36494'
            });
            $('#btnModeUsb').trigger('click');
        });
    }

    function stopCameraScanner() {
        if (html5QrCode && isCameraScanning) {
            html5QrCode.stop().then(function() {
                isCameraScanning = false;
            }).catch(function(err) {
                console.warn("Gagal menghentikan kamera: ", err);
                isCameraScanning = false;
            });
        }
    }

    function processScan(text) {
        if (!text) return;
        text = text.toUpperCase().trim();
        var parts = text.split(';');
        if (parts.length >= 3) {
            $('#sequence_no').val(parts[0]);
            $('#production_date').val(parts[1]);
            $('#type').val(parts[2]);
            $('#scannerInput').val('');
            searchRecords(parts[0], parts[1]);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Format QR Tidak Valid',
                text: 'Format: Sequence_No;Production_Date;Type',
                confirmButtonColor: '#F36494'
            });
            $('#scannerInput').val('');
        }
    }

    function searchRecords(sequenceNo, productionDate) {
        $('#resultArea').hide().html(
            '<div class="text-center py-4 bg-white rounded border p-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted mb-0">Mencari data marshalling & member...</p></div>'
        ).fadeIn(200);

        $.getJSON('{{ route("perakitan.comment.search") }}', {
            sequence_no: sequenceNo,
            production_date: productionDate
        }, function(res) {
            if (!res.found || !res.records || res.records.length === 0) {
                $('#resultArea').html(
                    '<div class="alert alert-warning text-center shadow-sm">' +
                    '<i class="fas fa-exclamation-triangle fa-2x mb-2"></i>' +
                    '<p class="mb-0 fw-bold">' + (res.message || 'Data tidak ditemukan.') + '</p></div>'
                ).fadeIn(200);
                return;
            }

            var records = res.records;
            var isMultiple = records.length > 1;

            var html = '<div class="row justify-content-center">';
            html += '  <div class="col-12 col-lg-8">';
            html += '    <div class="card shadow border-0 record-comment-box overflow-hidden">';
            
            // Header Card
            html += '      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3">';
            html += '        <span class="fw-bold"><i class="fas fa-edit me-1"></i>' + escHtml(records[0].Sequence_No) + ' - ' + escHtml(records[0].Type) + '</span>';
            if (isMultiple) {
                html += '        <span class="badge bg-white text-primary fw-bold" id="areaSliderCounter">Area 1 dari ' + records.length + '</span>';
            } else {
                html += '        <span class="badge bg-white text-primary fw-bold">' + escHtml(records[0].Area_Label) + '</span>';
            }
            html += '      </div>';

            html += '      <div class="card-body p-3">';

            // Carousel / Slider Wrapper jika lebih dari 1 area
            if (isMultiple) {
                html += '      <div id="areaCarousel" class="carousel slide" data-bs-ride="false" data-bs-interval="false" data-bs-touch="true">';
                html += '        <div class="carousel-inner">';
            }

            $.each(records, function(i, r) {
                var activeClass = (i === 0) ? ' active' : '';
                if (isMultiple) {
                    html += '      <div class="carousel-item' + activeClass + '">';
                }

                // Member Marshalling Profile Wrapper dengan Floating Chevrons
                html += '        <div class="profile-card-wrapper mb-3">';
                if (isMultiple) {
                    html += '          <button type="button" class="profile-chevron-btn btn-prev" onclick="$(\'#areaCarousel\').carousel(\'prev\')" title="Area Sebelumnya">';
                    html += '            <i class="fas fa-chevron-left"></i>';
                    html += '          </button>';
                    html += '          <button type="button" class="profile-chevron-btn btn-next" onclick="$(\'#areaCarousel\').carousel(\'next\')" title="Area Berikutnya">';
                    html += '            <i class="fas fa-chevron-right"></i>';
                    html += '          </button>';
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
                html += '        </div>'; // End profile-card-wrapper

                // Comment form (setiap submit input catatan baru)
                html += '        <form onsubmit="submitComment(event, ' + r.Id_Record + ')">';
                html += '          <div class="mb-3">';
                html += '            <label class="form-label fw-bold mb-1"><i class="fas fa-tags me-1 text-primary"></i>Kategori Kendala <span class="text-danger">*</span>:</label>';
                html += '            <div class="row g-1 text-center">';
                html += '              <div class="col-4">';
                html += '                <input class="btn-check" type="radio" name="perakitan_category_' + r.Id_Record + '" id="perakitan_cat_kosong_' + r.Id_Record + '" value="kosong" required>';
                html += '                <label class="btn btn-outline-danger w-100 fw-bold btn-sm py-2 px-1 text-nowrap" for="perakitan_cat_kosong_' + r.Id_Record + '">';
                html += '                  <i class="fas fa-times-circle me-1"></i>Kosong';
                html += '                </label>';
                html += '              </div>';
                html += '              <div class="col-4">';
                html += '                <input class="btn-check" type="radio" name="perakitan_category_' + r.Id_Record + '" id="perakitan_cat_kurang_' + r.Id_Record + '" value="kurang" checked required>';
                html += '                <label class="btn btn-outline-warning text-dark w-100 fw-bold btn-sm py-2 px-1 text-nowrap" for="perakitan_cat_kurang_' + r.Id_Record + '">';
                html += '                  <i class="fas fa-minus-circle me-1"></i>Kurang';
                html += '                </label>';
                html += '              </div>';
                html += '              <div class="col-4">';
                html += '                <input class="btn-check" type="radio" name="perakitan_category_' + r.Id_Record + '" id="perakitan_cat_salah_' + r.Id_Record + '" value="salah" required>';
                html += '                <label class="btn btn-outline-purple w-100 fw-bold btn-sm py-2 px-1 text-nowrap" for="perakitan_cat_salah_' + r.Id_Record + '">';
                html += '                  <i class="fas fa-exclamation-triangle me-1"></i>Salah';
                html += '                </label>';
                html += '              </div>';
                html += '            </div>';
                html += '          </div>';
                html += '          <div class="mb-2">';
                html += '            <label class="form-label fw-bold"><i class="fas fa-pen me-1"></i>Input Catatan Part Kurang (' + escHtml(r.Area_Label) + ') <span class="text-danger">*</span>:</label>';
                html += '            <textarea id="commentInput_' + r.Id_Record + '" class="form-control" rows="2" placeholder="Tuliskan part apa yang kosong/kurang/salah di area ' + escHtml(r.Area_Label) + '..." required></textarea>';
                html += '            <div class="invalid-feedback">Catatan part kurang wajib diisi.</div>';
                html += '          </div>';
                html += '          <div class="d-flex justify-content-between align-items-center">';
                html += '            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Area ' + escHtml(r.Area_Label) + '</small>';
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
            html += '    </div>'; // End card
            html += '  </div>';
            html += '</div>';

            $('#resultArea').html(html).fadeIn(200);

            // Jika slider aktif, bind swipe gesture & counter listener
            if (isMultiple) {
                $('#areaCarousel').on('slid.bs.carousel', function() {
                    var currentIndex = $('#areaCarousel .carousel-item.active').index() + 1;
                    var totalItems = $('#areaCarousel .carousel-item').length;
                    $('#areaSliderCounter').text('Area ' + currentIndex + ' dari ' + totalItems);
                });

                // Swipe handler touchscreen
                var touchStartX = 0;
                var touchEndX = 0;
                var carouselElem = document.getElementById('areaCarousel');
                carouselElem.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });

                carouselElem.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    handleSwipe();
                }, { passive: true });

                function handleSwipe() {
                    if (touchEndX < touchStartX - 40) {
                        $('#areaCarousel').carousel('next');
                    }
                    if (touchEndX > touchStartX + 40) {
                        $('#areaCarousel').carousel('prev');
                    }
                }
            }

            // Scroll halus ke result area
            $('html, body').animate({
                scrollTop: $('#resultArea').offset().top - 70
            }, 300);

            // Fokus ke input textarea pertama
            setTimeout(function() {
                $('#commentInput_' + records[0].Id_Record).focus();
            }, 350);
        }).fail(function() {
            $('#resultArea').html(
                '<div class="alert alert-danger text-center"><i class="fas fa-times-circle fa-2x mb-2"></i><p class="mb-0">Terjadi kesalahan saat mencari data.</p></div>'
            ).fadeIn(200);
        });
    }

    function submitComment(e, recordId) {
        e.preventDefault();
        var textarea = $('#commentInput_' + recordId);
        var comment = textarea.val();
        var category = $('input[name="perakitan_category_' + recordId + '"]:checked').val();
        var btn = $('#submitBtn_' + recordId);

        if (!category) {
            Swal.fire({
                icon: 'warning',
                title: 'Kategori Wajib Dipilih',
                text: 'Silakan pilih kategori kendala (Kosong, Kurang, atau Salah).',
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
            url: '{{ url("perakitan/comment") }}/' + recordId + '/store',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                comment: comment,
                category: category
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

                    // Hilangkan card isi member marshalling seperti tampilan awal (hanya card scan dan card list)
                    $('#resultArea').slideUp(250, function() {
                        $(this).empty();
                    });

                    // Bersihkan form scan agar siap scan ulang jika mau input lagi
                    $('#sequence_no').val('');
                    $('#production_date').val('');
                    $('#type').val('');
                    $('#scannerInput').val('').focus();

                    // Refresh daftar riwayat part kurang di bawah
                    resetAndLoadMyList();
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

    // 🔸 LOGIKA INFINITE SCROLL (LOAD PER 5 CARD)
    function resetAndLoadMyList(callback) {
        currentPage = 1;
        hasMoreData = true;
        isLoadingMore = false;
        $('#partKurangCardsContainer').html(
            '<div class="text-center py-4 text-muted">' +
            '  <div class="spinner-border spinner-border-sm text-primary" role="status"></div>' +
            '  <p class="mt-2 mb-0 small">Memuat daftar part kurang...</p>' +
            '</div>'
        );
        fetchMyListPage(currentPage, false, callback);
    }

    function loadMoreList() {
        if (isLoadingMore || !hasMoreData) return;
        isLoadingMore = true;
        $('#infiniteScrollStatus').fadeIn(150);
        currentPage++;
        fetchMyListPage(currentPage, true, function() {
            isLoadingMore = false;
            $('#infiniteScrollStatus').fadeOut(150);
        });
    }

    function fetchMyListPage(page, isAppend, callback) {
        var searchVal = $('#filterSearch').val();

        $.getJSON('{{ route("perakitan.comment.my-list") }}', {
            status: currentStatusFilter,
            search: searchVal,
            page: page
        }, function(res) {
            hasMoreData = res.has_more || false;
            var container = $('#partKurangCardsContainer');

            if (!res.records || res.records.length === 0) {
                if (!isAppend) {
                    container.html(
                        '<div class="text-center py-5 bg-white rounded border">' +
                        '  <i class="fas fa-box-open fa-3x text-muted mb-2"></i>' +
                        '  <p class="text-muted mb-0">Belum ada catatan part kurang yang cocok.</p>' +
                        '</div>'
                    );
                }
                if (typeof callback === 'function') callback();
                return;
            }

            var html = '';
            if (!isAppend) {
                html += '<div class="d-flex flex-column gap-3" id="partKurangCardListWrapper">';
            }

            $.each(res.records, function(i, item) {
                var isOke = (item.Status === 'oke');
                var badgeHtml = isOke 
                    ? '<span class="status-badge-oke"><i class="fas fa-check-circle me-1"></i>Sudah Diterima</span>'
                    : '<span class="status-badge-pending"><i class="fas fa-clock me-1"></i>Pending</span>';

                var cat = (item.Category || 'kurang').toLowerCase();
                var catBadge = '';
                if (cat === 'kosong') {
                    catBadge = '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>KOSONG</span>';
                } else if (cat === 'salah') {
                    catBadge = '<span class="badge text-white" style="background-color:#6f42c1;"><i class="fas fa-exclamation-triangle me-1"></i>SALAH</span>';
                } else {
                    catBadge = '<span class="badge bg-warning text-dark"><i class="fas fa-minus-circle me-1"></i>KURANG</span>';
                }

                html += '<div class="part-kurang-card p-3 shadow-sm" id="partCard_' + item.Id_Part_Kurang + '">';
                
                // Header baris card: Seq, Type, Area, Category & Status
                html += '  <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2 gap-2">';
                html += '    <div class="d-flex align-items-center gap-2">';
                html += '      <span class="badge bg-dark fs-6">Seq ' + escHtml(item.Sequence_No) + '</span>';
                html += '      <span class="badge bg-info text-dark">' + escHtml(item.Type) + '</span>';
                html += '      <span class="badge bg-primary">' + escHtml(item.Area_Label) + '</span>';
                html += '      ' + catBadge;
                html += '    </div>';
                html += '    <div id="statusBadgeContainer_' + item.Id_Part_Kurang + '">' + badgeHtml + '</div>';
                html += '  </div>';

                // Konten tengah: Catatan & Data Member Marshalling
                html += '  <div class="row g-2 align-items-center">';
                html += '    <div class="col-12 col-md-7">';
                html += '      <div class="p-2 rounded bg-light border">';
                html += '        <small class="text-muted d-block mb-1"><i class="fas fa-comment-dots me-1"></i>Catatan Part Kurang:</small>';
                html += '        <p class="mb-1 text-dark fw-bold" style="white-space: pre-line;">' + escHtml(item.Perakitan_Comment) + '</p>';
                html += '        <small class="text-muted"><i class="far fa-clock me-1"></i>Diajukan: ' + escHtml(item.Perakitan_Comment_Time) + '</small>';
                html += '      </div>';
                html += '    </div>';

                // Member Marshalling info (Foto dari rifa atau avatar abu-abu)
                html += '    <div class="col-12 col-md-5">';
                html += '      <div class="d-flex align-items-center p-2 rounded bg-light border">';
                if (item.Member_Photo) {
                    html += '    <img src="' + item.Member_Photo + '" class="rounded me-2 border" style="width: 48px; height: 55px; object-fit: cover;" onerror="this.outerHTML=\'<div class=\\\'rounded me-2 border d-flex align-items-center justify-content-center\\\' style=\\\'width: 48px; height: 55px; background: #e9ecef;\\\'><i class=\\\'fas fa-user text-secondary\\\'></i></div>\'">';
                } else {
                    html += '    <div class="rounded me-2 border d-flex align-items-center justify-content-center" style="width: 48px; height: 55px; background: #e9ecef;"><i class="fas fa-user text-secondary"></i></div>';
                }
                html += '        <div class="overflow-hidden">';
                html += '          <small class="text-muted d-block">' + escHtml(item.Area_Label) + ':</small>';
                html += '          <div class="fw-bold text-truncate text-dark">' + escHtml(item.Member_Name) + '</div>';
                html += '          <small class="text-muted">NIK: ' + escHtml(item.Member_Nik) + '</small>';
                html += '        </div>';
                html += '      </div>';
                html += '    </div>';
                html += '  </div>';

                // Footer tombol aksi
                html += '  <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">';
                html += '    <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i>Prod. Date: ' + escHtml(item.Production_Date) + '</small>';
                html += '    <div id="actionBtnContainer_' + item.Id_Part_Kurang + '">';
                if (!isOke) {
                    html += '      <button class="btn btn-success btn-sm px-3 fw-bold" onclick="markReceived(' + item.Id_Part_Kurang + ')">';
                    html += '        <i class="fas fa-check-circle me-1"></i>Sudah Diterima';
                    html += '      </button>';
                } else {
                    html += '      <span class="text-success small fw-semibold"><i class="fas fa-check-double me-1"></i>Part Telah Diterima</span>';
                }
                html += '    </div>';
                html += '  </div>';

                html += '</div>'; // End part-kurang-card
            });

            if (!isAppend) {
                html += '</div>';
                container.html(html);
            } else {
                $('#partKurangCardListWrapper').append(html);
            }

            if (typeof callback === 'function') callback();
        }).fail(function() {
            if (!isAppend) {
                $('#partKurangCardsContainer').html(
                    '<div class="alert alert-danger text-center"><i class="fas fa-exclamation-circle me-1"></i>Gagal memuat data part kurang.</div>'
                );
            }
            if (typeof callback === 'function') callback();
        });
    }

    function markReceived(id) {
        Swal.fire({
            title: 'Konfirmasi Penerimaan',
            text: 'Apakah part kurang untuk sequence ini sudah Anda terima dengan lengkap?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check me-1"></i>Ya, Sudah Diterima',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                var btnContainer = $('#actionBtnContainer_' + id);
                btnContainer.html('<button class="btn btn-secondary btn-sm disabled"><span class="spinner-border spinner-border-sm me-1"></span>Memproses...</button>');

                $.ajax({
                    url: '{{ url("perakitan/comment") }}/' + id + '/receive',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Status berhasil diubah menjadi Sudah Diterima.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            // Ubah badge & tombol secara realtime
                            $('#statusBadgeContainer_' + id).html('<span class="status-badge-oke"><i class="fas fa-check-circle me-1"></i>Sudah Diterima</span>');
                            btnContainer.html('<span class="text-success small fw-semibold"><i class="fas fa-check-double me-1"></i>Part Telah Diterima</span>');
                            
                            // Jika filter aktif adalah 'pending', refresh list agar item yang selesai otomatis tersaring
                            if (currentStatusFilter === 'pending') {
                                resetAndLoadMyList();
                            }
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal memperbarui status.',
                            confirmButtonColor: '#F36494'
                        });
                        resetAndLoadMyList();
                    }
                });
            }
        });
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
@endsection


