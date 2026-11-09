@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Edit Type Traktor</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.types.update', $type->Id_Type) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Kategori (Main Type)</label>
                        <select name="Id_Main_Type" class="form-control">
                            <option value="">-- Pilih Kategori (Opsional) --</option>
                            @foreach($mainTypes as $mt)
                                <option value="{{ $mt->Id_Main_Type }}" {{ $type->Id_Main_Type == $mt->Id_Main_Type ? 'selected' : '' }}>{{ $mt->Main_Type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ID Type</label>
                        <input type="number" name="Id_Type" class="form-control" value="{{ $type->Id_Type }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <input type="text" name="Type" class="form-control" value="{{ $type->Type }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.types.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
