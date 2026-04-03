<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Queue;
use App\Models\Counter;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KioskQueueController extends Controller
{
    public function services()
    {
        $services = Service::where('is_active', true)->get();
        return response()->json($services);
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $service = Service::findOrFail($request->service_id);
        $today = Carbon::today()->toDateString();

        $lastQueue = Queue::where('service_id', $service->id)
            ->where('date', $today)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastQueue) {
            $numericPart = (int) substr($lastQueue->number, strlen($service->prefix));
            $nextNumber = $numericPart + 1;
        }

        $queueNumber = $service->prefix . str_pad($nextNumber, $service->total_digits, '0', STR_PAD_LEFT);

        $queue = Queue::create([
            'number' => $queueNumber,
            'service_id' => $service->id,
            'status' => 'waiting',
            'date' => $today,
        ]);

        return response()->json([
            'queue' => $queue->load('service'),
            'number' => $queueNumber,
            'service_name' => $service->name,
            'date' => $today,
        ], 201);
    }

    public function status($number)
    {
        $today = Carbon::today()->toDateString();

        $queue = Queue::with(['service', 'counter'])
            ->where('number', $number)
            ->where('date', $today)
            ->first();

        if (!$queue) {
            return response()->json(['message' => 'Antrian tidak ditemukan'], 404);
        }

        return response()->json($queue);
    }

    public function display()
    {
        $today = Carbon::today()->toDateString();
        $setting = Setting::first();

        $calledQueues = Queue::with(['service', 'counter'])
            ->where('date', $today)
            ->whereIn('status', ['called', 'serving'])
            ->orderBy('called_at', 'desc')
            ->get();

        $services = Service::where('is_active', true)
            ->withCount(['queues' => fn($q) => $q->where('date', $today)->where('status', 'waiting')])
            ->get();

        $counters = Counter::with('service')
            ->where('is_active', true)
            ->get()
            ->map(function ($counter) use ($today) {
                $counter->current_queue = Queue::where('counter_id', $counter->id)
                    ->where('date', $today)
                    ->whereIn('status', ['called', 'serving'])
                    ->first();
                return $counter;
            });

        return response()->json([
            'setting' => $setting,
            'called_queues' => $calledQueues,
            'services' => $services,
            'counters' => $counters,
        ]);
    }
}
