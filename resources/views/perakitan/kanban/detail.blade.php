@extends('layouts.main')

@section('style')
<style>
    .report-btn { transition: all 0.2s; }
    .report-btn:hover { transform: scale(1.05); }
    .pdf-page-section { transition: opacity 0.2s; }
    .filter-input { font-size: 0.8rem; padding: 4px 6px; width: 100%; box-sizing: border-box; border: 1px solid #ccc; border-radius: 3px; }
    .filter-input:focus { outline: none; border-color: #F36494; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="page-title text-primary mb-0">Detail Record</h4>
                <small class="text-muted">
                    {{ $record->Sequence_No_Record }} |
                    {{ $record->Production_Date_Record }} |
                    {{ ucwords(str_replace('_', ' ', $record->Area)) }} |
                    Member: {{ $record->member->nama ?? 'Unknown' }}
                </small>
            </div>
            <a href="{{ route('perakitan.kanban.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="recordPartsTable" class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="white-space:nowrap;">Seq</th>
                                <th style="white-space:nowrap;">Code Part</th>
                                <th style="white-space:nowrap;">Name Part</th>
                                <th style="white-space:nowrap;">Mode</th>
                                <th style="white-space:nowrap;">Code Rack</th>
                                <th style="white-space:nowrap;">Diff</th>
                                <th style="white-space:nowrap;">Box</th>
                                <th style="white-space:nowrap;">Qty</th>
                                <th style="white-space:nowrap;">Qty Rec</th>
                                <th style="white-space:nowrap;">Time Rec</th>
                                <th style="white-space:nowrap;">Stat Part</th>
                                <th style="white-space:nowrap;">Report Empty</th>
                            </tr>
                            <tr class="filter-row">
                                <th><input type="text" class="filter-input" placeholder="Filter" data-col="0"></th>
                                <th><input type="text" class="filter-input" placeholder="Filter" data-col="1"></th>
                                <th><input type="text" class="filter-input" placeholder="Filter" data-col="2"></th>
                                <th><input type="text" class="filter-input" placeholder="Filter" data-col="3"></th>
                                <th><input type="text" class="filter-input" placeholder="Filter" data-col="4"></th>
                                <th><input type="text" class="filter-input" placeholder="Filter" data-col="5"></th>
                                <th><input type="text" class="filter-input" placeholder="Filter" data-col="6"></th>
                                <th><input type="text" class="filter-input" placeholder="Filter" data-col="7"></th>
                                <th><input type="text" class="filter-input" placeholder="Filter" data-col="8"></th>
                                <th><input type="text" class="filter-input" placeholder="Filter" data-col="9"></th>
                                <th><input type="text" class="filter-input" placeholder="Filter" data-col="10"></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($record->recordLists as $rl)
                            <tr id="rl-row-{{ $rl->Id_Record_List }}">
                                <td>{{ $rl->Sequence_No }}</td>
                                <td>{{ $rl->Code_Part }}</td>
                                <td style="font-size:0.8rem;">{{ $rl->Name_Part ? (strlen($rl->Name_Part) > 20 ? substr($rl->Name_Part, 0, 20) . '...' : $rl->Name_Part) : '-' }}</td>
                                <td>{!! $rl->Mode === 'ai' ? '<span class="badge bg-info">AI</span>' : '<span class="badge bg-secondary">Manual</span>' !!}</td>
                                <td>{{ $rl->Code_Rack }}</td>
                                <td>{{ $rl->Difference ?? '-' }}</td>
                                <td>{{ $rl->Box ?? '-' }}</td>
                                <td>{{ $rl->Qty }}</td>
                                <td>{{ $rl->Qty_Record ?? '-' }}</td>
                                <td style="white-space:nowrap;font-size:0.8rem;">
                                    @if($rl->Time_Record)
                                        {{ \Carbon\Carbon::parse($rl->Time_Record)->format('d/m/Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if(!$rl->Time_Record)
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif((int)$rl->Qty_Record === (int)$rl->Qty)
                                        <span class="badge bg-success">OK</span>
                                    @elseif($rl->Status_Ng === 'ng_ok')
                                        <span class="badge bg-info">NG-OK</span>
                                    @else
                                        <span class="badge bg-danger">NG</span>
                                    @endif
                                </td>
                                <td id="report-cell-{{ $rl->Id_Record_List }}">
                                    @if($rl->Report_Empty)
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-box-open"></i> Dilaporkan Kosong
                                        </span><br>
                                        <small class="text-muted" style="font-size:0.7rem;">
                                            {{ \Carbon\Carbon::parse($rl->Report_Empty)->format('d/m/Y H:i') }}<br>
                                            NIK: {{ $rl->Reporter_Nik }}
                                        </small>
                                    @else
                                        <button type="button" class="btn btn-outline-warning btn-sm report-btn" onclick="reportEmpty({{ $rl->Id_Record_List }})">
                                            <i class="fas fa-box-open"></i> Laporkan Kosong
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">Tidak ada data part.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @php
            $pdfCount = $pdfs->count();
            $pdfChunks = $pdfs->chunk(4);
            $pdfTotalPages = $pdfChunks->count();
        @endphp

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                <h5 class="card-title mb-0 text-white" style="font-size: 1rem;">
                    <i class="fas fa-file-pdf me-2"></i>PDF Procedure Training (Bulan Ini)
                </h5>
                @if($pdfCount > 0)
                    <span class="badge bg-light text-primary">Total: {{ $pdfCount }} Procedure</span>
                @endif
            </div>
            <div class="card-body p-3">
                @if($pdfCount === 0)
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                        Tidak ada PDF procedure training untuk NIK {{ Auth::guard('perakitan')->user()->nik ?? '-' }} pada bulan ini.
                    </div>
                @else
                    @foreach($pdfChunks as $pageIndex => $chunk)
                        <div class="pdf-page-section {{ $pageIndex > 0 ? 'd-none' : '' }}" id="pdf-page-{{ $pageIndex + 1 }}">
                            <div class="row g-3">
                                @foreach($chunk as $pdf)
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100 border shadow-sm">
                                            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                                <strong class="text-truncate text-dark" style="max-width: 75%; font-size: 0.85rem;" title="{{ $pdf->name }}">
                                                    <i class="far fa-file-pdf text-danger me-1"></i> {{ $pdf->name }}
                                                </strong>
                                                <small class="badge bg-info text-white" style="font-size:0.7rem;">{{ $pdf->tractor }} - {{ $pdf->area }}</small>
                                            </div>
                                            <div class="card-body p-1" style="height: 380px;">
                                                <iframe src="{{ $pdf->url }}" width="100%" height="100%" style="border: none; border-radius: 4px;"></iframe>
                                            </div>
                                            <div class="card-footer py-1 px-3 bg-white text-end">
                                                <a href="{{ $pdf->url }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                                    <i class="fas fa-external-link-alt me-1"></i> Buka Fullscreen
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @if($pdfTotalPages > 1)
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-prev-pdf" onclick="changePdfPage(-1)" disabled>
                                <i class="fas fa-chevron-left me-1"></i> Sebelumnya
                            </button>
                            <span class="text-muted fw-bold" style="font-size: 0.85rem;">
                                Halaman <span id="pdf-current-page">1</span> dari {{ $pdfTotalPages }}
                            </span>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-next-pdf" onclick="changePdfPage(1)">
                                Selanjutnya <i class="fas fa-chevron-right ms-1"></i>
                            </button>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    var currentPdfPage = 1;
    var totalPdfPages = {{ $pdfTotalPages }};

    function changePdfPage(direction) {
        var newPage = currentPdfPage + direction;
        if (newPage < 1 || newPage > totalPdfPages) return;

        $('#pdf-page-' + currentPdfPage).addClass('d-none');
        $('#pdf-page-' + newPage).removeClass('d-none');

        currentPdfPage = newPage;
        $('#pdf-current-page').text(currentPdfPage);

        $('#btn-prev-pdf').prop('disabled', currentPdfPage === 1);
        $('#btn-next-pdf').prop('disabled', currentPdfPage === totalPdfPages);
    }

    $(document).ready(function() {
        $('.filter-input').on('keyup change', function() {
            var col = $(this).data('col');
            var val = $(this).val().toLowerCase();
            var $rows = $('#recordPartsTable tbody tr');
            if ($rows.length === 1 && $rows.find('td[colspan]').length) return;

            $rows.each(function() {
                var $td = $(this).children('td').eq(col);
                var text = $td.text().toLowerCase();
                $(this).toggle(text.indexOf(val) > -1);
            });
        });
    });

    function reportEmpty(id) {
        if (!confirm('Laporkan part ini sebagai kosong?')) return;
        $.post('{{ url("perakitan/kanban") }}/' + id + '/report-empty', {
            _token: '{{ csrf_token() }}'
        }, function(res) {
            if (res.success) {
                var cell = $('#report-cell-' + id);
                cell.html(
                    '<span class="badge bg-secondary"><i class="fas fa-box-open"></i> Dilaporkan Kosong</span><br>' +
                    '<small class="text-muted" style="font-size:0.7rem;">' +
                    '{{ now()->format('d/m/Y H:i') }}<br>' +
                    'NIK: {{ Auth::guard('perakitan')->user()->nik }}' +
                    '</small>'
                );
            }
        }).fail(function() {
            alert('Gagal melaporkan part kosong.');
        });
    }
</script>
@endsection