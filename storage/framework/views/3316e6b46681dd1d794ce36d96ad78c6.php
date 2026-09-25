<?php $__env->startSection('style'); ?>
<style>
    .summary-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
    }
    .progress-xs { height: 6px; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="page-title text-primary mb-0">Admin Dashboard</h4>
            <form method="GET" class="d-flex align-items-center gap-2">
                <input type="date" name="date" class="form-control form-control-sm" value="<?php echo e($date); ?>" onchange="this.form.submit()">
                <a href="<?php echo e(route('admin.dashboard.export', ['date' => $date])); ?>" class="btn btn-success btn-sm text-nowrap"><i class="fas fa-file-excel"></i> Export</a>
            </form>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="summary-icon text-white" style="background:#F36494;"><i class="fas fa-users"></i></div>
                        <div>
                            <div class="fs-4 fw-bold"><?php echo e($totalMembers); ?></div>
                            <small class="text-muted">Member</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="summary-icon text-white" style="background:#00b894;"><i class="fas fa-check"></i></div>
                        <div>
                            <div class="fs-4 fw-bold text-success"><?php echo e($totalDone); ?></div>
                            <small class="text-muted">Done</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <a href="<?php echo e(route('admin.ng.index')); ?>" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="summary-icon text-white" style="background:#e17055;"><i class="fas fa-exclamation-triangle"></i></div>
                            <div>
                                <div class="fs-4 fw-bold text-danger"><?php echo e($totalNg); ?></div>
                                <small class="text-muted">NG (<?php echo e($date); ?>)</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <?php if(count($chartData) > 0): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <strong>Completion by Member</strong>
            </div>
            <div class="card-body" style="height:250px;">
                <canvas id="chartBar"></canvas>
            </div>
        </div>
        <?php endif; ?>

        <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area => $areaGroups): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="mb-4">
            <h5 class="text-secondary border-bottom pb-2 mb-3"><i class="fas fa-map-marker-alt text-danger me-2"></i> <?php echo e(ucwords(str_replace('_', ' ', $area))); ?></h5>
            <?php $__currentLoopData = $areaGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userId => $typeGroups): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $memberName = $typeGroups->first()->first()->member->nama ?? 'Unknown';
                $initial = strtoupper(substr($memberName, 0, 1));
                $memberTotal = 0; $memberDone = 0;
                $memberDurationSeconds = 0;
                foreach ($typeGroups as $type => $typeRecords) {
                    $memberTotal += $typeRecords->count();
                    $memberDone += $typeRecords->filter(fn($r) => $r->recordLists->every(fn($rl) => $rl->Time_Record !== null))->count();
                    $memberDurationSeconds += $typeRecords->sum('computed_duration');
                }
                $memberPct = $memberTotal > 0 ? round($memberDone / $memberTotal * 100) : 0;
                $memberDurationMinutes = round($memberDurationSeconds / 60);
                $memberDurationStr = $memberDurationMinutes . ' Menit';
            ?>
            <div class="card border-0 shadow-sm mb-3 ms-2">
                <div class="card-header bg-white py-2 d-flex align-items-center gap-3">
                    <span class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;background:#F36494;font-size:14px;"><?php echo e($initial); ?></span>
                    <div class="flex-grow-1">
                        <strong class="d-block" style="font-size:15px;"><?php echo e($memberName); ?></strong>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress progress-xs flex-grow-1" style="max-width:200px;">
                                <div class="progress-bar <?php echo e($memberPct == 100 ? 'bg-success' : 'bg-warning'); ?>" style="width:<?php echo e($memberPct); ?>%"></div>
                            </div>
                            <small class="text-muted"><?php echo e($memberDone); ?>/<?php echo e($memberTotal); ?> | <i class="far fa-clock"></i> <?php echo e($memberDurationStr); ?></small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    <?php $__currentLoopData = $typeGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $typeRecords): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $totalRecs = $typeRecords->count();
                            $doneRecs = $typeRecords->filter(fn($r) => $r->recordLists->every(fn($rl) => $rl->Time_Record !== null))->count();
                            $isComplete = $totalRecs > 0 && $totalRecs == $doneRecs;
                            $pct = $totalRecs > 0 ? round($doneRecs / $totalRecs * 100) : 0;
                            $collapseId = 'collapse-' . $area . '-' . $userId . '-' . $loop->index;
                            
                            $typeDurationSeconds = $typeRecords->sum('computed_duration');
                            $typeDurationMinutes = round($typeDurationSeconds / 60);
                            $typeDurationStr = $typeDurationMinutes . ' Menit';
                        ?>
                        <div class="type-header d-flex align-items-center justify-content-between border-bottom pb-1 mb-1 ps-2" data-bs-toggle="collapse" data-bs-target="#<?php echo e($collapseId); ?>" style="cursor:pointer;">
                            <div class="d-flex align-items-center gap-2 flex-grow-1 me-3">
                                <i class="fas fa-chevron-right fa-xs type-chevron"></i>
                                <span class="fw-medium small" style="min-width:80px;"><?php echo e($type); ?></span>
                                <div class="progress progress-xs flex-grow-1" style="max-width:150px;">
                                    <div class="progress-bar <?php echo e($isComplete ? 'bg-success' : 'bg-warning'); ?>" style="width:<?php echo e($pct); ?>%"></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <span class="badge bg-light text-dark border"><i class="far fa-clock"></i> <?php echo e($typeDurationStr); ?></span>
                                <?php if($isComplete): ?>
                                    <span class="badge bg-success">Full</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">On Progress</span>
                                <?php endif; ?>
                                <span class="badge bg-secondary"><?php echo e($doneRecs); ?>/<?php echo e($totalRecs); ?></span>
                            </div>
                        </div>
                        <div class="collapse" id="<?php echo e($collapseId); ?>">
                            <?php $__currentLoopData = $typeRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $recDone = $record->recordLists->filter(fn($rl) => $rl->Time_Record !== null)->count();
                                $recTotal = $record->recordLists->count();
                                $recComplete = $recTotal > 0 && $recDone == $recTotal;
                                
                                $recDurationMinutes = round($record->computed_duration / 60);
                                $recDurationStr = $recDurationMinutes . ' Menit';
                            ?>
                            <div class="record-row d-flex align-items-center border-bottom py-1 ps-4 pe-2" data-id="<?php echo e($record->Id_Record); ?>" style="cursor:pointer;">
                                <span class="small flex-grow-1"><?php echo e($record->Sequence_No_Record); ?></span>
                                <span class="small text-muted me-3"><i class="far fa-clock"></i> <?php echo e($recDurationStr); ?></span>
                                <span class="small text-muted me-3"><?php echo e($record->Production_Date_Record); ?></span>
                                <span class="badge <?php echo e($recComplete ? 'bg-success' : 'bg-warning text-dark'); ?>"><?php echo e($recDone); ?>/<?php echo e($recTotal); ?></span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p class="mb-0 fs-5">No data</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="recordModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="recordDetailContent"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="ngImageModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content border border-2 border-danger">
            <div class="modal-header">
                <h5 class="modal-title">NG Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="ngImageContent"></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script src="<?php echo e(asset('assets/js/plugin/chart.js/chart.min.js')); ?>"></script>
<script>
    $(document).ready(function() {
        $('.type-header').on('click', function() {
            var chevron = $(this).find('.type-chevron');
            chevron.toggleClass('fa-chevron-right fa-chevron-down');
        });

        $(document).on('click', '.record-row', function() {
            var id = $(this).data('id');
            $.get("<?php echo e(url('admin/records')); ?>/" + id, function(data) {
                var html = '<div class="mb-3"><strong>Sequence:</strong> ' + data.Sequence_No_Record + '<br>';
                var formattedArea = data.Area ? data.Area.replace(/_/g, ' ').replace(/\b\w/g, function(char) {
                    return char.toUpperCase();
                }) : '-';
                html += '<strong>Production Date:</strong> ' + data.Production_Date_Record + '<br>';
                html += '<strong>Type:</strong> ' + data.Type + '<br>';
                html += '<strong>Area:</strong> ' + formattedArea + '<br>';
                html += '<strong>Member:</strong> ' + (data.member ? data.member.nama : '-') + '<br>';
                html += '<strong>Remark:</strong> ' + (data.Remark ? data.Remark : '-') + '</div>';
                html += '<div class="table-responsive"><table class="table table-bordered table-sm" style="font-size:0.75rem;"><thead><tr><th style="white-space:nowrap;">Seq</th><th style="white-space:nowrap;">Code Part</th><th style="white-space:nowrap;">Name Part</th><th style="white-space:nowrap;">Mode</th><th style="white-space:nowrap;">Code<br>Rack</th><th style="white-space:nowrap;">Diff</th><th style="white-space:nowrap;">Location</th><th style="white-space:nowrap;">Box</th><th style="white-space:nowrap;">Qty</th><th style="white-space:nowrap;">Qty<br>Rec</th><th style="white-space:nowrap;">Time<br>Rec</th><th style="white-space:nowrap;">Stat</th></tr></thead><tbody>';
                data.record_lists.forEach(function(rl) {
                    var statusHtml = '';
                    if (!rl.Time_Record) {
                        statusHtml = '<span class="badge bg-warning">Pending</span>';
                    } else if (parseInt(rl.Qty_Record) === parseInt(rl.Qty)) {
                        statusHtml = '<span class="badge bg-success">OK</span>';
                    } else if (rl.Status_Ng === 'ng_ok') {
                        var ngClick = rl.Image_Ng ? 'onclick="showNgImage(\'' + rl.Image_Ng + '\', ' + rl.Id_Record_List + ', ' + rl.Qty + ', ' + (rl.Qty_Record || 0) + ', \'ng_ok\')" style="cursor:pointer;"' : '';
                        statusHtml = '<span class="badge bg-info" ' + ngClick + '>NG-OK</span>';
                    } else {
                        var ngClick = rl.Image_Ng ? 'onclick="showNgImage(\'' + rl.Image_Ng + '\', ' + rl.Id_Record_List + ', ' + rl.Qty + ', ' + (rl.Qty_Record || 0) + ', \'\')" style="cursor:pointer;"' : '';
                        statusHtml = '<span class="badge bg-danger" ' + ngClick + '>NG</span>';
                    }
                    var modeBadge = rl.Mode === 'ai' ? '<span class="badge bg-info">AI</span>' : '<span class="badge bg-secondary">Manual</span>';
                    var enterTime = rl.Time_Record ? rl.Time_Record.substring(0, 10) + '<br>' + rl.Time_Record.substring(11, 19) : '-';
                    var namePart = rl.Name_Part ? (rl.Name_Part.length > 15 ? rl.Name_Part.substring(0, 15) + '...' : rl.Name_Part) : '-';
                    html += '<tr><td>' + rl.Sequence_No + '</td><td>' + rl.Code_Part + '</td><td style="font-size:0.7rem;">' + namePart + '</td><td>' + modeBadge + '</td><td>' + rl.Code_Rack + '</td><td>' + (rl.Difference || '-') + '</td><td>' + (rl.Location_Rack || '-') + '</td><td>' + (rl.Box || '-') + '</td><td>' + rl.Qty + '</td><td>' + (rl.Qty_Record || '-') + '</td><td>' + enterTime + '</td><td>' + statusHtml + '</td></tr>';
                });
                html += '</tbody></table></div>';
                $('#recordDetailContent').html(html);
                $('#recordModal').modal('show');
            });
        });
    });

    function showNgImage(imagePath, recordListId, expectedQty, recordedQty, statusNg) {
        var html = '<div class="border rounded p-2 mb-3 bg-light"><strong>Expected Qty:</strong> ' + expectedQty + ' &nbsp;|&nbsp; <strong>Recorded Qty:</strong> ' + recordedQty + '</div>';
        html += '<img src="<?php echo e(url("")); ?>/' + imagePath + '" class="img-fluid mb-3 border rounded" style="max-height:400px;">';
        html += '<br>';
        if (statusNg !== 'ng_ok') {
            html += '<button type="button" class="btn btn-success" onclick="approveNg(' + recordListId + ')"><i class="fas fa-check"></i> Approve (Set OK)</button>';
        }
        $('#ngImageContent').html(html);
        $('#ngImageModal').modal('show');
    }

    function approveNg(recordListId) {
        if (!confirm('Approve this NG item as OK?')) return;
        $.post("<?php echo e(url('admin/record-lists')); ?>/" + recordListId + "/approve", {
            _token: "<?php echo e(csrf_token()); ?>"
        }, function(response) {
            if (response.success) {
                $('#ngImageModal').modal('hide');
                $('#recordModal').modal('hide');
                location.reload();
            }
        });
    }

var chartLabels = <?php echo json_encode(array_column($chartData, 'member')); ?>;
var chartValues = <?php echo json_encode(array_column($chartData, 'done')); ?>;
console.log('Members:', JSON.stringify(chartLabels));
console.log('Values:', JSON.stringify(chartValues));
var ctx = document.getElementById('chartBar');
if (ctx) {
    ctx = ctx.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Done',
                data: chartValues,
                backgroundColor: '#F36494'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { display: false },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        stepSize: 1,
                        fontSize: 11
                    },
                    gridLines: { color: '#e9ecef' }
                }],
                xAxes: [{
                    ticks: {
                        fontSize: 11,
                        autoSkip: false,
                        maxRotation: 30,
                        minRotation: 0
                    },
                    gridLines: { display: false }
                }]
            }
        }
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\iseki_marshalling\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>