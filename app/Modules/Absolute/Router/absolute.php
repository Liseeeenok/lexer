<?php

use App\Modules\Absolute\Controller\AbsoluteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AbsoluteController::class, 'index'])->name('index');
