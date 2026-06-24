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
        <div class="page-header">
            <div>
                <h4 class="page-title text-primary mb-0">Scan Part</h4>
                <small>Record: <strong class="text-primary">{{ $record->Sequence_No_Record }}</strong> | Area: <strong class="text-primary">{{ ucwords(str_replace('_', ' ', $record->Area)) }}</strong></small>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h1 class="text-center text-primary mb-0" style="font-size: 170px;"><strong>{{ $recordList->Code_Rack }}</strong></h1>
                <h5>{{ $recordList->Code_Part }} - {{ $recordList->Name_Part }}</h5>
                <p class="text-muted mb-0">Location: <strong class="text-primary">{{ $recordList->Location_Rack }}</strong> | Mode: <strong class="text-primary">{{ ucfirst($recordList->Mode) }}</strong> | Pembeda: <strong class="text-primary">{{ $recordList->Difference }}</strong></p>
                <p class="text-muted mb-0">Box: <strong class="text-primary">{{ $recordList->Box }}</strong> | Qty: <strong class="text-primary">{{ $recordList->Qty }}</strong></p>
            </div>
        </div>

        <form action="{{ route('member.record.update-part', $recordList->Id_Record_List) }}" method="POST" id="partForm">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Step 1: Scan Rack QR Code</h6>
                        </div>
                        <div class="card-body text-center">
                            <div id="reader_rack"></div>
                            <button type="button" id="scanRack" class="btn btn-primary mt-2" disabled><i class="fas fa-camera"></i> Scan Rack <span id="scanRackTimer" class="badge bg-light text-dark ms-1">7</span></button>
                            <button type="button" id="stopRackScan" class="btn btn-secondary mt-2" style="display:none;"><i class="fas fa-stop"></i> Stop</button>
                            <div class="mt-2">
                                <label class="form-label">Scanned Code Rack</label>
                                <input type="text" name="Code_Rack" id="Code_Rack" class="form-control" readonly required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="is_empty" name="Is_Empty" value="1">
                                <label class="form-check-label fw-bold" for="is_empty">Part Kosong</label>
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
                                <input type="number" name="Qty_Record" id="Qty_Record" class="form-control" min="0">
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

    var skipChars = { '-': true, '.': true, '_': true, '/': true, ',': true, ' ': true };
    var audioCache = {};
    var currentTimeout = null;
    var currentAudio = null;
    var loopTimeout = null;

    function getFastAudio(ch) {
        if (!audioCache[ch]) {
            var audio = new Audio('{{ asset("assets/sounds") }}/' + ch + '.mp3');
            audio.playbackRate = 2;
            audio.preload = 'auto';
            audioCache[ch] = audio;
        }
        return audioCache[ch];
    }

    function playCharSounds(chars, index, onComplete) {
        if (currentTimeout) { clearTimeout(currentTimeout); currentTimeout = null; }
        if (currentAudio) { currentAudio.pause(); currentAudio.currentTime = 0; }
        if (index >= chars.length) {
            if (onComplete) onComplete();
            return;
        }
        var ch = chars[index];
        if (skipChars[ch]) { playCharSounds(chars, index + 1, onComplete); return; }
        var audio = getFastAudio(ch);
        audio.currentTime = 0;
        currentAudio = audio;
        function handleNext() { playCharSounds(chars, index + 1, onComplete); }
        function startPlayback() {
            var duration = audio.duration;
            if (!duration || duration === Infinity || isNaN(duration)) {
                audio.onended = handleNext;
            } else {
                audio.onended = null;
                var stopTimeMs = ((duration * 0.7) / audio.playbackRate) * 1000;
                currentTimeout = setTimeout(function() {
                    audio.pause();
                    currentTimeout = null;
                    handleNext();
                }, stopTimeMs);
            }
            var playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise.catch(function(error) { console.log("Playback dicegah:", error); handleNext(); });
            }
        }
        if (audio.duration && audio.duration !== Infinity) { startPlayback(); }
        else { audio.onloadedmetadata = startPlayback; audio.onerror = handleNext; }
    }

    function stopAllSounds() {
        if (loopTimeout) { clearTimeout(loopTimeout); loopTimeout = null; }
        if (currentTimeout) { clearTimeout(currentTimeout); currentTimeout = null; }
        if (currentAudio) { currentAudio.pause(); currentAudio.currentTime = 0; currentAudio = null; }
    }

    var boksAudio = new Audio('{{ asset("assets/sounds") }}/boks.mp3');
    boksAudio.playbackRate = 2;
    boksAudio.preload = 'auto';

    var boxValue = '{{ $recordList->Box }}';
    var qtyValue = '{{ $recordList->Qty }}';

    function playSequence() {
        var codeRack = '{{ $recordList->Code_Rack }}'.toLowerCase();
        playCharSounds(codeRack.split(''), 0, function() {
            currentTimeout = setTimeout(function() {
                currentTimeout = null;
                if (currentAudio) { currentAudio.pause(); currentAudio.currentTime = 0; }
                currentAudio = boksAudio;
                boksAudio.currentTime = 0;
                function afterBoks() {
                    playCharSounds(boxValue.split(''), 0, function() {
                        currentTimeout = setTimeout(function() {
                            currentTimeout = null;
                            playCharSounds(qtyValue.toString().split(''), 0, function() {
                                loopTimeout = setTimeout(function() {
                                    loopTimeout = null;
                                    playSequence();
                                }, 500);
                            });
                        }, 500);
                    });
                }
                var playPromise = boksAudio.play();
                if (playPromise !== undefined) {
                    playPromise.then(function() {
                        var duration = boksAudio.duration;
                        if (!duration || duration === Infinity || isNaN(duration)) {
                            boksAudio.onended = afterBoks;
                        } else {
                            boksAudio.onended = null;
                            currentTimeout = setTimeout(function() {
                                boksAudio.pause();
                                currentTimeout = null;
                                afterBoks();
                            }, ((duration * 0.7) / boksAudio.playbackRate) * 1000);
                        }
                    }).catch(function(error) { console.log("Playback dicegah:", error); afterBoks(); });
                } else {
                    afterBoks();
                }
            }, 500);
        });
    }

    $(document).ready(function() {
        setTimeout(function() {
            playSequence();
        }, 3000);

        var seconds = 7;
        var timer = setInterval(function() {
            seconds--;
            if (seconds <= 0) {
                clearInterval(timer);
                $('#scanRack').prop('disabled', false);
                $('#scanRackTimer').text('');
            } else {
                $('#scanRackTimer').text(seconds);
            }
        }, 1000);
    });

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

    $('#is_empty').on('change', function() {
        if ($(this).is(':checked')) {
            if (window.currentMode === 'manual') {
                $('#Qty_Record').val(0);
            }
            $('#Qty_Record').prop('required', false);
            checkFormReady();
        } else {
            $('#Qty_Record').val('').prop('required', true);
            checkFormReady();
        }
    });

    window.checkFormReady = function() {
        if ($('#is_empty').is(':checked')) {
            $('#submitPartBtn').prop('disabled', $('#Code_Rack').val() ? false : true);
        } else {
            $('#submitPartBtn').prop('disabled', !($('#Code_Rack').val() && $('#Qty_Record').val()));
        }
    };

    $('#Qty_Record').on('input', function() {
        checkFormReady();
    });

    $('#partForm').on('keypress', function(e) {
        if (e.which === 13) return false;
    });
</script>
@endsection
