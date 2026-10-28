@extends('layouts.main')

@section('style')
    <style>
        .member-autocomplete {
            position: relative;
        }

        #memberSuggestions {
            position: absolute;
            z-index: 1050;
            width: 100%;
            max-height: 260px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
        }

        #memberSuggestions .suggestion-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
        }

        #memberSuggestions .suggestion-item:hover {
            background: #f8bbd0;
        }

        #memberSuggestions .suggestion-item .suggestion-nik {
            font-size: 12px;
            color: #6c757d;
        }
    </style>
@endsection


@section('content')
    <div class="container">
        <div class="page-inner">

            {{-- Page Header --}}
            <div class="page-header">
                <h4 class="page-title text-primary mb-0">
                    Member Perakitan
                </h4>

                <small>
                    Atur area &amp; box kerja member perakitan.
                    Satu member bisa di banyak area/box.
                </small>
            </div>


            {{-- Add Member --}}
            <div class="card mb-3">
                <div class="card-body">

                    <form
                        action="{{ route('admin.member-perakitans.store') }}"
                        method="POST"
                        class="row g-2 align-items-end"
                        id="addMemberPerakitanForm"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="nik"
                            id="selectedNik"
                            value=""
                        >

                        {{-- Member --}}
                        <div class="col-md-3">
                            <label class="form-label">
                                Nama Member
                            </label>

                            <div class="member-autocomplete">

                                <input
                                    type="text"
                                    id="memberNameInput"
                                    class="form-control"
                                    placeholder="Ketik nama member..."
                                    autocomplete="off"
                                    required
                                >

                                <div id="memberSuggestions"></div>

                            </div>
                        </div>


                        {{-- Area --}}
                        <div class="col-md-3">
                            <label class="form-label">
                                Area
                            </label>

                            <select
                                name="area"
                                id="areaSelect"
                                class="form-control"
                                required
                            >
                                <option value="">
                                    Pilih Area
                                </option>

                                @foreach($validAreas as $area => $boxes)
                                    <option value="{{ $area }}">
                                        {{ ucwords(str_replace('_', ' ', $area)) }}
                                    </option>
                                @endforeach

                            </select>
                        </div>


                        {{-- Box --}}
                        <div class="col-md-3">
                            <label class="form-label">
                                Box
                            </label>

                            <select
                                name="box"
                                id="boxInput"
                                class="form-control"
                                required
                                disabled
                            >
                                <option value="">
                                    Pilih Area terlebih dahulu
                                </option>
                            </select>
                        </div>


                        {{-- Submit --}}
                        <div class="col-auto">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                id="addBtn"
                                disabled
                            >
                                <i class="fas fa-plus"></i>
                                Tambah
                            </button>
                        </div>

                    </form>

                </div>
            </div>


            {{-- Table --}}
            <div class="card">
                <div class="card-body">

                    <div class="table-responsive">

                        <table
                            id="memberPerakitansTable"
                            class="table table-bordered table-striped"
                        >
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Member</th>
                                <th>Area</th>
                                <th>Box</th>
                                <th>Action</th>
                            </tr>
                            </thead>

                        </table>

                    </div>

                </div>
            </div>

        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- EDIT MODAL --}}
    {{-- ========================================================= --}}

    <div
        class="modal fade"
        id="editMemberPerakitanModal"
        tabindex="-1"
    >
        <div class="modal-dialog modal-sm modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header py-2">

                    <h6 class="modal-title fw-bold">
                        <i class="fas fa-edit me-2 text-primary"></i>
                        Edit Member Perakitan
                    </h6>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <form id="editMemberPerakitanForm">

                    @csrf
                    @method('PUT')

                    <input
                        type="hidden"
                        id="editId"
                        value=""
                    >


                    <div class="modal-body">

                        {{-- Edit Area --}}
                        <div class="mb-2">

                            <label class="form-label">
                                Area
                            </label>

                            <select
                                name="area"
                                id="editAreaSelect"
                                class="form-control"
                                required
                            >
                                <option value="">
                                    Pilih Area
                                </option>

                                @foreach($validAreas as $area => $boxes)
                                    <option value="{{ $area }}">
                                        {{ ucwords(str_replace('_', ' ', $area)) }}
                                    </option>
                                @endforeach

                            </select>

                        </div>


                        {{-- Edit Box --}}
                        <div class="mb-2">

                            <label class="form-label">
                                Box
                            </label>

                            <select
                                name="box"
                                id="editBoxInput"
                                class="form-control"
                                required
                                disabled
                            >
                                <option value="">
                                    Pilih Area terlebih dahulu
                                </option>
                            </select>

                        </div>

                    </div>


                    <div class="modal-footer py-1">

                        <button
                            type="button"
                            class="btn btn-secondary btn-sm"
                            data-bs-dismiss="modal"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            id="saveEditBtn"
                            class="btn btn-primary btn-sm"
                        >
                            <i class="fas fa-save me-1"></i>
                            Simpan
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>


@endsection


@section('script')
    <script>

        $(document).ready(function () {

            /*
            |--------------------------------------------------------------------------
            | AREA -> BOX DATA
            |--------------------------------------------------------------------------
            */

            const validAreas = @json($validAreas);

            console.log('Valid Areas:', validAreas);


            /*
            |--------------------------------------------------------------------------
            | DATATABLE
            |--------------------------------------------------------------------------
            */

            var table = $('#memberPerakitansTable').DataTable({

                pageLength: 50,

                lengthMenu: [
                    10,
                    25,
                    50,
                    100
                ],

                processing: true,

                serverSide: true,

                ajax: "{{ url('admin/member-perakitans') }}",

                columns: [

                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: 'nik',
                        name: 'nik'
                    },

                    {
                        data: 'member_name',
                        name: 'member_name',
                        orderable: false
                    },

                    {
                        data: 'area',
                        name: 'area'
                    },

                    {
                        data: 'box',
                        name: 'box'
                    },

                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }

                ]

            });


            /*
            |--------------------------------------------------------------------------
            | MEMBER AUTOCOMPLETE
            |--------------------------------------------------------------------------
            */

            var searchTimer = null;

            var suggestions = $('#memberSuggestions');


            $('#memberNameInput').on('input', function () {

                var q = $(this).val().trim();

                $('#selectedNik').val('');

                updateAddBtn();

                clearTimeout(searchTimer);


                if (q.length < 2) {

                    suggestions
                        .hide()
                        .empty();

                    return;
                }


                searchTimer = setTimeout(function () {

                    $.ajax({

                        url: "{{ route('admin.member-perakitans.search') }}",

                        data: {
                            q: q
                        },

                        type: 'GET',

                        success: function (res) {

                            suggestions.empty();


                            if (!res || res.length === 0) {

                                suggestions
                                    .append(
                                        '<div class="suggestion-item text-muted">' +
                                        'Tidak ditemukan' +
                                        '</div>'
                                    )
                                    .show();

                                return;
                            }


                            $.each(res, function (i, m) {

                                var item =
                                    $('<div class="suggestion-item"></div>');


                                item.append(
                                    '<div>' +
                                    m.nama +
                                    '</div>'
                                );


                                item.append(
                                    '<div class="suggestion-nik">' +
                                    'NIK: ' +
                                    m.nik +
                                    '</div>'
                                );


                                item.on('click', function () {

                                    $('#selectedNik').val(m.nik);

                                    $('#memberNameInput').val(m.nama);

                                    suggestions
                                        .hide()
                                        .empty();

                                    updateAddBtn();

                                });


                                suggestions.append(item);

                            });


                            suggestions.show();

                        }

                    });

                }, 250);

            });


            /*
            |--------------------------------------------------------------------------
            | ADD BUTTON
            |--------------------------------------------------------------------------
            */

            function updateAddBtn() {

                var valid =
                    $('#selectedNik').val() &&
                    $('#areaSelect').val() &&
                    $('#boxInput').val();

                $('#addBtn').prop(
                    'disabled',
                    !valid
                );

            }


            /*
            |--------------------------------------------------------------------------
            | AREA -> BOX FOR ADD FORM
            |--------------------------------------------------------------------------
            */

            $('#areaSelect').on('change', function () {

                const area = $(this).val();

                const boxes =
                    validAreas[area] || [];

                const boxSelect =
                    $('#boxInput');


                boxSelect.empty();


                if (!area) {

                    boxSelect
                        .append(
                            '<option value="">Pilih Area terlebih dahulu</option>'
                        )
                        .prop('disabled', true);

                    updateAddBtn();

                    return;
                }


                boxSelect
                    .append(
                        '<option value="">Pilih Box</option>'
                    )
                    .prop('disabled', false);


                boxes.forEach(function (box) {

                    boxSelect.append(
                        $('<option>', {
                            value: box,
                            text: box
                        })
                    );

                });


                updateAddBtn();

            });


            /*
            |--------------------------------------------------------------------------
            | BOX CHANGE
            |--------------------------------------------------------------------------
            */

            $('#boxInput').on('change', function () {

                updateAddBtn();

            });


            /*
            |--------------------------------------------------------------------------
            | CLOSE AUTOCOMPLETE
            |--------------------------------------------------------------------------
            */

            $(document).on('click', function (e) {

                if (
                    !$(e.target)
                        .closest('.member-autocomplete')
                        .length
                ) {

                    suggestions.hide();

                }

            });


            /*
            |--------------------------------------------------------------------------
            | OPEN EDIT MODAL
            |--------------------------------------------------------------------------
            */

            $(document).on('click', '.edit-btn', function () {

                const id =
                    $(this).data('id');

                const area =
                    $(this).data('area');

                const box =
                    $(this).data('box');


                $('#editId').val(id);


                /*
                |--------------------------------------------------------------------------
                | Set Area first
                |--------------------------------------------------------------------------
                */

                $('#editAreaSelect')
                    .val(area)
                    .trigger('change');


                /*
                |--------------------------------------------------------------------------
                | Box options are now generated from Area
                |--------------------------------------------------------------------------
                */

                $('#editBoxInput')
                    .val(box);


                $('#editMemberPerakitanModal')
                    .modal('show');

            });


            /*
            |--------------------------------------------------------------------------
            | AREA -> BOX FOR EDIT FORM
            |--------------------------------------------------------------------------
            */

            $('#editAreaSelect').on('change', function () {

                const area =
                    $(this).val();

                const boxes =
                    validAreas[area] || [];

                const boxSelect =
                    $('#editBoxInput');


                boxSelect.empty();


                if (!area) {

                    boxSelect
                        .append(
                            '<option value="">Pilih Area terlebih dahulu</option>'
                        )
                        .prop('disabled', true);

                    return;
                }


                boxSelect
                    .prop('disabled', false)
                    .append(
                        '<option value="">Pilih Box</option>'
                    );


                boxes.forEach(function (box) {

                    boxSelect.append(
                        $('<option>', {
                            value: box,
                            text: box
                        })
                    );

                });

            });


            /*
            |--------------------------------------------------------------------------
            | SUBMIT EDIT
            |--------------------------------------------------------------------------
            */

            $('#editMemberPerakitanForm').on('submit', function (e) {

                e.preventDefault();


                var id =
                    $('#editId').val();

                var btn =
                    $('#saveEditBtn');


                btn
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>' +
                        'Menyimpan...'
                    );


                $.ajax({

                    url:
                        "{{ url('admin/member-perakitans') }}/" +
                        id,

                    type: 'PUT',

                    data: {

                        _token:
                            '{{ csrf_token() }}',

                        area:
                            $('#editAreaSelect').val(),

                        box:
                            $('#editBoxInput').val()

                    },


                    success: function (res) {

                        btn
                            .prop('disabled', false)
                            .html(
                                '<i class="fas fa-save me-1"></i>' +
                                'Simpan'
                            );


                        $('#editMemberPerakitanModal')
                            .modal('hide');


                        table.ajax.reload(
                            null,
                            false
                        );


                        Swal.fire({

                            icon: 'success',

                            title: 'Berhasil',

                            text: res.message,

                            timer: 1500,

                            showConfirmButton: false

                        });

                    },


                    error: function (xhr) {

                        btn
                            .prop('disabled', false)
                            .html(
                                '<i class="fas fa-save me-1"></i>' +
                                'Simpan'
                            );


                        Swal.fire({

                            icon: 'error',

                            title: 'Gagal',

                            text:
                                (
                                    xhr.responseJSON &&
                                    xhr.responseJSON.message
                                )
                                    ? xhr.responseJSON.message
                                    : 'Gagal menyimpan perubahan.',

                            confirmButtonColor: '#F36494'

                        });

                    }

                });

            });


            /*
            |--------------------------------------------------------------------------
            | DELETE
            |--------------------------------------------------------------------------
            */

            $(document).on('click', '.delete-btn', function () {

                var id =
                    $(this).data('id');


                if (
                    confirm(
                        'Hapus member dari area/box ini?'
                    )
                ) {

                    $.ajax({

                        url:
                            "{{ url('admin/member-perakitans') }}/" +
                            id,

                        type: 'DELETE',

                        data: {
                            _token:
                                '{{ csrf_token() }}'
                        },


                        success: function (res) {

                            table.ajax.reload();

                        }

                    });

                }

            });


        });

    </script>
@endsection
