@extends('layouts.main')

@section('style')
<style>
    .member-card-photo {
        width: 110px;
        height: 140px;
        object-fit: cover;
        border-radius: 8px;
    }
    .member-photo-placeholder-sm {
        width: 110px;
        height: 140px;
        border-radius: 8px;
    }
    .record-comment-box {
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }
    .record-comment-box:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title text-primary"><i class="fas fa-comment-dots me-2"></i>Part Kurang</h4>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Scan QR Kanban</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Scan QR dari label produksi</label>
                            <input type="text" id="scannerInput" class="form-control" placeholder="Scan QR Code dengan USB scanner..." autofocus style="text-transform: uppercase;">
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Sequence No</label>
                                <input type="text" id="sequence_no" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Production Date</label>
                                <input type="text" id="production_date" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type</label>
                                <input type="text" id="type" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="resultArea" class="mt-4" style="display:none;"></div>
    </div>
</div>
@endsection

@section('script')
<script>
    $('#scannerInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            processScan($(this).val());
        }
    });

    function processScan(text) {
        if (!text) return;
        text = text.toUpperCase().trim();
        var parts = text.split(';');
        if (parts.length >= 3) {
            $('#sequence_no').val(parts[0]);
            $('#production_date').val(parts[1]);
            $('#type').val(parts[2]);
            $('#scannerInput').val('');
            searchRecords(parts[0], parts[1]);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Format QR Tidak Valid',
                text: 'Format: Sequence_No;Production_Date;Type',
                confirmButtonColor: '#F36494'
            });
            $('#scannerInput').val('');
        }
    }

    function searchRecords(sequenceNo, productionDate) {
        $('#resultArea').hide().html(
            '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Mencari data marshalling & member...</p></div>'
        ).fadeIn(200);

        $.getJSON('{{ route("perakitan.comment.search") }}', {
            sequence_no: sequenceNo,
            production_date: productionDate
        }, function(res) {
            if (!res.found) {
                $('#resultArea').html(
                    '<div class="alert alert-warning text-center shadow-sm">' +
                    '<i class="fas fa-exclamation-triangle fa-2x mb-2"></i>' +
                    '<p class="mb-0 fw-bold">' + res.message + '</p></div>'
                ).fadeIn(200);
                return;
            }

            var html = '<div class="row g-3">';
            $.each(res.records, function(i, r) {
                html += '<div class="col-lg-6">';
                html += '  <div class="card h-100 border record-comment-box">';
                html += '    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">';
                html += '      <span class="badge bg-primary fs-6">' + escHtml(r.Area_Label) + '</span>';
                html += '      <small class="text-muted"><i class="far fa-clock me-1"></i>' + escHtml(r.Time_Record) + '</small>';
                html += '    </div>';
                html += '    <div class="card-body">';

                // Member Marshalling Profile
                html += '      <div class="d-flex align-items-center mb-3 p-2 bg-light rounded border">';
                if (r.Member_Photo) {
                    html += '        <img src="' + r.Member_Photo + '" class="member-card-photo border me-3" onerror="this.style.display=\'none\'">';
                } else {
                    html += '        <div class="member-photo-placeholder-sm bg-white d-flex align-items-center justify-content-center border me-3"><i class="fas fa-user fa-3x text-secondary"></i></div>';
                }
                html += '        <div>';
                html += '          <span class="badge bg-secondary mb-1">Member Marshalling</span>';
                html += '          <h5 class="mb-0 fw-bold text-dark">' + escHtml(r.Member_Name) + '</h5>';
                html += '          <small class="text-muted d-block">NIK: ' + escHtml(r.Member_Nik) + '</small>';
                html += '          <small class="text-muted d-block">Traktor: <strong>' + escHtml(r.Type) + '</strong> | Seq: <strong>' + escHtml(r.Sequence_No) + '</strong></small>';
                html += '        </div>';
                html += '      </div>';

                // Existing comment display
                html += '      <div id="commentDisplay_' + r.Id_Record + '" class="mb-3" ' + (r.Perakitan_Comment ? '' : 'style="display:none;"') + '>';
                html += '        <label class="form-label text-success fw-bold"><i class="fas fa-check-circle me-1"></i>Keterangan Part Kurang Tersimpan:</label>';
                html += '        <div class="p-2 border rounded bg-white text-dark">';
                html += '          <p class="mb-1 text-comment-content">' + (r.Perakitan_Comment ? escHtml(r.Perakitan_Comment) : '') + '</p>';
                html += '          <small class="text-muted text-comment-meta">';
                if (r.Perakitan_Comment) {
                    html += '<i class="fas fa-user-edit me-1"></i>' + escHtml(r.Perakitan_Name) + ' (' + escHtml(r.Perakitan_Nik) + ') &bull; ' + escHtml(r.Perakitan_Comment_Time);
                }
                html += '          </small>';
                html += '        </div>';
                html += '      </div>';

                // Comment form
                html += '      <form onsubmit="submitComment(event, ' + r.Id_Record + ')">';
                html += '        <div class="mb-2">';
                html += '          <label class="form-label fw-bold"><i class="fas fa-pen me-1"></i>' + (r.Perakitan_Comment ? 'Ubah Catatan Part Kurang' : 'Input Catatan Part Kurang') + ':</label>';
                html += '          <textarea id="commentInput_' + r.Id_Record + '" class="form-control" rows="2" placeholder="Tuliskan catatan part kurang untuk record ini..." required>' + (r.Perakitan_Comment ? escHtml(r.Perakitan_Comment) : '') + '</textarea>';
                html += '        </div>';
                html += '        <div class="text-end">';
                html += '          <button type="submit" id="submitBtn_' + r.Id_Record + '" class="btn btn-primary btn-sm">';
                html += '            <i class="fas fa-save me-1"></i>Simpan';
                html += '          </button>';
                html += '        </div>';
                html += '      </form>';

                html += '    </div>';
                html += '  </div>';
                html += '</div>';
            });
            html += '</div>';
            $('#resultArea').html(html).fadeIn(200);
        }).fail(function() {
            $('#resultArea').html(
                '<div class="alert alert-danger text-center"><i class="fas fa-times-circle fa-2x mb-2"></i><p class="mb-0">Terjadi kesalahan saat mencari data.</p></div>'
            ).fadeIn(200);
        });
    }

    function submitComment(e, recordId) {
        e.preventDefault();
        var comment = $('#commentInput_' + recordId).val();
        var btn = $('#submitBtn_' + recordId);

        if (!comment.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Komentar tidak boleh kosong.',
                confirmButtonColor: '#F36494'
            });
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

        $.ajax({
            url: '{{ url("perakitan/comment") }}/' + recordId + '/store',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                comment: comment
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Simpan');
                if (res.success) {
                    var displayDiv = $('#commentDisplay_' + recordId);
                    displayDiv.find('.text-comment-content').text(res.Perakitan_Comment);
                    displayDiv.find('.text-comment-meta').html('<i class="fas fa-user-edit me-1"></i>' + escHtml(res.Perakitan_Name) + ' (' + escHtml(res.Perakitan_Nik) + ') &bull; ' + escHtml(res.Perakitan_Comment_Time));
                    displayDiv.slideDown();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Catatan part kurang berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Simpan');
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan saat menyimpan catatan part kurang.',
                    confirmButtonColor: '#F36494'
                });
            }
        });
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endsection
