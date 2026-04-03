<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $queues = Queue::with(['service', 'counter'])
            ->when($request->service_id, fn($q, $v) => $q->where('service_id', $v))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->date, fn($q, $v) => $q->whereDate('date', $v))
            ->when($request->search, fn($q, $s) => $q->where('number', 'like', "%{$s}%"))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json($queues);
    }

    public function destroy(Queue $queue)
    {
        $queue->delete();
        return response()->json(['message' => 'Antrian berhasil dihapus']);
    }
}
