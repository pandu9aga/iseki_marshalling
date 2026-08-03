@extends('layouts.main')

@section('style')
<style>
    .pdf-page-section {
        transition: opacity 0.2s;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="page-title text-primary mb-0">Preview Prosedur</h4>
                <small class="text-muted">
                    Traktor: {{ $tractor }} | Member: {{ Auth::guard('perakitan')->user()->nama ?? '-' }}
                </small>
            </div>
            <a href="{{ route('perakitan.prosedur.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i> Berikut adalah preview prosedur untuk tipe traktor <strong>{{ $tractor }}</strong>
        </div>

        @php
        $pdfCount = $pdfs->count();
        $pdfChunks = $pdfs->chunk(4);
        $pdfTotalPages = $pdfChunks->count();
        @endphp

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                <h5 class="card-title mb-0 text-white" style="font-size: 1rem;">
                    <i class="fas fa-file-pdf me-2"></i>Daftar Prosedur
                </h5>
                @if($pdfCount > 0)
                <span class="badge bg-light text-primary">Total: {{ $pdfCount }} Prosedur</span>
                @endif
            </div>
            <div class="card-body p-3">
                @if($pdfCount === 0)
                <div class="text-center text-muted py-4">
                    <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                    Tidak ada prosedur training yang tersedia untuk traktor {{ $tractor }}.
                </div>
                @else
                @foreach($pdfChunks as $pageIndex => $chunk)
                <div class="pdf-page-section {{ $pageIndex > 0 ? 'd-none' : '' }}" id="pdf-page-{{ $pageIndex + 1 }}">
                    <div class="row g-3">
                        @foreach($chunk as $pdf)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border shadow-sm">
                                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                    <strong class="text-truncate text-dark" style="max-width: 75%; font-size: 0.85rem;" title="{{ $pdf->name }}">
                                        <i class="far fa-file-pdf text-danger me-1"></i> {{ $pdf->name }}
                                    </strong>
                                    <small class="badge bg-info text-white" style="font-size:0.7rem;">{{ $pdf->tractor }} - {{ $pdf->area }}</small>
                                </div>
                                <div class="card-body p-1" style="height: 380px;">
                                    <iframe src="{{ $pdf->url }}" width="100%" height="100%" style="border: none; border-radius: 4px;"></iframe>
                                </div>
                                <div class="card-footer py-1 px-3 bg-white text-end">
                                    <a href="{{ $pdf->url }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                        <i class="fas fa-external-link-alt me-1"></i> Buka Fullscreen
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                @if($pdfTotalPages > 1)
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-prev-pdf" onclick="changePdfPage(-1)" disabled>
                        <i class="fas fa-chevron-left me-1"></i> Sebelumnya
                    </button>
                    <span class="text-muted fw-bold" style="font-size: 0.85rem;">
                        Halaman <span id="pdf-current-page">1</span> dari {{ $pdfTotalPages }}
                    </span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-next-pdf" onclick="changePdfPage(1)">
                        Selanjutnya <i class="fas fa-chevron-right ms-1"></i>
                    </button>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    var currentPdfPage = 1;
    var totalPdfPages = {
        {
            $pdfTotalPages
        }
    };

    function changePdfPage(direction) {
        var newPage = currentPdfPage + direction;
        if (newPage < 1 || newPage > totalPdfPages) return;

        $('#pdf-page-' + currentPdfPage).addClass('d-none');
        $('#pdf-page-' + newPage).removeClass('d-none');

        currentPdfPage = newPage;
        $('#pdf-current-page').text(currentPdfPage);

        $('#btn-prev-pdf').prop('disabled', currentPdfPage === 1);
        $('#btn-next-pdf').prop('disabled', currentPdfPage === totalPdfPages);
    }
</script>
@endsection