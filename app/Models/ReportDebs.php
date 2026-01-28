<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportDebs extends Model
{
    // 1. Nombre de la vista en tu base de datos
    protected $table = 'view_report_debts'; 

    // 2. Como es una vista, no tiene llave primaria autoincremental
    protected $primaryKey = null;
    public $incrementing = false;

    // 3. Desactivar timestamps (a menos que la vista los incluya)
    public $timestamps = false;

    /**
     * Opcional: Impedir que se intenten hacer inserts o updates 
     * desde Eloquent, ya que las vistas suelen ser de solo lectura.
     */
    public function save(array $options = [])
    {
        return false;
    }
}