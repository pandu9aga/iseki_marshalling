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
                    <p class="text-muted">Take a photo and tap on an item to count. Expected count: <strong>{{ $recordList->Qty }}</strong></p>

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
                        <p class="text-muted mt-1" id="countInstruction"><i class="fas fa-hand-pointer"></i> Tap on one item to count it.</p>
                        <div class="count-sensitivity mt-2" id="countSensitivityArea" style="display:none;">
                            <input type="range" id="countThreshold" min="40" max="99" value="75" step="1">
                            <span id="countThresholdLabel">75%</span>
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
<script src="{{ asset('assets/js/plugin/opencv.js') }}" async onload="onOpenCvReady();"></script>
@endif
<script>
    let cvReady = false;
    let expectedQty = {{ $recordList->Qty ?? 0 }};
    let currentMode = @json($recordList->Mode ?? 'manual');
    let countDetections = [];
    let countManualAdds = [];
    let countOriginalImage = null;
    let countOriginalDataUrl = null;
    let lastClickX = 0, lastClickY = 0;
    let finalCount = 0;

    function onOpenCvReady() { cvReady = true; }

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

    @if($recordList->Mode == 'ai')
    let countStream = null;

    async function startCountCamera() {
        try {
            if (countStream) countStream.getTracks().forEach(t => t.stop());
            countStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } } });
            document.getElementById('countVideo').srcObject = countStream;
            $('#countCapturePrompt').hide();
            $('#countCameraContainer').show();
        } catch(e) { alert('Camera error: ' + e.message); }
    }

    function stopCountCamera() {
        if (countStream) { countStream.getTracks().forEach(t => t.stop()); countStream = null; }
        $('#countCameraContainer').hide();
        $('#countCapturePrompt').show();
    }

    function capturePhoto() {
        var video = document.getElementById('countVideo');
        var canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        countOriginalDataUrl = canvas.toDataURL('image/jpeg', 0.92);
        stopCountCamera();
        loadCountImage(countOriginalDataUrl);
    }

    function loadCountImage(dataUrl) {
        countOriginalDataUrl = dataUrl;
        $('#image_data').val(dataUrl);
        var img = new Image();
        img.onload = function() {
            var w = img.width, h = img.height;
            var MAX = 640;
            if (w > MAX || h > MAX) { var r = Math.min(MAX/w, MAX/h); w = Math.floor(w*r); h = Math.floor(h*r); }
            var canvas = document.getElementById('countCanvas');
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);

            if (countOriginalImage && countOriginalImage.delete) countOriginalImage.delete();
            if (typeof cv !== 'undefined' && cvReady) {
                countOriginalImage = cv.imread(canvas);
            }

            countDetections = []; countManualAdds = []; lastClickX = 0; lastClickY = 0;
            $('#countBadge').hide(); $('#countSensitivityArea').hide();
            $('#countCapturePrompt').hide();
            $('#countCanvasArea').show();
        };
        img.src = dataUrl;
    }

    function redrawCanvas() {
        if (!countOriginalImage) return;
        var canvas = document.getElementById('countCanvas');
        if (typeof cv !== 'undefined' && cvReady) {
            cv.imshow(canvas, countOriginalImage);
        }
        var ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#00FF00'; ctx.lineWidth = 2;
        countDetections.forEach(function(d, i) {
            ctx.strokeRect(d.x, d.y, d.w, d.h);
            ctx.fillStyle = 'rgba(0,255,0,0.15)'; ctx.fillRect(d.x, d.y, d.w, d.h);
            ctx.fillStyle = '#00FF00'; ctx.font = 'bold 12px Arial'; ctx.fillText(i+1, d.x+2, d.y+12);
        });
        ctx.fillStyle = 'rgba(255,165,0,0.7)'; ctx.strokeStyle = '#FFA500';
        countManualAdds.forEach(function(p) {
            ctx.beginPath(); ctx.arc(p.x, p.y, 12, 0, Math.PI*2); ctx.stroke(); ctx.fill();
            ctx.fillStyle = '#fff'; ctx.font = 'bold 10px Arial'; ctx.fillText('+', p.x-4, p.y+4);
            ctx.fillStyle = 'rgba(255,165,0,0.7)';
        });
        var total = countDetections.length + countManualAdds.length;
        $('#countBadge').text(total).show();
        finalCount = total;
        if (total > 0) {
            $('#Qty_Record').val(total);
            checkFormReady();
        }
    }

    function runFallbackMatching(clickX, clickY) {
        if (!countOriginalImage || !cvReady) return;
        $('#countProcessing').show(); $('#countProcessingText').text('Counting...');
        setTimeout(function() {
            try {
                var src = countOriginalImage;
                var gray = new cv.Mat();
                cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
                var half = 40;
                var tx = Math.max(0, Math.floor(clickX - half));
                var ty = Math.max(0, Math.floor(clickY - half));
                var tw = Math.min(half*2, gray.cols - tx);
                var th = Math.min(half*2, gray.rows - ty);
                if (tw < 20 || th < 20) { $('#countProcessing').hide(); gray.delete(); return; }
                var tmpl = gray.roi(new cv.Rect(tx, ty, tw, th));
                var thr = parseInt($('#countThreshold').val()) / 100;
                var boxes = [];
                var res = new cv.Mat();
                cv.matchTemplate(gray, tmpl, res, cv.TM_CCOEFF_NORMED);
                for (var r=0; r<res.rows; r++) for (var c=0; c<res.cols; c++) {
                    var v = res.floatPtr(r,c)[0];
                    if (v >= thr) boxes.push({x:c, y:r, w:tmpl.cols, h:tmpl.rows, score:v});
                }
                res.delete(); tmpl.delete(); gray.delete();
                countDetections = nms(boxes, 0.3);
                redrawCanvas();
                $('#countProcessing').hide();
                $('#countSensitivityArea').show();
            } catch(e) { console.error(e); $('#countProcessing').hide(); alert('Count failed'); }
        }, 100);
    }

    function nms(boxes, overlap) {
        if (!boxes.length) return [];
        boxes.sort(function(a,b) { return b.score - a.score; });
        var result = [];
        while (boxes.length) {
            var best = boxes.shift();
            result.push(best);
            boxes = boxes.filter(function(b) {
                var x1 = Math.max(best.x, b.x);
                var y1 = Math.max(best.y, b.y);
                var x2 = Math.min(best.x + best.w, b.x + b.w);
                var y2 = Math.min(best.y + best.h, b.y + b.h);
                var inter = Math.max(0, x2 - x1) * Math.max(0, y2 - y1);
                var union = best.w * best.h + b.w * b.h - inter;
                return inter / union < overlap;
            });
        }
        return result;
    }

    $('#countCanvas').on('click', function(e) {
        if (!countOriginalImage) return;
        var canvas = this;
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width / rect.width;
        var scaleY = canvas.height / rect.height;
        var cx = (e.clientX - rect.left) * scaleX;
        var cy = (e.clientY - rect.top) * scaleY;

        for (var i = countDetections.length-1; i >= 0; i--) {
            var d = countDetections[i];
            if (Math.abs(cx - (d.x + d.w/2)) < d.w/2 && Math.abs(cy - (d.y + d.h/2)) < d.h/2) {
                countDetections.splice(i, 1);
                redrawCanvas();
                return;
            }
        }
        for (var i = countManualAdds.length-1; i >= 0; i--) {
            var p = countManualAdds[i];
            if (Math.abs(cx - p.x) < 15 && Math.abs(cy - p.y) < 15) {
                countManualAdds.splice(i, 1);
                redrawCanvas();
                return;
            }
        }
        if (countDetections.length === 0 && countManualAdds.length === 0) {
            lastClickX = cx; lastClickY = cy;
            runFallbackMatching(cx, cy);
        } else {
            countManualAdds.push({x: Math.round(cx), y: Math.round(cy)});
            redrawCanvas();
        }
    });

    $('#countThreshold').on('input', function() {
        $('#countThresholdLabel').text($(this).val() + '%');
    });
    $('#countThreshold').on('change', function() {
        if (lastClickX > 0 || lastClickY > 0) {
            countManualAdds = [];
            runFallbackMatching(lastClickX, lastClickY);
        }
    });

    $('#startCountCamera').on('click', startCountCamera);
    $('#closeCountCamera').on('click', stopCountCamera);
    $('#captureCountPhoto').on('click', capturePhoto);
    $('#countFileUpload').on('click', function() { $('#countPhotoInput').click(); });
    $('#countPhotoInput').on('change', function() {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) { loadCountImage(e.target.result); };
        reader.readAsDataURL(file);
    });

    $('#retakePhoto').on('click', async function() {
        if (countOriginalImage && countOriginalImage.delete) countOriginalImage.delete();
        countOriginalImage = null; countDetections = []; countManualAdds = []; finalCount = 0;
        $('#Qty_Record').val('');
        $('#countCanvasArea').hide();
        $('#countCapturePrompt').show();
        stopCountCamera();
    });

    $('#clearCount').on('click', function() {
        countDetections = []; countManualAdds = []; finalCount = 0; lastClickX = 0; lastClickY = 0;
        $('#Qty_Record').val('');
        if (countOriginalImage && typeof cv !== 'undefined' && cvReady) {
            redrawCanvas();
        }
        $('#countBadge').hide(); $('#countSensitivityArea').hide();
    });
    @endif

    function checkFormReady() {
        if ($('#Code_Rack').val() && $('#Qty_Record').val()) {
            $('#submitPartBtn').prop('disabled', false);
        }
    }

    $('#Qty_Record').on('input', function() {
        if ($(this).val()) checkFormReady();
    });

    $('#partForm').on('keypress', function(e) {
        if (e.which === 13) return false;
    });
</script>
@endsection
