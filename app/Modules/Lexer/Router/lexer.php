<?php

use App\Modules\Lexer\Controller\CompilerController;
use Illuminate\Support\Facades\Route;

Route::get('/lexer', [CompilerController::class, 'index'])->name('index');
