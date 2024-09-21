<?php

use App\Modules\Lexer\Controller\LexerController;
use Illuminate\Support\Facades\Route;

Route::get('/lexer', [LexerController::class, 'index'])->name('index');
