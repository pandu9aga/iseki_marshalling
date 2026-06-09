@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Create Type Traktor</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.types.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">ID Type</label>
                        <input type="number" name="Id_Type" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <input type="text" name="Type" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('admin.types.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
