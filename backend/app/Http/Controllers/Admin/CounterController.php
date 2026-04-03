<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function index(Request $request)
    {
        $counters = Counter::with('service')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->paginate($request->per_page ?? 10);

        return response()->json($counters);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id',
            'is_active' => 'boolean',
        ]);

        $counter = Counter::create($request->only('name', 'service_id', 'is_active'));

        return response()->json($counter->load('service'), 201);
    }

    public function update(Request $request, Counter $counter)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id',
            'is_active' => 'boolean',
        ]);

        $counter->update($request->only('name', 'service_id', 'is_active'));

        return response()->json($counter->load('service'));
    }

    public function destroy(Counter $counter)
    {
        $counter->delete();
        return response()->json(['message' => 'Loket berhasil dihapus']);
    }
}
