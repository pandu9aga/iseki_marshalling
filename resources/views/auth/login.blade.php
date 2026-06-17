@extends('layouts.main')

@section('style')
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
@endsection

@section('content')
@php
    $colors = ['#fbb', '#f9a', '#fcc', '#f8a', '#f9b', '#fdd', '#fda', '#faa'];
    $sizes = [8, 9, 10, 11, 12, 13, 14, 15];
@endphp
@for ($i = 0; $i < 333; $i++)
@php
    $left = rand(2, 98);
    $duration = rand(7, 15);
    $delay = rand(0, 10);
    $size = $sizes[array_rand($sizes)];
    $color = $colors[array_rand($colors)];
@endphp
<div class="sakura" style="left:{{ $left }}%;width:{{ $size }}px;height:{{ $size }}px;background:{{ $color }};animation-duration:{{ $duration }}s;animation-delay:{{ $delay }}s;"></div>
@endfor
<div class="login-container">
    <div class="card shadow-sm">
        <div class="card-header text-center pt-4">
            <h3 class="fw-bold text-primary">Login Marshalling</h3>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills nav-justified mb-4" id="loginTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="member-tab" data-bs-toggle="pill" href="#member" role="tab">Member</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="admin-tab" data-bs-toggle="pill" href="#admin" role="tab">Admin</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="member" role="tabpanel">
                    <form action="{{ route('login.member') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-primary">NIK</label>
                            <input type="text" name="nik" class="form-control" placeholder="Input NIK" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-primary">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Input Password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login Member</button>
                    </form>
                </div>

                <div class="tab-pane fade" id="admin" role="tabpanel">
                    <form action="{{ route('login.admin') }}" method="POST">
                        @csrf
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
        </div>
    </div>
</div>
@endsection
