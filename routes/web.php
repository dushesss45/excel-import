<?php

use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\RowsController;
use App\Http\Middleware\BasicAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([BasicAuthMiddleware::class])->group(function() {
    Route::get('/upload-form', [ExcelImportController::class, 'showForm'])->name('excel.upload.form');
    Route::post('/upload-excel', [ExcelImportController::class, 'upload'])->name('excel.upload');
});

Route::get('/rows', [RowsController::class, 'index'])->name('rows.index');

Route::get('/api-docs', function () {
    return redirect('/swagger/index.html');
});