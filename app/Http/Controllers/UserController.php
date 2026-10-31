<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Hanya Administrator (dibatasi via route middleware 'role:admin_ho')
class UserController extends Controller
{
    public function index()
    {
        $users = User::with('cabang')->orderBy('name')->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $cabangs = Cabang::orderBy('nama_cabang')->get();

        return view('users.create', compact('cabangs'));
    }

    public function show(User $user)
    {
        $user->load('cabang');

        return view('users.show', compact('user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'required|string|max:150|unique:users,username',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin_ho,admin,staff_cabang',
            'cabang_id' => 'required_if:role,staff_cabang|nullable|exists:cabangs,id',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['cabang_id'] = $data['role'] === 'staff_cabang' ? $data['cabang_id'] : null;
        User::create($data);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $cabangs = Cabang::orderBy('nama_cabang')->get();

        return view('users.edit', compact('user', 'cabangs'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'required|string|max:150|unique:users,username,'.$user->id,
            'password' => 'nullable|min:6|confirmed',
            'role' => 'required|in:admin_ho,admin,staff_cabang',
            'cabang_id' => 'required_if:role,staff_cabang|nullable|exists:cabangs,id',
        ]);

        $data['cabang_id'] = $data['role'] === 'staff_cabang' ? $data['cabang_id'] : null;

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
