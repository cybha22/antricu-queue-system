<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from ?? Carbon::today()->subDays(6)->toDateString();
        $to = $request->to ?? Carbon::today()->toDateString();

        $summary = [
            'total' => Queue::whereBetween('date', [$from, $to])->count(),
            'served' => Queue::whereBetween('date', [$from, $to])->where('status', 'served')->count(),
            'cancelled' => Queue::whereBetween('date', [$from, $to])->where('status', 'cancelled')->count(),
            'avg_wait' => round(Queue::whereBetween('date', [$from, $to])
                ->whereNotNull('called_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, called_at)) as avg')
                ->value('avg') ?? 0),
        ];

        $perService = Service::withCount([
            'queues as total' => fn($q) => $q->whereBetween('date', [$from, $to]),
            'queues as served' => fn($q) => $q->whereBetween('date', [$from, $to])->where('status', 'served'),
            'queues as cancelled' => fn($q) => $q->whereBetween('date', [$from, $to])->where('status', 'cancelled'),
        ])->get();

        $daily = [];
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);
        while ($start->lte($end)) {
            $d = $start->toDateString();
            $daily[] = [
                'date' => $d,
                'label' => Carbon::parse($d)->translatedFormat('d M'),
                'total' => Queue::where('date', $d)->count(),
                'served' => Queue::where('date', $d)->where('status', 'served')->count(),
            ];
            $start->addDay();
        }

        return response()->json([
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'per_service' => $perService,
            'daily' => $daily,
        ]);
    }
}
