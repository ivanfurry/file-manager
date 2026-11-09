<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FolderController;

// Главная страница сразу открывает файловый менеджер
Route::get('/', [FileController::class, 'index'])->name('dashboard');

// Маршруты для папок
Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
Route::put('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

// Маршруты для файлов
Route::post('/files', [FileController::class, 'store'])->name('files.store');
Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
Route::get('/files/{file}/preview', [FileController::class, 'preview'])->name('files.preview');
Route::put('/files/{file}/move', [FileController::class, 'move'])->name('files.move');
Route::delete('/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');

// Маршруты для тегов
Route::post('/files/{file}/tags', [FileController::class, 'addTag'])->name('files.tags.add');
Route::delete('/files/{file}/tags/{tag}', [FileController::class, 'removeTag'])->name('files.tags.remove');