<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->paginate($request->per_page ?? 10);

        return response()->json($services);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:10|unique:services,prefix',
            'total_digits' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
        ]);

        $service = Service::create($request->only('name', 'prefix', 'total_digits', 'is_active'));

        return response()->json($service, 201);
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:10|unique:services,prefix,' . $service->id,
            'total_digits' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
        ]);

        $service->update($request->only('name', 'prefix', 'total_digits', 'is_active'));

        return response()->json($service);
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Layanan berhasil dihapus']);
    }
}
