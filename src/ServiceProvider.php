<?php

namespace GeoSot\EnvEditor;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Vendor name.
     *
     * @var string
     */
    protected $vendor = 'geo-sv';
    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'env-editor';

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
            __DIR__."/../config/{$this->package}.php",
            $this->package
        );

        $this->app->singleton(EnvEditor::class, function () {
            return new EnvEditor(config($this->package));
        });

        $this->app->alias(EnvEditor::class, 'env-editor');
    }

    private function loadResources()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', $this->package);
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', $this->package);
    }

    private function publishResources()
    {
        $this->publishes([
            __DIR__."/../config/{$this->package}.php" => config_path($this->package.'.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/resources/views/' => resource_path("views/vendor/{$this->vendor}/{$this->package}"),
        ], 'views');

        $this->publishes([
            __DIR__.'/resources/lang/' => resource_path("lang/vendor/{$this->vendor}"),
        ], 'translations');
    }
}
