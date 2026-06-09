@extends('layouts.main')

@section('style')
<style>
    .table-xs { font-size: 11px !important; width: 100% !important; }
    .table-xs td, .table-xs th { padding: 2px 4px !important; font-size: 11px !important; white-space: nowrap; }
    .table-xs .badge { font-size: 11px !important; padding: 1px 3px !important; }
    .table-xs td { max-width: 80px; overflow: hidden; text-overflow: ellipsis; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="page-title text-primary mb-0">Record Part</h4>
                <small>Record: {{ $record->Sequence_No_Record }} | Area: {{ ucwords(str_replace('_', ' ', $record->Area)) }}</small>
            </div>
            <a href="{{ route('member.records.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="card">
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-xs table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 5%;">Seq</th>
                                <th style="width: 12%;">Code Rack</th>
                                <th style="width: 10%;">Location</th>
                                <th style="width: 8%;">Box</th>
                                <th style="width: 15%;">Code Part</th>
                                <th>Name Part</th>
                                <th style="width: 8%;">Qty</th>
                                <th style="width: 8%;">Mode</th>
                                <th style="width: 10%;">Qty Record</th>
                                <th style="width: 10%;">Time Record</th>
                                <th style="width: 10%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($record->recordLists as $i => $rl)
                            @php
                                $isCurrent = ($i == $currentIndex);
                                $isDone = $rl->Time_Record !== null;
                                $isOk = $isDone && $rl->Qty_Record == $rl->Qty;
                                $isNgOk = $isDone && !$isOk && $rl->Status_Ng == 'ng_ok';
                                $isNg = $isDone && !$isOk && !$isNgOk;
                                $isClickableNg = ($isNg || $isNgOk) && $rl->Mode == 'ai' && $rl->Image_Ng;
                            @endphp
                            <tr class="{{ $isDone ? ($isOk ? 'table-success' : ($isNgOk ? 'table-info' : 'table-danger')) : ($isCurrent ? 'table-primary' : '') }}"
                                @if($isCurrent)
                                onclick="window.location='{{ route('member.record.scan-part', [$record->Id_Record, $rl->Id_Record_List]) }}'"
                                style="cursor: pointer;"
                                @elseif($isClickableNg)
                                onclick="showMemberNgImage('{{ $rl->Image_Ng }}', {{ $rl->Qty }}, {{ $rl->Qty_Record ?? 0 }})"
                                style="cursor: pointer;"
                                @endif
                            >
                                <td>{{ $rl->Sequence_No }}</td>
                                <td>{{ $rl->Code_Rack }}</td>
                                <td>{{ $rl->Location_Rack }}</td>
                                <td>{{ $rl->Box }}</td>
                                <td>{{ $rl->Code_Part }}</td>
                                <td>{{ $rl->Name_Part }}</td>
                                <td>{{ $rl->Qty }}</td>
                                <td>{{ ucfirst($rl->Mode) }}</td>
                                <td>{{ $rl->Qty_Record ?? '-' }}</td>
                                <td>{{ $rl->Time_Record ?? '-' }}</td>
                                <td>
                                    @if($isDone)
                                        @if($isOk)
                                            <span class="badge bg-success">OK</span>
                                        @elseif($isNgOk)
                                            <span class="badge bg-info">NG-OK</span>
                                        @else
                                            <span class="badge bg-danger">NG</span>
                                        @endif
                                    @elseif($isCurrent)
                                        <span class="badge bg-primary">Current</span>
                                    @else
                                        <span class="badge bg-secondary">Waiting</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="memberNgModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content border border-2 border-danger">
            <div class="modal-header">
                <h5 class="modal-title">NG Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="memberNgContent"></div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function showMemberNgImage(imagePath, expectedQty, recordedQty) {
        var html = '<div class="border rounded p-2 mb-3 bg-light"><strong>Expected Qty:</strong> ' + expectedQty + ' &nbsp;|&nbsp; <strong>Recorded Qty:</strong> ' + recordedQty + '</div>';
        html += '<img src="{{ url("") }}/' + imagePath + '" class="img-fluid mb-3 border rounded" style="max-height:400px;">';
        $('#memberNgContent').html(html);
        $('#memberNgModal').modal('show');
    }
</script>
@endsection
