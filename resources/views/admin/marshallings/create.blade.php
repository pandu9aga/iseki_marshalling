@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Create Marshalling</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.marshallings.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="Id_Type" class="form-control" required>
                                <option value="">Select Type</option>
                                @foreach($types as $type)
                                <option value="{{ $type->Id_Type }}" {{ $selectedTypeId == $type->Id_Type ? 'selected' : '' }}>{{ $type->Type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sequence No</label>
                            <input type="number" name="Sequence_No" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Code Part</label>
                            <input type="text" name="Code_Part" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name Part</label>
                            <input type="text" name="Name_Part" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Code Rack</label>
                            <input type="text" name="Code_Rack" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Difference</label>
                            <input type="text" name="Difference" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location Rack</label>
                            <input type="text" name="Location_Rack" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Box</label>
                            <input type="text" name="Box" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Qty</label>
                            <input type="number" name="Qty" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mode</label>
                            <select name="Mode" class="form-control" required>
                                <option value="manual">Manual</option>
                                <option value="ai">AI</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Area</label>
                            <select name="Area" class="form-control" required>
                                <option value="">Select Area</option>
                                <option value="sub_assy">Sub Assy</option>
                                <option value="sub_engine">Sub Engine</option>
                                <option value="transmisi">Transmisi</option>
                                <option value="main_line">Main Line</option>
                                <option value="mowcol">Mowcol</option>
                                <option value="front_axle">Front Axle</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('admin.marshallings.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
