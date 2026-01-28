<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\debsController;

Route::get('/debs', [debsController::class, 'index']);
