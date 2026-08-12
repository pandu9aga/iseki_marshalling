@extends('layouts.main')

@section('style')
<style>
    .member-autocomplete { position: relative; }
    #memberSuggestions {
        position: absolute; z-index: 1050; width: 100%;
        max-height: 260px; overflow-y: auto;
        background: #fff; border: 1px solid #ddd; border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
    }
    #memberSuggestions .suggestion-item {
        padding: 10px 12px; cursor: pointer;
        border-bottom: 1px solid #f1f1f1;
    }
    #memberSuggestions .suggestion-item:hover { background: #f8bbd0; }
    #memberSuggestions .suggestion-item .suggestion-nik { font-size: 12px; color: #6c757d; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary mb-0">Member Area</h4>
            <small>Atur area kerja member. Satu area bisa untuk banyak member, satu member bisa di banyak area.</small>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.member-areas.store') }}" method="POST" class="row g-2 align-items-end" id="addMemberAreaForm">
                    @csrf
                    <input type="hidden" name="nik" id="selectedNik" value="">
                    <div class="col-md-4">
                        <label class="form-label">Nama Member</label>
                        <div class="member-autocomplete">
                            <input type="text" id="memberNameInput" class="form-control" placeholder="Ketik nama member..." autocomplete="off" required>
                            <div id="memberSuggestions"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Area</label>
                        <select name="area" id="areaSelect" class="form-control" required>
                            <option value="">Pilih Area</option>
                            @foreach($validAreas as $area)
                            <option value="{{ $area }}">{{ ucwords(str_replace('_', ' ', $area)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary" id="addBtn" disabled><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="memberAreasTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Member</th>
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
        var table = $('#memberAreasTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: "{{ url('admin/member-areas') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'nik', name: 'nik' },
                { data: 'member_name', name: 'member_name', orderable: false },
                { data: 'area', name: 'area' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        var searchTimer = null;
        var suggestions = $('#memberSuggestions');

        $('#memberNameInput').on('input', function() {
            var q = $(this).val().trim();
            $('#selectedNik').val('');
            updateAddBtn();
            clearTimeout(searchTimer);
            if (q.length < 2) {
                suggestions.hide().empty();
                return;
            }
            searchTimer = setTimeout(function() {
                $.ajax({
                    url: "{{ route('admin.member-areas.search') }}",
                    data: { q: q },
                    type: 'GET',
                    success: function(res) {
                        suggestions.empty();
                        if (!res || res.length === 0) {
                            suggestions.append('<div class="suggestion-item text-muted">Tidak ditemukan</div>').show();
                            return;
                        }
                        $.each(res, function(i, m) {
                            var item = $('<div class="suggestion-item"></div>');
                            item.append('<div>' + m.nama + '</div>');
                            item.append('<div class="suggestion-nik">NIK: ' + m.nik + '</div>');
                            item.on('click', function() {
                                $('#selectedNik').val(m.nik);
                                $('#memberNameInput').val(m.nama);
                                suggestions.hide().empty();
                                updateAddBtn();
                            });
                            suggestions.append(item);
                        });
                        suggestions.show();
                    }
                });
            }, 250);
        });

        function updateAddBtn() {
            $('#addBtn').prop('disabled', !($('#selectedNik').val() && $('#areaSelect').val()));
        }

        $('#areaSelect').on('change', updateAddBtn);

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.member-autocomplete').length) {
                suggestions.hide();
            }
        });

        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            if (confirm('Hapus member dari area ini?')) {
                $.ajax({
                    url: "{{ url('admin/member-areas') }}/" + id,
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