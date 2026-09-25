@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">Kategori (Main Type)</h4>
            <div>
                <button type="button" class="btn btn-warning text-white" data-bs-toggle="modal" data-bs-target="#importModal"><i class="fas fa-file-import"></i> Import</button>
                <a href="{{ route('admin.main-types.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Kategori</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="mainTypesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Main Type</th>
                                <th>Jumlah Sub-Type</th>
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

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.main-types.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Kategori & Sub-Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Excel (.xls, .xlsx)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted">
                            Format Excel (Dengan Header di baris 1):<br>
                            - <strong>Kolom A:</strong> Nama Sub Type (Contoh: MF1GC25FJRE3)<br>
                            - <strong>Kolom B:</strong> Nama Main Type (Contoh: GC)<br>
                            <em>(Data akan diimport mulai dari baris ke-2 / Row 2)</em>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('script')
<script>
    $(document).ready(function() {
        var table = $('#mainTypesTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: "{{ url('admin/main-types') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Main_Type', name: 'Main_Type' },
                { data: 'types_count', name: 'types_count', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            if (confirm('Are you sure?')) {
                $.ajax({
                    url: "{{ url('admin/main-types') }}/" + id,
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
