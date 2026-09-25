@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Edit Kategori (Main Type)</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.main-types.update', $mainType->Id_Main_Type) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori (Main Type)</label>
                        <input type="text" name="Main_Type" class="form-control" value="{{ $mainType->Main_Type }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.main-types.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
