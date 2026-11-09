@extends('layouts.main')

@section('style')
<style>
    @keyframes boxFadeIn {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .box-card {
        opacity: 0;
        animation: boxFadeIn 0.4s ease-out forwards;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .box-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(243, 100, 148, 0.25);
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">Foto Part - {{ $type->Type }} - {{ ucwords(str_replace('_', ' ', $area)) }}</h4>
            <div>
                <a href="{{ route('admin.foto-parts.index') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    @forelse($boxes as $i => $box)
                    <div class="col-md-3 col-sm-4 col-6 mb-3">
                        <a href="{{ route('admin.foto-parts.parts', ['type' => $type->Id_Type, 'area' => $area, 'box' => $box]) }}"
                           class="card text-center border-primary box-card text-decoration-none" style="animation-delay: {{ $i * 0.08 }}s">
                            <div class="card-body">
                                <i class="fas fa-box fa-2x text-primary mb-2"></i>
                                <h5 class="mb-0 text-primary">Box {{ $box }}</h5>
                            </div>
                        </a>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted">Belum ada box untuk area ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
