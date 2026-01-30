<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\debsController;

Route::prefix('debs')->group(function () {
    Route::get('/filtered', [debsController::class, 'filteredData']); // GET /api/debs/filtered
});