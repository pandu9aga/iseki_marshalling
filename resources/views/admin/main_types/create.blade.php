@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Create Kategori (Main Type)</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.main-types.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori (Main Type)</label>
                        <input type="text" name="Main_Type" class="form-control" placeholder="Contoh: NT Series" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('admin.main-types.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
