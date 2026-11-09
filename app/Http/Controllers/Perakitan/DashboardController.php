<?php

namespace App\Http\Controllers\Perakitan;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('perakitan')->user();
        return view('perakitan.dashboard', compact('user'));
    }
}
