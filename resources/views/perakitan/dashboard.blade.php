@extends('layouts.main')

@section('style')
<style>
    .summary-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary mb-0">Dashboard Perakitan</h4>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-user-cog fa-4x text-primary mb-3"></i>
                        <h5 class="fw-bold">Selamat Datang, {{ $user->nama }}</h5>
                        <p class="text-muted mb-0">Anda login sebagai Perakitan</p>
                        <p class="text-muted">NIK: {{ $user->nik }} | Team: {{ $user->team }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
