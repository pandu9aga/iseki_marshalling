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
        $request->merge([
            'name' => trim($request->name ?? ''),
            'password' => trim($request->password ?? ''),
        ]);

        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ], [
            'name.required' => 'Nama admin wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $admin = \App\Models\User::where('name', $request->name)->first();

        if ($admin && $admin->password === $request->password) {
            $this->clearPreviousAuth($request);
            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()
            ->withErrors(['auth_error' => 'Nama admin atau password tidak sesuai.'])
            ->with('active_tab', 'admin')
            ->withInput($request->only('name'));
    }

    public function loginMember(Request $request)
    {
        $request->merge([
            'nik' => trim($request->nik ?? ''),
            'password' => trim($request->password ?? ''),
        ]);

        $request->validate([
            'nik' => 'required',
            'password' => 'required',
        ], [
            'nik.required' => 'NIK member wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $member = \App\Models\Member::where('nik', $request->nik)->first();

        if ($member && $member->password === $request->password) {
            $this->clearPreviousAuth($request);
            Auth::guard('member')->login($member);
            $request->session()->regenerate();
            return redirect()->route('member.records.index');
        }

        return back()
            ->withErrors(['auth_error' => 'NIK atau password Marshalling tidak sesuai.'])
            ->with('active_tab', 'member')
            ->withInput($request->only('nik'));
    }

    public function loginPerakitan(Request $request)
    {
        $request->merge([
            'nik' => trim($request->nik ?? ''),
            'password' => trim($request->password ?? ''),
        ]);

        $request->validate([
            'nik' => 'required',
            'password' => 'required',
        ], [
            'nik.required' => 'NIK perakitan wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $perakitan = \App\Models\Perakitan::where('nik', $request->nik)->first();

        if ($perakitan && $perakitan->password === $request->password) {
            $this->clearPreviousAuth($request);
            Auth::guard('perakitan')->login($perakitan);
            $request->session()->regenerate();
            return redirect()->route('perakitan.dashboard');
        }

        return back()
            ->withErrors(['auth_error' => 'NIK atau password Perakitan tidak sesuai.'])
            ->with('active_tab', 'perakitan')
            ->withInput($request->only('nik'));
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
