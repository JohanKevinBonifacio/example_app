<?php

namespace App\Services;

use App\Models\ReportDebs;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{

    public function exportToXlsx(string $startDate, string $endDate): StreamedResponse
    {
        $fileName = "reporte_{$startDate}_{$endDate}.csv";

        return response()->streamDownload(function () use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'Nombre Completo', 'DNI', 'Email', 'Teléfono',
                'Compañía', 'Tipo de deuda', 'Situación', 'Atraso',
                'Entidad', 'Monto total', 'Línea total', 'Línea usada',
                'Reporte subido el', 'Estado'
            ], ';');

            ReportDebs::whereBetween('fecha_creacion', [
                $startDate . ' 00:00:00', 
                $endDate . ' 23:59:59'
            ])->chunk(200, function ($deudas) use ($file) {
                foreach ($deudas as $report) {
                    fputcsv($file, [
                        $report->id,
                        $report->nombre_completo,
                        $report->dni,
                        $report->email,
                        $report->telefono,
                        $report->entidad,
                        $report->tipo_registro,
                        $report->situacion,
                        $report->dias_vencimiento,
                        $report->entidad,
                        $report->monto_deuda,
                        $report->linea_credito_aprobada,
                        $report->linea_credito_utilizada,
                        $report->fecha_creacion,
                        'Activo'
                    ], ';');
                }
            });

            fclose($file);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}