@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Edit User Admin</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
