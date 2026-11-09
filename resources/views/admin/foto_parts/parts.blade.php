@extends('layouts.main')

@section('style')
<style>
    #viewPhotoModal .modal-body {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #000;
        padding: 0;
    }
    .part-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .part-card:hover { transform: translateY(-4px); box-shadow: 0 0.5rem 1rem rgba(243, 100, 148, 0.25); }
    .part-photo-wrap { height: 180px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .part-photo-wrap img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; }
    .part-no-photo { color: #aaa; text-align: center; font-size: 0.85rem; }
    #viewPhotoModal img {
        max-width: 100%;
        max-height: 100vh;
        object-fit: contain;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">
                Foto Part - {{ $type->Type }} - {{ ucwords(str_replace('_', ' ', $area)) }} - Box {{ $box }}
            </h4>
            <div>
                <a href="{{ route('admin.foto-parts.boxes', ['type' => $type->Id_Type, 'area' => $area]) }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-muted small">Foto dikelola di aplikasi Recorder.</p>
                <div class="row">
                    @forelse($marshallings as $m)
                    <div class="col-md-3 col-sm-4 col-6 mb-3">
                        <div class="card h-100 border-primary part-card">
                            <div class="part-photo-wrap">
                                @if($m->photo_url)
                                    {{-- Foto dari Recorder; kalau tidak ada (404) tampilkan placeholder --}}
                                    <img src="{{ $m->photo_url }}" alt="{{ $m->Code_Rack }}" class="btn-view-photo" data-photo-url="{{ $m->photo_url }}"
                                         loading="lazy" onerror="this.replaceWith(document.getElementById('noPhotoTpl').content.cloneNode(true))">
                                @else
                                    <div class="part-no-photo"><i class="fas fa-image fa-2x mb-1"></i><br>Belum ada foto</div>
                                @endif
                            </div>
                            <div class="card-body p-2">
                                <h6 class="mb-1 text-primary">{{ $m->Code_Rack }}</h6>
                                <div class="small">{{ $m->Name_Part }}</div>
                                <div class="small text-muted">Seq {{ $m->Sequence_No }} · Qty {{ $m->Qty }}</div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted">Belum ada part untuk box ini.</div>
                    @endforelse
                    <template id="noPhotoTpl"><div class="part-no-photo"><i class="fas fa-image fa-2x mb-1"></i><br>Belum ada foto</div></template>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lihat Foto (Full Screen) -->
<div class="modal fade" id="viewPhotoModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Foto Part</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <img id="viewPhotoImg" src="" alt="Foto Part">
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        var viewPhotoModal = new bootstrap.Modal(document.getElementById('viewPhotoModal'));

        $(document).on('click', '.btn-view-photo', function() {
            $('#viewPhotoImg').attr('src', $(this).data('photo-url'));
            viewPhotoModal.show();
        });
    });
</script>
@endsection
