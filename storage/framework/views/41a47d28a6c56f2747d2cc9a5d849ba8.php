<?php $__env->startSection('style'); ?>
<style>
    #partKurangTable .badge { font-size: 11px; padding: 3px 6px; }
    #carouselModal .modal-body {
        display: flex;
        flex-direction: column;
        padding: 10px 20px;
    }
    .carousel-box {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        background: #fff;
        flex-grow: 1;
        min-height: 65vh;
    }
    #carouselInner, #carouselInner .carousel-inner, #carouselInner .carousel-item {
        height: 100%;
    }
    .carousel-box .carousel-item { padding: 5px; }
    .slide-label {
        font-size: 1rem;
        color: #6c757d;
        font-weight: 600;
    }
    .slide-value { font-size: 1.05rem; }
    .member-photo {
        width: 100%;
        height: 55vh;
        object-fit: cover;
    }
    .member-photo-placeholder {
        width: 100%;
        height: 55vh;
    }
    #carouselCounter {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 0.85rem;
        z-index: 5;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
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
                            <input type="date" id="filter_date" class="form-control form-control-sm text-center fw-bold" value="">
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
                    <div class="col-12 col-sm-6 col-md-3 col-lg-3">
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
                    <div class="col-12 col-sm-12 col-md-12 col-lg-3 d-flex flex-wrap gap-2 justify-content-lg-end mt-2 mt-lg-0">
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
                { data: 'comment', name: 'comment' },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false }
            ]
        });

        $('#filter_date, #filter_status, #filter_member, #filter_reporter').on('change', function() {
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
        panggilanAudio.currentTime = 0;
        panggilanAudio.play().catch(function(err) {
            console.warn('Panggilan audio play blocked/error:', err);
            // Browser autoplay policy might require interaction
            var currentItem = carouselData[carouselActiveIndex];
            if (currentItem && currentItem.member_audio) {
                namaAudio.src = currentItem.member_audio;
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

    function showCarousel() {
        stopCarousel();
        stopAudioPlayback();

        $.getJSON('<?php echo e(route("admin.part-kurang.carousel")); ?>', function(data) {
            carouselData = data;
            carouselActiveIndex = 0;
            buildCarousel();
            $('#carouselModal').modal('show');
            playCurrentSlideAudio();
        });
    }

    function buildCarousel() {
        var inner = $('#carouselInnerContent');
        inner.empty();
        if (carouselData.length === 0) {
            inner.html('<div class="carousel-item active"><div class="py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3"></i><p>Tidak ada laporan part kurang yang pending.</p></div></div>');
            $('#carouselCounter').text('0 / 0');
            $('#audioRepetitionCounter').text('Panggilan: 0 / 0');
            return;
        }
        $.each(carouselData, function(i, item) {
            var active = i === carouselActiveIndex ? ' active' : '';
            var html = '<div class="carousel-item' + active + '" id="carousel_slide_' + i + '">';
            html += '<div class="row h-100">';

            // Left: Member Marshalling photo + name
            html += '<div class="col-md-3 d-flex flex-column align-items-center justify-content-center text-center border-end">';
            if (item.member_photo) {
                html += '<img src="' + item.member_photo + '" class="member-photo border" onerror="this.style.display=\'none\'">';
            } else {
                html += '<div class="member-photo member-photo-placeholder bg-light d-flex align-items-center justify-content-center border"><i class="fas fa-user fa-8x text-secondary"></i></div>';
            }
            html += '<div class="mt-3">';
            html += '  <span class="badge bg-primary mb-1">Marshalling</span>';
            html += '  <strong class="d-block text-dark" style="font-size:2rem;">' + escHtml(item.member_name) + '</strong>';
            html += '  <small class="text-muted d-block">NIK: ' + escHtml(item.member_nik || '-') + '</small>';
            if (item.member_audio) {
                html += '  <span class="badge bg-light text-success border mt-1"><i class="fas fa-check-circle me-1"></i>Audio Terdaftar</span>';
            } else {
                html += '  <span class="badge bg-light text-muted border mt-1"><i class="fas fa-volume-mute me-1"></i>Audio Belum Diupload</span>';
            }
            html += '</div>';
            html += '</div>';

            // Middle: Kanban Details & Catatan Part Kurang
            html += '<div class="col-md-6 border-start border-end d-flex flex-column justify-content-start py-2 h-100">';
            html += '<div class="text-start w-100 px-4 pt-1 d-flex flex-column h-100">';
            html += '  <div class="row mb-2"><div class="col-5 slide-label">Seq Record</div><div class="col-7 slide-value fw-bold text-primary" style="font-size:1.2rem;">' + escHtml(item.sequence) + '</div></div>';
            html += '  <div class="row mb-2"><div class="col-5 slide-label">Prod Date</div><div class="col-7 slide-value">' + escHtml(item.production_date) + '</div></div>';
            html += '  <div class="row mb-2"><div class="col-5 slide-label">Type Traktor</div><div class="col-7 slide-value fw-bold">' + escHtml(item.type) + '</div></div>';
            html += '  <div class="row mb-2"><div class="col-5 slide-label">Area</div><div class="col-7 slide-value"><span class="badge bg-primary fs-6">' + escHtml(item.area) + '</span></div></div>';
            html += '  <div class="row mb-2"><div class="col-5 slide-label">Waktu Marshalling</div><div class="col-7 slide-value">' + escHtml(item.time_record) + '</div></div>';
            html += '  <div class="row mb-2"><div class="col-5 slide-label">Waktu Komentar</div><div class="col-7 slide-value">' + escHtml(item.perakitan_comment_time) + '</div></div>';
            html += '  <hr class="my-2">';
            html += '  <div class="d-flex flex-column flex-grow-1 mb-2">';
            html += '    <div class="slide-label text-danger fw-bold mb-1" style="font-size:1.05rem;"><i class="fas fa-clipboard-list me-1"></i>Catatan Part Kurang:</div>';
            html += '    <div class="w-100 text-dark fw-bold flex-grow-1 overflow-auto" style="background:#fff3cd; border-radius:8px; padding:14px 18px; font-size:4rem; border:1px solid #ffeeba; border-left:6px solid #ffc107; word-break:break-word; min-height:140px;">' + escHtml(item.perakitan_comment) + '</div>';
            html += '  </div>';
            html += '</div>';
            html += '</div>';

            // Right: Member Perakitan (Commenter) photo + name
            html += '<div class="col-md-3 text-center d-flex flex-column align-items-center justify-content-center border-start py-3">';
            if (item.perakitan_photo) {
                html += '<img src="' + item.perakitan_photo + '" class="member-photo border" onerror="this.style.display=\'none\'">';
            } else {
                html += '<div class="member-photo member-photo-placeholder bg-light d-flex align-items-center justify-content-center border"><i class="fas fa-user fa-8x text-secondary"></i></div>';
            }
            html += '<div class="mt-3">';
            html += '  <span class="badge bg-info mb-1">Perakitan</span>';
            html += '  <strong class="d-block text-dark" style="font-size:2rem;">' + escHtml(item.perakitan_name) + '</strong>';
            html += '  <small class="text-muted d-block">NIK: ' + escHtml(item.perakitan_nik || '-') + '</small>';
            html += '</div>';
            html += '</div>';

            html += '</div>';
            inner.append(html);
        });
        updateCounter();
    }

    function stopCarousel() {
        stopAudioPlayback();
    }

    function slideNext() {
        stopAudioPlayback();

        // Selalu cek data terbaru ke server setiap kali akan berganti slide (semua pending)
        $.getJSON('<?php echo e(route("admin.part-kurang.carousel")); ?>', function(data) {
            var currentId = (carouselData[carouselActiveIndex]) ? carouselData[carouselActiveIndex].Id_Part_Kurang : null;
            carouselData = data;

            if (carouselData.length === 0) {
                carouselActiveIndex = 0;
                buildCarousel();
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

    $('#carouselModal').on('hidden.bs.modal', function() {
        stopCarousel();
    });

    function exportPartKurangExcel() {
        var date = $('#filter_date').val() || '';
        var status = $('#filter_status').val() || '';
        var memberId = $('#filter_member').val() || '';
        var reporterNik = $('#filter_reporter').val() || '';

        var params = new URLSearchParams();
        if (date) params.append('filter_date', date);
        if (status) params.append('filter_status', status);
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