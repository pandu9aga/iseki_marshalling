@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">My Record List</h4>
            <div>
                <a href="{{ route('member.record.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-qrcode"></i> New Record</a>
                <a href="{{ url('member/records/export') }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Export</a>
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
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#recordsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ url('member/records') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Sequence_No_Record', name: 'Sequence_No_Record' },
                { data: 'Production_Date_Record', name: 'Production_Date_Record' },
                { data: 'Type', name: 'Type' },
                { data: 'Area', name: 'Area' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection
