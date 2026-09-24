@extends('layouts.main')

@section('style')
<style>
    #partKurangTable .badge { font-size: 11px; padding: 3px 6px; }
    #carouselModal .modal-body {
        display: flex;
        flex-direction: column;
        padding: 5px 15px;
        height: calc(100vh - 60px);
        overflow: hidden;
    }
    .carousel-box {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 6px;
        background: #fff;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }
    #carouselInner, #carouselInner .carousel-inner, #carouselInner .carousel-item {
        height: 100%;
    }
    .carousel-box .carousel-item { padding: 2px; }
    .slide-info-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 4px 8px;
        line-height: 1.15;
    }
    .slide-info-card .info-title {
        font-size: 0.72rem;
        color: #6c757d;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    .slide-info-card .info-text {
        font-size: 0.95rem;
        font-weight: 700;
        color: #212529;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .photo-wrapper {
        position: relative;
        width: 100%;
        flex-grow: 1;
        display: flex;
    }
    .member-photo {
        width: 100%;
        flex-grow: 1;
        max-height: calc(100vh - 180px);
        object-fit: cover;
        border-radius: 6px;
    }
    .member-photo-placeholder {
        width: 100%;
        flex-grow: 1;
        max-height: calc(100vh - 180px);
        border-radius: 6px;
    }
    .audio-indicator-icon {
        position: absolute;
        top: 8px;
        left: 8px;
        background: rgba(0, 0, 0, 0.65);
        color: #fff;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        z-index: 3;
    }
    .audio-indicator-icon.enabled {
        color: #28a745;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 2px 5px rgba(0,0,0,0.25);
    }
    .audio-indicator-icon.disabled {
        color: #dc3545;
        background: rgba(255, 255, 255, 0.8);
    }
    .member-name-heading {
        font-size: 2.2rem;
        line-height: 1.15;
        font-weight: 800;
        color: #111;
        word-break: break-word;
    }
    .comment-big-box {
        background: #fff3cd;
        border: 2px solid #ffeeba;
        border-left: 8px solid #ffc107;
        border-radius: 8px;
        padding: 12px 20px;
        font-size: 8vw;
        line-height: 1.15;
        color: #111;
        font-weight: 900;
        word-break: break-word;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    #carouselCounter {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 0.85rem;
        z-index: 5;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0"><i class="fas fa-clipboard-list me-2"></i>Laporan Part Kurang</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row mb-3 align-items-end g-2">
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label fw-bold mb-1">Tanggal</label>
                        <div class="input-group input-group-sm">
                            <button type="button" class="btn btn-outline-primary" id="btnDatePrev" title="Mundur 1 Hari">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <input type="date" id="filter_date" class="form-control form-control-sm text-center fw-bold" value="{{ $today }}">
                            <button type="button" class="btn btn-outline-primary" id="btnDateNext" title="Maju 1 Hari">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnDateClear" title="Semua Tanggal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label fw-bold mb-1">Status</label>
                        <select id="filter_status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="diterima">Diterima / Oke</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label fw-bold mb-1">Kategori</label>
                        <select id="filter_category" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            <option value="kosong">Kosong</option>
                            <option value="kurang">Kurang</option>
                            <option value="salah">Salah</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label fw-bold mb-1">Member Marshalling</label>
                        <select id="filter_member" class="form-select form-select-sm">
                            <option value="">Semua Member Marshalling</option>
                            @foreach($marshallingMembers as $mm)
                                <option value="{{ $mm->id }}">{{ $mm->nama }} ({{ $mm->nik }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label fw-bold mb-1">Pelapor (Perakitan)</label>
                        <select id="filter_reporter" class="form-select form-select-sm">
                            <option value="">Semua Pelapor</option>
                            @foreach($reporters as $rep)
                                <option value="{{ $rep->nik }}">{{ $rep->nama }} ({{ $rep->nik }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-2 d-flex flex-wrap gap-2 justify-content-lg-end mt-2 mt-lg-0">
                        <button type="button" class="btn btn-info btn-sm fw-bold" onclick="showCarousel()">
                            <i class="fas fa-play-circle me-1"></i> Show Slideshow
                        </button>
                        <button type="button" class="btn btn-success btn-sm fw-bold" id="btnExportExcel" onclick="exportPartKurangExcel()">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="partKurangTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Member Marshalling</th>
                                <th>Seq Record</th>
                                <th>Prod Date</th>
                                <th>Type</th>
                                <th>Area</th>
                                <th>Waktu Komentar</th>
                                <th>Reporter NIK</th>
                                <th>Reporter Nama</th>
                                <th>Kategori</th>
                                <th>Catatan Part Kurang</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="carouselModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title text-primary mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Part Kurang
                    </h5>
                    <span id="audioStatusBadge" class="badge bg-info text-white"><i class="fas fa-volume-up me-1"></i>Audio Aktif (Ulang 5x)</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="stopCarousel()"></button>
            </div>
            <div class="modal-body position-relative">
                <span id="carouselCounter" class="badge bg-secondary">0 / 0</span>
                <div id="carouselContainer" class="carousel-box text-center">
                    <div id="carouselInner" class="carousel slide" data-bs-ride="false" data-bs-interval="false">
                        <div class="carousel-inner" id="carouselInnerContent"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="slidePrev()">
                        <i class="fas fa-chevron-left me-1"></i> Previous
                    </button>
                    <div class="text-muted small d-flex align-items-center">
                        <span id="audioRepetitionCounter">Putaran: 0 / 5</span>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="slideNext()">
                        Next <i class="fas fa-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audio Player Element for Slideshow -->
<audio id="panggilanPlayer" src="{{ asset('assets/sounds/panggilan_kepada.MP3') }}" preload="auto"></audio>
<audio id="namaPlayer" preload="auto"></audio>
@endsection

@section('script')
<script>
    var carouselInterval = null;
    var carouselData = [];
    var carouselActiveIndex = 0;
    var audioLoopCount = 0;
    var isAudioPlaying = false;
    var shouldContinueAudio = false;

    var panggilanAudio = document.getElementById('panggilanPlayer');
    var namaAudio = document.getElementById('namaPlayer');

    // Tune audio slideshow agar nada lebih tinggi/melengking (tanpa CDN)
    function setupAudioTuning(el, rate) {
        if (!el) return;
        el.playbackRate = rate || 1.18; // ~18% lebih tinggi & melengking
        el.preservesPitch = false;
        if ('mozPreservesPitch' in el) el.mozPreservesPitch = false;
        if ('webkitPreservesPitch' in el) el.webkitPreservesPitch = false;
    }

    setupAudioTuning(panggilanAudio, 1.18);
    setupAudioTuning(namaAudio, 1.15);

    $(document).ready(function() {
        var table = $('#partKurangTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.part-kurang.list') }}",
                data: function(d) {
                    d.filter_date = $('#filter_date').val();
                    d.filter_status = $('#filter_status').val();
                    d.filter_category = $('#filter_category').val();
                    d.member_id = $('#filter_member').val();
                    d.reporter_nik = $('#filter_reporter').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'member_name', name: 'member_name' },
                { data: 'sequence_record', name: 'sequence_record' },
                { data: 'production_date', name: 'production_date' },
                { data: 'type_record', name: 'type_record' },
                { data: 'area_record', name: 'area_record' },
                { data: 'comment_time', name: 'comment_time' },
                { data: 'reporter_nik', name: 'reporter_nik' },
                { data: 'reporter_name', name: 'reporter_name' },
                { data: 'category_badge', name: 'category' },
                { data: 'comment', name: 'comment' },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false }
            ]
        });

        $('#filter_date, #filter_status, #filter_category, #filter_member, #filter_reporter').on('change', function() {
            table.ajax.reload();
        });

        // Tombol Clear Tanggal (Tampilkan Semua)
        $('#btnDateClear').on('click', function() {
            $('#filter_date').val('').trigger('change');
        });

        // Tombol Chevron Mundur 1 Hari
        $('#btnDatePrev').on('click', function() {
            var curr = $('#filter_date').val();
            if (!curr) curr = new Date().toISOString().split('T')[0];
            var d = new Date(curr);
            d.setDate(d.getDate() - 1);
            var prevDate = d.toISOString().split('T')[0];
            $('#filter_date').val(prevDate).trigger('change');
        });

        // Tombol Chevron Maju 1 Hari
        $('#btnDateNext').on('click', function() {
            var curr = $('#filter_date').val();
            if (!curr) curr = new Date().toISOString().split('T')[0];
            var d = new Date(curr);
            d.setDate(d.getDate() + 1);
            var nextDate = d.toISOString().split('T')[0];
            $('#filter_date').val(nextDate).trigger('change');
        });

        // Setup audio sequencer listeners
        panggilanAudio.addEventListener('ended', function() {
            if (!shouldContinueAudio) return;
            var currentItem = carouselData[carouselActiveIndex];
            if (currentItem && currentItem.member_audio) {
                namaAudio.src = currentItem.member_audio;
                setupAudioTuning(namaAudio, 1.15);
                namaAudio.play().catch(function(err) {
                    console.warn('Nama audio play error:', err);
                    onAudioCycleEnd();
                });
            } else {
                // Jika tidak ada audio nama member, langsung anggap 1 repetisi selesai
                setTimeout(onAudioCycleEnd, 1000);
            }
        });

        panggilanAudio.addEventListener('error', function() {
            if (!shouldContinueAudio) return;
            // Jika panggilan_kepada gagal, coba putar nama atau lewati
            var currentItem = carouselData[carouselActiveIndex];
            if (currentItem && currentItem.member_audio) {
                namaAudio.src = currentItem.member_audio;
                namaAudio.play().catch(function() {
                    onAudioCycleEnd();
                });
            } else {
                setTimeout(onAudioCycleEnd, 2000);
            }
        });

        namaAudio.addEventListener('ended', function() {
            if (!shouldContinueAudio) return;
            onAudioCycleEnd();
        });

        namaAudio.addEventListener('error', function() {
            if (!shouldContinueAudio) return;
            onAudioCycleEnd();
        });
    });

    function onAudioCycleEnd() {
        if (!shouldContinueAudio) return;
        audioLoopCount++;
        $('#audioRepetitionCounter').text('Panggilan: ' + audioLoopCount + ' / 5');

        if (audioLoopCount < 5) {
            // Tunggu jeda singkat lalu putar lagi
            setTimeout(function() {
                if (!shouldContinueAudio) return;
                playPanggilan();
            }, 800);
        } else {
            // Sudah 5x putar, beralih ke slide berikutnya (otomatis fetch data terbaru)
            audioLoopCount = 0;
            slideNext();
        }
    }

    function playCurrentSlideAudio() {
        stopAudioPlayback();
        if (carouselData.length === 0) return;

        shouldContinueAudio = true;
        audioLoopCount = 0;
        $('#audioRepetitionCounter').text('Panggilan: 1 / 5');

        playPanggilan();
    }

    function playPanggilan() {
        if (!shouldContinueAudio) return;
        setupAudioTuning(panggilanAudio, 1.18);
        panggilanAudio.currentTime = 0;
        panggilanAudio.play().catch(function(err) {
            console.warn('Panggilan audio play blocked/error:', err);
            // Browser autoplay policy might require interaction
            var currentItem = carouselData[carouselActiveIndex];
            if (currentItem && currentItem.member_audio) {
                namaAudio.src = currentItem.member_audio;
                setupAudioTuning(namaAudio, 1.15);
                namaAudio.play().catch(function() {
                    setTimeout(onAudioCycleEnd, 3000);
                });
            } else {
                setTimeout(onAudioCycleEnd, 3000);
            }
        });
    }

    function stopAudioPlayback() {
        shouldContinueAudio = false;
        try {
            panggilanAudio.pause();
            panggilanAudio.currentTime = 0;
            namaAudio.pause();
            namaAudio.currentTime = 0;
        } catch(e) {}
    }

    var emptyPollInterval = null;

    function stopEmptyPolling() {
        if (emptyPollInterval) {
            clearInterval(emptyPollInterval);
            emptyPollInterval = null;
        }
    }

    function startEmptyPolling() {
        stopEmptyPolling();
        // Jalankan polling setiap 5 detik
        emptyPollInterval = setInterval(function() {
            checkPendingData();
        }, 5000);
    }

    function checkPendingData() {
        // Cek jika modal sedang terbuka
        var modalEl = $('#carouselModal');
        if (!modalEl.is(':visible') && !modalEl.hasClass('show')) {
            stopEmptyPolling();
            return;
        }

        // Gunakan parameter timestamp untuk mencegah caching browser
        $.ajax({
            url: '{{ route("admin.part-kurang.carousel") }}',
            type: 'GET',
            dataType: 'json',
            cache: false,
            data: { _t: new Date().getTime() },
            success: function(data) {
                if (data && data.length > 0) {
                    stopEmptyPolling();
                    carouselData = data;
                    carouselActiveIndex = 0;
                    buildCarousel();
                    goToSlide(0);
                }
            },
            error: function(err) {
                console.warn('Gagal cek pending data:', err);
            }
        });
    }

    function showCarousel() {
        stopCarousel();
        stopAudioPlayback();

        // Tampilkan modal terlebih dahulu
        $('#carouselModal').modal('show');

        // Render state loading sementara di modal
        carouselData = [];
        buildCarousel(true);

        $.ajax({
            url: '{{ route("admin.part-kurang.carousel") }}',
            type: 'GET',
            dataType: 'json',
            cache: false,
            data: { _t: new Date().getTime() },
            success: function(data) {
                carouselData = data || [];
                carouselActiveIndex = 0;
                buildCarousel();
                if (carouselData.length > 0) {
                    goToSlide(0);
                } else {
                    startEmptyPolling();
                }
            },
            error: function() {
                carouselData = [];
                buildCarousel();
                startEmptyPolling();
            }
        });
    }

    function buildCarousel(isLoading) {
        var inner = $('#carouselInnerContent');
        inner.empty();

        if (isLoading) {
            inner.html('<div class="carousel-item active"><div class="py-5 text-muted"><div class="spinner-border text-primary mb-3" role="status"></div><p class="mb-0">Memuat data part kurang...</p></div></div>');
            $('#carouselCounter').text('- / -');
            $('#audioRepetitionCounter').text('Panggilan: 0 / 0');
            return;
        }

        if (carouselData.length === 0) {
            inner.html('<div class="carousel-item active"><div class="py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 text-secondary"></i><p class="fs-5 fw-bold mb-1">Tidak ada laporan part kurang yang pending.</p><small class="text-primary"><i class="fas fa-sync-alt fa-spin me-1"></i>Mengecek data baru otomatis tiap 5 detik...</small></div></div>');
            $('#carouselCounter').text('0 / 0');
            $('#audioRepetitionCounter').text('Panggilan: 0 / 0');
            return;
        }

        stopEmptyPolling();
        $.each(carouselData, function(i, item) {
            var active = i === carouselActiveIndex ? ' active' : '';
            var html = '<div class="carousel-item' + active + '" id="carousel_slide_' + i + '">';
            html += '<div class="row h-100 g-2 align-items-stretch">';

            // Left: Member Marshalling (Label di atas foto, Foto dimaksimalkan, NIK dihilangkan, Nama diperbesar, Audio icon di pojok kiri bawah)
            html += '<div class="col-md-3 d-flex flex-column text-center border-end pe-2 h-100">';
            html += '  <div class="mb-1">';
            html += '    <span class="badge bg-primary px-3 py-1 fs-6 fw-bold text-uppercase w-100">Marshalling</span>';
            html += '  </div>';
            html += '  <div class="photo-wrapper">';
            if (item.member_photo) {
                html += '    <img src="' + item.member_photo + '" class="member-photo border" onerror="this.style.display=\'none\'">';
            } else {
                html += '    <div class="member-photo member-photo-placeholder bg-light d-flex align-items-center justify-content-center border"><i class="fas fa-user fa-8x text-secondary"></i></div>';
            }
            if (item.member_audio) {
                html += '    <div class="audio-indicator-icon enabled" title="Audio Aktif / Terdaftar"><i class="fas fa-volume-up"></i></div>';
            } else {
                html += '    <div class="audio-indicator-icon disabled" title="Audio Nonaktif / Belum Ada"><i class="fas fa-volume-mute"></i></div>';
            }
            html += '  </div>';
            html += '  <div class="mt-2 text-center">';
            html += '    <strong class="d-block member-name-heading" title="' + escHtml(item.member_name) + '">' + escHtml(item.member_name) + '</strong>';
            html += '  </div>';
            html += '</div>';

            // Middle: Kanban Details dibuat mepet 3 kolom & Catatan Part Kurang Diperbesar Maksimal (2 Baris)
            var categoryBadge = '';
            var cat = (item.category || 'kurang').toLowerCase();
            if (cat === 'kosong') {
                categoryBadge = '<span class="badge bg-danger text-white fs-6 px-3 py-1 text-uppercase fw-bold"><i class="fas fa-times-circle me-1"></i>Kosong</span>';
            } else if (cat === 'salah') {
                categoryBadge = '<span class="badge text-white fs-6 px-3 py-1 text-uppercase fw-bold" style="background-color: #6f42c1;"><i class="fas fa-exclamation-circle me-1"></i>Salah</span>';
            } else {
                categoryBadge = '<span class="badge bg-warning text-dark fs-6 px-3 py-1 text-uppercase fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>Kurang</span>';
            }

            html += '<div class="col-md-6 border-start border-end px-3 d-flex flex-column justify-content-between h-100">';
            html += '  <div class="w-100 pt-1">';
            html += '    <div class="row g-1 mb-2 text-start">';
            html += '      <div class="col-4"><div class="slide-info-card"><div class="info-title">Seq Record</div><div class="info-text text-primary">' + escHtml(item.sequence) + '</div></div></div>';
            html += '      <div class="col-4"><div class="slide-info-card"><div class="info-title">Prod Date</div><div class="info-text">' + escHtml(item.production_date) + '</div></div></div>';
            html += '      <div class="col-4"><div class="slide-info-card"><div class="info-title">Type Traktor</div><div class="info-text">' + escHtml(item.type) + '</div></div></div>';
            html += '      <div class="col-4"><div class="slide-info-card"><div class="info-title">Area</div><div class="info-text text-primary">' + escHtml(item.area) + '</div></div></div>';
            html += '      <div class="col-4"><div class="slide-info-card"><div class="info-title">Waktu Marshalling</div><div class="info-text">' + escHtml(item.time_record) + '</div></div></div>';
            html += '      <div class="col-4"><div class="slide-info-card"><div class="info-title">Waktu Komentar</div><div class="info-text">' + escHtml(item.perakitan_comment_time) + '</div></div></div>';
            html += '    </div>';
            html += '  </div>';
            html += '  <div class="d-flex flex-column flex-grow-1 w-100 mb-1">';
            html += '    <div class="d-flex justify-content-between align-items-center mb-1">';
            html += '      <div class="text-danger fw-bold text-start" style="font-size:1.15rem;"><i class="fas fa-clipboard-list me-1"></i>Catatan Part Kurang:</div>';
            html += '      <div>' + categoryBadge + '</div>';
            html += '    </div>';
            html += '    <div class="comment-big-box">' + escHtml(item.perakitan_comment) + '</div>';
            html += '  </div>';
            html += '</div>';

            // Right: Member Perakitan (Label di atas foto, Foto dimaksimalkan, NIK dihilangkan, Label pelapor dihilangkan, Nama diperbesar)
            html += '<div class="col-md-3 d-flex flex-column text-center border-start ps-2 h-100">';
            html += '  <div class="mb-1">';
            html += '    <span class="badge bg-info px-3 py-1 fs-6 fw-bold text-uppercase w-100 text-white">Perakitan</span>';
            html += '  </div>';
            html += '  <div class="photo-wrapper">';
            if (item.perakitan_photo) {
                html += '    <img src="' + item.perakitan_photo + '" class="member-photo border" onerror="this.style.display=\'none\'">';
            } else {
                html += '    <div class="member-photo member-photo-placeholder bg-light d-flex align-items-center justify-content-center border"><i class="fas fa-user fa-8x text-secondary"></i></div>';
            }
            html += '  </div>';
            html += '  <div class="mt-2 text-center">';
            html += '    <strong class="d-block member-name-heading" title="' + escHtml(item.perakitan_name) + '">' + escHtml(item.perakitan_name) + '</strong>';
            html += '  </div>';
            html += '</div>';

            html += '</div>';
            html += '</div>';
            inner.append(html);
        });
        updateCounter();
    }

    function stopCarousel() {
        stopAudioPlayback();
        stopEmptyPolling();
    }

    function slideNext() {
        stopAudioPlayback();

        // Selalu cek data terbaru ke server setiap kali akan berganti slide (semua pending)
        $.ajax({
            url: '{{ route("admin.part-kurang.carousel") }}',
            type: 'GET',
            dataType: 'json',
            cache: false,
            data: { _t: new Date().getTime() },
            success: function(data) {
                var currentId = (carouselData[carouselActiveIndex]) ? carouselData[carouselActiveIndex].Id_Part_Kurang : null;
                carouselData = data || [];

                if (carouselData.length === 0) {
                    carouselActiveIndex = 0;
                    buildCarousel();
                    startEmptyPolling();
                    return;
                }

            // Cari index dari item berikutnya
            var nextIndex = 0;
            if (currentId !== null) {
                var foundIndex = carouselData.findIndex(function(it) {
                    return it.Id_Part_Kurang === currentId;
                });
                if (foundIndex !== -1) {
                    nextIndex = foundIndex + 1;
                    if (nextIndex >= carouselData.length) {
                        nextIndex = 0;
                    }
                } else {
                    // Item lama sudah tidak pending / selesai, arahkan ke slide pada posisi yang sama atau 0
                    nextIndex = carouselActiveIndex % carouselData.length;
                }
            }

            carouselActiveIndex = nextIndex;
            buildCarousel();
            goToSlide(carouselActiveIndex);
            }
        }).fail(function() {
            // Fallback jika fetch gagal (misal koneksi terputus sesaat)
            if (carouselData.length > 0) {
                carouselActiveIndex = (carouselActiveIndex + 1) % carouselData.length;
                goToSlide(carouselActiveIndex);
            }
        });
    }

    function slidePrev() {
        if (carouselData.length === 0) return;
        stopAudioPlayback();

        carouselActiveIndex--;
        if (carouselActiveIndex < 0) {
            carouselActiveIndex = carouselData.length - 1;
        }

        goToSlide(carouselActiveIndex);
    }

    function goToSlide(index) {
        $('.carousel-item').removeClass('active');
        $('#carousel_slide_' + index).addClass('active');
        carouselActiveIndex = index;
        updateCounter();
        playCurrentSlideAudio();
    }

    function updateCounter() {
        if (carouselData.length === 0) {
            $('#carouselCounter').text('0 / 0');
        } else {
            $('#carouselCounter').text((carouselActiveIndex + 1) + ' / ' + carouselData.length);
        }
    }

    $('#carouselModal').on('shown.bs.modal', function() {
        if (carouselData.length === 0) {
            startEmptyPolling();
        }
    });

    $('#carouselModal').on('hidden.bs.modal', function() {
        stopCarousel();
    });

    function exportPartKurangExcel() {
        var date = $('#filter_date').val() || '';
        var status = $('#filter_status').val() || '';
        var category = $('#filter_category').val() || '';
        var memberId = $('#filter_member').val() || '';
        var reporterNik = $('#filter_reporter').val() || '';

        var params = new URLSearchParams();
        if (date) params.append('filter_date', date);
        if (status) params.append('filter_status', status);
        if (category) params.append('filter_category', category);
        if (memberId) params.append('member_id', memberId);
        if (reporterNik) params.append('reporter_nik', reporterNik);

        var url = "{{ route('admin.part-kurang.export') }}?" + params.toString();
        window.location.href = url;
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
