<?php

namespace GeoSot\EnvEditor;

use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public const VENDOR = 'geo-sv';

    public const PACKAGE = 'env-editor';

    public const TRANSLATE_PREFIX = self::PACKAGE.'::env-editor.';

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadResources();
        $this->publishResources();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/env-editor.php',
            static::PACKAGE
        );

        $this->app->singleton(EnvEditor::class, function () {
            return new EnvEditor(
                new Repository(config(static::PACKAGE)),
                $this->app->make(Filesystem::class)
            );
        });

        $this->app->alias(EnvEditor::class, 'env-editor');
    }

    private function loadResources(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', static::PACKAGE);
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', static::PACKAGE);
    }

    private function publishResources(): void
    {
        $this->publishes([
            __DIR__.'/../config/env-editor.php' => config_path(static::PACKAGE.'.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('/views/vendor/'.static::PACKAGE),
        ], 'views');

        $this->publishes([
            __DIR__.'/../resources/lang/' => resource_path('lang/vendor/'.static::PACKAGE),
        ], 'translations');
    }
}
