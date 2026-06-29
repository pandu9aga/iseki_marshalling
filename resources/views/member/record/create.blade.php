@extends('layouts.main')

@section('style')
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Scan Record</h4>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Scan QR Code</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('member.record.store') }}" method="POST" id="recordForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Scan QR dari label produksi</label>
                                <input type="text" id="scannerInput" class="form-control" placeholder="Scan QR Code dengan USB scanner..." autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sequence No</label>
                                <input type="text" name="sequence_no" id="sequence_no" class="form-control" readonly required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Production Date</label>
                                <input type="text" name="production_date" id="production_date" class="form-control" readonly required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <input type="text" name="type" id="type" class="form-control" readonly>
                            </div>
                            <input type="hidden" name="area" id="area" value="">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($remarkRecord)
<form id="remarkForm" action="{{ route('member.record.save-remark', $remarkRecord->Id_Record) }}" method="POST">
    @csrf
    <div class="modal fade" id="remarkModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Selesai</h5>
                </div>
                <div class="modal-body">
                    <p>Semua part berhasil dicatat!</p>
                    <div class="mb-3">
                        <label for="Remark" class="form-label">Catatan <small class="text-muted">(opsional)</small></label>
                        <textarea name="Remark" id="Remark" class="form-control" rows="4" placeholder="Masukkan catatan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan & Selesai</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endif

<div class="modal fade" id="areaModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Area</h5>
            </div>
            <div class="modal-body">
                <p>Silakan pilih area untuk record ini:</p>
                <select id="modalArea" class="form-control">
                    <option value="">Memuat area...</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirmArea" disabled><i class="fas fa-check"></i> Konfirmasi & Submit</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    var scanBuffer = '';
    var scanTimer = null;

    $('#scannerInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            processScan($(this).val());
            return;
        }
    });

    function processScan(text) {
        if (!text) return;
        var parts = text.split(';');
        if (parts.length >= 4) {
            $('#sequence_no').val(parts[0]);
            $('#production_date').val(parts[1]);
            $('#type').val(parts[2]);
            $('#scannerInput').val('');

            $.getJSON('{{ route("member.record.areas-by-type") }}', { type: parts[2] }, function(areas) {
                var $area = $('#modalArea');
                $area.empty().append('<option value="">Pilih Area</option>');
                $.each(areas, function(i, area) {
                    $area.append('<option value="' + area + '">' +
                        area.replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); }) +
                        '</option>');
                });
                if (areas.length === 1) {
                    $('#modalArea').val(areas[0]);
                    $('#confirmArea').prop('disabled', false);
                }
                $('#areaModal').modal('show');
            });
        } else {
            alert('Format QR tidak valid. Format: Sequence_No;Production_Date;...;Type');
            $('#scannerInput').val('');
        }
    }

    $('#modalArea').on('change', function() {
        $('#confirmArea').prop('disabled', !$(this).val());
    });

    $('#confirmArea').on('click', function() {
        var area = $('#modalArea').val();
        if (!area) return;
        $('#area').val(area);
        $('#areaModal').modal('hide');
        $('#recordForm').submit();
    });

    @if($remarkRecord)
    $(function() {
        $('#remarkModal').modal('show');
    });
    @endif
</script>
@endsection
