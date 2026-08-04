@extends('layouts.main')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary">Prosedur Perakitan</h4>
        </div>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i> Prosedur hanya menampilkan yang ter-assign pada training ASPRO untuk NIK Anda.
        </div>

        <div class="row">
            @forelse($tractors as $tractor)
            <div class="col-md-4 col-sm-6 mb-4">
                <a href="{{ route('perakitan.prosedur.show', $tractor->name) }}" class="text-decoration-none">
                    <div class="card card-stats card-round border-0 shadow-sm h-100 table-hover">
                        <div class="card-body">
                            <div class="text-center">
                                @if($tractor->photo)
                                <div class="mb-2 d-flex align-items-center justify-content-center bg-white">
                                    <img src="{{ $tractor->photo }}" alt="{{ $tractor->name }}" style="max-width:100%;max-height:150px;object-fit:contain;" onerror="this.style.display='none'">
                                </div>
                                @endif
                                <p class="card-category mb-1">Tipe Traktor</p>
                                <h4 class="card-title">{{ $tractor->name }}</h4>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <div class="col-12">
                <div class="alert alert-warning text-center shadow-sm">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p class="mb-0 fw-bold">Belum ada prosedur yang diassign untuk NIK Anda.</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
