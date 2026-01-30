<?php

namespace App\Http\Controllers;

use App\Services\ExcelExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportExcel(Request $request, ExcelExportService $excelService)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
        
        return $excelService->exportToXlsx(
            $request->start_date,
            $request->end_date
        );
    }
}