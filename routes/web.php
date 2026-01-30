<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\debsController;
use App\Http\Controllers\ExportController;

Route::post('/exportar-excel', [ExportController::class, 'exportExcel'])->name('export.excel');

Route::get('/debs', [debsController::class, 'indexWeb'])->name('debs.index');