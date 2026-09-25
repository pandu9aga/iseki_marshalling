@extends('layouts.main')

@section('style')
<style>
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
            <h4 class="page-title text-primary">Scan Record</h4>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="card-title mb-0">Scan QR Code</h5>
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
                        <form action="{{ route('member.record.store') }}" method="POST" id="recordForm">
                            @csrf
                            <!-- Mode Scanner USB / Keyboard -->
                            <div id="usbScannerBox" class="mb-3">
                                <label class="form-label">Scan QR dari label produksi</label>
                                <input type="text" id="scannerInput" class="form-control" placeholder="Scan QR Code dengan USB scanner..." autofocus style="text-transform: uppercase;">
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

<div class="modal fade" id="areaAlertModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Perhatian</h5>
            </div>
            <div class="modal-body">
                <p id="areaAlertMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

@if($duplicateKanban)
<div class="modal fade" id="duplicateKanbanModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Kanban Telah Di-Scan</h5>
            </div>
            <div class="modal-body">
                <p class="mb-2">Kanban dengan Sequence No <strong>{{ $duplicateKanban }}</strong> sudah pernah discan. Proses record dibatalkan.</p>
                @if($existingRecord && $existingMember)
                <hr>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Discan oleh:</div>
                    <div class="col-8">{{ $existingMember['nama'] }} (NIK: {{ $existingMember['nik'] }})</div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Tanggal Scan:</div>
                    <div class="col-8">{{ $existingRecord['time_record'] ? date('d/m/Y H:i:s', strtotime($existingRecord['time_record'])) : '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Type:</div>
                    <div class="col-8">{{ $existingRecord['type'] }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Area:</div>
                    <div class="col-8">{{ ucwords(str_replace('_', ' ', $existingRecord['area'])) }}</div>
                </div>
                @if($matchedAreas && count($matchedAreas) > 0)
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Area Cocok:</div>
                    <div class="col-8">
                        <span class="badge bg-success">{{ implode(', ', array_map(fn($a) => ucwords(str_replace('_', ' ', $a)), $matchedAreas)) }}</span>
                    </div>
                </div>
                @endif
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('script')
<script src="{{ asset('assets/js/plugin/html5-qrcode.min.js') }}"></script>
<script>
    var html5QrCode = null;
    var isCameraScanning = false;
    var scanHandled = false; // one-shot guard agar callback tidak double-fire

    // --- USB / Keyboard Scanner ---
    $('#scannerInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            processScan($(this).val());
        }
    });

    // --- Camera Scanner (html5-qrcode) ---
    function startCameraScanner() {
        if (isCameraScanning) return;
        scanHandled = false;

        // Bersihkan DOM lama agar html5-qrcode tidak konflik
        $('#cameraReader').empty();
        html5QrCode = new Html5Qrcode("cameraReader");

        var config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            function(decodedText) {
                // Guard: hanya proses satu kali per sesi scan
                if (scanHandled) return;
                scanHandled = true;

                if (navigator.vibrate) navigator.vibrate(100);
                // Panggil processScan langsung — stop kamera dilakukan di dalamnya
                processScan(decodedText);
            },
            function(errorMessage) {
                // scanning frame... abaikan
            }
        ).then(function() {
            isCameraScanning = true;
        }).catch(function(err) {
            console.error("Gagal membuka kamera:", err);
            isCameraScanning = false;
            html5QrCode = null;
            Swal.fire({
                icon: 'error',
                title: 'Kamera Tidak Dapat Dibuka',
                text: 'Pastikan izin kamera telah diberikan pada browser atau gunakan Scanner USB.',
                confirmButtonColor: '#F36494'
            });
            switchToUsb();
        });
    }

    function stopCameraScanner() {
        if (html5QrCode && isCameraScanning) {
            html5QrCode.stop().then(function() {
                isCameraScanning = false;
                html5QrCode = null;
                forceReleaseCamera('#cameraReader');
            }).catch(function() {
                isCameraScanning = false;
                html5QrCode = null;
                forceReleaseCamera('#cameraReader');
            });
        } else {
            isCameraScanning = false;
            html5QrCode = null;
            forceReleaseCamera('#cameraReader');
        }
    }

    function forceReleaseCamera(containerSelector) {
        var $container = $(containerSelector);
        $container.find('video').each(function() {
            if (this.srcObject) {
                this.srcObject.getTracks().forEach(function(t) { try { t.stop(); } catch(e) {} });
                this.srcObject = null;
            }
        });
        $container.empty();
    }

    function switchToUsb() {
        $('.scan-mode-btn').removeClass('active');
        $('#btnModeUsb').addClass('active');
        $('#cameraScannerBox').slideUp(200);
        $('#usbScannerBox').slideDown(200, function() {
            $('#scannerInput').focus();
        });
    }

    // --- Auto Focus saat klik/sentuh layar kamera ---
    $('#cameraReader').on('click touchstart', function(e) {
        if (!isCameraScanning) return;
        triggerCameraAutoFocus(this, e);
    });

    function triggerCameraAutoFocus(container, event) {
        var $container = $(container);
        var offset = $container.offset();
        var src = event.originalEvent || event;
        var pt = (src.touches && src.touches[0]) ? src.touches[0] : src;
        var clickX = (pt.pageX || 0) - offset.left;
        var clickY = (pt.pageY || 0) - offset.top;
        if (!clickX || !clickY) { clickX = $container.width() / 2; clickY = $container.height() / 2; }

        var $ring = $('<div class="camera-focus-ring"></div>');
        $ring.css({ left: clickX + 'px', top: clickY + 'px' });
        $container.append($ring);
        setTimeout(function() { $ring.addClass('active'); }, 10);
        setTimeout(function() { $ring.addClass('fade-out'); setTimeout(function() { $ring.remove(); }, 400); }, 600);

        var videoEl = $container.find('video')[0];
        if (!videoEl || !videoEl.srcObject) return;
        var track = videoEl.srcObject.getVideoTracks()[0];
        if (!track || !track.applyConstraints) return;
        try {
            var cap = track.getCapabilities ? track.getCapabilities() : {};
            if (cap.focusMode) {
                track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] }).catch(function() {});
            }
            if (cap.pointsOfInterest) {
                var normX = Math.min(1, Math.max(0, clickX / $container.width()));
                var normY = Math.min(1, Math.max(0, clickY / $container.height()));
                track.applyConstraints({ advanced: [{ pointsOfInterest: [{ x: normX, y: normY }] }] }).catch(function() {});
            }
        } catch(e) {}
    }

    // --- Auto close kamera saat pindah tab / tutup browser ---
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden' && isCameraScanning) {
            stopCameraScanner();
            switchToUsb();
        }
    });
    window.addEventListener('pagehide', function() { if (isCameraScanning) stopCameraScanner(); });
    window.addEventListener('beforeunload', function() { if (isCameraScanning) stopCameraScanner(); });

    // --- Toggle Buttons ---
    $('#btnModeUsb').on('click', function() {
        $('.scan-mode-btn').removeClass('active');
        $(this).addClass('active');
        stopCameraScanner();
        $('#cameraScannerBox').slideUp(200);
        $('#usbScannerBox').slideDown(200, function() { $('#scannerInput').focus(); });
    });

    $('#btnModeCamera').on('click', function() {
        if (isCameraScanning) return;
        $('.scan-mode-btn').removeClass('active');
        $(this).addClass('active');
        $('#usbScannerBox').slideUp(200);
        $('#cameraScannerBox').slideDown(200, function() { startCameraScanner(); });
    });

    $('#btnStopCamera').on('click', function() {
        stopCameraScanner();
        switchToUsb();
    });

    // --- Proses hasil scan ---
    function processScan(text) {
        if (!text) return;
        text = $.trim(text).toUpperCase();

        // Jika dipanggil dari kamera, hentikan kamera dulu
        if (isCameraScanning) {
            stopCameraScanner();
            switchToUsb();
        }

        var parts = text.split(';');
        if (parts.length < 3) {
            Swal.fire({
                icon: 'warning',
                title: 'Format QR Tidak Valid',
                html: 'Format: <strong>Sequence_No;Production_Date;Type</strong><br>Terbaca: <code>' + text + '</code>',
                confirmButtonColor: '#F36494'
            });
            $('#scannerInput').val('');
            return;
        }

        $('#sequence_no').val(parts[0]);
        $('#production_date').val(parts[1]);
        $('#type').val(parts[2]);
        $('#scannerInput').val('');

        $.when(
            $.getJSON('{{ route("member.record.areas-by-type") }}', { type: parts[2] }),
            $.getJSON('{{ route("member.record.my-areas") }}')
        ).done(function(typeRes, myRes) {
            var typeAreas = typeRes[0] || [];
            var myAreas = myRes[0] || [];

            if (!myAreas || myAreas.length === 0) {
                showAreaAlert('NIK belum didaftarkan di member area. Silakan hubungi admin.');
                return;
            }

            var matched = typeAreas.filter(function(a) { return myAreas.indexOf(a) !== -1; });

            if (matched.length === 0) {
                showAreaAlert('Area anda (' + myAreas.map(function(a) { return a.replace(/_/g, ' '); }).join(', ') + ') tidak cocok dengan tipe yang di-scan.');
                return;
            }

            if (matched.length === 1) {
                $('#area').val(matched[0]);
                $('#recordForm').submit();
                return;
            }

            var $area = $('#modalArea');
            $area.empty().append('<option value="">Pilih Area</option>');
            $.each(matched, function(i, area) {
                $area.append('<option value="' + area + '">' +
                    area.replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); }) +
                    '</option>');
            });
            $('#confirmArea').prop('disabled', true);
            $('#areaModal').modal('show');
        }).fail(function() {
            showAreaAlert('Terjadi kesalahan saat memuat area. Silakan coba lagi.');
        });
    }

    function showAreaAlert(msg) {
        $('#areaAlertMessage').text(msg);
        $('#areaAlertModal').modal('show');
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
    $(function() { $('#remarkModal').modal('show'); });
    @endif
    @if($duplicateKanban)
    $(function() { $('#duplicateKanbanModal').modal('show'); });
    @endif
</script>
@endsection
