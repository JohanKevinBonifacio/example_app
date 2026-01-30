<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ReportDebs extends Model
{
    protected $table = 'view_report_debts';
    
    public $timestamps = false;
    
    protected $fillable = [];
    

    protected $casts = [
        'monto_deuda' => 'decimal:2',
        'linea_credito_aprobada' => 'decimal:2',
        'linea_credito_utilizada' => 'decimal:2',
        'dias_vencimiento' => 'integer',
        'fecha_creacion' => 'datetime',
        'fecha_suscripcion_real' => 'datetime'
    ];
    
    public function scopeDateRange(Builder $query, ?string $start, ?string $end): Builder
    {
        if ($start) {
            $query->where('fecha_creacion', '>=', $start);
        }
        if ($end) {
            $query->where('fecha_creacion', '<=', $end . ' 23:59:59');
        }
        return $query;
    }
    
    public function getReportAttributes(): array
    {
        return [
            'id' => $this->id,
            'Nombre Completo' => $this->nombre_completo,
            'DNI' => $this->dni,
            'Email' => $this->email,
            'Teléfono' => $this->telefono,
            'Compañía' => $this->entidad,
            'Tipo de deuda' => $this->mapDebtType($this->tipo_registro),
            'Situación' => $this->mapSituation($this->situacion),
            'Atraso' => $this->dias_vencimiento,
            'Entidad' => $this->entidad,
            'Monto total' => $this->monto_deuda,
            'Línea total' => $this->linea_credito_aprobada,
            'Línea usada' => $this->linea_credito_utilizada,
            'Reporte subido el' => $this->fecha_creacion?->format('d/m/Y H:i:s'),
            'Estado' => $this->getGeneralStatus()
        ];
    }
}