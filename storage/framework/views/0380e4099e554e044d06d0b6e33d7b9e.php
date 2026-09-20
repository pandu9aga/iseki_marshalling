<?php $__env->startSection('style'); ?>
<style>
    #ngTable .badge { font-size: 11px; padding: 3px 6px; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="page-title text-primary mb-0">NG Record List</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Date</label>
                        <input type="date" id="filter_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label>Member</label>
                        <select id="filter_member" class="form-control form-control-sm">
                            <option value="">All</option>
                            <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->id); ?>"><?php echo e($m->nama); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Area</label>
                        <select id="filter_area" class="form-control form-control-sm">
                            <option value="">All</option>
                            <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($a); ?>"><?php echo e(ucwords(str_replace('_', ' ', $a))); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Type</label>
                        <select id="filter_type" class="form-control form-control-sm">
                            <option value="">All</option>
                            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->Type); ?>"><?php echo e($t->Type); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="ngTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Member</th>
                                <th>Sequence Record</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Qty Record</th>
                                <th>Code Part</th>
                                <th>Name Part</th>
                                <th>Difference</th>
                                <th>Box</th>
                                <th>Area</th>
                                <th>Production Date</th>
                                <th>Time Record</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
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
<script>
    $(document).ready(function() {
        var table = $('#ngTable').DataTable({
            pageLength: 50,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?php echo e(url('admin/ng')); ?>",
                data: function(d) {
                    d.filter_date = $('#filter_date').val();
                    d.filter_member = $('#filter_member').val();
                    d.filter_area = $('#filter_area').val();
                    d.filter_type = $('#filter_type').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'member_name', name: 'member_name' },
                { data: 'sequence_record', name: 'sequence_record' },
                { data: 'type_record', name: 'type_record' },
                { data: 'Qty', name: 'Qty' },
                { data: 'Qty_Record', name: 'Qty_Record' },
                { data: 'Code_Part', name: 'Code_Part' },
                { data: 'Name_Part', name: 'Name_Part' },
                { data: 'Difference', name: 'Difference' },
                { data: 'Box', name: 'Box' },
                { data: 'area_record', name: 'area_record' },
                { data: 'production_date', name: 'production_date' },
                { data: 'time_record', name: 'time_record' }
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).css('cursor', 'pointer');
                $(row).on('click', function() {
                    showNgDetail(data);
                });
            }
        });

        $('#filter_date, #filter_member, #filter_area, #filter_type').on('change', function() {
            table.ajax.reload();
        });
    });

    function showNgDetail(data) {
        $.get("<?php echo e(url('admin/ng-detail')); ?>/" + data.Id_Record_List, function(resp) {
            var html = '<div class="border rounded p-2 mb-3 bg-light text-start">';
            html += '<strong>Code Part:</strong> ' + resp.Code_Part + '<br>';
            html += '<strong>Name Part:</strong> ' + resp.Name_Part + '<br>';
            html += '<strong>Expected Qty:</strong> ' + resp.Qty + '<br>';
            html += '<strong>Recorded Qty:</strong> ' + (resp.Qty_Record || '-') + '<br>';
            html += '<strong>Code Rack:</strong> ' + resp.Code_Rack + '<br>';
            html += '<strong>Difference:</strong> ' + (resp.Difference || '-') + '<br>';
            html += '<strong>Box:</strong> ' + (resp.Box || '-') + '<br>';
            html += '<strong>Mode:</strong> ' + (resp.Mode === 'ai' ? 'AI' : 'Manual');
            if (resp.Image_Ng) {
                html += '</div>';
                html += '<img src="<?php echo e(url("")); ?>/' + resp.Image_Ng + '" class="img-fluid mb-3 border rounded" style="max-height:400px;">';
                html += '<br>';
                html += '<button type="button" class="btn btn-success" onclick="approveNg(' + resp.Id_Record_List + ')"><i class="fas fa-check"></i> Approve (Set OK)</button>';
            } else {
                html += '</div><p class="text-muted">No image available</p>';
            }
            $('#ngImageContent').html(html);
            $('#ngImageModal').modal('show');
        });
    }

    function approveNg(recordListId) {
        if (!confirm('Approve this NG item as OK?')) return;
        $.post("<?php echo e(url('admin/record-lists')); ?>/" + recordListId + "/approve", {
            _token: "<?php echo e(csrf_token()); ?>"
        }, function(response) {
            if (response.success) {
                $('#ngImageModal').modal('hide');
                $('#ngTable').DataTable().ajax.reload();
            }
        });
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\iseki_marshalling\resources\views/admin/records/ng.blade.php ENDPATH**/ ?>