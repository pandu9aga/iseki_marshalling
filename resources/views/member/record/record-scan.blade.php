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
    #cameraScannerContainer {
        border-radius: 10px;
        overflow: hidden;
        background: #000;
        position: relative;
    }
    #cameraReader {
        position: relative;
        cursor: pointer;
    }
    #cameraReader video {
        width: 100% !important;
        height: auto !important;
        border-radius: 8px;
        cursor: pointer;
    }
    .camera-focus-ring {
        position: absolute;
        width: 60px;
        height: 60px;
        border: 2px solid #20c997;
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(32, 201, 151, 0.7);
        pointer-events: none;
        transform: translate(-50%, -50%) scale(1.4);
        opacity: 1;
        transition: transform 0.25s ease-out, opacity 0.4s ease-out;
        z-index: 9999;
    }
    .camera-focus-ring.active {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.9;
    }
    .camera-focus-ring.fade-out {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.85);
    }
    .scan-mode-btn.active {
        background-color: #F36494 !important;
        color: #fff !important;
        border-color: #F36494 !important;
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
                <p class="text-muted mb-0">Code: <strong class="text-primary detail-rack">{{ $recordList->Code_Rack }}</strong> | Box: <strong class="text-primary detail-rack">{{ $recordList->Box }}</strong> | Qty: <strong class="text-primary detail-rack">{{ $recordList->Qty }}</strong></p>
                <p class="text-muted mb-0">Mode: <strong class="text-primary">{{ ucfirst($recordList->Mode) }}</strong> | Pembeda: <strong class="text-primary">{{ $recordList->Difference }}</strong></p>
            </div>
        </div>

        <form action="{{ route('member.record.update-part', $recordList->Id_Record_List) }}" method="POST" id="partForm">
            @csrf
            <input type="hidden" name="Is_Empty" id="is_empty_flag" value="0">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0">Step 1: Scan Rack QR Code</h6>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary scan-mode-btn active" id="btnModeUsb">
                                    <i class="fas fa-barcode me-1"></i>Scanner
                                </button>
                                <button type="button" class="btn btn-outline-primary scan-mode-btn" id="btnModeCamera">
                                    <i class="fas fa-camera me-1"></i>Kamera
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Mode Scanner USB / Keyboard -->
                            <div id="usbScannerBox">
                                <div class="mb-3">
                                    <label class="form-label">Scan Code Rack <span id="scanTimer" class="badge bg-success text-white ms-1">Ready</span></label>
                                    <input type="text" id="scannerRackInput" class="form-control" placeholder="Scan Code Rack dengan USB scanner..." style="text-transform: uppercase;">
                                </div>
                            </div>

                            <!-- Mode Scanner Kamera HP (html5-qrcode) -->
                            <div id="cameraScannerBox" style="display: none;" class="mb-3">
                                <div id="cameraScannerContainer" class="p-2 text-center">
                                    <div id="cameraReader" style="width: 100%; min-height: 220px;"></div>
                                    <div class="mt-2 d-flex justify-content-between align-items-center flex-wrap gap-1">
                                        <small class="text-white"><i class="fas fa-hand-pointer me-1 text-warning"></i>Ketuk preview kamera untuk fokus</small>
                                        <button type="button" class="btn btn-danger btn-sm" id="btnStopCamera">
                                            <i class="fas fa-stop me-1"></i>Tutup Kamera
                                        </button>
                                    </div>
                                </div>
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
<script src="{{ asset('assets/js/plugin/html5-qrcode.min.js') }}"></script>
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

    // Web Audio Context untuk manipulasi karakter suara (lebih melengking & anti copyright) murni tanpa CDN
    var _audioCtx = null;
    function getAudioContext() {
        if (!_audioCtx) {
            var AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                _audioCtx = new AudioContextClass();
            }
        }
        if (_audioCtx && _audioCtx.state === 'suspended') {
            _audioCtx.resume().catch(function() {});
        }
        return _audioCtx;
    }

    function setupAudioElement(audio, speed, shouldAdjustPitch) {
        if (!audio) return;
        audio.playbackRate = speed || 1.0;
        if (shouldAdjustPitch) {
            audio.preservesPitch = false;
            if ('mozPreservesPitch' in audio) audio.mozPreservesPitch = false;
            if ('webkitPreservesPitch' in audio) audio.webkitPreservesPitch = false;
        } else {
            // Normal (folder a tidak perlu di-adjust)
            audio.preservesPitch = true;
            if ('mozPreservesPitch' in audio) audio.mozPreservesPitch = true;
            if ('webkitPreservesPitch' in audio) audio.webkitPreservesPitch = true;
        }
    }

    function getFastAudio(ch, theme, speed) {
        theme = theme || 'a';
        var cacheKey = theme + '_' + ch;
        if (!audioCache[cacheKey]) {
            var soundSrc = window.SoundCache ? window.SoundCache.getUrl(theme, ch) : '{{ asset("assets/sounds") }}/' + theme + '/' + ch + '.mp3';
            var audio = new Audio(soundSrc);
            audio.preload = 'auto';
            audioCache[cacheKey] = audio;
        }
        var a = audioCache[cacheKey];
        // Jika folder 'a', suara normal tanpa adjust pitch
        var adjust = (theme !== 'a');
        setupAudioElement(a, speed || 1.0, adjust);
        return a;
    }

    function getBoksAudio(theme, speed) {
        theme = theme || 'b';
        speed = speed || 1.0;
        var cacheKey = theme + '_boks';
        if (!audioCache[cacheKey]) {
            var boksSrc = window.SoundCache ? window.SoundCache.getUrl(theme, 'boks') : '{{ asset("assets/sounds") }}/' + theme + '/boks.mp3';
            var boksAudio = new Audio(boksSrc);
            boksAudio.preload = 'auto';
            audioCache[cacheKey] = boksAudio;
        }
        var b = audioCache[cacheKey];
        var adjust = (theme !== 'a');
        setupAudioElement(b, speed, adjust);
        return b;
    }

    var isAutoplayBlocked = false;

    function handleAutoplayBlocked() {
        if (isAutoplayBlocked) return;
        isAutoplayBlocked = true;
        console.warn("Autoplay audio diblokir browser, menunggu interaksi pengguna...");
        
        // Pasang handler global sekali di document (klik, ketik, tap) untuk membuka audio
        var unlockAudio = function() {
            $(document).off('click.audioUnlock keydown.audioUnlock touchstart.audioUnlock');
            isAutoplayBlocked = false;
            stopAllSounds();
            playSequence();
        };

        $(document).on('click.audioUnlock keydown.audioUnlock touchstart.audioUnlock', unlockAudio);
    }

    function playCharSounds(chars, index, theme, speed, onComplete) {
        if (typeof speed === 'function') {
            onComplete = speed;
            speed = 1.0;
        }
        if (typeof theme === 'function') {
            onComplete = theme;
            theme = 'a';
            speed = 1.0;
        }
        theme = theme || 'a';
        speed = speed || 1.0;

        if (currentTimeout) { clearTimeout(currentTimeout); currentTimeout = null; }
        if (currentAudio) { currentAudio.pause(); currentAudio.currentTime = 0; }
        if (index >= chars.length) {
            if (onComplete) onComplete();
            return;
        }
        var ch = chars[index];
        if (skipChars[ch]) { playCharSounds(chars, index + 1, theme, speed, onComplete); return; }
        var audio = getFastAudio(ch, theme, speed);
        audio.currentTime = 0;
        currentAudio = audio;
        function handleNext() { playCharSounds(chars, index + 1, theme, speed, onComplete); }
        function startPlayback() {
            var playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise.then(function() {
                    isAutoplayBlocked = false;
                    var duration = audio.duration;
                    if (!duration || duration === Infinity || isNaN(duration)) {
                        audio.onended = handleNext;
                    } else {
                        audio.onended = null;
                        var factor = (theme === 'b') ? 0.72 : 0.70;
                        var stopTimeMs = ((duration * factor) / audio.playbackRate) * 1000;
                        currentTimeout = setTimeout(function() {
                            audio.pause();
                            currentTimeout = null;
                            handleNext();
                        }, stopTimeMs);
                    }
                }).catch(function(error) {
                    console.log("Playback dicegah:", error);
                    handleAutoplayBlocked();
                });
            } else {
                var duration = audio.duration;
                if (!duration || duration === Infinity || isNaN(duration)) {
                    audio.onended = handleNext;
                } else {
                    audio.onended = null;
                    var factor = (theme === 'b') ? 0.72 : 0.70;
                    var stopTimeMs = ((duration * factor) / audio.playbackRate) * 1000;
                    currentTimeout = setTimeout(function() {
                        audio.pause();
                        currentTimeout = null;
                        handleNext();
                    }, stopTimeMs);
                }
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

    /**
     * Konversi bilangan bulat (0 - 999999) menjadi urutan token audio bahasa Indonesia
     * Contoh: 103 -> ['100', '3'], 12 -> ['2', 'belas'], 25 -> ['2', 'puluh', '5'], 115 -> ['100', '5', 'belas']
     */
    function numberToIndonesianTokens(n) {
        n = parseInt(n, 10);
        if (isNaN(n)) return [];
        if (n === 0) return ['0'];

        var tokens = [];

        function convertUnder1000(num) {
            var res = [];
            if (num >= 100) {
                var hundreds = Math.floor(num / 100);
                if (hundreds === 1) {
                    res.push('100'); // 'seratus'
                } else {
                    res.push(hundreds.toString());
                    res.push('ratus');
                }
                num %= 100;
            }

            if (num >= 20) {
                var tens = Math.floor(num / 10);
                res.push(tens.toString());
                res.push('puluh');
                num %= 10;
                if (num > 0) {
                    res.push(num.toString());
                }
            } else if (num === 11) {
                res.push('11'); // 'sebelas'
            } else if (num === 10) {
                res.push('10'); // 'sepuluh'
            } else if (num >= 12 && num <= 19) {
                var digit = num % 10;
                res.push(digit.toString());
                res.push('belas');
            } else if (num > 0) {
                res.push(num.toString());
            }

            return res;
        }

        if (n >= 1000) {
            var thousands = Math.floor(n / 1000);
            if (thousands === 1) {
                tokens.push('1000'); // 'seribu'
            } else {
                tokens = tokens.concat(convertUnder1000(thousands));
                tokens.push('ribu');
            }
            n %= 1000;
        }

        if (n > 0) {
            tokens = tokens.concat(convertUnder1000(n));
        }

        return tokens;
    }

    /**
     * Mem-parse string Location menjadi token audio:
     * - Huruf dieja satu per satu (misal 'A' -> 'a')
     * - Angka / blok angka dibaca dengan kaidah bilangan Indonesia (misal '103' -> ['100', '3'])
     */
    function parseLocationToTokens(str) {
        if (!str) return [];
        var parts = str.toString().match(/[a-zA-Z]+|[0-9]+/g);
        if (!parts) return [];
        var tokens = [];
        parts.forEach(function(part) {
            if (/^\d+$/.test(part)) {
                tokens = tokens.concat(numberToIndonesianTokens(part));
            } else {
                tokens = tokens.concat(part.toLowerCase().split(''));
            }
        });
        return tokens;
    }

    function parseQtyToTokens(qty) {
        return numberToIndonesianTokens(qty);
    }

    var locationValue = '{{ $recordList->Location_Rack }}';
    var boxValue = '{{ $recordList->Box }}';
    var qtyValue = '{{ $recordList->Qty }}';

    function playSequence() {
        // 1. Bunyikan Location Rack (sound b, speed 1.08 melengking halus)
        var locationTokens = parseLocationToTokens(locationValue);
        playCharSounds(locationTokens, 0, 'b', 1.3, function() {
            loopTimeout = setTimeout(function() {
                // 2. Bunyikan Qty (sound a, normal tanpa adjust)
                var qtyTokens = parseQtyToTokens(qtyValue);
                playCharSounds(qtyTokens, 0, 'a', 1.0, function() {
                    loopTimeout = setTimeout(function() {
                        // 3. Bunyikan Box (sound b): diawali 'boks' lalu kode box
                        function afterBoks() {
                            loopTimeout = setTimeout(function() {
                                playCharSounds(boxValue.toLowerCase().split(''), 0, 'b', 1.08, function() {
                                    // Repeat loop pemutaran suara kembali ke awal setelah jeda 500ms
                                    loopTimeout = setTimeout(function() {
                                        playSequence();
                                    }, 500);
                                });
                            }, 100);
                        }

                        var boksAudio = getBoksAudio('b', 1.08);
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
                                    }, ((duration * 0.9) / boksAudio.playbackRate) * 1000);
                                }
                            }).catch(function(error) {
                                console.log("Playback boks dicegah:", error);
                                handleAutoplayBlocked();
                            });
                        } else {
                            afterBoks();
                        }
                    }, 200);
                });
            }, 200);
        });
    }

    function startScanCountdown() {
        playSequence();

        var timerEl = $('#scanTimer');
        timerEl.text('Ready').removeClass('bg-light text-dark').addClass('bg-success text-white');
        $('#scannerRackInput').prop('disabled', false).focus();
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

    function handleRackScan(text) {
        text = $.trim(text).toUpperCase();
        if (!text) return;

        $('#Code_Rack').val(text).removeClass('is-valid is-invalid');
        if (text === expectedCodeRack) {
            stopAllSounds();
            stopCameraScanner();
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
            if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
        }

        if (window.isPunished) {
            checkFormReady();
        }
    }

    $('#scannerRackInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var text = $(this).val();
            $(this).val('');
            handleRackScan(text);
        }
    });

    // Scanner Kamera HP (html5-qrcode) Handler
    var html5QrCode = null;
    var isCameraScanning = false;

    function startCameraScanner() {
        if (isCameraScanning) return;

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("cameraReader");
        }

        var config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            function(decodedText, decodedResult) {
                if (decodedText) {
                    if (navigator.vibrate) navigator.vibrate(100);
                    handleRackScan(decodedText);
                }
            },
            function(errorMessage) {
                // scanning frame... abaikan
            }
        ).then(function() {
            isCameraScanning = true;
        }).catch(function(err) {
            console.error("Gagal membuka kamera:", err);
            Swal.fire({
                icon: 'error',
                title: 'Kamera Tidak Dapat Dibuka',
                text: 'Pastikan izin kamera telah diberikan pada browser atau gunakan Scanner USB.',
                confirmButtonColor: '#F36494'
            });
            $('#btnModeUsb').trigger('click');
        });
    }

    // Trigger Auto Focus saat preview kamera diklik / disentuh
    function triggerCameraAutoFocus(containerSelector, event) {
        var $container = $(containerSelector);
        if (!$container.length) return;

        // 1. Tampilkan animasi focus ring di titik klik/sentuh
        var offset = $container.offset();
        var clickX = (event.pageX || (event.originalEvent && event.originalEvent.touches && event.originalEvent.touches[0].pageX)) - offset.left;
        var clickY = (event.pageY || (event.originalEvent && event.originalEvent.touches && event.originalEvent.touches[0].pageY)) - offset.top;

        if (isNaN(clickX) || isNaN(clickY) || clickX <= 0 || clickY <= 0) {
            clickX = $container.width() / 2;
            clickY = $container.height() / 2;
        }

        var $ring = $('<div class="camera-focus-ring"></div>');
        $ring.css({ left: clickX + 'px', top: clickY + 'px' });
        $container.append($ring);

        setTimeout(function() {
            $ring.addClass('active');
        }, 10);

        setTimeout(function() {
            $ring.addClass('fade-out');
            setTimeout(function() {
                $ring.remove();
            }, 400);
        }, 600);

        // 2. Dapatkan MediaStreamTrack dari video element
        var videoEl = $container.find('video')[0];
        if (!videoEl || !videoEl.srcObject) return;

        var stream = videoEl.srcObject;
        var tracks = stream.getVideoTracks();
        if (!tracks || !tracks.length) return;

        var track = tracks[0];
        if (!track.getCapabilities || !track.applyConstraints) return;

        try {
            var capabilities = track.getCapabilities();
            if (capabilities.focusMode) {
                // Jika browser mendukung focusMode continuous / single-shot
                track.applyConstraints({
                    advanced: [{ focusMode: "continuous" }]
                }).catch(function() {
                    // Fallback coba focusMode manual lalu continuous
                    track.applyConstraints({
                        advanced: [{ focusMode: "manual" }]
                    }).then(function() {
                        setTimeout(function() {
                            track.applyConstraints({
                                advanced: [{ focusMode: "continuous" }]
                            }).catch(function() {});
                        }, 100);
                    }).catch(function() {});
                });
            }

            // Jika browser mendukung pointsOfInterest (tap-to-focus point)
            if (capabilities.pointsOfInterest) {
                var normX = Math.min(1, Math.max(0, clickX / $container.width()));
                var normY = Math.min(1, Math.max(0, clickY / $container.height()));
                track.applyConstraints({
                    advanced: [{
                        pointsOfInterest: [{ x: normX, y: normY }]
                    }]
                }).catch(function() {});
            }
        } catch (e) {
            console.log("Autofocus apply constraints info:", e);
        }
    }

    // Pasang listener tap/click pada kamera reader
    $('#cameraReader').on('click', function(e) {
        triggerCameraAutoFocus(this, e);
    });

    // Fungsi pembersihan & pelepasan hardware kamera secara tuntas
    function forceReleaseCamera(containerSelector) {
        var $video = $(containerSelector).find('video');
        if ($video.length) {
            $video.each(function() {
                if (this.srcObject) {
                    var stream = this.srcObject;
                    var tracks = stream.getTracks();
                    tracks.forEach(function(track) {
                        try { track.stop(); } catch(e) {}
                    });
                    this.srcObject = null;
                }
            });
        }
    }

    function stopCameraScanner() {
        if (html5QrCode && isCameraScanning) {
            html5QrCode.stop().then(function() {
                isCameraScanning = false;
                forceReleaseCamera('#cameraReader');
            }).catch(function(err) {
                console.warn("Gagal stop kamera:", err);
                isCameraScanning = false;
                forceReleaseCamera('#cameraReader');
            });
        } else {
            forceReleaseCamera('#cameraReader');
        }
    }

    // Tutup semua scanner kamera aktif dan kembalikan UI ke mode USB
    function closeCameraToUsbMode() {
        if (isCameraScanning) {
            stopCameraScanner();
            $('.scan-mode-btn').removeClass('active');
            $('#btnModeUsb').addClass('active');
            $('#cameraScannerBox').hide();
            $('#usbScannerBox').show();
        }
        // Jika kamera AI counting sedang terbuka, tutup juga
        if (typeof stopCountCamera === 'function') {
            try { stopCountCamera(); } catch(e) {}
        }
    }

    // Auto close kamera saat pindah tab, minimize browser, atau keluar browser
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            closeCameraToUsbMode();
        }
    });

    window.addEventListener('pagehide', function() {
        closeCameraToUsbMode();
    });

    window.addEventListener('beforeunload', function() {
        closeCameraToUsbMode();
    });

    // Toggle Mode Scan USB vs Kamera HP
    $('#btnModeUsb').on('click', function() {
        $('.scan-mode-btn').removeClass('active');
        $(this).addClass('active');
        stopCameraScanner();
        $('#cameraScannerBox').slideUp(200);
        $('#usbScannerBox').slideDown(200, function() {
            $('#scannerRackInput').focus();
        });
    });

    $('#btnModeCamera').on('click', function() {
        $('.scan-mode-btn').removeClass('active');
        $(this).addClass('active');
        $('#usbScannerBox').slideUp(200);
        $('#cameraScannerBox').slideDown(200, function() {
            startCameraScanner();
        });
    });

    $('#btnStopCamera').on('click', function() {
        $('#btnModeUsb').trigger('click');
    });

    $('#btnPartKosong').on('click', function() {
        stopAllSounds();
        stopCameraScanner();
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

    $('#submitPartBtn').on('click', function() {
        stopAllSounds();
    });

    $('#Qty_Record').on('input', function() {
        if (window.isPunished) {
            checkFormReady();
        }
    });

    $('#partForm').on('keypress', function(e) {
        if (e.which === 13) return false;
    });

    $('#partForm').on('submit', function() {
        stopAllSounds();
    });
</script>
@endsection