<?php

use GeoSot\EnvEditor\Controllers\EnvController;
use Illuminate\Support\Facades\Route;

$routeMainName = config('env-editor.route.name');

Route::prefix(config('env-editor.route.prefix'))
    ->middleware(config('env-editor.route.middleware'))
    ->group(function () use ($routeMainName) {
        Route::get('/', [EnvController::class, 'index'])->name($routeMainName.'.index');

        Route::post('key', [EnvController::class, 'addKey'])->name($routeMainName.'.key');
        Route::patch('key', [EnvController::class, 'editKey']);
        Route::delete('key', [EnvController::class, 'deleteKey']);

        Route::delete('clear-cache', [EnvController::class, 'clearConfigCache'])->name($routeMainName.'.clearConfigCache');

        Route::prefix('files')->group(function () use ($routeMainName) {
            Route::get('/', [EnvController::class, 'getBackupFiles'])
                ->name($routeMainName.'.getBackups');
            Route::post('create-backup', [EnvController::class, 'createBackup'])
                ->name($routeMainName.'.createBackup');
            Route::post('restore-backup/{filename?}', [EnvController::class, 'restoreBackup'])
                ->name($routeMainName.'.restoreBackup');
            Route::delete('destroy-backup/{filename?}', [EnvController::class, 'destroyBackup'])
                ->name($routeMainName.'.destroyBackup');

            Route::get('download/{filename?}', [EnvController::class, 'download'])
                ->name($routeMainName.'.download');
            Route::post('upload', [EnvController::class, 'upload'])
                ->name($routeMainName.'.upload');
        });
    });
