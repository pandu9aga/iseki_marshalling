@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Scan Kanban</h4>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Scan QR Kanban</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Scan QR dari label produksi</label>
                            <input type="text" id="scannerInput" class="form-control" placeholder="Scan QR Code dengan USB scanner..." autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sequence No</label>
                            <input type="text" id="sequence_no" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Production Date</label>
                            <input type="text" id="production_date" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <input type="text" id="type" class="form-control" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="resultArea" class="mt-3" style="display:none;"></div>
    </div>
</div>
@endsection

@section('script')
<script>
    $('#scannerInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            processScan($(this).val());
        }
    });

    function processScan(text) {
        if (!text) return;
        var parts = text.split(';');
        if (parts.length >= 3) {
            $('#sequence_no').val(parts[0]);
            $('#production_date').val(parts[1]);
            $('#type').val(parts[2]);
            $('#scannerInput').val('');
            searchRecords(parts[0], parts[1]);
        } else {
            alert('Format QR tidak valid. Format: Sequence_No;Production_Date;Type');
            $('#scannerInput').val('');
        }
    }

    function searchRecords(sequenceNo, productionDate) {
        $('#resultArea').hide().html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Mencari data...</p></div>').fadeIn(200);

        $.getJSON('{{ route("perakitan.kanban.search") }}', {
            sequence_no: sequenceNo,
            production_date: productionDate
        }, function(res) {
            if (!res.found) {
                $('#resultArea').html(
                    '<div class="alert alert-warning text-center">' +
                    '<i class="fas fa-exclamation-triangle fa-2x mb-2"></i>' +
                    '<p class="mb-0 fw-bold">' + res.message + '</p></div>'
                ).fadeIn(200);
                return;
            }
            var html = '<div class="card border-0 shadow-sm"><div class="card-header bg-white"><strong>Ditemukan ' + res.records.length + ' record</strong></div><div class="card-body p-2">';
            $.each(res.records, function(i, r) {
                html += '<div class="d-flex align-items-center justify-content-between border-bottom py-2 px-2 record-item" style="cursor:pointer;" data-id="' + r.Id_Record + '">' +
                    '<div>' +
                    '<strong>' + r.Area_Label + '</strong><br>' +
                    '<small class="text-muted">' + r.Sequence_No + ' | ' + r.Production_Date + '</small><br>' +
                    '<small class="text-muted">Member: ' + r.Member + '</small>' +
                    '</div>' +
                    '<a href="{{ url("perakitan/kanban") }}/' + r.Id_Record + '/detail" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> Lihat Detail</a>' +
                    '</div>';
            });
            html += '</div></div>';
            $('#resultArea').html(html).fadeIn(200);
        }).fail(function() {
            $('#resultArea').html(
                '<div class="alert alert-danger text-center"><i class="fas fa-times-circle fa-2x mb-2"></i><p class="mb-0">Terjadi kesalahan saat mencari data.</p></div>'
            ).fadeIn(200);
        });
    }
</script>
@endsection
