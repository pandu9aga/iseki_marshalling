<?php $__env->startSection('style'); ?>
<style>
    body {
        overflow: hidden;
        position: relative;
    }
    .login-container {
        max-width: 400px;
        margin: 100px auto;
        position: relative;
        z-index: 1;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 0;
    }
    .sakura {
        position: fixed;
        pointer-events: auto;
        z-index: 0;
        top: -20px;
        border-radius: 50% 0 50% 0;
        opacity: 0.7;
        animation: fall linear infinite;
        transition: transform 0.4s ease, opacity 0.4s ease;
        cursor: default;
    }
    .sakura:hover {
        transform: translate(80px, -60px) rotate(180deg) scale(1.5) !important;
        opacity: 0.2 !important;
        animation: none !important;
        transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.5s ease;
    }

    @keyframes fall {
        0% { transform: translateY(-20px) rotate(0deg) scale(1); opacity: 0.7; }
        100% { transform: translateY(100vh) rotate(720deg) scale(0.3); opacity: 0; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $colors = ['#fbb', '#f9a', '#fcc', '#f8a', '#f9b', '#fdd', '#fda', '#faa'];
    $sizes = [8, 9, 10, 11, 12, 13, 14, 15];
?>
<?php for($i = 0; $i < 333; $i++): ?>
<?php
    $left = rand(2, 98);
    $duration = rand(7, 15);
    $delay = rand(0, 10);
    $size = $sizes[array_rand($sizes)];
    $color = $colors[array_rand($colors)];
?>
<div class="sakura" style="left:<?php echo e($left); ?>%;width:<?php echo e($size); ?>px;height:<?php echo e($size); ?>px;background:<?php echo e($color); ?>;animation-duration:<?php echo e($duration); ?>s;animation-delay:<?php echo e($delay); ?>s;"></div>
<?php endfor; ?>
<div class="login-container">
    <div class="card shadow-sm">
        <div class="card-header text-center pt-4">
            <h3 class="fw-bold text-primary">Login Marshalling</h3>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills nav-justified mb-4" id="loginTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="member-tab" data-bs-toggle="pill" href="#member" role="tab">Marshalling</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="perakitan-tab" data-bs-toggle="pill" href="#perakitan" role="tab">Perakitan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="admin-tab" data-bs-toggle="pill" href="#admin" role="tab">Admin</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="member" role="tabpanel">
                    <form action="<?php echo e(route('login.member')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label text-primary">NIK</label>
                            <input type="text" name="nik" class="form-control" placeholder="Input NIK" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-primary">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Input Password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login Marshalling</button>
                    </form>
                </div>

                <div class="tab-pane fade" id="perakitan" role="tabpanel">
                    <form action="<?php echo e(route('login.perakitan')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label text-primary">NIK</label>
                            <input type="text" name="nik" class="form-control" placeholder="Input NIK" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-primary">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Input Password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login Perakitan</button>
                    </form>
                </div>

                <div class="tab-pane fade" id="admin" role="tabpanel">
                    <form action="<?php echo e(route('login.admin')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label text-primary">Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Input Name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-primary">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Input Password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login Admin</button>
                    </form>
                </div>
            </div>

            <br>
            <hr class="my-3 text-muted">
            <div class="text-center">
                <a href="<?php echo e(route('public.part-kurang.index')); ?>" class="btn btn-outline-primary w-100 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center">
                    <i class="fas fa-clipboard-list fa-lg me-2"></i>Part Kurang
                </a>
                <small class="text-muted d-block mt-1">Input & Penerimaan Part Kurang via Scan QR Member</small>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\iseki_marshalling\resources\views/auth/login.blade.php ENDPATH**/ ?>