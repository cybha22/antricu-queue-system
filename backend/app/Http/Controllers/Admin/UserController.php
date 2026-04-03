<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('counter')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->paginate($request->per_page ?? 10);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,operator',
            'counter_id' => 'nullable|exists:counters,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'counter_id' => $request->counter_id,
        ]);

        return response()->json($user->load('counter'), 201);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,operator',
            'counter_id' => 'nullable|exists:counters,id',
        ]);

        $data = $request->only('name', 'email', 'role', 'counter_id');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json($user->load('counter'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Pengguna berhasil dihapus']);
    }
}
