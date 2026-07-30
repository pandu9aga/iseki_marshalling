@extends('layouts.main')

@section('style')
<style>
    #reportEmptyTable .badge { font-size: 11px; padding: 3px 6px; }
    .carousel-box {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        background: #fff;
        min-height: 250px;
    }
    .carousel-box .carousel-item { padding: 10px; }
    .slide-label {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 600;
        min-width: 110px;
    }
    .slide-value { font-size: 0.95rem; }
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
            <h4 class="page-title text-primary mb-0">Report Empty</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Date</label>
                        <input type="date" id="filter_date" class="form-control form-control-sm" value="{{ $today }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-info btn-sm" onclick="showCarousel()">
                            <i class="fas fa-eye me-1"></i> Show
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="reportEmptyTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Member</th>
                                <th>Seq Record</th>
                                <th>Prod Date</th>
                                <th>Type</th>
                                <th>Code Part</th>
                                <th>Name Part</th>
                                <th>Box</th>
                                <th>Qty</th>
                                <th>Area</th>
                                <th>Report Time</th>
                                <th>Reporter NIK</th>
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
                    <i class="fas fa-box-open me-2"></i>Detail Report Empty Hari Ini
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
        var table = $('#reportEmptyTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('admin/report-empty') }}",
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
                { data: 'Code_Part', name: 'Code_Part' },
                { data: 'Name_Part', name: 'Name_Part' },
                { data: 'Box', name: 'Box' },
                { data: 'Qty', name: 'Qty' },
                { data: 'area_record', name: 'area_record' },
                { data: 'report_empty_time', name: 'report_empty_time' },
                { data: 'reporter_nik', name: 'reporter_nik' }
            ]
        });

        $('#filter_date').on('change', function() {
            table.ajax.reload();
        });
    });

    function showCarousel() {
        stopCarousel();
        var date = $('#filter_date').val() || '{{ $today }}';

        $.getJSON('{{ url("admin/report-empty/carousel") }}', { date: date }, function(data) {
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
            inner.html('<div class="carousel-item active"><div class="py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3"></i><p>Tidak ada data report empty untuk hari ini.</p></div></div>');
            $('#carouselCounter').text('0 / 0');
            return;
        }
        $.each(carouselData, function(i, item) {
            var active = i === 0 ? ' active' : '';
            var html = '<div class="carousel-item' + active + '">';
            html += '<div class="text-start">';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Code Part</div><div class="col-7 slide-value fw-bold">' + escHtml(item.Code_Part) + '</div></div>';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Name Part</div><div class="col-7 slide-value">' + escHtml(item.Name_Part || '-') + '</div></div>';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Code Rack</div><div class="col-7 slide-value">' + escHtml(item.Code_Rack) + '</div></div>';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Box</div><div class="col-7 slide-value">' + escHtml(item.Box || '-') + '</div></div>';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Qty</div><div class="col-7 slide-value">' + item.Qty + '</div></div>';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Difference</div><div class="col-7 slide-value">' + escHtml(item.Difference || '-') + '</div></div>';
            html += '<hr>';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Sequence Record</div><div class="col-7 slide-value">' + escHtml(item.sequence) + '</div></div>';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Production Date</div><div class="col-7 slide-value">' + escHtml(item.production_date) + '</div></div>';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Type</div><div class="col-7 slide-value">' + escHtml(item.type) + '</div></div>';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Area</div><div class="col-7 slide-value">' + escHtml(item.area) + '</div></div>';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Member</div><div class="col-7 slide-value">' + escHtml(item.member) + '</div></div>';
            html += '<hr>';
            html += '<div class="row mb-2"><div class="col-5 slide-label">Reporter</div><div class="col-7 slide-value">' + escHtml(item.reporter_name) + '</div></div>';
            html += '<div class="row mb-0"><div class="col-5 slide-label">Report Time</div><div class="col-7 slide-value">' + item.report_empty + '</div></div>';
            html += '</div></div>';
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
        goToSlide(carouselActiveIndex);
    }

    function slidePrev() {
        if (carouselData.length === 0) return;
        carouselActiveIndex = (carouselActiveIndex - 1 + carouselData.length) % carouselData.length;
        goToSlide(carouselActiveIndex);
    }

    function goToSlide(index) {
        carouselActiveIndex = index;
        var items = $('#carouselInnerContent .carousel-item');
        items.removeClass('active');
        $(items[index]).addClass('active');
        updateCounter();

        // refresh data on last slide
        if (index === items.length - 1) {
            refreshCarouselData();
        }
    }

    function updateCounter() {
        $('#carouselCounter').text((carouselActiveIndex + 1) + ' / ' + carouselData.length);
    }

    function refreshCarouselData() {
        var date = $('#filter_date').val() || '{{ $today }}';
        $.getJSON('{{ url("admin/report-empty/carousel") }}', { date: date }, function(data) {
            var oldLen = carouselData.length;
            carouselData = data;
            if (data.length !== oldLen) {
                var activeItem = $('#carouselInnerContent .carousel-item.active');
                var activeIdx = carouselActiveIndex;
                buildCarousel();
                goToSlide(Math.min(activeIdx, data.length - 1));
            }
        });
    }

    function escHtml(str) {
        if (!str) return '-';
        return $('<span>').text(str).html();
    }
</script>
@endsection
