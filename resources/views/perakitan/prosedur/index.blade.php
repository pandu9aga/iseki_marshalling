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
                <a href="{{ route('perakitan.prosedur.show', $tractor) }}" class="text-decoration-none">
                    <div class="card card-stats card-round border-0 shadow-sm h-100 table-hover">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-primary bubble-shadow-small">
                                        <i class="fas fa-tractor"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Tipe Traktor</p>
                                        <h4 class="card-title">{{ $tractor }}</h4>
                                    </div>
                                </div>
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
