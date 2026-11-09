@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">Foto Part</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="fotoPartsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kategori (Main Type)</th>
                                <th>Type</th>
                                <th>Location (Area)</th>
                                <th>List Marshalling</th>
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
        $('#fotoPartsTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: "{{ url('admin/foto-parts') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'main_type', name: 'mainType.Main_Type' },
                { data: 'Type', name: 'Type' },
                { data: 'location_areas', name: 'location_areas', orderable: false, searchable: false },
                { data: 'list_marshalling', name: 'marshallings_count', searchable: false },
            ]
        });
    });
</script>
@endsection
