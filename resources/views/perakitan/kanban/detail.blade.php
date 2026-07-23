@extends('layouts.main')

@section('style')
<style>
    .report-btn { transition: all 0.2s; }
    .report-btn:hover { transform: scale(1.05); }
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
                    <table class="table table-bordered table-sm mb-0">
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
    </div>
</div>
@endsection

@section('script')
<script>
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
