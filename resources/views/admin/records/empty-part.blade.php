@extends('layouts.main')

@section('style')
<style>
    #emptyTable .badge { font-size: 11px; padding: 3px 6px; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">Empty Part List</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Date</label>
                        <input type="date" id="filter_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label>Member</label>
                        <select id="filter_member" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Area</label>
                        <select id="filter_area" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($areas as $a)
                            <option value="{{ $a }}">{{ ucwords(str_replace('_', ' ', $a)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Type</label>
                        <select id="filter_type" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($types as $t)
                            <option value="{{ $t->Type }}">{{ $t->Type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="emptyTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Member</th>
                                <th>Sequence Record</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Qty Record</th>
                                <th>Code Part</th>
                                <th>Name Part</th>
                                <th>Difference</th>
                                <th>Box</th>
                                <th>Area</th>
                                <th>Production Date</th>
                                <th>Time Record</th>
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
        var table = $('#emptyTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('admin/empty-part') }}",
                data: function(d) {
                    d.filter_date = $('#filter_date').val();
                    d.filter_member = $('#filter_member').val();
                    d.filter_area = $('#filter_area').val();
                    d.filter_type = $('#filter_type').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'member_name', name: 'member_name' },
                { data: 'sequence_record', name: 'sequence_record' },
                { data: 'type_record', name: 'type_record' },
                { data: 'Qty', name: 'Qty' },
                { data: 'Qty_Record', name: 'Qty_Record' },
                { data: 'Code_Part', name: 'Code_Part' },
                { data: 'Name_Part', name: 'Name_Part' },
                { data: 'Difference', name: 'Difference' },
                { data: 'Box', name: 'Box' },
                { data: 'area_record', name: 'area_record' },
                { data: 'production_date', name: 'production_date' },
                { data: 'time_record', name: 'time_record' }
            ]
        });

        $('#filter_date, #filter_member, #filter_area, #filter_type').on('change', function() {
            table.ajax.reload();
        });
    });
</script>
@endsection
