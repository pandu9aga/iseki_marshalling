<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::guard('member')->check()) {
            return redirect()->route('member.records.index');
        }
        if (Auth::guard('perakitan')->check()) {
            return redirect()->route('perakitan.dashboard');
        }
        return view('auth.login');
    }

    private function clearPreviousAuth(Request $request): void
    {
        Auth::guard('admin')->logout();
        Auth::guard('member')->logout();
        Auth::guard('perakitan')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function loginAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $admin = \App\Models\User::where('name', $request->name)->first();

        if ($admin && $admin->password === $request->password) {
            $this->clearPreviousAuth($request);
            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'name' => 'Invalid credentials.',
        ])->onlyInput('name');
    }

    public function loginMember(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'password' => 'required',
        ]);

        $member = \App\Models\Member::where('nik', $request->nik)->first();

        if ($member && $member->password === $request->password) {
            $this->clearPreviousAuth($request);
            Auth::guard('member')->login($member);
            $request->session()->regenerate();
            return redirect()->route('member.records.index');
        }

        return back()->withErrors([
            'nik' => 'Invalid credentials.',
        ])->onlyInput('nik');
    }

    public function loginPerakitan(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'password' => 'required',
        ]);

        $perakitan = \App\Models\Perakitan::where('nik', $request->nik)->first();

        if ($perakitan && $perakitan->password === $request->password) {
            $this->clearPreviousAuth($request);
            Auth::guard('perakitan')->login($perakitan);
            $request->session()->regenerate();
            return redirect()->route('perakitan.dashboard');
        }

        return back()->withErrors([
            'nik' => 'Invalid credentials.',
        ])->onlyInput('nik');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('member')->logout();
        Auth::guard('perakitan')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
