<?php $__env->startSection('style'); ?>
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
        padding: 10px 16px;
        color: #111;
        font-weight: 900;
        word-break: break-word;
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        overflow: hidden;
        position: relative;
    }
    .comment-text-inner {
        width: 100%;
        line-height: 1.15;
        display: inline-block;
        word-break: break-word;
        transition: font-size 0.05s ease;
    }
    #carouselCounter {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 0.85rem;
        z-index: 5;
    }
    /* Member Area Profile Cards */
    .member-area-card {
        border-radius: 10px;
        border: 1px solid #e9ecef;
        background: #ffffff;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .member-area-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .member-profile-thumb {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #F36494;
    }
    .member-profile-thumb-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8bbd0;
        color: #F36494;
        font-weight: bold;
        font-size: 1.1rem;
        border: 2px solid #F36494;
    }
    .area-badge-title {
        background-color: #fdf2f4;
        color: #F36494;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
        border: 1px solid #f8bbd0;
    }
    .member-stat-badge {
        font-size: 0.72rem;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-weight: 600;
        line-height: 1.2;
    }
    .member-stat-badge.badge-unit {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }
    .member-stat-badge.badge-part {
        background-color: #e3f2fd;
        color: #1565c0;
        border: 1px solid #bbdefb;
    }
    .member-stat-badge.badge-kurang {
        background-color: #fff3e0;
        color: #e65100;
        border: 1px solid #ffe0b2;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-3 g-2">
            <div>
                <h4 class="page-title text-primary mb-0"><i class="fas fa-clipboard-list me-2"></i>Laporan Part Kurang</h4>
                <small class="text-muted">Statistik perolehan dan laporan part kurang berdasarkan tanggal terpilih</small>
            </div>
            <!-- Filter Tanggal Bagian Atas -->
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold small text-muted text-nowrap"><i class="fas fa-calendar-alt me-1 text-primary"></i>Filter Tanggal:</span>
                <div class="input-group input-group-sm" style="width: auto;">
                    <button type="button" class="btn btn-outline-primary" id="btnDateTopPrev" title="Mundur 1 Hari">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <input type="date" id="filter_date_top" class="form-control form-control-sm text-center fw-bold" value="<?php echo e($today); ?>" style="max-width: 140px;">
                    <button type="button" class="btn btn-outline-primary" id="btnDateTopNext" title="Maju 1 Hari">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnDateTopClear" title="Semua Tanggal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Card Statistik Perolehan -->
        <div class="row g-3 mb-3">
            <!-- 1. Perolehan Unit (Record) Selesai -->
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 mb-0 h-100" style="border-left: 5px solid #28a745 !important; border-radius: 10px;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.75rem; letter-spacing: 0.5px;">Unit Selesai <span class="stat-date-label">Hari Ini</span></small>
                            <h2 class="mb-0 fw-bold text-success mt-1" id="statTodayDoneRecords"><?php echo e(number_format($todayDoneRecords ?? 0)); ?></h2>
                            <small class="text-muted" style="font-size: 0.75rem;">Record Traktor Selesai</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: rgba(40, 167, 69, 0.12); color: #28a745;">
                            <i class="fas fa-car fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Jumlah Keseluruhan Record List -->
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 mb-0 h-100" style="border-left: 5px solid #0d6efd !important; border-radius: 10px;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Record List <span class="stat-date-label">Hari Ini</span></small>
                            <h2 class="mb-0 fw-bold text-primary mt-1" id="statTodayRecordLists"><?php echo e(number_format($todayRecordListsCount ?? 0)); ?></h2>
                            <small class="text-muted" style="font-size: 0.75rem;">Total Item Part</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: rgba(13, 110, 253, 0.12); color: #0d6efd;">
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Jumlah Part Salah -->
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 mb-0 h-100" style="border-left: 5px solid #6f42c1 !important; border-radius: 10px;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.75rem; letter-spacing: 0.5px;">Part Salah <span class="stat-date-label">Hari Ini</span></small>
                            <h2 class="mb-0 fw-bold mt-1" id="statTodayPartSalah" style="color: #6f42c1;"><?php echo e(number_format($todayPartSalahCount ?? 0)); ?></h2>
                            <small class="text-muted" style="font-size: 0.75rem;">Laporan Kategori Salah</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: rgba(111, 66, 193, 0.12); color: #6f42c1;">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!empty($membersByArea) && count($membersByArea) > 0): ?>
        <!-- Card Profil Member Marshalling Berdasarkan Area -->
        <div class="card mb-3 shadow-sm border-0" id="sectionMemberArea">
            <?php
                $initialActiveAreaCount = 0;
                foreach ($membersByArea as $areaKey => $membersList) {
                    $hasActive = collect($membersList)->contains(function($item) {
                        return ($item['unit_selesai'] ?? 0) > 0 || ($item['part_selesai'] ?? 0) > 0 || ($item['part_kurang'] ?? 0) > 0;
                    });
                    if ($hasActive) $initialActiveAreaCount++;
                }
            ?>
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark" style="font-size: 0.95rem;">
                    <i class="fas fa-users text-primary me-2"></i>Profil Member Marshalling Berdasarkan Area
                </span>
                <span class="badge bg-light text-muted border" id="badgeActiveAreaCount"><?php echo e($initialActiveAreaCount); ?> Area Aktif</span>
            </div>
            <div class="card-body p-3">
                <div class="alert alert-light border text-center py-3 mb-0 text-muted <?php echo e($initialActiveAreaCount > 0 ? 'd-none' : ''); ?>" id="emptyMemberAreaAlert">
                    <i class="fas fa-info-circle me-1 text-primary"></i>Tidak ada aktivitas member (Unit, Part, atau Kurang) pada tanggal terpilih.
                </div>
                <div class="row g-3" id="memberAreaListRow">
                    <?php $__currentLoopData = $membersByArea; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $areaKey => $membersList): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $activeCountInThisArea = collect($membersList)->filter(function($item) {
                            return ($item['unit_selesai'] ?? 0) > 0 || ($item['part_selesai'] ?? 0) > 0 || ($item['part_kurang'] ?? 0) > 0;
                        })->count();
                        $hasActiveInitialMember = $activeCountInThisArea > 0;
                    ?>
                    <div class="col-12 col-md-6 col-lg-4 area-card-col <?php echo e(!$hasActiveInitialMember ? 'd-none' : ''); ?>" data-area="<?php echo e($areaKey); ?>">
                        <div class="member-area-card p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
                                <span class="area-badge-title">
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo e(ucwords(str_replace('_', ' ', $areaKey))); ?>

                                </span>
                                <span class="badge bg-secondary rounded-pill area-member-badge" style="font-size: 0.75rem;">
                                    <span class="area-active-count"><?php echo e($activeCountInThisArea); ?></span> Member
                                </span>
                            </div>
                            <div class="d-flex flex-column gap-2 member-list-wrapper">
                                <?php $__currentLoopData = $membersList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $isZero = (($m['unit_selesai'] ?? 0) == 0 && ($m['part_selesai'] ?? 0) == 0 && ($m['part_kurang'] ?? 0) == 0);
                                ?>
                                <div class="d-flex align-items-start gap-2 p-2 rounded hover-bg border member-item-row <?php echo e($isZero ? 'd-none' : ''); ?>" data-nik="<?php echo e($m['nik']); ?>" style="background-color: #fafbfc;">
                                    <?php if(!empty($m['photo'])): ?>
                                        <img src="<?php echo e($m['photo']); ?>" alt="<?php echo e($m['nama']); ?>" class="member-profile-thumb mt-1" onerror="this.onerror=null; this.src=''; this.className='member-profile-thumb-placeholder mt-1'; this.innerHTML='<?php echo e(strtoupper(substr($m['nama'], 0, 1))); ?>';">
                                    <?php else: ?>
                                        <div class="member-profile-thumb-placeholder mt-1">
                                            <?php echo e(strtoupper(substr($m['nama'], 0, 1))); ?>

                                        </div>
                                    <?php endif; ?>
                                    <div class="overflow-hidden flex-grow-1">
                                        <strong class="d-block text-truncate text-dark mb-1" style="font-size: 0.9rem;" title="<?php echo e($m['nama']); ?>">
                                            <?php echo e($m['nama']); ?>

                                        </strong>
                                        <div class="d-flex flex-wrap gap-1">
                                            <span class="member-stat-badge badge-unit" title="Unit Selesai">
                                                <i class="fas fa-check-circle"></i> Unit: <strong class="member-unit-val" data-nik="<?php echo e($m['nik']); ?>"><?php echo e($m['unit_selesai'] ?? 0); ?></strong>
                                            </span>
                                            <span class="member-stat-badge badge-part" title="Part Selesai (Scanned)">
                                                <i class="fas fa-boxes"></i> Part: <strong class="member-part-val" data-nik="<?php echo e($m['nik']); ?>"><?php echo e($m['part_selesai'] ?? 0); ?></strong>
                                            </span>
                                            <span class="member-stat-badge badge-kurang" title="Part Kurang">
                                                <i class="fas fa-exclamation-circle"></i> Kurang: <strong class="member-kurang-val" data-nik="<?php echo e($m['nik']); ?>"><?php echo e($m['part_kurang'] ?? 0); ?></strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3 align-items-end g-2">
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label fw-bold mb-1">Tanggal</label>
                        <div class="input-group input-group-sm">
                            <button type="button" class="btn btn-outline-primary" id="btnDatePrev" title="Mundur 1 Hari">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <input type="date" id="filter_date" class="form-control form-control-sm text-center fw-bold" value="<?php echo e($today); ?>">
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
                            <?php $__currentLoopData = $marshallingMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($mm->id); ?>"><?php echo e($mm->nama); ?> (<?php echo e($mm->nik); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label fw-bold mb-1">Pelapor (Perakitan)</label>
                        <select id="filter_reporter" class="form-select form-select-sm">
                            <option value="">Semua Pelapor</option>
                            <?php $__currentLoopData = $reporters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($rep->nik); ?>"><?php echo e($rep->nama); ?> (<?php echo e($rep->nik); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                <th>Time Record</th>
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
<audio id="panggilanPlayer" src="<?php echo e(asset('assets/sounds/panggilan_kepada.MP3')); ?>" preload="auto"></audio>
<audio id="namaPlayer" preload="auto"></audio>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
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
                url: "<?php echo e(route('admin.part-kurang.list')); ?>",
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
                { data: 'time_record', name: 'time_record' },
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

        // Fungsi Ajax untuk refresh angka di Card Statistik Perolehan sesuai tanggal terpilih
        function fetchSummaryStats(selectedDate) {
            var todayStr = '<?php echo e($today); ?>';
            var labelText = 'Hari Ini';
            if (selectedDate) {
                if (selectedDate === todayStr) {
                    labelText = 'Hari Ini';
                } else {
                    var parts = selectedDate.split('-');
                    if (parts.length === 3) {
                        labelText = '(' + parts[2] + '/' + parts[1] + '/' + parts[0] + ')';
                    } else {
                        labelText = '(' + selectedDate + ')';
                    }
                }
            } else {
                labelText = '(Semua)';
            }
            $('.stat-date-label').text(labelText);

            $.ajax({
                url: "<?php echo e(route('admin.part-kurang.stats')); ?>",
                type: 'GET',
                data: { filter_date: selectedDate },
                dataType: 'json',
                success: function(res) {
                    if (res && res.success) {
                        $('#statTodayDoneRecords').text(res.done_records);
                        $('#statTodayRecordLists').text(res.record_lists_count);
                        $('#statTodayPartSalah').text(res.part_salah_count);

                        if (res.member_stats) {
                            var visibleAreaCount = 0;
                            var totalVisibleMembers = 0;

                            // Update nilai tiap member dan atur visibilitas
                            $('.member-item-row').each(function() {
                                var nik = $(this).data('nik');
                                var s = res.member_stats[nik];
                                var unitVal = (s && s.unit_selesai !== undefined) ? parseInt(s.unit_selesai) : 0;
                                var partVal = (s && s.part_selesai !== undefined) ? parseInt(s.part_selesai) : 0;
                                var kurangVal = (s && s.part_kurang !== undefined) ? parseInt(s.part_kurang) : 0;

                                $(this).find('.member-unit-val').text(unitVal);
                                $(this).find('.member-part-val').text(partVal);
                                $(this).find('.member-kurang-val').text(kurangVal);

                                // Sembunyikan jika ketiga datanya bernilai 0
                                if (unitVal === 0 && partVal === 0 && kurangVal === 0) {
                                    $(this).addClass('d-none');
                                } else {
                                    $(this).removeClass('d-none');
                                    totalVisibleMembers++;
                                }
                            });

                            // Update tiap kolom card area: sembunyikan jika tidak ada member aktif di dalamnya
                            $('.area-card-col').each(function() {
                                var activeInArea = $(this).find('.member-item-row:not(.d-none)').length;
                                $(this).find('.area-active-count').text(activeInArea);
                                if (activeInArea === 0) {
                                    $(this).addClass('d-none');
                                } else {
                                    $(this).removeClass('d-none');
                                    visibleAreaCount++;
                                }
                            });

                            // Update badge header dan pesan kosong
                            if (visibleAreaCount === 0) {
                                $('#emptyMemberAreaAlert').removeClass('d-none');
                                $('#badgeActiveAreaCount').text('0 Area Aktif');
                            } else {
                                $('#emptyMemberAreaAlert').addClass('d-none');
                                $('#badgeActiveAreaCount').text(visibleAreaCount + ' Area Aktif');
                            }
                        }
                    }
                },
                error: function(err) {
                    console.warn("Gagal memuat ringkasan statistik:", err);
                }
            });
        }

        // Sinkronisasi Tanggal: Saat input bawah berubah
        $('#filter_date').on('change', function() {
            var val = $(this).val();
            if ($('#filter_date_top').val() !== val) {
                $('#filter_date_top').val(val);
            }
            table.ajax.reload();
            fetchSummaryStats(val);
        });

        // Sinkronisasi Tanggal: Saat input atas berubah
        $('#filter_date_top').on('change', function() {
            var val = $(this).val();
            if ($('#filter_date').val() !== val) {
                $('#filter_date').val(val);
            }
            table.ajax.reload();
            fetchSummaryStats(val);
        });

        $('#filter_status, #filter_category, #filter_member, #filter_reporter').on('change', function() {
            table.ajax.reload();
        });

        // Tombol Filter Tanggal Bawah
        $('#btnDateClear').on('click', function() {
            $('#filter_date').val('').trigger('change');
        });

        $('#btnDatePrev').on('click', function() {
            var curr = $('#filter_date').val();
            if (!curr) curr = new Date().toISOString().split('T')[0];
            var d = new Date(curr);
            d.setDate(d.getDate() - 1);
            var prevDate = d.toISOString().split('T')[0];
            $('#filter_date').val(prevDate).trigger('change');
        });

        $('#btnDateNext').on('click', function() {
            var curr = $('#filter_date').val();
            if (!curr) curr = new Date().toISOString().split('T')[0];
            var d = new Date(curr);
            d.setDate(d.getDate() + 1);
            var nextDate = d.toISOString().split('T')[0];
            $('#filter_date').val(nextDate).trigger('change');
        });

        // Tombol Filter Tanggal Atas (Tersambung otomatis)
        $('#btnDateTopClear').on('click', function() {
            $('#filter_date_top').val('').trigger('change');
        });

        $('#btnDateTopPrev').on('click', function() {
            var curr = $('#filter_date_top').val();
            if (!curr) curr = new Date().toISOString().split('T')[0];
            var d = new Date(curr);
            d.setDate(d.getDate() - 1);
            var prevDate = d.toISOString().split('T')[0];
            $('#filter_date_top').val(prevDate).trigger('change');
        });

        $('#btnDateTopNext').on('click', function() {
            var curr = $('#filter_date_top').val();
            if (!curr) curr = new Date().toISOString().split('T')[0];
            var d = new Date(curr);
            d.setDate(d.getDate() + 1);
            var nextDate = d.toISOString().split('T')[0];
            $('#filter_date_top').val(nextDate).trigger('change');
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
            url: '<?php echo e(route("admin.part-kurang.carousel")); ?>',
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
            url: '<?php echo e(route("admin.part-kurang.carousel")); ?>',
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
            html += '    <div class="comment-big-box"><span class="comment-text-inner">' + escHtml(item.perakitan_comment) + '</span></div>';
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
            url: '<?php echo e(route("admin.part-kurang.carousel")); ?>',
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

    function fitCommentText(slideIndex) {
        var idx = (typeof slideIndex !== 'undefined') ? slideIndex : carouselActiveIndex;
        var slideEl = document.getElementById('carousel_slide_' + idx);
        if (!slideEl) return;

        var box = slideEl.querySelector('.comment-big-box');
        var inner = slideEl.querySelector('.comment-text-inner');
        if (!box || !inner) return;

        // Hitung batas ruang yang tersedia di dalam box (kurangi padding)
        var style = window.getComputedStyle(box);
        var paddingX = (parseFloat(style.paddingLeft) || 0) + (parseFloat(style.paddingRight) || 0);
        var paddingY = (parseFloat(style.paddingTop) || 0) + (parseFloat(style.paddingBottom) || 0);
        var maxW = (box.clientWidth - paddingX) || 100;
        var maxH = (box.clientHeight - paddingY) || 100;

        // Binary search font size dalam satuan px agar pas dan tidak meluap (overflow)
        // Mulai dari font besar (~8vw hingga 130px) turun sampai teks muat
        var maxFontSize = Math.min(130, Math.floor(window.innerWidth * 0.08));
        var minFontSize = 14;
        var bestFontSize = minFontSize;

        var low = minFontSize;
        var high = maxFontSize;

        while (low <= high) {
            var mid = Math.floor((low + high) / 2);
            inner.style.fontSize = mid + 'px';

            var isOverflowing = (inner.scrollHeight > maxH + 2) || (inner.scrollWidth > maxW + 2);
            if (!isOverflowing) {
                bestFontSize = mid;
                low = mid + 1; // coba ukuran lebih besar
            } else {
                high = mid - 1; // terlalu besar, perkecil
            }
        }

        inner.style.fontSize = bestFontSize + 'px';
    }

    function goToSlide(index) {
        $('.carousel-item').removeClass('active');
        $('#carousel_slide_' + index).addClass('active');
        carouselActiveIndex = index;
        updateCounter();

        // Sesuaikan ukuran font catatan secara dinamis agar pas di kotak
        setTimeout(function() {
            fitCommentText(index);
        }, 10);

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
        } else {
            fitCommentText();
        }
    });

    // Auto-fit saat ukuran window diubah/resize
    $(window).on('resize', function() {
        if ($('#carouselModal').hasClass('show')) {
            fitCommentText();
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

        var url = "<?php echo e(route('admin.part-kurang.export')); ?>?" + params.toString();
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\iseki_marshalling\resources\views/admin/records/part-kurang.blade.php ENDPATH**/ ?>