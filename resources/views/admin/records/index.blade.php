@extends('layouts.main')

@section('style')
<style>
    #recordModal .modal-body { overflow-x: auto; }
    #recordModal table { font-size: 0.75rem; }
    #recordModal table td, #recordModal table th { white-space: nowrap; padding: 0.2rem 0.3rem; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">Record List</h4>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-filter"></i> Filter</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="filter_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Member</label>
                        <select name="filter_member" class="form-select form-select-sm">
                            <option value="">All Members</option>
                            @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Area</label>
                        <select name="filter_area" class="form-select form-select-sm">
                            <option value="">All Areas</option>
                            @foreach($areas as $area)
                            @if($area)
                            <option value="{{ $area }}">{{ ucwords(str_replace('_', ' ', $area)) }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="filter_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            @foreach($types as $type)
                            <option value="{{ $type->Type }}">{{ $type->Type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
                        <button type="reset" class="btn btn-secondary btn-sm"><i class="fas fa-undo"></i> Reset</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="recordsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Sequence No</th>
                                <th>Production Date</th>
                                <th>Type</th>
                                <th>Area</th>
                                <th>Member</th>
                                <th>Time Record</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="recordModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="recordDetailContent"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="ngImageModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content border border-2 border-danger">
            <div class="modal-header">
                <h5 class="modal-title">NG Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="ngImageContent"></div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var table = $('#recordsTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('admin/records') }}",
                data: function(d) {
                    d.filter_date = $('#filterForm [name="filter_date"]').val();
                    d.filter_member = $('#filterForm [name="filter_member"]').val();
                    d.filter_area = $('#filterForm [name="filter_area"]').val();
                    d.filter_type = $('#filterForm [name="filter_type"]').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Sequence_No_Record', name: 'Sequence_No_Record' },
                { data: 'Production_Date_Record', name: 'Production_Date_Record' },
                { data: 'Type', name: 'Type' },
                { data: 'Area', name: 'Area' },
                { data: 'member_name', name: 'member_name' },
                { data: 'Time_Record', name: 'Time_Record' },
                { data: 'status', name: 'status' }
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).css('cursor', 'pointer');
                $(row).on('click', function() {
                    showRecordDetail(data.Id_Record);
                });
            }
        });

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });

        $('#filterForm').on('reset', function(e) {
            $(this).find('input, select').val('');
            table.ajax.reload();
        });
    });

    function showRecordDetail(id) {
        $.get("{{ url('admin/records') }}/" + id, function(data) {
            var html = '<div class="mb-3"><strong>Sequence:</strong> ' + data.Sequence_No_Record + '<br>';
            var formattedArea = data.Area ? data.Area.replace(/_/g, ' ').replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            }) : '-';
            html += '<strong>Production Date:</strong> ' + data.Production_Date_Record + '<br>';
            html += '<strong>Type:</strong> ' + data.Type + '<br>';
            html += '<strong>Area:</strong> ' + formattedArea + '<br>';
            html += '<strong>Member:</strong> ' + (data.member ? data.member.nama : '-') + '</div>';
            html += '<div class="table-responsive"><table class="table table-bordered table-sm" style="font-size:0.75rem;"><thead><tr><th style="white-space:nowrap;">Seq</th><th style="white-space:nowrap;">Code Part</th><th style="white-space:nowrap;">Name Part</th><th style="white-space:nowrap;">Mode</th><th style="white-space:nowrap;">Code<br>Rack</th><th style="white-space:nowrap;">Box</th><th style="white-space:nowrap;">Qty</th><th style="white-space:nowrap;">Qty<br>Rec</th><th style="white-space:nowrap;">Time<br>Rec</th><th style="white-space:nowrap;">Stat</th></tr></thead><tbody>';
            data.record_lists.forEach(function(rl) {
                var statusHtml = '';
                if (!rl.Time_Record) {
                    statusHtml = '<span class="badge bg-warning">Pending</span>';
                } else if (parseInt(rl.Qty_Record) === parseInt(rl.Qty)) {
                    statusHtml = '<span class="badge bg-success">OK</span>';
                } else if (rl.Status_Ng === 'ng_ok') {
                    var ngClick = rl.Image_Ng ? 'onclick="showNgImage(\'' + rl.Image_Ng + '\', ' + rl.Id_Record_List + ', ' + rl.Qty + ', ' + (rl.Qty_Record || 0) + ', \'ng_ok\')" style="cursor:pointer;"' : '';
                    statusHtml = '<span class="badge bg-info" ' + ngClick + '>NG-OK</span>';
                } else {
                    var ngClick = rl.Image_Ng ? 'onclick="showNgImage(\'' + rl.Image_Ng + '\', ' + rl.Id_Record_List + ', ' + rl.Qty + ', ' + (rl.Qty_Record || 0) + ', \'\')" style="cursor:pointer;"' : '';
                    statusHtml = '<span class="badge bg-danger" ' + ngClick + '>NG</span>';
                }
                var modeBadge = rl.Mode === 'ai' ? '<span class="badge bg-info">AI</span>' : '<span class="badge bg-secondary">Manual</span>';
                var enterTime = rl.Time_Record ? rl.Time_Record.substring(0, 10) + '<br>' + rl.Time_Record.substring(11, 19) : '-';
                var namePart = rl.Name_Part ? (rl.Name_Part.length > 15 ? rl.Name_Part.substring(0, 15) + '...' : rl.Name_Part) : '-';
                html += '<tr><td>' + rl.Sequence_No + '</td><td>' + rl.Code_Part + '</td><td style="font-size:0.7rem;">' + namePart + '</td><td>' + modeBadge + '</td><td>' + rl.Code_Rack + '</td><td>' + (rl.Box || '-') + '</td><td>' + rl.Qty + '</td><td>' + (rl.Qty_Record || '-') + '</td><td>' + enterTime + '</td><td>' + statusHtml + '</td></tr>';
            });
            html += '</tbody></table></div>';
            $('#recordDetailContent').html(html);
            $('#recordModal').modal('show');
        });
    }

    function showNgImage(imagePath, recordListId, expectedQty, recordedQty, statusNg) {
        var html = '<div class="border rounded p-2 mb-3 bg-light"><strong>Expected Qty:</strong> ' + expectedQty + ' &nbsp;|&nbsp; <strong>Recorded Qty:</strong> ' + recordedQty + '</div>';
        html += '<img src="{{ url("") }}/' + imagePath + '" class="img-fluid mb-3 border rounded" style="max-height:400px;">';
        html += '<br>';
        if (statusNg !== 'ng_ok') {
            html += '<button type="button" class="btn btn-success" onclick="approveNg(' + recordListId + ')"><i class="fas fa-check"></i> Approve (Set OK)</button>';
        }
        $('#ngImageContent').html(html);
        $('#ngImageModal').modal('show');
    }

    function approveNg(recordListId) {
        if (!confirm('Approve this NG item as OK?')) return;
        $.post("{{ url('admin/record-lists') }}/" + recordListId + "/approve", {
            _token: "{{ csrf_token() }}"
        }, function(response) {
            if (response.success) {
                $('#ngImageModal').modal('hide');
                $('#recordModal').modal('hide');
                $('#recordsTable').DataTable().ajax.reload();
            }
        });
    }
</script>
@endsection
