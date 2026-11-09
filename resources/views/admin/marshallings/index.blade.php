@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center flex-wrap" style="gap: 15px;">
                <h4 class="page-title text-primary mb-0">Marshalling{{ $selectedType ? ' - ' . $selectedType->Type : '' }}</h4>
                <div class="d-flex align-items-center flex-wrap" style="gap: 5px;">
                    <a href="{{ route('admin.marshallings.index', $selectedTypeId ? ['type_id' => $selectedTypeId] : []) }}" class="badge {{ !$selectedArea ? 'badge-primary' : 'badge-secondary' }}" style="font-size:13px; padding:5px 10px;">
                        All ({{ $totalAll }})
                    </a>
                    @foreach($areas as $a)
                    <a href="{{ route('admin.marshallings.index', ['area' => $a->Area, 'type_id' => $selectedTypeId]) }}" class="badge {{ $selectedArea === $a->Area ? 'badge-primary' : 'badge-secondary' }}" style="font-size:13px; padding:5px 10px;">
                        {{ ucwords(str_replace('_', ' ', $a->Area)) }} ({{ $a->total }})
                    </a>
                    @endforeach
                </div>
            </div>
            <div class="my-2">
                <button type="button" class="btn btn-warning text-white" data-bs-toggle="modal" data-bs-target="#importModal"><i class="fas fa-file-import"></i> Import</button>
                <a href="{{ route('admin.marshallings.export', request()->only(['type_id', 'area'])) }}" class="btn btn-success" onclick="event.preventDefault(); var url=this.href; window.open(url);"><i class="fas fa-file-excel"></i> Export</a>
                <a href="{{ route('admin.marshallings.export-zip') }}" class="btn btn-secondary text-white" onclick="event.preventDefault(); var url=this.href; window.open(url);"><i class="fas fa-file-archive"></i> Export All (ZIP)</a>
                <a href="{{ route('admin.marshallings.history', request()->only(['type_id', 'area'])) }}" class="btn btn-info text-white"><i class="fas fa-history"></i> Riwayat</a>
                <a href="{{ route('admin.marshallings.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Marshalling</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3" style="gap: 8px;">
                    <span class="text-muted">Urutkan:</span>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn {{ $scanOrder === 'box' ? 'btn-primary' : 'btn-outline-primary' }} sort-mode-btn" data-mode="box"><i class="fas fa-box"></i> Per Box</button>
                        <button type="button" class="btn {{ $scanOrder === 'sequence' ? 'btn-primary' : 'btn-outline-primary' }} sort-mode-btn" data-mode="sequence"><i class="fas fa-sort-numeric-down"></i> Per Sequence</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="marshallingsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Type</th>
                            <th>Sequence No</th>
                            <th>Code Part</th>
                            <th>Name Part</th>
                            <th>Code Rack</th>
                            <th>Difference</th>
                            <th>Location</th>
                            <th>Box</th>
                            <th>Qty</th>
                            <th>Mode</th>
                            <th>Area</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.marshallings.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Marshallings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Excel</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted">
                            Format header: Sequence_No, Type_Tractor, Code_Part, Name_Part, Code_Rack, Difference, Location_Rack, Box, Qty, Mode, Area.<br>
                            Data mulai row 2. Type_Tractor harus sudah ada di master Type.
                        </small>
                    </div>
                    <div id="importProgressWrap" class="d-none">
                        <div class="progress" style="height: 22px;">
                            <div id="importProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <small id="importProgressText" class="d-block mt-2 text-muted"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="importCancelBtn" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var url = "{{ url('admin/marshallings') }}";
        var params = new URLSearchParams(window.location.search);
        if (params.get('area')) url += '?area=' + params.get('area');
        if (params.get('type_id')) url += (url.includes('?') ? '&' : '?') + 'type_id=' + params.get('type_id');

        var sortMode = '{{ $scanOrder }}';

        var table = $('#marshallingsTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: { url: url, data: function (d) { d.sort_mode = sortMode; } },
            order: [], // urutan default diatur di controller sesuai sort_mode (Per Box / Per Sequence)
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'type_name', name: 'type_name' },
                { data: 'Sequence_No', name: 'Sequence_No' },
                { data: 'Code_Part', name: 'Code_Part' },
                { data: 'Name_Part', name: 'Name_Part' },
                { data: 'Code_Rack', name: 'Code_Rack' },
                { data: 'Difference', name: 'Difference', render: function(data) { return data ? data.replace(/;/g, '<br>') : '-'; } },
                { data: 'Location_Rack', name: 'Location_Rack' },
                { data: 'Box', name: 'Box' },
                { data: 'Qty', name: 'Qty' },
                { data: 'Mode', name: 'Mode' },
                { data: 'Area', name: 'Area' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // ===== Import dengan progress =====
        var importRunning = false;
        var pollTimer = null;
        var $importForm = $('#importForm');
        var $submitBtn = $importForm.find('button[type=submit]');
        var $bar = $('#importProgressBar');
        var $text = $('#importProgressText');

        function setImportBar(percent, message, state) {
            percent = Math.max(0, Math.min(100, Math.round(percent)));
            $bar.css('width', percent + '%').attr('aria-valuenow', percent).text(percent + '%');
            $bar.removeClass('bg-success bg-danger');
            if (state === 'success') $bar.addClass('bg-success').removeClass('progress-bar-animated progress-bar-striped');
            else if (state === 'error') $bar.addClass('bg-danger').removeClass('progress-bar-animated progress-bar-striped');
            else $bar.addClass('progress-bar-animated progress-bar-striped');
            $text.removeClass('text-muted text-success text-danger')
                .addClass(state === 'success' ? 'text-success' : (state === 'error' ? 'text-danger' : 'text-muted'))
                .text(message || '');
        }

        function setImportLocked(locked) {
            importRunning = locked;
            $submitBtn.prop('disabled', locked);
            $('#importCancelBtn, #importModal .btn-close').prop('disabled', locked);
            $importForm.find('input[name=file]').prop('disabled', locked);
            $submitBtn.html(locked ? '<i class="fas fa-spinner fa-spin"></i> Mengimport...' : 'Import');
        }

        function stopPolling() {
            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
        }

        // Cegah modal ditutup saat import berjalan
        $('#importModal').on('hide.bs.modal', function(e) {
            if (importRunning) e.preventDefault();
        });

        $importForm.on('submit', function(e) {
            e.preventDefault();
            if (importRunning) return;
            if (!$importForm.find('input[name=file]')[0].files.length) return;

            var progressId = Date.now() + '-' + Math.random().toString(36).slice(2);
            // FormData dibuat sebelum input file di-disable agar file tetap terkirim
            var formData = new FormData(this);
            formData.append('progress_id', progressId);

            $('#importProgressWrap').removeClass('d-none');
            setImportBar(0, 'Mengunggah file... 0%');
            setImportLocked(true);

            var uploadDone = false;
            var finished = false;
            var polling = false;

            function startPolling() {
                if (pollTimer) return;
                pollTimer = setInterval(function() {
                    if (polling || finished) return;
                    polling = true;
                    $.getJSON("{{ url('admin/marshallings/import-progress') }}/" + progressId)
                        .done(function(res) {
                            if (finished) return;
                            setImportBar(res.percent || 0, res.message || 'Memproses...');
                        })
                        .always(function() { polling = false; });
                }, 700);
            }

            function finish(ok, message) {
                finished = true;
                stopPolling();
                setImportBar(ok ? 100 : $bar.attr('aria-valuenow'), message, ok ? 'success' : 'error');
                if (ok) {
                    // Reload (query string dipertahankan) agar DataTable & hitungan area ter-refresh
                    setTimeout(function() { window.location.reload(); }, 800);
                } else {
                    setImportLocked(false);
                }
            }

            $.ajax({
                url: $importForm.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                xhr: function() {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.onprogress = function(ev) {
                            if (!ev.lengthComputable || uploadDone) return;
                            var pct = Math.round(ev.loaded / ev.total * 100);
                            if (pct >= 100) {
                                uploadDone = true;
                                setImportBar(0, 'Menunggu server memproses...');
                                startPolling();
                            } else {
                                setImportBar(pct, 'Mengunggah file... ' + pct + '%');
                            }
                        };
                    }
                    return xhr;
                },
                success: function(res) {
                    finish(!!res.success, res.message || 'Import selesai.');
                },
                error: function(xhr) {
                    var msg = 'Terjadi kesalahan saat import. Silakan coba lagi.';
                    var json = xhr.responseJSON;
                    if (xhr.status === 0) {
                        msg = 'Koneksi terputus. Periksa jaringan lalu coba lagi.';
                    } else if (xhr.status === 419) {
                        msg = 'Sesi habis. Muat ulang halaman lalu coba lagi.';
                    } else if (xhr.status === 413) {
                        msg = 'Ukuran file terlalu besar.';
                    } else if (json && json.errors) {
                        msg = Object.keys(json.errors).map(function(k) { return json.errors[k].join(' '); }).join(' ');
                    } else if (json && json.message) {
                        msg = json.message;
                    }
                    finish(false, msg);
                }
            });
        });

        // Ganti mode urut; reset urutan header kolom supaya mode yang dipilih yang dipakai
        $('.sort-mode-btn').on('click', function() {
            sortMode = $(this).data('mode');
            $('.sort-mode-btn').removeClass('btn-primary').addClass('btn-outline-primary');
            $(this).removeClass('btn-outline-primary').addClass('btn-primary');
            table.order([]).draw();
        });

        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            if (confirm('Are you sure?')) {
                $.ajax({
                    url: "{{ url('admin/marshallings') }}/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        table.ajax.reload();
                    }
                });
            }
        });
    });
</script>
@endsection
