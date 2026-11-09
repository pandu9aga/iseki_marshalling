@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary mb-0">Marshalling Setting</h4>
        </div>
        <div class="alert alert-info">
            Urutan scan default berlaku untuk record baru. Pengecualian menimpa default untuk No. Sequence produksi (nomor kanban) tertentu.
            Perubahan tidak memengaruhi record yang sudah dibuat.
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Urutan Scan Default</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.marshalling-settings.default') }}" method="POST" class="d-flex align-items-center" style="gap: 16px;">
                    @csrf
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="scan_order" id="orderBox" value="box" {{ $scanOrder === 'box' ? 'checked' : '' }}>
                        <label class="form-check-label" for="orderBox">Per Box</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="scan_order" id="orderSequence" value="sequence" {{ $scanOrder === 'sequence' ? 'checked' : '' }}>
                        <label class="form-check-label" for="orderSequence">Per Sequence</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Pengecualian per No. Sequence Produksi</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.marshalling-settings.overrides.store') }}" method="POST" class="row g-2 align-items-end mb-3">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label">No. Sequence</label>
                        <input type="text" name="sequence_no" class="form-control" maxlength="50" value="{{ old('sequence_no') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Mode</label>
                        <select name="mode" class="form-select" required>
                            <option value="box">Per Box</option>
                            <option value="sequence">Per Sequence</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" maxlength="255" value="{{ old('keterangan') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table id="overridesTable" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. Sequence</th>
                                <th>Mode</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
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
        var table = $('#overridesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.marshalling-settings.index') }}",
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'sequence_no' },
                { data: 'mode' },
                { data: 'keterangan', defaultContent: '-' },
                { data: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.delete-btn', function() {
            if (confirm('Hapus pengecualian ini?')) {
                $.ajax({
                    url: "{{ url('admin/marshalling-settings/overrides') }}/" + $(this).data('id'),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() { table.ajax.reload(); }
                });
            }
        });
    });
</script>
@endsection
