<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportDebs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class debsController extends Controller
{
    protected $defaultTtl = 10; // 10 segundos
    
    public function index(){
        // Crear clave única para esta consulta
    $cacheKey = 'deudas_api';

        if (Cache::has($cacheKey)) {
            Log::info("✅ CACHE HIT - Usando datos de caché para: " . $cacheKey);
            $deudas = Cache::get($cacheKey);
            
            return response()->json($deudas);
        }

        $deudas = ReportDebs::where('monto_deuda', '>', 1000)
                        ->orderBy('dias_vencimiento', 'asc')
                        ->get();

        // Almacenar en caché
        Cache::put($cacheKey, $deudas, $this->defaultTtl);
        Log::info("💾 Datos almacenados en caché: " . $cacheKey);

        return response()->json($deudas);
    }

        // Para Web
    public function indexWeb()
    {
        $deudas = ReportDebs::all();
        return view('debs.debs', compact('deudas'));
    }
}
