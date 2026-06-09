@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Edit Marshalling</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.marshallings.update', $marshalling->Id_Marshalling) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="Id_Type" class="form-control" required>
                                <option value="">Select Type</option>
                                @foreach($types as $type)
                                <option value="{{ $type->Id_Type }}" {{ $marshalling->Id_Type == $type->Id_Type ? 'selected' : '' }}>{{ $type->Type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sequence No</label>
                            <input type="number" name="Sequence_No" class="form-control" value="{{ $marshalling->Sequence_No }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Code Part</label>
                            <input type="text" name="Code_Part" class="form-control" value="{{ $marshalling->Code_Part }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name Part</label>
                            <input type="text" name="Name_Part" class="form-control" value="{{ $marshalling->Name_Part }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Code Rack</label>
                            <input type="text" name="Code_Rack" class="form-control" value="{{ $marshalling->Code_Rack }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Difference</label>
                            <input type="text" name="Difference" class="form-control" value="{{ $marshalling->Difference }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location Rack</label>
                            <input type="text" name="Location_Rack" class="form-control" value="{{ $marshalling->Location_Rack }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Box</label>
                            <input type="text" name="Box" class="form-control" value="{{ $marshalling->Box }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Qty</label>
                            <input type="number" name="Qty" class="form-control" value="{{ $marshalling->Qty }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mode</label>
                            <select name="Mode" class="form-control" required>
                                <option value="manual" {{ $marshalling->Mode == 'manual' ? 'selected' : '' }}>Manual</option>
                                <option value="ai" {{ $marshalling->Mode == 'ai' ? 'selected' : '' }}>AI</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Area</label>
                            <select name="Area" class="form-control" required>
                                <option value="">Select Area</option>
                                <option value="sub_assy" {{ $marshalling->Area == 'sub_assy' ? 'selected' : '' }}>Sub Assy</option>
                                <option value="sub_engine" {{ $marshalling->Area == 'sub_engine' ? 'selected' : '' }}>Sub Engine</option>
                                <option value="transmisi" {{ $marshalling->Area == 'transmisi' ? 'selected' : '' }}>Transmisi</option>
                                <option value="main_line" {{ $marshalling->Area == 'main_line' ? 'selected' : '' }}>Main Line</option>
                                <option value="mowcol" {{ $marshalling->Area == 'mowcol' ? 'selected' : '' }}>Mowcol</option>
                                <option value="front_axle" {{ $marshalling->Area == 'front_axle' ? 'selected' : '' }}>Front Axle</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.marshallings.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
