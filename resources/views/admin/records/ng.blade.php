@extends('layouts.main')

@section('style')
<style>
    #ngTable .badge { font-size: 11px; padding: 3px 6px; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">NG Record List</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ngTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Member</th>
                                <th>Sequence Record</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Qty Record</th>
                                <th>Code Part</th>
                                <th>Name Part</th>
                                <th>Area</th>
                                <th>Production Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
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
        var table = $('#ngTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: "{{ url('admin/ng') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'member_name', name: 'member_name' },
                { data: 'sequence_record', name: 'sequence_record' },
                { data: 'type_record', name: 'type_record' },
                { data: 'Qty', name: 'Qty' },
                { data: 'Qty_Record', name: 'Qty_Record' },
                { data: 'Code_Part', name: 'Code_Part' },
                { data: 'Name_Part', name: 'Name_Part' },
                { data: 'area_record', name: 'area_record' },
                { data: 'production_date', name: 'production_date' }
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).css('cursor', 'pointer');
                $(row).on('click', function() {
                    showNgDetail(data);
                });
            }
        });
    });

    function showNgDetail(data) {
        $.get("{{ url('admin/ng-detail') }}/" + data.Id_Record_List, function(resp) {
            var html = '<div class="border rounded p-2 mb-3 bg-light text-start">';
            html += '<strong>Code Part:</strong> ' + resp.Code_Part + '<br>';
            html += '<strong>Name Part:</strong> ' + resp.Name_Part + '<br>';
            html += '<strong>Expected Qty:</strong> ' + resp.Qty + '<br>';
            html += '<strong>Recorded Qty:</strong> ' + (resp.Qty_Record || '-') + '<br>';
            html += '<strong>Code Rack:</strong> ' + resp.Code_Rack + '<br>';
            html += '<strong>Mode:</strong> ' + (resp.Mode === 'ai' ? 'AI' : 'Manual');
            if (resp.Image_Ng) {
                html += '</div>';
                html += '<img src="{{ url("") }}/' + resp.Image_Ng + '" class="img-fluid mb-3 border rounded" style="max-height:400px;">';
                html += '<br>';
                html += '<button type="button" class="btn btn-success" onclick="approveNg(' + resp.Id_Record_List + ')"><i class="fas fa-check"></i> Approve (Set OK)</button>';
            } else {
                html += '</div><p class="text-muted">No image available</p>';
            }
            $('#ngImageContent').html(html);
            $('#ngImageModal').modal('show');
        });
    }

    function approveNg(recordListId) {
        if (!confirm('Approve this NG item as OK?')) return;
        $.post("{{ url('admin/record-lists') }}/" + recordListId + "/approve", {
            _token: "{{ csrf_token() }}"
        }, function(response) {
            if (response.success) {
                $('#ngImageModal').modal('hide');
                $('#ngTable').DataTable().ajax.reload();
            }
        });
    }
</script>
@endsection