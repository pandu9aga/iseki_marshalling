@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="page-title text-primary mb-0">Riwayat Backup Marshalling</h4>
                @if ($typeFilter || $areaFilter)
                    <div class="mt-1">
                        @if ($typeFilter)
                            <span class="badge badge-primary" style="font-size:13px; padding:5px 10px;">Type: {{ $selectedType->Type ?? $typeFilter }}</span>
                        @endif
                        @if ($areaFilter)
                            <span class="badge badge-primary" style="font-size:13px; padding:5px 10px;">Area: {{ ucwords(str_replace('_', ' ', $areaFilter)) }}</span>
                        @endif
                        <a href="{{ route('admin.marshallings.history') }}" class="badge badge-secondary" style="font-size:13px; padding:5px 10px;">Tampilkan Semua</a>
                    </div>
                @endif
            </div>
            <div class="my-2">
                <a href="{{ route('admin.marshallings.index', request()->only(['type_id', 'area'])) }}" class="btn btn-secondary text-white"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="historyTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Type</th>
                                <th>Area</th>
                                <th>Tanggal Backup</th>
                                <th>File</th>
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
        var table = $('#historyTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: "{!! route('admin.marshallings.history', request()->only(['type_id', 'type', 'area'])) !!}",
            order: [],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'type', name: 'type', orderable: false },
                { data: 'area', name: 'area', orderable: false },
                { data: 'datetime', name: 'datetime', orderable: false },
                { data: 'file', name: 'file', orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.delete-history-btn', function() {
            var file = $(this).data('file');
            if (confirm('Hapus riwayat ' + file + '?')) {
                $.ajax({
                    url: "{{ url('admin/marshallings/history') }}/" + encodeURIComponent(file),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus riwayat.');
                    }
                });
            }
        });
    });
</script>
@endsection
