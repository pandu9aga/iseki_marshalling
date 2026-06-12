@extends('layouts.main')

@section('style')
<style>
    #recordsTable { font-size: 11px !important; }
    #recordsTable th, #recordsTable td { padding: 5px 5px !important; font-size: 11px !important; }
    #recordsTable .badge { font-size: 11px !important; padding: 1px 3px !important; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">My Record List</h4>
            <div>
                <a href="{{ route('member.record.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-qrcode"></i> New Record</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="recordsTable" class="table table-bordered table-striped table-sm" style="font-size: 12px; width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Type</th>
                                <th>Sequence No</th>
                                <th>Production Date</th>
                                <th>Area</th>
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
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#recordsTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: "{{ url('member/records') }}",
            autoWidth: false,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Type', name: 'Type' },
                { data: 'Sequence_No_Record', name: 'Sequence_No_Record' },
                { data: 'Production_Date_Record', name: 'Production_Date_Record' },
                { data: 'Area', name: 'Area' },
                { data: 'Time_Record', name: 'Time_Record' },
                { data: 'status', name: 'status' }
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).css('cursor', 'pointer');
                $(row).attr('title', 'Click to view');
                $(row).on('click', function() {
                    window.location = "{{ url('member/record') }}/" + data.DT_RowId + "/record-part";
                });
            }
        });
    });
</script>
@endsection
