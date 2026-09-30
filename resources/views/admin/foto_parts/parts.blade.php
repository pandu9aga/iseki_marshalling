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
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Sequence</th>
                                <th>Nama Part</th>
                                <th>Code Rack</th>
                                <th>Qty</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($marshallings as $i => $m)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $m->Sequence_No }}</td>
                                <td>{{ $m->Name_Part }}</td>
                                <td>{{ $m->Code_Rack }}</td>
                                <td>{{ $m->Qty }}</td>
                                <td>
                                    @if($m->fotoPart)
                                        <button type="button" class="btn btn-primary btn-sm btn-view-photo" data-photo-url="{{ asset($m->fotoPart->Photo_Path) }}" title="Lihat Foto">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @else
                                        <span class="text-muted small me-1">Belum ada foto</span>
                                    @endif
                                    <button type="button" class="btn btn-info btn-sm btn-upload-photo" data-marshalling-id="{{ $m->Id_Marshalling }}" title="Upload Foto">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada part untuk box ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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

<!-- Modal Upload Foto -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="uploadPhotoForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="marshalling_id" id="uploadMarshallingId">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Foto Part</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Foto</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" required>
                        <small class="text-muted">Foto akan otomatis dikonversi ke JPG dan dikompres di bawah 1MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var viewPhotoModal = new bootstrap.Modal(document.getElementById('viewPhotoModal'));
        var uploadPhotoModal = new bootstrap.Modal(document.getElementById('uploadPhotoModal'));

        $(document).on('click', '.btn-view-photo', function() {
            $('#viewPhotoImg').attr('src', $(this).data('photo-url'));
            viewPhotoModal.show();
        });

        $(document).on('click', '.btn-upload-photo', function() {
            $('#uploadMarshallingId').val($(this).data('marshalling-id'));
            $('#uploadPhotoForm')[0].reset();
            uploadPhotoModal.show();
        });

        $('#uploadPhotoForm').on('submit', function(e) {
            e.preventDefault();
            var marshallingId = $('#uploadMarshallingId').val();
            var formData = new FormData(this);
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Uploading...');

            $.ajax({
                url: "{{ url('admin/foto-parts/part') }}/" + marshallingId + "/upload",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    uploadPhotoModal.hide();
                    location.reload();
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Upload gagal. Silahkan coba lagi.';
                    alert(msg);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Upload');
                }
            });
        });
    });
</script>
@endsection
