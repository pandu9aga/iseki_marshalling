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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Main Type</th>
                                <th>Sub Type</th>
                                <th style="width:140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mainTypes as $index => $mainType)
                            @php
                            $subTypes = $mainType->types;
                            $rowspan = max($subTypes->count(), 1);
                            @endphp
                            @forelse($subTypes as $subIndex => $subType)
                            <tr>
                                @if($subIndex === 0)
                                <td rowspan="{{ $rowspan }}">{{ $index + 1 }}</td>
                                <td rowspan="{{ $rowspan }}" class="fw-bold">{{ $mainType->Main_Type }}</td>
                                @endif
                                <td><i class="fas fa-tractor me-2 text-muted"></i>{{ $subType->Type }}</td>
                                @if($subIndex === 0)
                                <td rowspan="{{ $rowspan }}">
                                    <a href="{{ route('admin.main-types.edit', $mainType->Id_Main_Type) }}" class="btn btn-warning btn-sm text-white"><i class="fas fa-edit"></i></a>
                                    <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $mainType->Id_Main_Type }}"><i class="fas fa-trash"></i></button>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $mainType->Main_Type }}</td>
                                <td class="text-muted fst-italic">Belum ada sub type</td>
                                <td>
                                    <a href="{{ route('admin.main-types.edit', $mainType->Id_Main_Type) }}" class="btn btn-warning btn-sm text-white"><i class="fas fa-edit"></i></a>
                                    <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $mainType->Id_Main_Type }}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            @endforelse
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    Belum ada kategori (Main Type).
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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
@endsection

@section('script')
<script>
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        if (confirm('Yakin hapus kategori ini? Sub Type yang terhubung akan kehilangan kategorinya (tidak ikut terhapus).')) {
            $.ajax({
                url: "{{ url('admin/main-types') }}/" + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    location.reload();
                }
            });
        }
    });
</script>
@endsection