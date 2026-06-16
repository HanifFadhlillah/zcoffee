<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'cashier')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)->letters()->numbers()],
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'cashier',
        ]);

        return back()->with('success', "Akun kasir '{$data['name']}' berhasil dibuat.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        if ($user->role === 'admin') {
            return back()->with('error', 'Tidak bisa menghapus akun admin.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Akun '{$name}' berhasil dihapus.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', Password::min(8)->letters()->numbers()],
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Password '{$user->name}' berhasil direset.");
    }
}
