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
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="page-title text-primary mb-0">Rangkuman Jam Marshalling</h4>
            <div class="d-flex align-items-center gap-2">
                <form method="GET" class="mb-0">
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
                </form>
                <a href="{{ route('admin.summary.export', ['date' => $date]) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </a>
            </div>
        </div>

        @if(count($summaryData) > 0)
        <div class="row g-2 mb-3">
            @foreach($summaryData as $row)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="summary-icon text-white bg-primary"><i class="far fa-clock"></i></div>
                        <div>
                            <div class="fs-5 fw-bold text-dark">{{ $row['duration'] }}</div>
                            <small class="text-muted text-uppercase fw-bold">{{ $row['area'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Area</th>
                                <th>Total Marshalling (Selesai/Total)</th>
                                <th>Total Jam</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summaryData as $index => $row)
                                <tr data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}" style="cursor: pointer;">
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold">{{ $row['area'] }}</td>
                                    <td>
                                        @if($row['completed_tractors'] == $row['total_tractors'])
                                            <span class="badge bg-success">{{ $row['completed_tractors'] }} / {{ $row['total_tractors'] }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ $row['completed_tractors'] }} / {{ $row['total_tractors'] }}</span>
                                        @endif
                                    </td>
                                    <td><i class="far fa-clock text-muted"></i> {{ $row['duration'] }}</td>
                                    <td class="text-end"><i class="fas fa-chevron-down text-muted"></i></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="p-0 border-0">
                                        <div class="collapse" id="collapse-{{ $index }}">
                                            <div class="p-3 bg-light border-bottom">
                                                <table class="table table-sm table-hover mb-0 bg-white">
                                                    <thead class="table-secondary">
                                                        <tr>
                                                            <th>Kategori (Main Type)</th>
                                                            <th>Perolehan (Selesai/Total)</th>
                                                            <th>Total Jam</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($row['main_types'] as $mtIndex => $mtRow)
                                                            <tr data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}-mt-{{ $mtIndex }}" style="cursor: pointer;">
                                                                <td class="fw-bold"><i class="fas fa-layer-group me-2 text-muted"></i>{{ $mtRow['main_type'] }}</td>
                                                                <td>
                                                                    @if($mtRow['completed_tractors'] == $mtRow['total_tractors'])
                                                                        <span class="badge bg-success">{{ $mtRow['completed_tractors'] }} / {{ $mtRow['total_tractors'] }}</span>
                                                                    @else
                                                                        <span class="badge bg-warning text-dark">{{ $mtRow['completed_tractors'] }} / {{ $mtRow['total_tractors'] }}</span>
                                                                    @endif
                                                                </td>
                                                                <td><i class="far fa-clock text-muted"></i> {{ $mtRow['duration'] }}</td>
                                                                <td class="text-end"><i class="fas fa-chevron-down text-muted"></i></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="4" class="p-0 border-0">
                                                                    <div class="collapse" id="collapse-{{ $index }}-mt-{{ $mtIndex }}">
                                                                        <div class="p-3 border-bottom" style="background-color: #fcfcfc;">
                                                                            <table class="table table-sm table-hover mb-0 bg-white">
                                                                                <thead class="table-light">
                                                                                    <tr>
                                                                                        <th>Tipe Traktor</th>
                                                                                        <th>Perolehan Traktor (Selesai/Total)</th>
                                                                                        <th>Total Jam</th>
                                                                                        <th></th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    @foreach($mtRow['types'] as $typeIndex => $typeRow)
                                                                                        <tr data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}-mt-{{ $mtIndex }}-type-{{ $typeIndex }}" style="cursor: pointer;">
                                                                                            <td class="fw-bold"><i class="fas fa-tractor me-2 text-muted"></i>{{ $typeRow['type'] }}</td>
                                                                                            <td>
                                                                                                @if($typeRow['completed_tractors'] == $typeRow['total_tractors'])
                                                                                                    <span class="badge bg-success">{{ $typeRow['completed_tractors'] }} / {{ $typeRow['total_tractors'] }}</span>
                                                                                                @else
                                                                                                    <span class="badge bg-warning text-dark">{{ $typeRow['completed_tractors'] }} / {{ $typeRow['total_tractors'] }}</span>
                                                                                                @endif
                                                                                            </td>
                                                                                            <td><i class="far fa-clock text-muted"></i> {{ $typeRow['duration'] }}</td>
                                                                                            <td class="text-end"><i class="fas fa-chevron-down text-muted"></i></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td colspan="4" class="p-0 border-0">
                                                                                                <div class="collapse" id="collapse-{{ $index }}-mt-{{ $mtIndex }}-type-{{ $typeIndex }}">
                                                                                                    <div class="p-3 border-bottom" style="background-color: #f4f4f4;">
                                                                                                        <table class="table table-sm table-bordered mb-0 bg-white">
                                                                                                            <thead class="table-light">
                                                                                                                <tr>
                                                                                                                    <th>Nama Member</th>
                                                                                                                    <th>Perolehan Traktor (Selesai/Total)</th>
                                                                                                                    <th>Total Jam Member</th>
                                                                                                                </tr>
                                                                                                            </thead>
                                                                                                            <tbody>
                                                                                                                @foreach($typeRow['members'] as $member)
                                                                                                                <tr>
                                                                                                                    <td><i class="fas fa-user me-2 text-muted"></i>{{ $member['name'] }}</td>
                                                                                                                    <td>
                                                                                                                        @if($member['completed'] == $member['total'])
                                                                                                                            <span class="badge bg-success">{{ $member['completed'] }} / {{ $member['total'] }}</span>
                                                                                                                        @else
                                                                                                                            <span class="badge bg-warning text-dark">{{ $member['completed'] }} / {{ $member['total'] }}</span>
                                                                                                                        @endif
                                                                                                                    </td>
                                                                                                                    <td><i class="far fa-clock me-1 text-muted"></i>{{ $member['duration'] }}</td>
                                                                                                                </tr>
                                                                                                                @endforeach
                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        Belum ada data untuk tanggal ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
