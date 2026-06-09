@extends('layouts.main')

@section('style')
<style>
    #reader {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Scan Record</h4>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Scan QR Code</h5>
                    </div>
                    <div class="card-body text-center">
                        <div id="reader"></div>
                        <button type="button" id="startScan" class="btn btn-primary mt-3"><i class="fas fa-camera"></i> Start Scan</button>
                        <button type="button" id="stopScan" class="btn btn-secondary mt-3" style="display:none;"><i class="fas fa-stop"></i> Stop</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Record Info</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('member.record.store') }}" method="POST" id="recordForm">
                            @csrf
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
                            <div class="mb-3">
                                <label class="form-label">Area</label>
                                <select name="area" id="area" class="form-control" required>
                                    <option value="">Select Area</option>
                                    @foreach($areas as $area)
                                    <option value="{{ $area }}">{{ ucwords(str_replace('_', ' ', $area)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
                                <i class="fas fa-save"></i> Create Record
                            </button>
                        </form>
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
    let html5QrcodeScanner = null;

    function onScanSuccess(decodedText, decodedResult) {
        var parts = decodedText.split(';');
        if (parts.length >= 4) {
            $('#sequence_no').val(parts[0]);
            $('#production_date').val(parts[3]);
            $('#type').val(parts[2]);
            $('#submitBtn').prop('disabled', false);
            stopCamera();
        } else {
            alert('Invalid QR format. Expected: Sequence;Type;Prod_Date');
        }
    }

    $('#startScan').on('click', function() {
        $(this).hide();
        $('#stopScan').show();
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: 250 });
        }
        html5QrcodeScanner.render(onScanSuccess);
    });

    $('#stopScan').on('click', function() {
        stopCamera();
    });

    function stopCamera() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().then(function() {
                $('#reader').html('');
                $('#startScan').show();
                $('#stopScan').hide();
            });
        }
    }
</script>
@endsection
