<?php $__env->startSection('style'); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <div>
                <h4 class="page-title text-primary mb-0">Scan Part</h4>
                <small>Record: <strong class="text-primary"><?php echo e($record->Sequence_No_Record); ?></strong> | Area: <strong class="text-primary"><?php echo e(ucwords(str_replace('_', ' ', $record->Area))); ?></strong></small>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h1 class="text-center text-primary mb-0 rack-big-text"><strong><?php echo e($recordList->Location_Rack); ?></strong></h1>
                <h5><?php echo e($recordList->Code_Part); ?> - <?php echo e($recordList->Name_Part); ?></h5>
                <p class="text-muted mb-0">Code Rack: <strong class="text-primary detail-rack"><?php echo e($recordList->Code_Rack); ?></strong> | Box: <strong class="text-primary detail-rack"><?php echo e($recordList->Box); ?></strong> | Qty: <strong class="text-primary detail-rack"><?php echo e($recordList->Qty); ?></strong></p>
                <p class="text-muted mb-0">Mode: <strong class="text-primary"><?php echo e(ucfirst($recordList->Mode)); ?></strong> | Pembeda: <strong class="text-primary"><?php echo e($recordList->Difference); ?></strong></p>
            </div>
        </div>

        <form action="<?php echo e(route('member.record.update-part', $recordList->Id_Record_List)); ?>" method="POST" id="partForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="Is_Empty" id="is_empty_flag" value="0">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Step 1: Scan Rack QR Code</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Scan Code Rack <span id="scanTimer" class="badge bg-light text-dark ms-1">3</span></label>
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
                    <?php if($isPunished): ?>
                        <?php if($recordList->Mode == 'manual'): ?>
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
                        <?php else: ?>
                        <div class="card mb-3 scan-locked" id="step2CardAI">
                            <div class="card-header">
                                <h6 class="mb-0">Step 2: AI Object Counting</h6>
                            </div>
                            <div class="card-body text-center">
                                <p class="text-muted">Take a photo and block on an item to count. Expected count: <strong><?php echo e($recordList->Qty); ?></strong></p>

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
                        <?php endif; ?>
                    <?php else: ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Step 2: Qty Otomatis</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2">Qty part ini akan diisi otomatis dari list saat scan rack selesai.</p>
                            <h3 class="text-primary mb-0"><strong><?php echo e($recordList->Qty); ?></strong></h3>
                            <p class="text-muted mb-0 mt-2 small">Setelah scan rack yang sesuai, part otomatis dilanjutkan ke berikutnya.</p>
                        </div>
                        <input type="hidden" name="Qty_Record" id="Qty_Record" value="<?php echo e($recordList->Qty); ?>">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if($isPunished): ?>
            <button type="submit" class="btn btn-primary w-100" id="submitPartBtn" disabled>
                <i class="fas fa-check"></i> Submit Record
            </button>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if(session('box_transition')): ?>
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
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<?php if($recordList->Mode == 'ai' && $isPunished): ?>
<script src="<?php echo e(asset('assets/js/plugin/opencv.js')); ?>" async onload="window.onOpenCvReady();"></script>
<script src="<?php echo e(asset('assets/js/plugin/record-scan-ai.js')); ?>"></script>
<?php endif; ?>
<script>
    window.cvReady = false;
    window.expectedQty = <?php echo e($recordList->Qty ?? 0); ?>;
    window.currentMode = <?php echo json_encode($recordList->Mode ?? 'manual', 15, 512) ?>;
    window.isPunished = <?php echo json_encode($isPunished ?? false, 15, 512) ?>;
    var expectedCodeRack = '<?php echo e($recordList->Code_Rack); ?>'.toUpperCase();
    var hasBoxTransition = <?php echo json_encode(session('box_transition') ? true : false, 15, 512) ?>;

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
            var soundSrc = window.SoundCache ? window.SoundCache.getUrl(theme, ch) : '<?php echo e(asset("assets/sounds")); ?>/' + theme + '/' + ch + '.mp3';
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
            var boksSrc = window.SoundCache ? window.SoundCache.getUrl(theme, 'boks') : '<?php echo e(asset("assets/sounds")); ?>/' + theme + '/boks.mp3';
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

    var locationValue = '<?php echo e($recordList->Location_Rack); ?>';
    var boxValue = '<?php echo e($recordList->Box); ?>';
    var qtyValue = '<?php echo e($recordList->Qty); ?>';

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

        var scanDelay = 3;
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
                    stopAllSounds();
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
        stopAllSounds();
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\iseki_marshalling\resources\views/member/record/record-scan.blade.php ENDPATH**/ ?>