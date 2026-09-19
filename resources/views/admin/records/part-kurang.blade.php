@extends('layouts.main')

@section('style')
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
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0"><i class="fas fa-clipboard-list me-2"></i>Laporan Part Kurang</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" id="filter_date" class="form-control form-control-sm" value="{{ $today }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-info btn-sm" onclick="showCarousel()">
                            <i class="fas fa-eye me-1"></i> Show Slideshow
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
                <h5 class="modal-title text-primary">
                    <i class="fas fa-clipboard-list me-2"></i>Detail Laporan Part Kurang Hari Ini
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="stopCarousel()"></button>
            </div>
            <div class="modal-body position-relative">
                <span id="carouselCounter" class="badge bg-secondary">0 / 0</span>
                <div id="carouselContainer" class="carousel-box text-center">
                    <div id="carouselInner" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000" data-bs-pause="false">
                        <div class="carousel-inner" id="carouselInnerContent"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="slidePrev()">
                        <i class="fas fa-chevron-left me-1"></i> Previous
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="slideNext()">
                        Next <i class="fas fa-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    var carouselInterval = null;
    var carouselData = [];
    var carouselActiveIndex = 0;

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
                { data: 'comment', name: 'comment' }
            ]
        });

        $('#filter_date').on('change', function() {
            table.ajax.reload();
        });
    });

    function showCarousel() {
        stopCarousel();
        var date = $('#filter_date').val() || '{{ $today }}';

        $.getJSON('{{ route("admin.part-kurang.carousel") }}', { date: date }, function(data) {
            carouselData = data;
            carouselActiveIndex = 0;
            buildCarousel();
            $('#carouselModal').modal('show');
            startCarousel();
        });
    }

    function buildCarousel() {
        var inner = $('#carouselInnerContent');
        inner.empty();
        if (carouselData.length === 0) {
            inner.html('<div class="carousel-item active"><div class="py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3"></i><p>Tidak ada laporan part kurang untuk tanggal ini.</p></div></div>');
            $('#carouselCounter').text('0 / 0');
            return;
        }
        $.each(carouselData, function(i, item) {
            var active = i === 0 ? ' active' : '';
            var html = '<div class="carousel-item' + active + '">';
            html += '<div class="row h-100">';

            // Left: Member Marshalling photo + name
            html += '<div class="col-md-3 d-flex flex-column align-items-center justify-content-center text-center border-end">';
            if (item.member_photo) {
                html += '<img src="' + item.member_photo + '" class="member-photo border" onerror="this.style.display=\'none\'">';
            } else {
                html += '<div class="member-photo member-photo-placeholder bg-light d-flex align-items-center justify-content-center border"><i class="fas fa-user fa-8x text-secondary"></i></div>';
            }
            html += '<div class="mt-3">';
            html += '  <span class="badge bg-secondary mb-1">Member Marshalling</span>';
            html += '  <strong class="d-block text-dark" style="font-size:1.25rem;">' + escHtml(item.member_name) + '</strong>';
            html += '  <small class="text-muted d-block">NIK: ' + escHtml(item.member_nik || '-') + '</small>';
            html += '</div>';
            html += '</div>';

            // Middle: Kanban Details & Catatan Part Kurang
            html += '<div class="col-md-6 border-start border-end d-flex align-items-center justify-content-center">';
            html += '<div class="text-start w-100 px-4 py-3 overflow-auto" style="max-height:65vh;">';
            html += '<div class="row mb-3"><div class="col-5 slide-label">Seq Record</div><div class="col-7 slide-value fw-bold text-primary" style="font-size:1.2rem;">' + escHtml(item.sequence) + '</div></div>';
            html += '<div class="row mb-3"><div class="col-5 slide-label">Prod Date</div><div class="col-7 slide-value">' + escHtml(item.production_date) + '</div></div>';
            html += '<div class="row mb-3"><div class="col-5 slide-label">Type Traktor</div><div class="col-7 slide-value fw-bold">' + escHtml(item.type) + '</div></div>';
            html += '<div class="row mb-3"><div class="col-5 slide-label">Area</div><div class="col-7 slide-value"><span class="badge bg-primary fs-6">' + escHtml(item.area) + '</span></div></div>';
            html += '<div class="row mb-3"><div class="col-5 slide-label">Waktu Marshalling</div><div class="col-7 slide-value">' + escHtml(item.time_record) + '</div></div>';
            html += '<div class="row mb-3"><div class="col-5 slide-label">Waktu Komentar</div><div class="col-7 slide-value">' + escHtml(item.perakitan_comment_time) + '</div></div>';
            html += '<hr class="my-3">';
            html += '<div class="row mb-0"><div class="col-4 slide-label text-danger fw-bold"><i class="fas fa-clipboard-list me-1"></i>Catatan Part Kurang</div><div class="col-8 slide-value text-dark fw-bold" style="background:#fff3cd; border-radius:8px; padding:12px 16px; font-size:1.15rem; border-left: 4px solid #ffc107;">' + escHtml(item.perakitan_comment) + '</div></div>';
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
            html += '  <span class="badge bg-primary mb-1">Member Perakitan</span>';
            html += '  <strong class="d-block text-dark" style="font-size:1.25rem;">' + escHtml(item.perakitan_name) + '</strong>';
            html += '  <small class="text-muted d-block">NIK: ' + escHtml(item.perakitan_nik || '-') + '</small>';
            html += '</div>';
            html += '</div>';

            html += '</div>';
            inner.append(html);
        });
        updateCounter();
    }

    function startCarousel() {
        stopCarousel();
        if (carouselData.length <= 1) return;
        carouselInterval = setInterval(function() {
            slideNext();
        }, 4000);
    }

    function stopCarousel() {
        if (carouselInterval) {
            clearInterval(carouselInterval);
            carouselInterval = null;
        }
    }

    function slideNext() {
        if (carouselData.length === 0) return;
        carouselActiveIndex = (carouselActiveIndex + 1) % carouselData.length;
        $('#carouselInner').carousel('next');
        updateCounter();
    }

    function slidePrev() {
        if (carouselData.length === 0) return;
        carouselActiveIndex = (carouselActiveIndex - 1 + carouselData.length) % carouselData.length;
        $('#carouselInner').carousel('prev');
        updateCounter();
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
