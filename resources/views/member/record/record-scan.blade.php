@extends('layouts.main')

@section('style')
<style>
    .scan-locked { opacity: 0.5; pointer-events: none; }
    .form-control[readonly].is-valid { border-color: #28a745; box-shadow: 0 0 0 0.2rem rgba(40,167,69,.25); }
    .form-control[readonly].is-invalid { border-color: #dc3545; box-shadow: 0 0 0 0.2rem rgba(220,53,69,.25); }
    .count-canvas-wrapper { position: relative; display: inline-block; max-width: 100%; }
    .rack-big-text { font-size: 170px; }
    @media (max-width: 767px) {
        .rack-big-text { font-size: 90px; }
    }
    @media (max-width: 480px) {
        .rack-big-text { font-size: 64px; }
    }
    .detail-rack {
        font-size: 24px;
    }
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
                <h1 class="text-center text-primary mb-0 rack-big-text"><strong>{{ $recordList->Location_Rack }}</strong></h1>
                <h5>{{ $recordList->Code_Part }} - {{ $recordList->Name_Part }}</h5>
                <p class="text-muted mb-0">No Rack: <strong class="text-primary detail-rack">{{ $recordList->Code_Rack }}</strong> | Qty: <strong class="text-primary detail-rack">{{ $recordList->Qty }}</strong> | Box: <strong class="text-primary detail-rack">{{ $recordList->Box }}</strong></p>
                <p class="text-muted mb-0">Mode: <strong class="text-primary">{{ ucfirst($recordList->Mode) }}</strong> | Pembeda: <strong class="text-primary">{{ $recordList->Difference }}</strong></p>
            </div>
        </div>

        <form action="{{ route('member.record.update-part', $recordList->Id_Record_List) }}" method="POST" id="partForm">
            @csrf
            <input type="hidden" name="Is_Empty" id="is_empty_flag" value="0">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Step 1: Scan Rack QR Code</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Scan Code Rack <span id="scanTimer" class="badge bg-light text-dark ms-1">7</span></label>
                                <input type="text" id="scannerRackInput" class="form-control" placeholder="Scan Code Rack dengan USB scanner..." disabled style="text-transform: uppercase;">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Scanned Code Rack</label>
                                <input type="text" name="Code_Rack" id="Code_Rack" class="form-control" readonly required>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <button type="button" id="btnPartKosong" class="btn btn-outline-danger w-100">
                                <i class="fas fa-box-open"></i> Part Kosong
                            </button>
                            <p class="text-muted mb-0 mt-2 small">Klik jika part ini kosong. Part akan langsung dilaporkan dan dilanjutkan ke part berikutnya.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @if($isPunished)
                        @if($recordList->Mode == 'manual')
                        <div class="card mb-3 scan-locked" id="step2Card">
                            <div class="card-header">
                                <h6 class="mb-0">Step 2: Input Qty (Manual)</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Qty Record</label>
                                    <input type="number" name="Qty_Record" id="Qty_Record" class="form-control" min="0" disabled>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="card mb-3 scan-locked" id="step2CardAI">
                            <div class="card-header">
                                <h6 class="mb-0">Step 2: AI Object Counting</h6>
                            </div>
                            <div class="card-body text-center">
                                <p class="text-muted">Take a photo and block on an item to count. Expected count: <strong>{{ $recordList->Qty }}</strong></p>

                                <div id="countCapturePrompt">
                                    <button type="button" id="startCountCamera" class="btn btn-primary" disabled><i class="fas fa-camera"></i> Open Camera</button>
                                    <br><small>or</small><br>
                                    <button type="button" id="countFileUpload" class="btn btn-outline-primary" disabled><i class="fas fa-upload"></i> Upload Photo</button>
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
                    @else
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Step 2: Qty Otomatis</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2">Qty part ini akan diisi otomatis dari list saat scan rack selesai.</p>
                            <h3 class="text-primary mb-0"><strong>{{ $recordList->Qty }}</strong></h3>
                            <p class="text-muted mb-0 mt-2 small">Setelah scan rack yang sesuai, part otomatis dilanjutkan ke berikutnya.</p>
                        </div>
                        <input type="hidden" name="Qty_Record" id="Qty_Record" value="{{ $recordList->Qty }}">
                    </div>
                    @endif
                </div>
            </div>
            @if($isPunished)
            <button type="submit" class="btn btn-primary w-100" id="submitPartBtn" disabled>
                <i class="fas fa-check"></i> Submit Record
            </button>
            @endif
        </form>
    </div>
</div>

@if(session('box_transition'))
<div class="modal fade" id="boxTransitionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered text-center">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-body py-5 px-4">
                <div class="mb-3">
                    <i class="fas fa-boxes text-warning" style="font-size: 64px;"></i>
                </div>
                <h2 class="fw-bold text-dark mb-2">Silahkan berganti box</h2>
                <p class="text-muted mb-4">Mempersiapkan box part berikutnya dalam:</p>
                <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle shadow-sm mb-3" style="width: 100px; height: 100px;">
                    <span class="display-4 fw-bold text-primary" id="boxCountdown">30</span>
                </div>
                <div>
                    <small class="text-muted">detik tersisa</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('script')
@if($recordList->Mode == 'ai' && $isPunished)
<script src="{{ asset('assets/js/plugin/opencv.js') }}" async onload="window.onOpenCvReady();"></script>
<script src="{{ asset('assets/js/plugin/record-scan-ai.js') }}"></script>
@endif
<script>
    window.cvReady = false;
    window.expectedQty = {{ $recordList->Qty ?? 0 }};
    window.currentMode = @json($recordList->Mode ?? 'manual');
    window.isPunished = @json($isPunished ?? false);
    var expectedCodeRack = '{{ $recordList->Code_Rack }}'.toUpperCase();
    var hasBoxTransition = @json(session('box_transition') ? true : false);

    window.onOpenCvReady = function() { window.cvReady = true; };

    var skipChars = { '-': true, '.': true, '_': true, '/': true, ',': true, ' ': true };
    var audioCache = {};
    var currentTimeout = null;
    var currentAudio = null;
    var loopTimeout = null;

    function getFastAudio(ch, theme) {
        theme = theme || 'a';
        var cacheKey = theme + '_' + ch;
        if (!audioCache[cacheKey]) {
            var audio = new Audio('{{ asset("assets/sounds") }}/' + theme + '/' + ch + '.mp3');
            audio.playbackRate = 2;
            audio.preload = 'auto';
            audioCache[cacheKey] = audio;
        }
        return audioCache[cacheKey];
    }

    function playCharSounds(chars, index, theme, onComplete) {
        if (typeof theme === 'function') {
            onComplete = theme;
            theme = 'a';
        }
        theme = theme || 'a';

        if (currentTimeout) { clearTimeout(currentTimeout); currentTimeout = null; }
        if (currentAudio) { currentAudio.pause(); currentAudio.currentTime = 0; }
        if (index >= chars.length) {
            if (onComplete) onComplete();
            return;
        }
        var ch = chars[index];
        if (skipChars[ch]) { playCharSounds(chars, index + 1, theme, onComplete); return; }
        var audio = getFastAudio(ch, theme);
        audio.currentTime = 0;
        currentAudio = audio;
        function handleNext() { playCharSounds(chars, index + 1, theme, onComplete); }
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

    // Suara boks menggunakan asset di folder 'a'
    var boksAudio = new Audio('{{ asset("assets/sounds/a/boks.mp3") }}');
    boksAudio.playbackRate = 2;
    boksAudio.preload = 'auto';

    var locationValue = '{{ $recordList->Location_Rack }}';
    var boxValue = '{{ $recordList->Box }}';
    var qtyValue = '{{ $recordList->Qty }}';

    function playSequence() {
        // 1. Bunyikan Location Rack (suara folder b)
        playCharSounds(locationValue.toLowerCase().split(''), 0, 'b', function() {
            loopTimeout = setTimeout(function() {
                function afterBoks() {
                    loopTimeout = setTimeout(function() {
                        // 3. Bunyikan Data Box (suara folder a)
                        playCharSounds(boxValue.toLowerCase().split(''), 0, 'a', function() {
                            loopTimeout = setTimeout(function() {
                                // 4. Bunyikan Qty (suara folder b)
                                playCharSounds(qtyValue.toString().split(''), 0, 'b');
                            }, 300);
                        });
                    }, 300);
                }

                // 2. Bunyikan kata 'boks' (suara folder a)
                if (boksAudio) {
                    boksAudio.currentTime = 0;
                    boksAudio.play().then(function() {
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

    function startScanCountdown() {
        setTimeout(function() {
            playSequence();
        }, 3000);

        var scanDelay = 7;
        var timerEl = $('#scanTimer');
        var interval = setInterval(function() {
            scanDelay--;
            timerEl.text(scanDelay);
            if (scanDelay <= 0) {
                clearInterval(interval);
                timerEl.text('Ready').removeClass('bg-light text-dark').addClass('bg-success text-white');
                $('#scannerRackInput').prop('disabled', false).focus();
            }
        }, 1000);
    }

    $(document).ready(function() {
        if (hasBoxTransition) {
            var modalEl = new bootstrap.Modal(document.getElementById('boxTransitionModal'));
            modalEl.show();

            var timeLeft = 30;
            var countdownEl = $('#boxCountdown');
            var transitionInterval = setInterval(function() {
                timeLeft--;
                countdownEl.text(timeLeft);
                if (timeLeft <= 0) {
                    clearInterval(transitionInterval);
                    modalEl.hide();
                    startScanCountdown();
                }
            }, 1000);
        } else {
            startScanCountdown();
        }
    });

    $('#scannerRackInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var text = $(this).val().toUpperCase();
            if (text) {
                $('#Code_Rack').val(text).removeClass('is-valid is-invalid');
                $(this).val('');
                if (text === expectedCodeRack) {
                    $('#Code_Rack').addClass('is-valid');
                    if (window.isPunished) {
                        $('#Qty_Record').prop('disabled', false);
                        $('#step2Card, #step2CardAI').removeClass('scan-locked');
                        $('#startCountCamera, #countFileUpload').prop('disabled', false);
                        $('#Qty_Record').focus();
                    } else {
                        $('#Qty_Record').val(window.expectedQty);
                        setTimeout(function() {
                            $('#partForm').submit();
                        }, 300);
                    }
                } else {
                    $('#Code_Rack').addClass('is-invalid');
                }
                if (window.isPunished) {
                    checkFormReady();
                }
            }
        }
    });

    $('#btnPartKosong').on('click', function() {
        $('#is_empty_flag').val('1');
        $('#Qty_Record').val(0);
        $('#partForm').submit();
    });

    window.checkFormReady = function() {
        if (!$('#Code_Rack').val()) {
            $('#submitPartBtn').prop('disabled', true);
            return;
        }
        $('#submitPartBtn').prop('disabled', !$('#Qty_Record').val());
    };

    $('#Qty_Record').on('input', function() {
        if (window.isPunished) {
            checkFormReady();
        }
    });

    $('#partForm').on('keypress', function(e) {
        if (e.which === 13) return false;
    });
</script>
@endsection