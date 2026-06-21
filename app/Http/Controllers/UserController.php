<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private array $roles = [
        'admin' => 'Admin',
        'pendaftaran' => 'Petugas Pendaftaran',
        'dokter' => 'Dokter',
        'kasir' => 'Kasir',
        'kepala_klinik' => 'Kepala Klinik',
        'pasien' => 'Pasien',
        'farmasi' => 'Farmasi',
    ];

    public function index()
    {
        return view('users.index', [
            'users' => User::latest()->paginate(10),
            'roles' => $this->roles,
        ]);
    }

    public function create()
    {
        return view('users.form', [
            'user' => null,
            'roles' => $this->roles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys($this->roles))],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        User::create($data);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.form', [
            'user' => $user,
            'roles' => $this->roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys($this->roles))],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_if(auth()->id() === $user->id, 422, 'User yang sedang login tidak dapat dihapus.');

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
