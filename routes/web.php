<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\debsController;

Route::get('/debs-view', [debsController::class, 'indexWeb']);