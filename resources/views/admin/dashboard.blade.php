@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Admin Dashboard</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <p>Welcome to Marshalling System, <strong>{{ Auth::guard('admin')->user()->name }}</strong>.</p>
            </div>
        </div>
    </div>
</div>
@endsection
