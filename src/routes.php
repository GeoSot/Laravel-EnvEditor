<?php

use Illuminate\Support\Facades\Route;

$package = 'env-editor';
$routeMainName = config($package.'.route.name');
$controllerName = 'GeoSot\EnvEditor\Controllers\\EnvController';

Route::prefix(config($package.'.route.prefix'))
    ->middleware(config($package.'.route.middleware'))
    ->group(function () use ($routeMainName, $controllerName) {
        Route::get('/', $controllerName.'@index')->name($routeMainName.'.index');

        Route::post('key', $controllerName.'@addKey')->name($routeMainName.'.key');
        Route::patch('key', $controllerName.'@editKey');
        Route::delete('key', $controllerName.'@deleteKey');

        Route::delete('clear-cache', $controllerName.'@clearConfigCache')->name($routeMainName.'.clearConfigCache');

        Route::prefix('files')->group(function () use ($routeMainName, $controllerName) {
            Route::get('/', $controllerName.'@getBackupFiles')
                ->name($routeMainName.'.getBackups');
            Route::post('create-backup', $controllerName.'@createBackup')
                ->name($routeMainName.'.createBackup');
            Route::post('restore-backup/{filename?}', $controllerName.'@restoreBackup')
                ->name($routeMainName.'.restoreBackup');
            Route::delete('destroy-backup/{filename?}', $controllerName.'@destroyBackup')
                ->name($routeMainName.'.destroyBackup');

            Route::get('download/{filename?}', $controllerName.'@download')
                ->name($routeMainName.'.download');
            Route::post('upload', $controllerName.'@upload')
                ->name($routeMainName.'.upload');
        });
    });
