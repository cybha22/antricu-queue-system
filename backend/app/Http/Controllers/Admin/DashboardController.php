<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Counter;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $todayStats = [
            'total' => Queue::where('date', $today)->count(),
            'waiting' => Queue::where('date', $today)->where('status', 'waiting')->count(),
            'called' => Queue::where('date', $today)->where('status', 'called')->count(),
            'serving' => Queue::where('date', $today)->where('status', 'serving')->count(),
            'served' => Queue::where('date', $today)->where('status', 'served')->count(),
            'cancelled' => Queue::where('date', $today)->where('status', 'cancelled')->count(),
        ];

        $avgWaitTime = Queue::where('date', $today)
            ->whereNotNull('called_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, called_at)) as avg_wait')
            ->value('avg_wait');

        $serviceStats = Service::withCount([
            'queues as total_today' => fn($q) => $q->where('date', $today),
            'queues as waiting_today' => fn($q) => $q->where('date', $today)->where('status', 'waiting'),
            'queues as served_today' => fn($q) => $q->where('date', $today)->where('status', 'served'),
        ])->get();

        $activeCounters = Counter::where('is_active', true)->count();
        $totalCounters = Counter::count();

        $weeklyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $weeklyTrend[] = [
                'date' => $date,
                'label' => Carbon::parse($date)->translatedFormat('D'),
                'total' => Queue::where('date', $date)->count(),
                'served' => Queue::where('date', $date)->where('status', 'served')->count(),
            ];
        }

        return response()->json([
            'today_stats' => $todayStats,
            'service_stats' => $serviceStats,
            'avg_wait_time' => round($avgWaitTime ?? 0),
            'active_counters' => $activeCounters,
            'total_counters' => $totalCounters,
            'weekly_trend' => $weeklyTrend,
        ]);
    }
}
