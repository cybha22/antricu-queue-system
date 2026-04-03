<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Queue;
use Illuminate\Http\Request;
use Carbon\Carbon;

class QueueCallController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $counter = $user->counter;
        $today = Carbon::today()->toDateString();

        $currentQueue = Queue::with('service')
            ->where('counter_id', $counter?->id)
            ->whereIn('status', ['called', 'serving'])
            ->where('date', $today)
            ->first();

        $waitingCount = Queue::where('service_id', $counter?->service_id)
            ->where('status', 'waiting')
            ->where('date', $today)
            ->count();

        $serviceId = $counter?->service_id;

        if ($user->isAdmin() || !$serviceId) {
            $todayStats = [
                'total' => Queue::where('date', $today)->count(),
                'waiting' => Queue::where('date', $today)->where('status', 'waiting')->count(),
                'called' => Queue::where('date', $today)->where('status', 'called')->count(),
                'serving' => Queue::where('date', $today)->where('status', 'serving')->count(),
                'served' => Queue::where('date', $today)->where('status', 'served')->count(),
            ];
        } else {
            $todayStats = [
                'total' => Queue::where('date', $today)->where('service_id', $serviceId)->count(),
                'waiting' => Queue::where('date', $today)->where('service_id', $serviceId)->where('status', 'waiting')->count(),
                'called' => Queue::where('date', $today)->where('service_id', $serviceId)->where('status', 'called')->count(),
                'serving' => Queue::where('date', $today)->where('service_id', $serviceId)->where('status', 'serving')->count(),
                'served' => Queue::where('date', $today)->where('service_id', $serviceId)->where('status', 'served')->count(),
            ];
        }

        $counters = Counter::with('service')->where('is_active', true)->get();

        return response()->json([
            'counter' => $counter?->load('service'),
            'current_queue' => $currentQueue,
            'waiting_count' => $waitingCount,
            'today_stats' => $todayStats,
            'counters' => $counters,
        ]);
    }

    public function open(Request $request)
    {
        $counter = $request->user()->counter;
        if (!$counter) {
            return response()->json(['message' => 'Anda tidak ditugaskan ke loket manapun'], 400);
        }

        $counter->update(['is_active' => true]);

        return response()->json(['message' => 'Loket berhasil dibuka', 'counter' => $counter->load('service')]);
    }

    public function close(Request $request)
    {
        $counter = $request->user()->counter;
        if (!$counter) {
            return response()->json(['message' => 'Anda tidak ditugaskan ke loket manapun'], 400);
        }

        $counter->update(['is_active' => false]);

        return response()->json(['message' => 'Loket berhasil ditutup', 'counter' => $counter->load('service')]);
    }

    public function callNext(Request $request)
    {
        $user = $request->user();
        $counter = $user->counter;
        $isRecall = $request->boolean('is_recall');

        if (!$counter) {
            return response()->json(['message' => 'Anda tidak ditugaskan ke loket manapun'], 400);
        }

        $today = Carbon::today()->toDateString();

        if ($isRecall) {
            $currentQueue = Queue::where('counter_id', $counter->id)
                ->where('status', 'called')
                ->where('date', $today)
                ->first();
            
            if (!$currentQueue) {
                return response()->json(['message' => 'Tidak ada antrian yang sedang dipanggil'], 400);
            }

            // Panggil ulang dengan update called_at (agar terdeteksi sebagai panggillan fresh di kiosk)
            $currentQueue->update(['called_at' => now()]);

            return response()->json([
                'message' => 'Antrian dipanggil ulang',
                'queue' => $currentQueue->load(['service', 'counter']),
            ]);
        }

        Queue::where('counter_id', $counter->id)
            ->whereIn('status', ['called', 'serving'])
            ->where('date', $today)
            ->update(['status' => 'served', 'served_at' => now()]);

        $nextQueue = Queue::where('service_id', $counter->service_id)
            ->where('status', 'waiting')
            ->where('date', $today)
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$nextQueue) {
            return response()->json(['message' => 'Tidak ada antrian menunggu', 'queue' => null]);
        }

        $nextQueue->update([
            'status' => 'called',
            'counter_id' => $counter->id,
            'called_at' => now(),
        ]);

        return response()->json([
            'message' => 'Antrian berhasil dipanggil',
            'queue' => $nextQueue->load(['service', 'counter']),
        ]);
    }

    public function serve(Request $request)
    {
        $counter = $request->user()->counter;
        if (!$counter) {
            return response()->json(['message' => 'Anda tidak ditugaskan ke loket manapun'], 400);
        }

        $today = Carbon::today()->toDateString();
        $queue = Queue::where('counter_id', $counter->id)
            ->where('status', 'called')
            ->where('date', $today)
            ->first();

        if ($queue) {
            $queue->update(['status' => 'serving', 'serving_at' => now()]);
            return response()->json(['message' => 'Layanan dimulai', 'queue' => $queue->load(['service', 'counter'])]);
        }

        return response()->json(['message' => 'Tidak ada antrian yang dipanggil'], 400);
    }

    public function complete(Request $request)
    {
        $counter = $request->user()->counter;
        if (!$counter) {
            return response()->json(['message' => 'Anda tidak ditugaskan ke loket manapun'], 400);
        }

        $today = Carbon::today()->toDateString();
        $queue = Queue::where('counter_id', $counter->id)
            ->whereIn('status', ['called', 'serving'])
            ->where('date', $today)
            ->first();

        if ($queue) {
            $queue->update(['status' => 'served', 'served_at' => now()]);
            return response()->json(['message' => 'Layanan selesai', 'queue' => $queue->load(['service', 'counter'])]);
        }

        return response()->json(['message' => 'Tidak ada antrian untuk diselesaikan'], 400);
    }

    public function skip(Request $request)
    {
        $counter = $request->user()->counter;
        if (!$counter) {
            return response()->json(['message' => 'Anda tidak ditugaskan ke loket manapun'], 400);
        }

        $today = Carbon::today()->toDateString();
        $queue = Queue::where('counter_id', $counter->id)
            ->where('status', 'called')
            ->where('date', $today)
            ->first();

        if ($queue) {
            $queue->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            return response()->json(['message' => 'Antrian dilewati', 'queue' => $queue->load(['service', 'counter'])]);
        }

        return response()->json(['message' => 'Tidak ada antrian yang dipanggil'], 400);
    }
}
