@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center flex-wrap" style="gap: 15px;">
                <h4 class="page-title text-primary mb-0">Marshalling{{ $selectedType ? ' - ' . $selectedType->Type : '' }}</h4>
                <div class="d-flex align-items-center flex-wrap" style="gap: 5px;">
                    <a href="{{ route('admin.marshallings.index', $selectedTypeId ? ['type_id' => $selectedTypeId] : []) }}" class="badge {{ !$selectedArea ? 'badge-primary' : 'badge-secondary' }}" style="font-size:13px; padding:5px 10px;">
                        All ({{ $totalAll }})
                    </a>
                    @foreach($areas as $a)
                    <a href="{{ route('admin.marshallings.index', ['area' => $a->Area, 'type_id' => $selectedTypeId]) }}" class="badge {{ $selectedArea === $a->Area ? 'badge-primary' : 'badge-secondary' }}" style="font-size:13px; padding:5px 10px;">
                        {{ $a->Area }} ({{ $a->total }})
                    </a>
                    @endforeach
                </div>
            </div>
            <div class="my-2">
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#importModal"><i class="fas fa-file-import"></i> Import</button>
                <a href="{{ route('admin.marshallings.export') }}" class="btn btn-success" onclick="event.preventDefault(); window.open(this.href); setTimeout(function(){ window.location.href='{{ route('admin.types.index') }}'; }, 2000);"><i class="fas fa-file-excel"></i> Export</a>
                <a href="{{ route('admin.marshallings.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Marshalling</a>
            </div>
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

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.marshallings.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Marshallings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Excel</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted">
                            Format header: Sequence_No, Type_Tractor, Code_Part, Name_Part, Code_Rack, Difference, Location_Rack, Box, Qty, Mode, Area.<br>
                            Data mulai row 2. Type_Tractor harus sudah ada di master Type.
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
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var url = "{{ url('admin/marshallings') }}";
        var params = new URLSearchParams(window.location.search);
        if (params.get('area')) url += '?area=' + params.get('area');
        if (params.get('type_id')) url += (url.includes('?') ? '&' : '?') + 'type_id=' + params.get('type_id');

        var table = $('#marshallingsTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: url,
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
