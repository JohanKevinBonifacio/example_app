<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportDebs;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class debsController extends Controller
{
    public function indexWeb()
    {
        $total = ReportDebs::count();
        $summary = Cache::remember('deudas_summary', 300, function () {
            return [
                'total' => ReportDebs::count(),
                'total_amount' => ReportDebs::sum('monto_deuda'),
                'avg_days' => round(ReportDebs::avg('dias_vencimiento') ?? 0, 2),
            ];
        });
        
        return view('debs.debs', [
            'total' => $total,
            'summary' => $summary
        ]);
    }

    public function filteredData(Request $request): JsonResponse
    {
        $request->validate([
            'min_amount' => 'nullable|numeric|min:0',
            'situation' => 'nullable|string',
            'page' => 'nullable|integer|min:1'
        ]);
        
        $perPage = 20;
        $page = $request->input('page', 1);
        
        $query = ReportDebs::query();
        
        if ($request->filled('min_amount')) {
            $query->where('monto_deuda', '>=', $request->min_amount);
        }
        
        if ($request->filled('situation')) {
            $query->where('situacion', $request->situation);
        }
        
        $deudas = $query->orderBy('fecha_creacion', 'desc')
                       ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $deudas->items(),
            'pagination' => [
                'total' => $deudas->total(),
                'per_page' => $deudas->perPage(),
                'current_page' => $deudas->currentPage(),
                'last_page' => $deudas->lastPage()
            ]
        ]);
    }
}