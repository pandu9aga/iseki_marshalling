@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">Type Traktor</h4>
            <a href="{{ route('admin.types.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Type</a>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="typesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Type</th>
                                <th>Type</th>
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
        var table = $('#typesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ url('admin/types') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Id_Type', name: 'Id_Type' },
                { data: 'Type', name: 'Type' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            if (confirm('Are you sure?')) {
                $.ajax({
                    url: "{{ url('admin/types') }}/" + id,
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
