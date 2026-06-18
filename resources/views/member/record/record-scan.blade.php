@extends('layouts.main')

@section('style')
<style>
    #reader_rack { width: 100%; max-width: 400px; margin: 0 auto; }
    .count-canvas-wrapper { position: relative; display: inline-block; max-width: 100%; }
    .count-canvas-wrapper canvas { max-width: 100%; border: 1px solid #ddd; border-radius: 8px; cursor: crosshair; }
    .count-badge {
        position: absolute; top: 10px; right: 10px;
        background: #e91e63; color: #fff;
        border-radius: 50%; width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    .count-processing-overlay {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5); display: flex;
        flex-direction: column; align-items: center; justify-content: center;
        border-radius: 8px; color: #fff;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="page-title text-primary mb-0">Scan Part</h4>
                <small>Record: {{ $record->Sequence_No_Record }} | Area: {{ ucwords(str_replace('_', ' ', $record->Area)) }}</small>
            </div>
            <a href="{{ route('member.record.record-part', $record->Id_Record) }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to List</a>
        </div>

        @if($prevCompleted)
        <div class="card mb-3">
            <div class="card-body">
                <h5>{{ $recordList->Code_Part }} - {{ $recordList->Name_Part }}</h5>
                <p class="text-muted mb-0">Expected Rack: <strong>{{ $recordList->Code_Rack }}</strong> | Location: <strong>{{ $recordList->Location_Rack }}</strong> | Qty: <strong>{{ $recordList->Qty }}</strong> | Box: <strong>{{ $recordList->Box }}</strong> | Mode: <strong>{{ ucfirst($recordList->Mode) }}</strong></p>
            </div>
        </div>

        <form action="{{ route('member.record.update-part', $recordList->Id_Record_List) }}" method="POST" id="partForm">
            @csrf
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Step 1: Scan Rack QR Code</h6>
                </div>
                <div class="card-body text-center">
                    <div id="reader_rack"></div>
                    <button type="button" id="scanRack" class="btn btn-primary mt-2"><i class="fas fa-camera"></i> Scan Rack</button>
                    <button type="button" id="stopRackScan" class="btn btn-secondary mt-2" style="display:none;"><i class="fas fa-stop"></i> Stop</button>
                    <div class="mt-2">
                        <label class="form-label">Scanned Code Rack</label>
                        <input type="text" name="Code_Rack" id="Code_Rack" class="form-control" readonly required>
                    </div>
                </div>
            </div>

            @if($recordList->Mode == 'manual')
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Step 2: Input Qty (Manual)</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Qty Record</label>
                        <input type="number" name="Qty_Record" id="Qty_Record" class="form-control" required min="0">
                    </div>
                </div>
            </div>
            @else
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Step 2: AI Object Counting</h6>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted">Take a photo and block on an item to count. Expected count: <strong>{{ $recordList->Qty }}</strong></p>

                    <div id="countCapturePrompt">
                        <button type="button" id="startCountCamera" class="btn btn-primary"><i class="fas fa-camera"></i> Open Camera</button>
                        <br><small>or</small><br>
                        <button type="button" id="countFileUpload" class="btn btn-outline-primary"><i class="fas fa-upload"></i> Upload Photo</button>
                        <input type="file" id="countPhotoInput" accept="image/*" style="display:none">
                    </div>

                    <div id="countCameraContainer" style="display:none;">
                        <video id="countVideo" width="100%" style="max-width:500px;" autoplay playsinline></video>
                        <br>
                        <button type="button" id="captureCountPhoto" class="btn btn-primary mt-2"><i class="fas fa-camera"></i> Capture</button>
                        <button type="button" id="closeCountCamera" class="btn btn-secondary mt-2"><i class="fas fa-times"></i> Close</button>
                    </div>

                    <div id="countCanvasArea" style="display:none;">
                        <div class="count-canvas-wrapper">
                            <canvas id="countCanvas"></canvas>
                            <div class="count-badge" id="countBadge" style="display:none;">0</div>
                            <div class="count-processing-overlay" id="countProcessing" style="display:none;">
                                <div class="spinner-border text-light" role="status"></div>
                                <p class="mt-2 mb-0" id="countProcessingText">Analyzing...</p>
                            </div>
                        </div>
                        <p class="text-muted mt-1" id="countInstruction"><i class="fas fa-hand-pointer"></i> Block on one item to count it.</p>
                        <div class="count-sensitivity mt-2" id="countSensitivityArea" style="display:none;">
                            <input type="range" id="countThreshold" min="40" max="99" value="75" step="1" style="display:none;">
                            <span id="countThresholdLabel" style="display:none;">75%</span>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="retakePhoto"><i class="fas fa-redo"></i> Retake</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearCount"><i class="fas fa-eraser"></i> Clear</button>
                        </div>
                        <input type="hidden" name="Qty_Record" id="Qty_Record" value="">
                        <input type="hidden" name="image_data" id="image_data" value="">
                    </div>
                </div>
            </div>
            @endif

            <button type="submit" class="btn btn-primary w-100" id="submitPartBtn" disabled>
                <i class="fas fa-check"></i> Submit Record
            </button>
        </form>
        @else
        <div class="alert alert-warning">Please complete the previous part first.</div>
        @endif
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/js/plugin/html5-qrcode.min.js') }}"></script>
@if($recordList->Mode == 'ai')
<script src="{{ asset('assets/js/plugin/opencv.js') }}" async onload="window.onOpenCvReady();"></script>
<script src="{{ asset('assets/js/plugin/record-scan-ai.js') }}"></script>
@endif
<script>
    window.cvReady = false;
    window.expectedQty = {{ $recordList->Qty ?? 0 }};
    window.currentMode = @json($recordList->Mode ?? 'manual');

    window.onOpenCvReady = function() { window.cvReady = true; };

    let rackScanner = null;

    function onRackScanSuccess(decodedText) {
        $('#Code_Rack').val(decodedText);
        stopRackScanner();
        checkFormReady();
    }

    $('#scanRack').on('click', function() {
        $(this).hide();
        $('#stopRackScan').show();
        if (!rackScanner) {
            rackScanner = new Html5QrcodeScanner('reader_rack', { fps: 10, qrbox: 200 });
        }
        rackScanner.render(onRackScanSuccess);
    });

    $('#stopRackScan').on('click', function() { stopRackScanner(); });

    function stopRackScanner() {
        if (rackScanner) {
            rackScanner.clear().then(function() {
                $('#reader_rack').html('');
                $('#scanRack').show();
                $('#stopRackScan').hide();
            });
        }
    }

    window.checkFormReady = function() {
        if ($('#Code_Rack').val() && $('#Qty_Record').val()) {
            $('#submitPartBtn').prop('disabled', false);
        }
    };

    $('#Qty_Record').on('input', function() {
        if ($(this).val()) window.checkFormReady();
    });

    $('#partForm').on('keypress', function(e) {
        if (e.which === 13) return false;
    });
</script>
@endsection
