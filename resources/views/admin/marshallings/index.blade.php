@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">Marshalling</h4>
            <a href="{{ route('admin.marshallings.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Marshalling</a>
        </div>
        <div class="card">
            <div class="card-body">
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
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var table = $('#marshallingsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ url('admin/marshallings') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'type_name', name: 'type_name' },
                { data: 'Sequence_No', name: 'Sequence_No' },
                { data: 'Code_Part', name: 'Code_Part' },
                { data: 'Name_Part', name: 'Name_Part' },
                { data: 'Code_Rack', name: 'Code_Rack' },
                { data: 'Qty', name: 'Qty' },
                { data: 'Mode', name: 'Mode' },
                { data: 'Area', name: 'Area' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
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
