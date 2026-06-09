@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">Record List</h4>
            <div>
                <form action="{{ url('admin/records/export') }}" method="GET" class="d-inline">
                    <input type="date" name="start_date" class="form-control-sm">
                    <input type="date" name="end_date" class="form-control-sm">
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Export</button>
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
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="recordModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="recordDetailContent"></div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var table = $('#recordsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ url('admin/records') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Sequence_No_Record', name: 'Sequence_No_Record' },
                { data: 'Production_Date_Record', name: 'Production_Date_Record' },
                { data: 'Type', name: 'Type' },
                { data: 'Area', name: 'Area' },
                { data: 'member_name', name: 'member_name' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.view-btn', function() {
            var id = $(this).data('id');
            $.get("{{ url('admin/records') }}/" + id, function(data) {
                var html = '<div class="mb-3"><strong>Sequence:</strong> ' + data.Sequence_No_Record + '<br>';
                html += '<strong>Production Date:</strong> ' + data.Production_Date_Record + '<br>';
                html += '<strong>Type:</strong> ' + data.Type + '<br>';
                html += '<strong>Area:</strong> ' + data.Area + '<br>';
                html += '<strong>Member:</strong> ' + (data.member ? data.member.nama : '-') + '</div>';
                html += '<table class="table table-bordered"><thead><tr><th>Sequence</th><th>Code Part</th><th>Name Part</th><th>Code Rack</th><th>Qty</th><th>Qty Record</th><th>Time Record</th><th>Status</th></tr></thead><tbody>';
                data.record_lists.forEach(function(rl) {
                    html += '<tr><td>' + rl.Sequence_No + '</td><td>' + rl.Code_Part + '</td><td>' + rl.Name_Part + '</td><td>' + rl.Code_Rack + '</td><td>' + rl.Qty + '</td><td>' + (rl.Qty_Record || '-') + '</td><td>' + (rl.Time_Record || '-') + '</td><td>' + (rl.Time_Record ? '<span class="badge bg-success">Done</span>' : '<span class="badge bg-warning">Pending</span>') + '</td></tr>';
                });
                html += '</tbody></table>';
                $('#recordDetailContent').html(html);
                $('#recordModal').modal('show');
            });
        });
    });
</script>
@endsection
