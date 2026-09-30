@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">Map Area</h4>
            <div>
                <button type="button" class="btn btn-warning text-white" data-bs-toggle="modal" data-bs-target="#importModal"><i class="fas fa-file-import"></i> Import</button>
                <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus"></i> Add Map Area</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <ul class="nav nav-pills nav-secondary" id="areaTabs" role="tablist">
                    @php
                        $areas = ['front_axle', 'sub_engine', 'transmisi', 'sub_assy', 'main_line', 'inspeksi', 'mowcol'];
                    @endphp
                    @foreach($areas as $index => $area)
                    <li class="nav-item">
                        <a class="nav-link {{ $index == 0 ? 'active' : '' }}" id="tab-{{ $area }}" data-bs-toggle="pill" href="#content-{{ $area }}" role="tab" data-area="{{ $area }}">
                            {{ ucwords(str_replace('_', ' ', $area)) }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="mapAreasTable" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Sequence No</th>
                                <th>Area</th>
                                <th>Location Rack</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.map-areas.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Map Areas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Excel</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted">Format header: Area, Location_Rack, Sequence_No</small>
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

<!-- Modal Add/Edit -->
<div class="modal fade" id="mapAreaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="mapAreaForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" id="mapAreaId">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Map Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Area</label>
                        <select name="Area" id="inputArea" class="form-select" required>
                            <option value="">Pilih Area</option>
                            @foreach($areas as $area)
                            <option value="{{ $area }}">{{ ucwords(str_replace('_', ' ', $area)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location Rack</label>
                        <input type="text" name="Location_Rack" id="inputLocation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sequence No</label>
                        <input type="number" name="Sequence_No" id="inputSequence" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        var currentArea = 'front_axle'; // Default tab aktif

        var table = $('#mapAreasTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.map-areas.index') }}",
                data: function(d) {
                    d.area = currentArea;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Sequence_No', name: 'Sequence_No' },
                { data: 'Area', name: 'Area' },
                { data: 'Location_Rack', name: 'Location_Rack' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#areaTabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
            currentArea = $(this).data('area');
            table.draw();
        });

        $('#btnAdd').on('click', function() {
            $('#modalTitle').text('Add Map Area');
            $('#mapAreaForm')[0].reset();
            $('#formMethod').val('POST');
            $('#mapAreaId').val('');
            $('#inputArea').val(currentArea); // default ke tab yang sedang aktif
            
            var modal = new bootstrap.Modal(document.getElementById('mapAreaModal'));
            modal.show();
        });

        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            var area = $(this).data('area');
            var location = $(this).data('location');
            var sequence = $(this).data('sequence');
            
            $('#modalTitle').text('Edit Map Area');
            $('#inputArea').val(area);
            $('#inputLocation').val(location);
            $('#inputSequence').val(sequence);
            $('#formMethod').val('PUT');
            $('#mapAreaId').val(id);
            
            var modal = new bootstrap.Modal(document.getElementById('mapAreaModal'));
            modal.show();
        });

        $('#mapAreaForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#mapAreaId').val();
            var url = id ? "{{ url('admin/map-areas') }}/" + id : "{{ route('admin.map-areas.store') }}";
            
            $.ajax({
                url: url,
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('mapAreaModal'));
                    if (modal) {
                        modal.hide();
                    }
                    table.draw();
                },
                error: function(err) {
                    alert('Error saving data');
                }
            });
        });

        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            if (confirm('Are you sure you want to delete this?')) {
                $.ajax({
                    url: "{{ url('admin/map-areas') }}/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        table.draw();
                    }
                });
            }
        });
    });
</script>
@endsection
